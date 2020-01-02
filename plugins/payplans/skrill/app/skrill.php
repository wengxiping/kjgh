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

class PPAppSkrill extends PPAppPayment
{
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || $task != 'notify') {
			return true;
		}

		$key = JRequest::getVar('transaction_id', null);
		
		if (!empty($key)) {
			JRequest::setVar('payment_key', $key, 'POST');
			return true;
		}
		
		return true;
	}

	/**
	 * Renders the payment form
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		
		$languageCode = $helper->getLanguageCode();
		$amount = $helper->getAmount($invoice->getTotal());
		$callbackUrls = $helper->getCallbackUrls($payment);
		$merchant = $this->getAppParam('merchantemail');
		
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('amount', $amount);

		$this->set('merchant', $merchant);
		$this->set('languageCode', $languageCode);
		
		$this->set('callbackUrls', $callbackUrls);

		if ($invoice->isRecurring()) {
			
			$expiration = $invoice->getExpiration(PP_RECURRING);
			$time = $helper->getRecurrenceTime($expiration);
			$endDate = $helper->calculateEndDate($invoice, $expiration);

			$this->set('recurringEndDate', $endDate);
			$this->set('recurringCycle', $time['unit']);
			$this->set('recurringPeriod', $time['period']);
		}


		return $this->display('form');
	}

	/**
	 * Log after a payment is received
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * This method is triggered when PayPal connects to our payment notification page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();

		// If same notification came more than one time
		// Check if transaction already exist then do nothing and return
		$transactionId = PP::normalize($data, 'mb_transaction_id', 0);
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);

		if ($transactions !== false) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);

				if ($transaction->getParam('status','') == $data['status']) {
					return true;
				}
			}
		}

		// Store the transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $data);

		$helper = $this->getHelper();

		// Determines if we should process payment notifications
		$status = PP::normalize($data, 'status', '');

		$hasErrors = false;

		// Failed transaction
		if ($helper->isTransactionFailed($status)) {
			$reason = PP::normalize($data, 'failed_reason_code', '');

			if ($reason) {
				$error = JText::_('COM_PP_SKRILL_FAILED_REASON_CODE' . $reason);
			}

			$response = array();
			$response['error_message'] = JText::_('COM_PAYPLANS_APP_MONEYBOOKERS_PAYMENT_FAIL');
			$response['data'] = $data;

			PP::logger()->log(PPLogger::LEVEL_ERROR, 'COM_PAYPLANS_APP_MONEYBOOKERS_LOGGER_ERROR_IN_PAYMENT', $payment, $response, 'PayplansPaymentFormatter', '', true);

			$transaction->amount = 0;
			return $transaction->save();
		}

		// Payment transaction
		if ($helper->isPaymentNotification($status)) {
			$merchantEmail = $this->getAppParam('merchantemail');

			// Ensure that the recipient e-mail is correct
			if ($merchantEmail !== JString::strtolower($data['pay_to_email'])) {
				$errors[] = JText::_($data['merchantemail']);
			}
		
			$amount = PP::normalize($data, 'amount', 0);

			$transaction->amount = $amount;
			
			$verified = $helper->verifySignature($data);

			if (!$verified) {

				$response = array();
				$response['error_message'] = JText::_('COM_PAYPLANS_APP_MONEYBOOKERS_PAYMENT_FAIL');
				$response['data'] = $data;

				PP::logger()->log(PPLogger::LEVEL_ERROR, 'COM_PAYPLANS_APP_MONEYBOOKERS_LOGGER_ERROR_IN_PAYMENT', $payment, $response, 'PayplansPaymentFormatter', '', true);

				$transaction->amount = 0;

				return $transaction->save();
			}
		}

		$message = $helper->getTransactionMessage($status);
		$transaction->message = $message;
		
		$transaction->save();

		return true;
	}
}