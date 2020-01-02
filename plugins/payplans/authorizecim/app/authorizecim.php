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

class PPAppAuthorizecim extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		// return true for event onPayplansSubscriptionBeforeSave
		if ($eventName == 'onPayplansSubscriptionBeforeSave') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

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
	 * Renders the payment input form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{		
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		$params = $this->getAppParams();

		$this->set('params', $params);
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

		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();

		$data['card_num'] = PP::normalizeCardNumber($data['card_num']);
		//
		// Step 1 :- Create Customer Profile and Customer Payment Profile
		//
		$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);
		$response = $helper->createCustomer($payment, $invoice, $data);

		if ($response->resultCode == 'Error' || $response->resultCode == 'ConnectionFailed') {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_ERROR_IN_CUSTOMER_PROFILE_CREATION'), $payment, (array) $response, 'PayplansPaymentFormatter', '', true);
			
			PP::info()->set($response->text . ' (' . $response->code . ')', 'error');
			return PP::redirect($redirect);
		}

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_subscr_id = isset($response->profileId) ? $response->profileId : 0;
		$transaction->message = 'COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_CUSTOMER_PROFILE_CREATED';

		$transactionParams = new JRegistry($response);
		$transaction->params = $transactionParams->toString();
		$transaction->save();

		PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_CUSTOMER_PROFILE_CREATED'), $payment, (array) $response, 'PayplansPaymentFormatter');

		// If it fails, we need to redirect accordingly.
		if ($response->resultCode != 'Ok' && $response->code != 'I00001') {
			PP::info()->set($response->text . ' (' . $response->code . ')', 'error');
			return PP::redirect($redirect);
		}

		// Case:- Customer profile created successfully now do your process
		$invoiceAmount = $invoice->getTotal();

		// Case of Free Trial 
		// Do not create Transaction Profile of user (Just create transaction  and mark invoice as paid)
		if ($invoiceAmount == '0.00' && $invoice->isRecurring()) {
			$recurrenceCount = $helper->getRecurrenceCount($invoice);

			$helper->processRecurringPayments($payment, $invoice, $invoiceAmount, $recurrence_count, $response->profileId,  $response->paymentProfileId);

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		//
		// Step 2 :- After successful profile creation create Transaction Profile
		//
		$transactionResponse = $helper->createTransaction($invoice, $payment, $invoiceAmount, $response->profileId, $response->paymentProfileId);
		if ($transactionResponse->resultCode == 'Error') {
			$transactionResponse = (array) $transactionResponse;

			// Do not store credit card details
			$unusedKeys = array('5', '6','10', '11'. '12', '50', '51');

			foreach ($unusedKeys as $key) {
				if (isset($transactionResponse[$key])) {
					unset($transactionResponse[$key]);
				}
			}

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_ERROR_IN_TRANSACTION_PROFILE_CREATION'), $payment, $transactionResponse, 'PayplansPaymentFormatter', '', true);
			
			$payment->save();

			// Redirect when there is an error
			PP::info()->set($transactionResponse['code'] . $transactionResponse['text'], 'error');
			return PP::redirect($redirect);
		}

		// Case:- Connection Failed then show proper error message
		if ($transactionResponse->resultCode == 'ConnectionFailed') {
			PP::info()->set($transactionResponse->code . $transactionResponse->text, 'error');

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_CURL_CONNECTION_ERROR'), $payment, (array) $transactionResponse, 'PayplansPaymentFormatter', '', true);
			return PP::redirect($redirect);
		}
		
		// Case:- When no connection error and no error in transaction then complete transaction
		$params = $helper->getTransactionParams($transactionResponse);

		// Activate the subscription only when reposnse is ok. Else just create a 0 amount of transaction.
		$amount = 0;

		if ($transactionResponse->resultCode == 'Ok' && $transactionResponse->code == 'I00001') {
			$amount = number_format($invoice->getTotal(), 2);
		}

		// Check for possible duplicate transactions
		$transactionId = $params->get('param6', 0);
		$subscriptionId = PP::normalize($response, 'profileId', 0);
		$parentTransaction = PP::normalize($response, 'paymentProfileId', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentTransaction);
		if ($transactions) {
			return true;
		}

		// If it is recurring subscriptions, we need to handle it differently
		if ($invoice->isRecurring()) {
			$recurrenceCount = $helper->getRecurrenceCount($invoice);
			$helper->processRecurringPayments($payment, $invoice, $amount, $recurrenceCount, $response->profileId, $response->paymentProfileId, $params);

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		// Otherwise we assume that this is just a standard plan
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->amount = $amount;
		$transaction->gateway_txn_id = $params->get('param6', 0);
		$transaction->gateway_subscr_id = PP::normalize($response, 'profileId', 0);
		$transaction->gateway_parent_txn = PP::normalize($response, 'paymentProfileId', 0);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_CIM_PAYMENT_COMPLETED_SUCCESSFULLY';

		$transaction->params = $params->toString();
		$transaction->save();

		$payment->save();			
				
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered when user cancels their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$gatewayParams = $payment->getGatewayParams();
		$profileId = $gatewayParams->get('profile_id', 0);

		$response = $helper->getCustomer($profileId, $payment);

		// If it has errors, we need to log it
		if ($response->resultCode == 'Error' || $response->resultCode == 'ConnectionFailed') {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_ERROR_IN_CUSTOMER_PROFILE_DETECTION'), $payment, (array) $response, 'PayplansPaymentFormatter', '', true);
			
			return false;
		}
		
		$deleteResponse = $helper->deleteCustomer($payment, $profileId, $response->paymentProfileId);

		if ($deleteResponse->resultCode == 'Error') {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_ERROR_IN_PAYMENT_PROFILE_DELETION'), $payment, (array) $deleteResponse, 'PayplansPaymentFormatter', '', true);

			return false;
		}
		
					
		$message = JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_CUSTOMER_PAYMENT_PROFILE_DELETED');
		PPLog::log(PPLogger::LEVEL_INFO, $message, $payment, (array) $response, 'PayplansPaymentFormatter');

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_txn_id = $profileId;


		$params = new JRegistry();
		$params->set('profile_id', $profileId);
		$params->set('pending_recur_count', 0);

		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_CIM_CANCEL_SUCCESS';
		$transaction->save();

		$payment->gateway_params = $params->toString();
		$payment->save();

		parent::onPayplansPaymentTerminate($payment, $controller);
		return true;
	}

	/**
	 * Initiated during cron to process recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();

		if (!$invoice->isRecurring()) {
			return;
		}

		$helper = $this->getHelper();
		$count = $invoiceCount + 1;
		
		$gatewayParams = $payment->getGatewayParams();
		$recurrenceCount = $gatewayParams->get('pending_recur_count');
		$connectionFailure = $gatewayParams->get('connection_faliure_attempt', 0);
		$authFailure = $gatewayParams->get('auth_faliure_attempt', 0);

		$params = $this->getAppParams();
		$maxFailure = $params->get('faliure_attempt', 5);
		$maxAuthFailure = $params->get('auth_faliure_attempt', 1);

		if ($recurrenceCount > 0 && $connectionFailure < $maxFailure && $authFailure < $maxAuthFailure) {
			$profileId = $gatewayParams->get('profile_id');
			$customerProfile = $helper->getCustomer($profileId, $payment);

			$amount = $invoice->getTotal($count);

			$connectionFailure++;
			$authFailure++;

			$transaction = $helper->createTransaction($invoice, $payment, $amount, $profileId, $customerProfile->paymentProfileId, $connectionFailure, $authFailure);

			if ($transaction->resultCode == 'ConnectionFailed') {
				PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_CURL_CONNECTION_ERROR'), $payment, (array) $transaction, 'PayplansPaymentFormatter', '', true);

				$payment->save();
			}

			$transactionParams = $helper->getTransactionParams($transaction);

			if ($transaction->resultCode && $transaction->resultCode != 'Ok') {
				$message = 'COM_PAYPLANS_LOGGER_AUTHORIZE_CIM_TRANSACTION_FAILED_ERROR';
				return PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, (array) $transaction, 'PayplansPaymentFormatter', '', false);
			}

			// set the amount if result code is OK else jsut create the transaction.
			$amount = 0;
			if ($transaction->resultCode == 'Ok') {
				$amount = number_format($invoice->getTotal($count), 2);
			}
				
			$helper->processRecurringPayments($payment, $invoice, $amount, $recurrenceCount, $profileId, $customerProfile->paymentProfileId, $transactionParams, $transaction->resultCode);
		}
	}

}