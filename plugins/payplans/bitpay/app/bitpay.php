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

class PPAppBitpay extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$recurring = $invoice->isRecurring();

		if ($recurring) {
			PP::info()->set('COM_PP_SELECTED_PAYMENT_METHOD_DOES_NOT_SUPPORT_RECURRING', 'error');
		}

		$user = $invoice->getBuyer();
		$merchantId = $helper->getMerchantId();

		$postData = array(
			'invoice_key' => $invoice->getKey(), 
			'hash' => crypt($invoice->getKey(), $merchantId)
		);

		$rootUrl = $helper->getNotifyUrl();

		$options = array(
			'price' => $invoice->getTotal(),
			'currency' => $invoice->getCurrency('isocode'),
			'notificationURL' => $rootUrl . 'index.php?option=com_payplans&view=payment&task=notify&gateway=bitpay&payment_key=' . $payment->getKey(),
			'redirectURL' => $rootUrl . 'index.php?option=com_payplans&gateway=bitpay&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
			'transactionSpeed' => 'high',
			'fullNotifications' => true,
			'notificationEmail' => $user->getEmail(),
			'orderID' => $payment->getKey(),
			'posData' => json_encode($postData),
			'physical' => false
		);

		$post = json_encode($options);

		$response = $helper->createInvoice($post, $payment, $invoice);

		if ($response === false || !isset($response->url) || !$response->url) {
			PP::info()->set('Invalid Response', 'error');
			return false;
		}

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();

		$transactionParams = new JRegistry($response);
		$transaction->params = $transactionParams->toString();
		$transaction->amount = 0;
		$transaction->message = 'Redirection successful';
		$transaction->save();

		// Redirect to bitpay's url
		PP::redirect($response->url);
	}
	
	/**
	 * Triggered when notification came from bitpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		
		// get the transaction instace of lib
		$dataInput = file_get_contents("php://input");
		$result = $helper->validate($dataInput);
		
		if (!$result) {
			$errors = array(JText::_('COM_PP_BITPAY_INVALID_DATA'));
			return $errors;
		}
		
		$response = $helper->confirm($result);

		// Ensure that there aren't any duplicates
		$transactionId = isset($response->id) ? $response->id : 0;
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);

		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);

				if (strtolower($transaction->getParam('status','')) == strtolower($response->status)) {
						return true;
				}
			}
		}

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_txn_id = isset($response->id) ? $response->id : 0;
		$transaction->gateway_parent_txn = 0;

		$transactionParams = new JRegistry($response);
		$transaction->params = $transactionParams->toString();

		// Ensure that it hasn't expired yet
		if ($response->currentTime > $response->expirationTime) {
			$message = JText::_('COM_PAYPLANS_APP_BITPAY_EXPIRATION_TIME_EXCEED');

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

			$transaction->message = $message;
			$transaction->save();

			return array($message);
		}

		$method = '';
		$message = '';

		if (isset($response->status)) {
			$method = 'process' . ucfirst($response->status);
		}

		$exists = method_exists($helper, $method);

		if (!$method || !$exists) {
			$message = 'COM_PAYPLANS_APP_BITPAY_INVALID_MESSAGE_TYPE';
		}

		if ($exists) {
			$helper->$method($payment, $response, $transaction);
		}

		$transaction->save();

		if ($message) {
			return $message;
		}

		return ' No Errors ';
	}
}
