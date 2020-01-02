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

class PayPlansViewPlan extends PayPlansAdminView
{
	/**
	 * Allows others to browse for plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'word');

		$theme = PP::themes();
		$theme->set('callback', $callback);
		$output = $theme->output('admin/plan/dialogs/browse');

		return $this->resolve($output);
	}

	public function recurrencevalidation()
	{
		$planId =  $this->input->get('id', 0, 'int');
		$plan = PP::plan($planId);
		$expTime = $plan->getExpiration();

		// get empty instances all payment type apps
		// $apps = PayplansHelperApp::getPurposeApps('payment');

		// TODO:: Port getPurposeApps function
		$model = PP::model('Gateways');
		$apps = $model->getItemsWithoutState(array('published' => 1));

		$time = array();

		if ($apps) {

			foreach ($apps as $app) {
				$helper = $app->getHelper();

				if (method_exists($helper, 'getRecurrenceTime')) {

					$time[$app->getType()] = $helper->getRecurrenceTime($expTime);

					if ($time[$app->getType()] === false) {
						$time[$app->getType()] = array('period' => JText::_('COM_PAYPLANS_NA'),
														'unit' => JText::_('COM_PAYPLANS_NA'),
														'frequency' => JText::_('COM_PAYPLANS_NA'),
														'message' => JText::_('COM_PAYPLANS_NA')
													);
					}
				}
			}

		}

		$theme = PP::themes();
		$theme->set('time', $time);
		$html = $theme->output('admin/plan/recurrence.validation');

		$theme->set('content', $html);
		$output = $theme->output('admin/plan/dialog.recurrence.validation');

		return $this->resolve($output);
	}
}
