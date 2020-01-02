<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPEventUpgrade extends PayPlans
{

	/**
	 * When the upgrade subscription is getting active,
	 * we need to
	 *  - mark existing subscription and order expired
	 *  - XITODO : mark existing order expired just before the new subscription is about to active(it not support upgrades for one out of multiple-subscription in a single order)
	 *  - XITODO : cancel recurring payments for previous subscription
	 *  - if normal payment then
	 *
	 * @param PayplansSubscription $previous
	 * @param PayplansSubscription $current
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onPayplansSubscriptionBeforeSave($previous=null, $current=null)
	{
		// Consider Previous State also
		if (isset($previous) && $previous->getStatus() == $current->getStatus()) {
			return true;
		}

		// if there is change in status of order
		if ($current->getStatus() != PP_SUBSCRIPTION_ACTIVE) {
			return true;
		}

		$order = $current->getOrder(PP_INSTANCE_REQUIRE);

		// is it upgrading from some plan ?
		$upgradingFrom = $order->getParam('upgrading_from',0);


		if (!$upgradingFrom) {
			// not upgrading. abort the process
			return true;
		}

		// user is upgrading, cancel his previous subscription and order
		$oldSub = PP::Subscription($upgradingFrom);

		$oldOrder = $oldSub->getOrder(true);
		$oldOrder->setStatus(PP_ORDER_EXPIRED)->save();

		$oldInvoices = $oldOrder->getInvoices(PP_INVOICE_PAID);
		$oldInvoice = array_shift($oldInvoices);

		if (!$oldInvoice->isRecurring()) {
			return true;
		}

		$oldPayment = $oldInvoice->getPayment();
		$supportPaymentCancel = 1;

		if (!empty($oldPayment)) {
			$paymentApp = $oldPayment->getApp(PP_INSTANCE_REQUIRE);
			$supportPaymentCancel = $paymentApp->isSupportPaymentCancellation($oldInvoice);
		}

		if ($supportPaymentCancel) {

			$result = $oldOrder->terminate();
			$message = JText::_('COM_PAYPLANS_UPGRADES_PAYMENT_CANCELLATION_IS_PROCESSESD');
			$content = array('user' => $oldInvoice->getBuyer()->getId(), 'invoice_key' => $oldInvoice->getKey() ,'result' => $result);
			PPLog::log(PPLogger::LEVEL_INFO, $message, $oldOrder, $content);

		} else {

			$message = JText::_('COM_PAYPLANS_UPGRADES_PAYMENT_CANCELLATION_CANNOT_PROCESSESD');
			$result = JText::_('COM_PAYPLANS_UPGRADES_PAYMENT_CANNOT_BE_CANCELLED');
			$content = array('user' => $oldInvoice->getBuyer()->getId(), 'invoice_key' => $oldInvoice->getKey(),'result' => $result);

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $oldOrder, $content);

			//send email to user for manual order cancellation

			$subject = JText::_('COM_PP_EMAIL_UPGRADES_ORDER_CANCELLATION_SUBJECT');
			$namespace = 'emails/upgrade/order.cancellation';

			// Send notification to the buyer
			$user = $oldInvoice->getBuyer();
			$data = array(
				'order_key' => $oldOrder->getKey(),
				'invoice_key' => $oldInvoice->getKey(),
				'subscription_key' => $oldSub->getKey(),
				'payment_key' => $oldPayment->getKey()
			);

			$mailer = PP::mailer();
			$mailer->send($user->getEmail(), $subject, $namespace, $data);
		}

		// We are marking ORDER expired,
		// so for recurring payments will not able to enable the order again

		return true;
	}


}
