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

class PayPlansViewUpgrades extends PayPlansAdminView
{


	/**
	 * Triggers via ajax calls to retrieve details about the plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUpgradeDetails()
	{
		if (!$this->my->id) {
			throw new Exception('You are not allowed to perform this operation');
		}

		$newPlanId = $this->input->get('upgrade_to', 0, 'int');
		$subscriptionId = $this->input->get('id', 0, 'int');

		$subscription = PP::subscription($subscriptionId);

		// Get available plans for the subscription
		$model = PP::model('Plan');

		if (!$model->canUpgradeTo($subscription, $newPlanId)) {
			throw new Exception('Current plan is not allowed to upgrade to the selected plan');
		}

		$old = new stdClass();
		$old->subscription = $subscription;
		$old->plan = $old->subscription->getPlan();
		$old->order = $old->subscription->getOrder();
		$old->invoices = $old->order->getInvoices(PP_INVOICE_PAID);

		if (count($old->invoices)) {
			$old->invoices = array_pop($old->invoices);
		} else {
			$old->invoices = $old->order->createInvoice();
		}

		$newPlan = PP::plan($newPlanId);
		$planPrice = $newPlan->getPrice();

		$result = PPUpgrade::calculateUnutilizedValue($old->subscription);

		$paidAmount = $result['paid'];
		$unutilized = $result['unutilized'];
		$unutilizedTax = $result['unutilizedTax'];

		$willTrialApply = PPUpgrade::willTrialApply($old->plan, $newPlan);

		if ($willTrialApply == PPUpgrade::APPLY_TRIAL_ALWAYS) {
			$expiration_type = $newPlan->getExpirationType();

			if ($expiration_type == 'recurring_trial_2' || $expiration_type == 'recurring_trial_1'){
				$planPrice 	= $newPlan->getPrice(PP_RECURRING_TRIAL_1);
			}
		}

		$themes = PP::themes();
		$response = new stdClass();

		$payableAmount = ($planPrice - $unutilized- $unutilizedTax);
		$response->payableAmount = ($payableAmount < 0) ? 0 : $payableAmount;
		$response->amount = $newPlan->getPrice();
		$response->currency = $newPlan->getCurrency();

		$response->price = $themes->html('html.amount', $newPlan->getPrice(), $response->currency);
		$response->unutilized = $themes->html('html.amount', $unutilized, $response->currency);
		$response->unutilizedTax = $themes->html('html.amount', $unutilizedTax, $response->currency);
		$response->payableAmount = $themes->html('html.amount', $payableAmount, $response->currency);

		return $this->resolve($response);
	}

	/**
	 * Renders the upgrade plan dialog from backend.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function upgrade()
	{
		$orderId = $this->input->get('orderId', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');

		if (!$userId) {
			return true;
		}

		$user = PP::user($userId);
		$order = PP::order($orderId);
		$subscription = $order->getSubscription();

		// Get available plans that can be upgraded to
		$model = PP::model('Plan');
		$planIds = $model->getAvailableUpgrades($subscription->getPlan()->getId());
		$plans = array();

		if ($planIds) {
			foreach ($planIds as $id) {
				$plan = PP::plan($id);
				$plans[] = $plan;
			}
		}

		$theme = PP::themes();
		$theme->set('subscription', $subscription);
		$theme->set('order', $order);
		$theme->set('plans', $plans);

		$output = $theme->output('admin/upgrades/dialogs/upgrade');

		return $this->resolve($output);
	}
}
