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

require_once(__DIR__ . '/formatter.php');

class PPAppEmail extends PPApp
{
	public function isApplicable($refObject = null, $eventName='')
	{
		// if not with reference to payment then return
		if ($eventName === 'onPayplansCron' || $eventName == 'getTemplatedata') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	public function onPayplansOrderAfterSave($prev, $new)
	{
		$this->triggerOnStatus($prev, $new);
	}

	public function onPayplansInvoiceAfterSave($prev, $new)
	{
		$this->triggerOnStatus($prev, $new);
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		$this->triggerOnStatus($prev, $new);
	}

	/**
	 * Common event for all rules that uses "on_status" for "when_to_send_email"
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function triggerOnStatus($prev, $new)
	{	
		if (!$this->helper->shouldSendEmail($prev, $new)) {
			return;
		}

		return $this->helper->send($new);
	}

	/**
	 * Triggered by cron event
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansCron()
	{
		$plans = $this->helper->getApplicablePlans();
		$applyAll = $this->helper->isApplicableToAllPlans();
		
		if (!$applyAll && !$plans) {
			return false;
		}

		$sentmail = 0;
		$subscriptions = array();

		//get the parameter when to email to check whether 
		//pre -expiry or post expiry email is to be send
		$whenToEmail = $this->helper->getWhenToSend();
		$expiry = $this->getAppParam($whenToEmail);

		// If the app is configured to be triggered when status changed, it should no longer be processed
		if ($whenToEmail == 'on_status') {
			return;
		}

		// On cart abandoned, we have nothing else to process
		if ($whenToEmail == 'on_cart_abondonment') {
			$this->cartAbandoned();
			return;
		}
		
		if ($whenToEmail == 'on_preexpiry') {
			$this->preExpiry($expiry);
			return;
		}

		$event = false;

		if ($whenToEmail == 'on_postactivation') {
			$event = 'postActivation';
			$subscriptions = $this->postActivation($expiry);
		}

		if ($whenToEmail == 'on_postexpiry') {
			$event = 'postExpiry';
			$subscriptions = $this->postExpiry($expiry);
		}
		
		if (!$subscriptions) {
			return;
		}
		
		// Only for post activation and post expiry
		foreach ($subscriptions as $id => $row) {
			$subscription = PP::subscription($row);

			$params = $subscription->getParams();
			$sent = $params->get($event . $expiry, false);

			if (!$sent) {
				$this->helper->send($subscription);

				// Mark emails as sent for the subscription
				$this->helper->markSent($subscription, $event, $expiry);
			}
		}
		
	}

	/**
	 * Notifies user when their subscription is going to be expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function preExpiry($expiry)
	{
		$model = PP::Model('Subscription');
		$subscriptions = $model->getPreExpirySubscriptions($this->helper->getApplicablePlans(), $expiry, $this->helper->isApplicableToAllPlans());

		//check for each subscription: if it is of recurring type and 
		//email is allowed to send only for last cycle of recurring then
		//unset the subscription which is not the last subscription of recurring cycle
		foreach ($subscriptions as $id => $row) {
			$subscription = PP::subscription($row);

			$order = $subscription->getOrder();
			$count = $subscription->getRecurrenceCount();

			if ($subscription->getExpirationType() == PP_RECURRING_TRIAL_1) {
				$count += 1;
			}

			if ($subscription->getExpirationType() == PP_RECURRING_TRIAL_2) {
				$count += 2;
			}

			$paidInvoices = $order->getInvoices(PP_INVOICE_PAID);
			$refundedInvoices = $order->getInvoices(PP_INVOICE_REFUNDED);

			$invoiceCount = count($paidInvoices) + count($refundedInvoices);

			// If it is a recurring type and we should send on tsshe last cycle, send the e-mails
			if ($subscription->isRecurring() && $this->helper->sendEmailForRecurringLastCycle()) {
				if ($invoiceCount != $count) {
					continue;
				}
			}

			// Ensure that we did not send it before
			$params = $subscription->getParams();
			$sent = $params->get('preExpiry' . $expiry . $invoiceCount, false);

			if (!$sent) {
				$this->helper->send($subscription);

				// Mark emails as sent for the subscription
				$this->helper->markSent($subscription, 'preExpiry', $expiry, $invoiceCount);
			}
		}
	}

	/**
	 * Sends e-mails to users who abandoned their cart
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function cartAbandoned()
	{
		$expiry = $this->getAppParam('on_cart_abondonment');
		$invoices = $this->helper->getAbandonedInvoices($expiry);

		if (!$invoices) {
			return false;
		}

		$event = 'oncartabondonment';

		foreach ($invoices as $id => $row) {
			$invoice = PP::invoice($row);

			 $subscription = $invoice->getSubscription();

			 // If subscription already active then do nothing
			 if ($subscription->getStatus() == PP_SUBSCRIPTION_ACTIVE) {
			 	return true;
			 }

			// Check if mail has already been sent
			$params = $invoice->getParams();
			$sent = $params->get('oncartabondonment' . $expiry, false);

			if (!$sent) {
				$this->helper->send($invoice);

				// Mark emails as sent for the subscription
				$this->helper->markSent($subscription, 'oncartabondonment', $expiry);
			}
		}

		return true;
	}

	/**
	 * Send e-mails for post activation 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function postActivation($expiry)
	{
		$model = PP::model('Subscription');

		$subscriptions = $model->getPostActivationSubscriptions($this->helper->getApplicablePlans(), $expiry, $this->helper->isApplicableToAllPlans());

		return $subscriptions;
	}

	/**
	 * Notifies user when their subscription has expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function postExpiry($expiry)
	{
		$model = PP::model('Subscription');
		$subscriptions = $model->getPostExpirySubscriptions($this->helper->getApplicablePlans(), $expiry, $this->helper->isApplicableToAllPlans());

		return $subscriptions;
	}
}