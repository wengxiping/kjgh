<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPHelperanalytics
{
	public $params = null;

	public function __construct()
	{
		$this->params = $this->getPluginParams();
	}

	/**
	 * Retrieve plugin params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPluginParams()
	{
		$plugin = JPluginHelper::getPlugin('payplans', 'analytics');
		$params = new JRegistry($plugin->params);
		return $params;
	}

	/**
	 * Method to start the tracking process of the event
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackEvent($user_id, $event_name, $args, $is_created = 1)
	{
		if ($args['email'] == 'not@registered.com' || empty($user_id)) {
			return;
		}

		$intercom_is_enabled = $this->params->get('intercom_is_enabled','');
		$mixpanel_is_enabled = $this->params->get('mixpanel_is_enabled','');
		$woopra_is_enabled = $this->params->get('woopra_is_enabled','');

		$prefix = "Analytics";

		if ($intercom_is_enabled) {
			$analytics[] = 'intercom';
			define('ANALYTICS_INTERCOM_APPID', $this->params->get('intercom_app_id',''));
			define('ANALYTICS_INTERCOM_APPKEY', $this->params->get('intercom_app_key',''));
		}
		if ($mixpanel_is_enabled) {
			$analytics[] = 'mixpanel';
			define('ANALYTICS_MIXPANEL_TOKEN',$this->params->get('mixpanel_token'));
		}

		if ($woopra_is_enabled) {
			$analytics[] = 'woopra';
		}

		foreach ($analytics as $analytic) {
			$className = $prefix . $analytic;
			require_once(dirname(__DIR__) . '/plugins/' . strtolower($analytic) . '/' . strtolower($analytic) . '.php');

			$tool = new $className();

			try {
				$tool->trackEvent($user_id, $event_name, $args, $is_created);
			} catch (Exception $e) {
				$message = JText::_('COM_PAYPLANS_ANALYTICS_PLUGIN_NOT_WORKING');
				$content = array('Message' => $message);

				PP::logger()->log(PPLogger::LEVEL_ERROR, $message, null, $content);
			}
		}
	}

	/**
	 * Retrieve subscription details
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDetailSubscription(PPSubscription $sub)
	{
		$ret = array();
		$plan = $sub->getPlan();
		$ret['sub_plan_id'] = $plan->getId();
		$ret['sub_plan_name'] = $sub->getTitle();

		return $ret;
	}

	/**
	 * Find Details When an Invoice Gets Paid
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDetailInvoice(PPInvoice $invoice)
	{
		$ret = array();

		$order = $invoice->getReferenceObject(true);
		$plan = $order->getPlan();

		$ret['sub_plan_id'] = $plan->getId();
		$ret['sub_plan_name'] = $invoice->getTitle();
		$ret['sub_revenue'] = $invoice->getTotal();
		$ret['sub_discount'] = $invoice->getDiscount();
		$ret['sub_inv_status'] = $invoice->getStatusName();

		return $ret;
	}

	/**
	 * Retrieve user id from email or username
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserId($email_or_username)
	{
		$static = null;

		if (!isset($static[$email_or_username])) {
			$model = PP::model('User');

			$userId = $model->getUserIdFromUsername($email_or_username);

			if (!$userId) {
				$userId = $model->getUserIdFromEmail($email_or_username);
			}

			$static[$email_or_username] = $userId;
		}

		return $static[$email_or_username];
	}
}
