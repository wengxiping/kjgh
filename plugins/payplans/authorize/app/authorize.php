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

class PPAppAuthorize extends PPAppPayment
{
	/**
	 * Determines if the invoice supports payment cancellation.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSupportPaymentCancellation($invoice)
	{
		$supported = false;

		if ($invoice->isRecurring()) {
			$supported = true;
		}
		
		return $supported;
	}

	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || $task != 'notify') {
			return true;
		}

		$paymentKey = $this->input->get('invoice_num', false);
		$isRecurring = $paymentKey ? false : true;

		if ($paymentKey) {
			$prefix = JString::substr($paymentKey, 0,3);
			if ($prefix !== 'PK_') {
				//get payment key from order key in payplans 1.4
				$orderId = PP::getIdFromKey($paymentKey);
				$order = PP::order($orderId);
				$paymentId = $order->getParam('payment_id');
				$paymentKey = PP::getKeyFromId($paymentId);
			} else {
				$paymentKey = JString::substr($paymentKey, 3);
			}

			$this->input->set('payment_key', $paymentKey, 'POST');
			return true;
		}

		// Recurring payments
		$paymentKey = $this->input->get('x_invoice_num', null);

		if ($isRecurring && $paymentKey) {
			
			$prefix = JString::substr($paymentKey, 0,3);
			if ($prefix === 'PK_') {
				$paymentKey = JString::substr($paymentKey, 3);
			}

			$this->input->set('payment_key', $paymentKey, 'POST');
			return true;
		}
		
		return true;
	}
	
	/**
	 * Renders the payment input form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{		
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		$helper = $this->getHelper();
		$sandbox = $helper->isSandbox();

		$this->set('sandbox', $sandbox);
		$this->set('params', $this->getAppParams());
		$this->set('payment', $payment);
		$this->set('invoice', $invoice);
		$this->set('amount', $amount);
		$this->set('currency', $invoice->getCurrency());

		return $this->display('form');
	}

	/**
	 * Payment complete page
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
		$helper->loadLibrary();

		$errors = array();
		$invoice = $payment->getInvoice();
		$params = $this->getAppParams();

		if ($invoice->isRecurring()) {
			$this->processRecurringRequest($payment, $data, $invoice, $params);
		}

		if (!$invoice->isRecurring()) {
			$this->processNonRecurringRequest($payment, $data, $invoice, $params);
		}

		$payment->save();

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Payment notification which only happens for recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{		
		$invoice = $payment->getInvoice();
		
		// Ensure that this request has a subscription id
		if (!isset($data['x_subscription_id']) || !$data['x_subscription_id']) {
			return ' No Errors ';
		}

		// Check if transaction already exists,if yes then do nothing and return
		$transactionId = PP::normalize($data, 'x_trans_id', 0);
		$subscriptionId = PP::normalize($data, 'x_subscription_id', 0);
		$parentId = PP::normalize($data, 'parent_txn_id', 0);
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentId);
		
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);

				if ($transaction->getParam('x_response_code','') == $data['x_response_code']) {
					return true;
				}
			}
		}

		// Create a new transaction for the recurring billing
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentId, $data);

		$errors = PPHelperAuthorize::processIPN($transaction, $data, $payment);
		$transaction->save();

		if ($errors) {
			return implode("\n", $errors);
		}

		return ' No Errors ';
	}

	/**
	 * Processes recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringRequest(PPPayment $payment, $data, PPInvoice $invoice, $params)
	{
		$helper = $this->getHelper();
		
		// Get subscription details
		$transactionData = $helper->getTransactionData($payment, $invoice, $data);
		$subscription = $helper->getSubscriptionData($payment, $invoice, $transactionData);		
		
		$arb = new AuthorizeNetARB(AUTHORIZENET_API_LOGIN_ID, AUTHORIZENET_TRANSACTION_KEY);
		$arb->setSandbox($helper->isSandbox());
		$arb->setRefId($payment->getKey());


		// Create the subscription
		$response = $arb->createSubscription($subscription);

		if (!$response) {
			return array("Transaction Failed");
		}
		
		$result = array();
		$result['refId'] = $response->getRefID();
		$result['result_code'] = $response->getResultCode();
		$result['response_code'] = $response->getMessageCode();
		$result['text'] = $response->getMessageText();

		// Create transaction when recurring profile created
		$transaction = PP::createTransaction($invoice, $payment, 0, $response->getSubscriptionId());
		$transaction->amount = 0;

		// If it has errors, log it
		if ($result['result_code'] == 'Error') {
			$errors = array();
			$errors['response_reason_code'] = $result['result_code'];
			$errors['response_code'] = $result['response_code'];
			$errors['response_reason_text'] = $result['text'];

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_AUTHORIZE_PAYMENT_PROCESS'), $payment, $result, 'PayplansPaymentFormatter', '', true);
			
			PP::info()->set($errors['response_reason_text'] . ' (' . $errors['response_code'] . ')', 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey(), false);
			return PP::redirect($redirect);
		}	  		
		
		$transaction->message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_RECURRING_PROFILE_CREATED';
		$transaction->save();

		PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_RECURRING_PROFILE_CREATED'), $payment, array(JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_RECURRING_PROFILE_CREATED_INFO_MESSAGE')), 'PayplansPaymentFormatter');	
	}

	/**
	 * Processes non recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processNonRecurringRequest(PPPayment $payment, $data, PPInvoice $invoice, $params)
	{
		$helper = $this->getHelper();

		$transactionData = $helper->getTransactionData($payment, $invoice, $data);
		$sandbox = $helper->isSandbox();

		$aim = new AuthorizeNetAIM();
		$aim->setSandbox($sandbox);
		$aim->setFields($transactionData);

		$response = $aim->authorizeAndCapture();
		$response->testmode = $sandbox;

		// If same notification came more than one time
		// Check if transaction already exists,if yes then do nothing and return
		$transactions = $this->getExistingTransaction($invoice->getId(), $response->transaction_id, 0, 0);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);
				
				if ($transaction->getParam('response_code','') == $response->response_code) {
					return true;
				}
			}
		}

		$transaction = PP::createTransaction($invoice, $payment, $response->transaction_id, 0, 0, $response);
		$transaction->amount = 0;

		// Transaction wasn't approved, we need to log this down
		if (!$response->approved) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_AUTHORIZE_PAYMENT_PROCESS');

			$errors = array();
			$errors['response_reason_code'] = $response->response_reason_code;
			$errors['response_code'] = $response->response_code;
			$errors['response_reason_text'] = $response->response_reason_text;
			
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $errors, 'PayplansPaymentFormatter','', true);

			// Redirect user
			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);

			PP::info()->set($errors['response_reason_text'].' ('.$errors['response_code'].')', 'error');
			return PP::redirect($redirect);
		}

		$transaction->amount = $response->amount;
		$transaction->message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_COMPLETED';
		$transaction->save();

		return true;
	}

	/**
	 * Terminates an authorize.net payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$helper = $this->getHelper();
		// Load the authorize library
		$helper->loadLibrary();

		$arb = new AuthorizeNetARB(AUTHORIZENET_API_LOGIN_ID, AUTHORIZENET_TRANSACTION_KEY);
		$arb->setSandbox($helper->isSandbox());

		$transactions = $payment->getTransactions();

		foreach ($transactions as $transaction) {
			$subscriptionId = $transaction->get('gateway_subscr_id', 0);

			if (!empty($subscriptionId)) {
				break;
			}
		}
		
		$arb->setRefId($payment->getKey());
		$response = $arb->cancelSubscription($subscriptionId);
		
		if (!$response) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_LOGGER_ERROR_IN_RECURRING_RESPONSE');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array(), 'PayplansPaymentFormatter', '', true);
			$errors['response_reason_text'] = $message;
			
			return false;
			// $theme->set('errors', $errors);
			// return $theme->output('app:/authorize/cancel_error');
		}

		$data = array();
		$data['refId'] = $response->getRefID();
		$data['result_code'] = $response->getResultCode();
		$data['response_code'] = $response->getMessageCode();
		$data['text'] = $response->getMessageText();

		$invoice = $payment->getInvoice();

		$transaction = PP::createTransaction($invoice, $payment, PP::normalize($response, 'x_trans_id', 0), $subscriptionId, PP::normalize($response, 'parent_txn_id', 0));

		$errors = array();	
		
		if ($data['result_code'] == 'Error') {
			$errors['response_reason_code'] = $data['result_code'];
			$errors['response_code'] = $data['response_code'];
			$errors['response_reason_text'] = $data['text'];
		}	
				
		if ($errors) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_LOGGER_ERROR_IN_RECURRING_CANCEL_PROCESS');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $errors, 'PayplansPaymentFormatter','', true);

			return false;
		}
		
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_TRANSACTION_FOR_CANCEL_ORDER';
		$transaction->save();
			
		$message = JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_LOGGER_SUCCESS_IN_RECURRING_CANCEL_PROCESS');
		PPLog::log(PPLogger::LEVEL_INFO, $message, $payment, array($message), 'PayplansPaymentFormatter');

		return parent::onPayplansPaymentTerminate($payment, $controller);
	}

}