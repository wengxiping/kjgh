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

class PPAppOgone extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}
	
	/**
	 * When controller called
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || ($task != 'notify')) {
			return true;
		}
		
		$paymentKey = $this->input->get('orderID', null);
		
		if (!empty($paymentKey)) {
			$this->input->set('payment_key', $paymentKey, 'POST');
			return true;
		}
		
		return true;
	}
	
	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$payload = $helper->getPaymentRequestPayload($payment);	
		$formUrl = $helper->getFormUrl();
		$currency = $invoice->getCurrency('isocode');

		$this->set('currency', $currency);
		$this->set('formUrl', $formUrl);
		$this->set('payload', $payload);
		$this->set('invoice', $invoice);
		
		return $this->display('form');
	}
	
	/**
	 * Triggered when notification come from payment gateway
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();

		$transactionId = PP::normalize($data, 'PAYID', 0);
		$subscriptionId = 0;
		$parentId = 0;

		// If already have transaction for same PAYID then don't create another transaction 
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentId);
		
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction);

				$payId = $transaction->getParam('PAYID');
				if ($transaction->getAmount() != 0 &&  $payId == $transactionId) {
					return true;
				}
			}
		}
		
		$helper = $this->getHelper();
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentId, $data);
		$source = $helper->removeSHAOutItems($data);
		ksort($source);

		$newSHA = $helper->generateSHAOut($source);
		
		// Create log if SHA doesn't match
		$signature = PP::normalize($data, 'SHASIGN', '');

		if ($newSHA != $signature) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_OGONE_SHA_DOES_NOT_MATCH');
			$error = array(
				'Expected' => $signature,
				'Actual' => $newSHA,
				'response' => $data
			);

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $error,'PayplansPaymentFormatter', '', true);
			
			$transaction->save();
				
			PP::info()->set('COM_PAYPLANS_PAYMENT_APP_OGONE_SHA_DOES_NOT_MATCH', 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=dashboard', false);
			return PP::redirect($redirect);
		}
			
		$status = PP::normalize($data, 'STATUS', '');
		$method = $status ? 'process_status_' . strtoupper($status) : false;

		if (method_exists($helper, $method)) {
			$result = $helper->$method($data, $payment, $transaction, $invoice);
		}

		//if any issue occurs in recurring then create error log
		if (!isset($result)) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::sprintf('COM_PAYPLANS_PAYMENT_APP_OGONE_OTHER_STATUS', $data['STATUS']), $payment, $data, 'PayplansPaymentFormatter', '', true);
			return true;
		}

		// save transaction
		$transaction->save();
			
		//if recurring then set a parameter 
		$recurring = $invoice->isRecurring();
		$gatewayParams = $payment->getGatewayParams();
		$pending_recur_count = $gatewayParams->get('pending_recur_count');

		if ($recurring) {
			if (!isset($pending_recur_count)) {
				$recurrenceCount = $helper->getRecurrenceCount($invoice);
			} else {
				$recurrenceCount = $pending_recur_count-1;
			}	

			$gatewayParams->set('pending_recur_count', $recurrenceCount);
			$payment->gateway_params = $gatewayParams->toString();
			$payment->save();
		}
		
		//in case of refund, don't redirect
		if ($data['STATUS'] == '7') {
			return true;
		}
		
		//redirect to invoice-thank you page after first payment request
		if (!isset($pending_recur_count)) {
			$redirect = PPR::_('index.php?option=com_payplans&view=invoice&task=thanks&invoice_key=' . $invoice->getKey() . '&tmpl=component', false);
			return PP::redirect($redirect);
		}
	}
	
	/**
	 * Terminate Recurring Subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		// Since ogone does not support cancellations, we need to send e-mails to admin
		$user = $payment->getBuyer();
		$invoice = $payment->getInvoice();
		$order = $invoice->getReferenceObject();
		$subscription = $order->getSubscription();

		$params = array(
			'userId' => $user->getId(),
			'name' => $user->getName(),
			'key' => $subscription->getKey()
		);

		$subject = JText::sprintf("COM_PAYPLANS_PAYMENT_APP_OGONE_RECURRING_CANCEL_REQUEST_FOR_SUBSCRIPTION_ID", $params['key']);
		

		$mailer = PP::mailer();
		$emails = $mailer->getAdminEmails();
		return $mailer->send($emails, $subject, 'plugins/payplans/ogone/emails/terminate', $params);
	}	
}