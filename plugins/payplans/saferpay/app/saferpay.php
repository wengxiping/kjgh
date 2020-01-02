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

class PPAppSaferPay extends PPAppPayment
{
	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		if (is_object($data)) {
			$data = (array)$data;
		}
			
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$postUrl = $helper->getPaymentUrl();
		$payload = $helper->getPaymentRequestPayload($invoice, $payment);
		
		$response = $helper->connect($postUrl, $payload);

		// Failure connecting to gateway
		if (!$response) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_SAFERPAY_PAYMENT_PROCESS'), $payment, $data, 'PayplansPaymentFormatter', '', true);

			PP::info()->set('COM_PAYPLANS_PAYMENT_APP_SAFERPAY_INVALID_RESPONSE', 'error');
			return false;
		}

		$redirectUrl = PP::normalize($response, 'RedirectUrl', '');

		if (!$redirectUrl) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_SAFERPAY_PAYMENT_PROCESS'), $payment, $response, 'PayplansPaymentFormatter', '', true);

			PP::info()->set('COM_PAYPLANS_PAYMENT_APP_SAFERPAY_NO_REDIRECT_URL', 'error');
			return false;
		}

		$_SESSION[$payment->getKey()] = PP::normalize($response, 'Token', '');

		$this->set('redirectUrl', $redirectUrl);
		return $this->display('form');
	}

	/**
	 * Triggered after the completion of payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}
		
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$paymentKey = PP::normalize($data, 'payment_key', '');

		// Get the token from the session
		$token = $_SESSION[$paymentKey];
		$payload = $helper->getResponsePayload($invoice, $token);

		$response = $helper->connect($helper->getResponseUrl(), $payload);

		$responseTransaction = PP::normalize($response, 'Transaction', array());
		$status = PP::normalize($responseTransaction, 'Status', '');

		// Not authorized
		if ($status != 'AUTHORIZED') {
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);				
		}

		$transactionId = PP::normalize($responseTransaction, 'Id', '');
		$payload = $helper->getPaymentCapturePayload($invoice, $transactionId);

		$paymentResponse = $helper->connect($helper->getPaymentCaptureUrl(), $payload);

		if (!$paymentResponse) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_SAFERPAY_PAYMENT_PROCESS');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		// Here we assume that the transaction was success
		$transactionId = PP::normalize($paymentResponse, 'TransactionId', 0);
		$subscriptionId = PP::normalize($paymentResponse, 'OrderId', 0);

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $paymentResponse);
		$status = PP::normalize($paymentResponse, 'Status', '');

		$transaction->message = JText::_('COM_PAYPLANS_PAYMENT_APP_SAFERPAY_TRANSACTION_FAILED');

		if ($status == 'CAPTURED') {
			$transaction->amount = $invoice->getTotal();
			$transaction->message = JText::_('COM_PAYPLANS_PAYMENT_APP_SAFERPAY_TRANSACTION_COMPLETED');
		}

		$state = $transaction->save();

		if (!$state) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD'), $payment, $data, 'PayplansPaymentFormatter', '', true);
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);	
	}
}
