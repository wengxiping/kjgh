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

PP::import('admin:/includes/upgrade/upgrade');

class PayPlansControllerOrder extends PayPlansController
{
	/**
	 * Allows user to cancel their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancelSubscription()
	{
		PP::requireLogin();

		$orderId = $this->getKey('order_key');

		if (!$orderId) {
			die('Invalid order id');
		}

		$order = PP::order($orderId);
		$subscription = $order->getSubscription();

		// Ensure that the subscription really belongs to the current viewer
		if ($order->getBuyer()->getId() != $this->my->id) {
			die('You do not own this order');
		}

		// Ensure that it really can be cancelled
		if (!$subscription->canCancel()) {
			die('The current subscription does not allow you to cancel');
		}

		$invoice = $order->getInvoice();
		$payment = $invoice->getPayment();

		$output = $order->terminate();

		$message = 'COM_PP_SUBSCRIPTION_CANCELLED_SUCCESSFULLY';
		$state = 'success';

		// @TODO: Determine if the subscription cancellation has failed

		$this->info->set($message, $state);
		$this->redirectToView('dashboard');
	}


	/**
	 * Allow user to upgrade their plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processUpgrade()
	{
		PP::requireLogin();

		$orderId = $this->getKey('key');
		$newPlanId = $this->input->get('upgrade_to', 0, 'int');

		if (!$orderId || !$newPlanId) {
			// upgrade failed.
			$message = JText::_('Invalid Id');

			$this->info->set($message, 'error');
			return $this->redirectToView('dashboard');
		}

		$order = PP::order($orderId);

		// check if user is the buyer or not
		$buyer = $order->getBuyer();
		if ($buyer->id != $this->my->id && !$this->my->isSiteAdmin()) {
			return $this->ajax->reject('You are not allow to perform upgrade on this subscription');
		}

		$sub = $order->getSubscription();
		$newPlan = PP::plan($newPlanId);

		// process upgrade
		$newInvoice = PPUpgrade::upgradeSubscription($sub, $newPlan, 'subscription');

		if ($newInvoice === false) {
			// upgrade failed.
			$message = JText::_('Upgrade failed');

			$this->info->set($message, 'error');
			return $this->redirectToView('dashboard');
		}

		// trigger onPayplansUpgradeBeforeDisplay, e.g discount related apps
		$args = array($newPlanId, $sub, $newInvoice);
		$results = PPEvent::trigger('onPayplansUpgradeBeforeDisplay', $args, '', $sub);

		// redirect to checkout page
		return $this->redirectToView('checkout', '', 'invoice_key=' . $newInvoice->getKey() . '&tmpl=component');
	}

	/**
	 * Allows user to delete their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteSubscription()
	{
		PP::requireLogin();

		$orderId = $this->getKey('order_key');

		if (!$orderId) {
			die('Invalid order id');
		}

		$order = PP::order($orderId);
		$subscription = $order->getSubscription();

		// Ensure that the subscription really belongs to the current viewer
		if ($order->getBuyer()->getId() != $this->my->id) {
			die('You do not own this order');
		}

		// delete order and subscription
		$order->delete();
		$state = $subscription->delete();

		if ($state === false) {
			// upgrade failed.
			$message = JText::_('Deleteion failed');

			$this->info->set($message, 'error');
			return $this->redirectToView('dashboard');
		}

		$message = 'COM_PP_SUBSCRIPTION_DELETED_SUCCESSFULLY';
		$state = 'success';

		// @TODO: Determine if the subscription cancellation has failed

		$this->info->set($message, $state);
		$this->redirectToView('dashboard');
	}
}
