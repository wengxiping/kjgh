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

require_once(__DIR__ . '/lib/alipay_submit.class.php');
require_once(__DIR__ . '/lib/alipay_notify.class.php');

class PPHelperAlipay extends PPHelperPayment
{
	/**
	 * Determines if the trade was successful
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSuccess($status)
	{
		$success = array('TRADE_FINISHED', 'TRADE_SUCCESS');

		if (in_array($status, $success)) {
			return true;
		}

		return false;
	}

	/**
	 * Prepares the configuration data for alipay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAlipayConfig()
	{
		static $options = null;

		if (is_null($options)) {
			$options = array(
				'partner' => trim($this->params->get('partner', '')),
				'key' => trim($this->params->get('private_key')),
				'sign_type' => 'MD5',
				'input_charset' => 'utf-8',
				'cacert' => PP_CACERT,
				'transport' => 'https',
				'sandbox' => $this->params->get('sandbox')
			);
		}

		return $options;
	}

	/**
	 * Retrieve the current site url. 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUrl($link)
	{
		// // For development purpose only, uncomment and set the url
		// $url = 'http://0db76afb.ngrok.io/' . $link;
		// return $url;

		return rtrim(JURI::root(), '/') . '/' . $link;
	}

	/**
	 * Retrieves the url used for submission to Alipay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getGateway()
	{
		static $gateway = null;

		if (is_null($gateway)) {
			$gateway = 'https://mapi.alipay.com/gateway.do?';

			if ($this->params->get('sandbox')) {
				$gateway = 'https://openapi.alipaydev.com/gateway.do?';
			}
		}

		return $gateway;
	}

	/**
	 * Generates a new notify action with alipay's library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createNotifyAction()
	{
		$notify = new AlipayNotify($this->getAlipayConfig());
		$response = $notify->verifyNotify();

		return $response;
	}

	/**
	 * Generates a new submit action with alipay's library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createSubmitAction($options)
	{
		$submit = new AlipaySubmit($this->getAlipayConfig());

		$options['partner'] = $this->params->get('partner');
		$options['seller_email'] = $this->params->get('seller_email');

		$result = new stdClass();
		$result->html = $submit->getFormInputs($options);
		$result->gateway = $this->getGateway();

		return $result;
	}
}
