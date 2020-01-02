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

require_once(__DIR__ . '/helper.php');

class PPAppebanx extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Determines if refunds are supported
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function supportForRefund()
	{
		return true;
	}

	/**
	 * when controller called
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view == 'payment' && $task == 'notify') {
			$hash = $this->input->get('hash_codes');
			$payload = array('hash' => $hash);

			$helper = $this->getHelper();
			$queryUrl = $helper->getQueryUrl();
			$response = $helper->connect($queryUrl, $payload);

			if ($response->status == 'SUCCESS') {
				$paymentKey = $response->payment->merchant_payment_code;
				if (!empty($paymentKey)) {
					$this->input->set('payment_key', $paymentKey);
					return true;
				}
			}
			return true;
		}

		if ($view == 'payment' && $task == 'complete') {
			$paymentKey = $this->input->get('merchant_payment_code');

			if (!empty($paymentKey)) {
				$this->input->set('payment_key', $paymentKey);
				return true;
			}

			return true;
		} 
			
		return true;
	}

	/**
	 * Render Payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$user = $payment->getBuyer(true);
		$params = $this->getAppParams();

		$payload = array(
			'payment_type_code' => '_all',
			'instalments' => '1-12',
			'currency_code' => $invoice->getCurrency('isocode'),
			'amount' => $invoice->getTotal(),
			'merchant_payment_code' => $payment->getKey(),
			'name' => $user->getName(),
			'email' => $user->getEmail()
		);

		$requestUrl = $helper->getRequestUrl();
		$response = $helper->connect($requestUrl, $payload);

		// Response failed
		if ($response->status != 'SUCCESS') {

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('Error connecting to Ebanx payment gateway'), $payment, array($response), 'PayplansPaymentFormatter', '', true);

			PP::info()->set($response->status_message, 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=plan');

			return PP::redirect($redirect);
		}

		$postUrl = $response->redirect_url;

		$this->set('postUrl', $postUrl);

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

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered when notification came from Ebanx
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$hash = $this->input->get('hash_codes');

		$helper = $this->getHelper();
		$queryUrl = $helper->getQueryUrl();

		$payload = array('hash' => $hash);

		$response = $helper->connect($queryUrl, $payload);
		$response = (array) $response;
		$valid = $helper->validate($response, $payment);

		if (!$valid) {
			return false;
		}

		// Here we assume the validation was successful
		$invoice = $payment->getInvoice();

		// Check for duplicate transactions
		$transactionId = PP::normalize($response['payment'], 'hash', 0);
		$subscriptionId = PP::normalize($response['payment'], 'subscriptionid', 0);
		$parentId = PP::normalize($response['payment'], 'subscriptionid', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentId);

		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				
				$transaction = PP::transaction($transaction->transaction_id);
				
				if ($transaction->getParam('status', '') == $response['payment']->status) {
					return true;
				}
			}
		}

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_txn_id = $transactionId;
		$transaction->gateway_subscr_id = $subscriptionId;
		$transaction->gateway_parent_txn = $parentId;

		$params = new JRegistry($response);
		$transaction->params = $params->toString();
		$transaction->amount = PP::normalize($response['payment'], 'amount_ext', 0);
		$transaction->message = 'COM_PAYPLANS_APP_EBANX_TRANSACTION_COMPLETED';

		return $transaction->save();
	}

	/**
	 * Triggered when Refund request initiated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refundRequest(PPTransaction $transaction, $refundAmount)
	{
		$helper = $this->getHelper();

		$payload = array(
			'operation' => 'request',
			'hash' => $transaction->getGatewayTxnId(),
			'amount' => $refundAmount,
			'description' => JText::_('Refund Requested')
		);

		$refundUrl = $helper->getRefundUrl();
		$response = $helper->connect($refundUrl, $payload);

		// Refund request failed
		if ($response->status != 'CO' || $response->status != 'RE') {
			$message = JText::_('COM_PAYPLANS_LOGGER_EBANX_ERROR_OCCURED_IN_PAYMENT_REFUND');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, (array) $response, 'PayplansPaymentFormatter', '', true);
			
			return false;
		}

		$invoice = $transaction->getInvoice();
		$response = (array) $result->payment;
		$details = (array) $response['refunds']['0'];
	
		// Ensure that there are no duplicates
		$transactionId = PP::normalize($details, 'id', 0);
		$subscriptionId = isset($response['payer']['id']) ? $response['payer']['id'] : 0;
		$parentId = PP::normalize($response, 'parent_txn_id', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentId);

		if (!empty($transactions)) {
		
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);

				if ($transaction->getParam('status', '') == $response['status']) {
					return true;
				}
			}
		}
		
		// get the transaction instace of lib
		$paymentId = PayplansHelperUtils::getIdFromKey($response['merchant_payment_code']);

		$newTransaction = PP::transaction();
		$newTransaction->user_id = $transaction->getBuyer();
		$newTransaction->invoice_id = $invoice->getId();
		$newTransaction->payment_id = $paymentId;
		$newTransaction->gateway_txn_id = $transactionId;
		$newTransaction->gateway_subscr_id = $subscriptionId;
		$newTransaction->gateway_parent_txn = $parentId;

		$params = new JRegistry($response);
		$newTransaction->params = $params->toString();

		$newTransaction->amount = -$details['amount_ext'];
		$newTransaction->message = 'COM_PAYPLANS_APP_EBANX_TRANSACTION__REFUND_COMPLETED';
		$newTransaction->save();

		return true;
	}
}