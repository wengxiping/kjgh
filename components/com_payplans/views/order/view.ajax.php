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

PP::import('site:/views/views');
PP::import('admin:/includes/upgrade/upgrade');


class PayPlansViewOrder extends PayPlansSiteView
{
	/**
	 * Confirm subscription cancellation dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmCancellation()
	{
		$key = $this->input->get('order_key', '', 'default');

		$theme = PP::themes();
		$theme->set('key', $key);
		$output = $theme->output('site/order/dialogs/confirm.cancel');

		return $this->ajax->resolve($output);
	}

	/**
	 * Confirm subscription cancellation dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmUpgrade()
	{
		PP::requireLogin();

		$key = $this->input->get('key', '', 'default');
		$orderId = PP::getIdFromInput('key');

		if (! $orderId) {
			return $this->ajax->reject('Invalid Order ID');
		}

		$order = PP::order($orderId);

		// check if user is the buyer or not
		$buyer = $order->getBuyer();
		if ($buyer->id != $this->my->id && !$this->my->isSiteAdmin()) {
			return $this->ajax->reject('You are not allow to perform upgrade on this subscription');
		}

		$sub = $order->getSubscription();
		$plans = PPUpgrade::findAvailableUpgrades($sub);

		$currentPlan = $sub->getPlan();

		$theme = PP::themes();
		$theme->set('key', $key);
		$theme->set('upgrade_to', $plans);
		$theme->set('plan', $currentPlan);
		$output = $theme->output('site/order/dialogs/confirm.upgrade');

		return $this->ajax->resolve($output);
	}


	//this function would set the upgrade details witout createing new order
	public function showUpgradeDetails()
	{
		PP::requireLogin();

		$key = $this->input->get('key', '', 'default');
		$planId = $this->input->get('id', 0, 'int');

		$orderId = PP::getIdFromInput('key');
		$curOrder = PP::order($orderId);

		// check if user is the buyer or not
		$buyer = $curOrder->getBuyer();
		if ($buyer->id != $this->my->id && !$this->my->isSiteAdmin()) {
			return $this->ajax->reject('You are not allow to perform upgrade on this subscription');
		}

		$curSub = $curOrder->getSubscription();
		$availablePlans = PPUpgrade::findAvailableUpgrades($curSub);

		if (!$planId || !isset($availablePlans[$planId])) {
			return $this->ajax->reject('Invalid plan');
		}

		$curPlan = $curSub->getPlan();
		$curOrderInvoices = $curOrder->getInvoices(PP_INVOICE_PAID);
		$newPlan = PP::Plan($planId);

		$curInvoice = null;

		if ($curOrderInvoices) {
			$curInvoice = array_pop($curOrderInvoices);
		} else {
			// now invoice found. lets create new one.
			$curInvoice = $curOrder->createInvoice();
		}

		$result = PPUpgrade::calculateUnutilizedValue($curSub);

		$paidAmount = $result['paid'];
		$unutilized = $result['unutilized'];
		$unutilizedTax = $result['unutilizedTax'];
		$planPrice = $newPlan->getPrice();

		$willTrialApply = PPUpgrade::willTrialApply($curPlan, $newPlan);
		if ($willTrialApply == PPUpgrade::APPLY_TRIAL_ALWAYS) {
			$expiration_type = $newPlan->getExpirationType();

			if ($expiration_type == 'recurring_trial_2' || $expiration_type == 'recurring_trial_1'){
				$planPrice 	= $newPlan->getPrice(PP_RECURRING_TRIAL_1);
			}
		}

		$themes = PP::themes();
		$response = new stdClass();

		$payableAmount = ($planPrice - $unutilized - $unutilizedTax);
		$response->payableAmount = ($payableAmount < 0) ? 0 : $payableAmount;
		$response->amount = $newPlan->getPrice();
		$response->currency = $newPlan->getCurrency();

		$response->price = $themes->html('html.amount', $newPlan->getPrice(), $response->currency);
		$response->unutilized = $themes->html('html.amount', $unutilized, $response->currency);
		$response->unutilizedTax = $themes->html('html.amount', $unutilizedTax, $response->currency);
		$response->payableAmount = $themes->html('html.amount', $payableAmount, $response->currency);

		return $this->ajax->resolve($response);

	}

	/**
	 * Confirm subscription Deleteion dialog
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmDeleteion()
	{
		$key = $this->input->get('order_key', '', 'default');

		$theme = PP::themes();
		$theme->set('key', $key);
		$output = $theme->output('site/order/dialogs/confirm.delete');

		return $this->ajax->resolve($output);
	}

}
