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

class PPAppEpay extends PPAppPayment
{
	/**
	 * Determines if ePay supports refunds
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function supportForRefund()
	{
		return true;
	}

	/**
	 * Recurring payment cancellation support
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSupportPaymentCancellation($invoice)
	{
		if ($invoice->isRecurring()) {
			return true;
		}
		return false;
	}

	/**
	 * Render Payment Page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();
		

		$postUrl = $helper->getPostUrl();
		$payload = $helper->getPaymentRequestPayload($payment);

		$this->set('postUrl', $postUrl);
		$this->set('payload', $payload);

		return $this->display('form');
	}

	/**
	 * Triggered after payment process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}

		//Get transaction data from payment gateway.
		$transactionId = PP::normalize($data, 'txnid', 0);
		$subscriptionId = PP::normalize($data, 'subscriptionid', 0);

		// If transaction id not coming in data 
		if (!isset($transactionId)) {
			$action	= 'error';

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_EPAY_ERROR'), $payment, $data, 'PayplansPaymentFormatter');
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();

		// If same notification came more than once, check if transaction already exists
		// if yes then do nothing and return
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0,0);

		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);
				$txnRefNum = $transaction->getParam('transactionid', '');
				if ($txnRefNum == $data['transactionid']) {
					return true;
				}
			}
		}

		// Create transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $data);

		$transaction->amount = $helper->formatAmount($data['amount'], true);
		$transaction->message = JText::_('COM_PAYPLANS_APP_EPAY_PAYMENT_COMPLETED_SUCCESSFULLY');
		$transaction->save();

		if ($invoice->isRecurring()) {

			$recurrenceCount = $helper->getRecurrenceCount($invoice);

			$params = new JRegistry();
			$params->set('subscriptionid', $subscriptionId);
			$params->set('pending_recur_count', $recurrenceCount);

			$payment->gateway_params = $params->toString();
			$payment->save();
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered on cron job for recurring payment request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();
		$invoiceCount = $invoiceCount + 1;

		if ($invoice->isRecurring()) {
			$recurrenceCount = $payment->getGatewayParam('pending_recur_count');

			if ($recurrenceCount > 0) {

				$helper->processRecurringPayment($payment, $invoiceCount);
			}
		}
	}

	/**
	 * Triggered when subscription cancelled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$invoice = $payment->getInvoice();
		$subscriptionId = $payment->getGatewayParam('subscriptionid');

		$helper = $this->getHelper();
		$result = $helper->processTerminatSubscription($payment);

		if ($result->deletesubscriptionResult != true ) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_LOGGER_ERROR_IN_RECURRING_CANCEL_PROCESS');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, (array) $result, 'PayplansPaymentFormatter','', true);

			return false;
		}

		// create transaction
		$transaction = PP::createTransaction($invoice, $payment, $subscriptionId, $subscriptionId, 0, $result);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_EPAY_CANCEL_SUCCESS';
		$transaction->save();

		$params = new JRegistry();
		$params->set('subscriptionid', $subscriptionId);
		$params->set('pending_recur_count', 0);

		$payment->gateway_params =  $params->toString();
		$payment->save();

		parent::onPayplansPaymentTerminate($payment, $controller);
		return true;
	}

	/**
	 * Triggered when refunding a transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refundRequest(PPTransaction $transaction, $refundAmount)
	{
		$helper = $this->getHelper();
		
		return $helper->processRefund($transaction, $refundAmount);
	}
}
