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

class PPAppPayflow extends PPAppPayment
{
	/**
	 * Determines if the invoice supports cancellation
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
	 * Renders payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{		
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		$helper = $this->getHelper();

		$paymentKey = $payment->getKey();
		$cancelUrl = $helper->getCancelUrl($paymentKey);
		$formUrl = $helper->getFormUrl($paymentKey);
		$sandbox = $helper->isSandbox();

		$this->set('sandbox', $sandbox);
		$this->set('payment', $payment);
		$this->set('amount', $amount);
		$this->set('invoice', $invoice);
		$this->set('formUrl', $formUrl);
		$this->set('cancelUrl', $cancelUrl);
		$this->set('paymentKey', $paymentKey);
		
		return $this->display('form');
	}

	/**
	 * After the user submits the complete payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$data['CUSTIP'] = @$_SERVER['REMOTE_ADDR'];
		$data['CUSTOM'] = $invoice->getKey() . '-' . $payment->getKey();

		if (!$invoice->isRecurring()) {
			$payload = $helper->getStandardPayload($invoice, $data);
			$response = $helper->connect('S', $payload, $payment);

			$resultCode = PP::normalize($response, 'RESULT', '');
			$status = PP::normalize($response, 'RESPMSG', '');

			if ($resultCode != 0 && $status != 'Approved') {
				
				PP::info()->set($status, 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
				return PP::redirect($redirect);
			}

			// Create a new transaction
			$state = $helper->createNewTransaction($invoice, $payment, $response);

			if ($state === false) {
				PP::info()->set($helper->getError(), 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);	
				return PP::redirect($redirect);
			}
		}

		if ($invoice->isRecurring()) {
			$payload = $helper->getRecurringPayload($invoice, $data);

			$response = $helper->createNewRecurringProfile($invoice, $payment, $payload);

			if ($response === false) {
				PP::info()->set($helper->getError(), 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
				return PP::redirect($redirect);
			}

			// Here we assume that the transaction was successful
			$state = $helper->createNewTransaction($invoice, $payment, $response, PP_RECURRING);

			if ($state === false) {
				PP::info()->set($helper->getError(), 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);	
				return PP::redirect($redirect);
			}

			$profileID = PP::normalize($response, 'PROFILEID', 0);

			$params = new JRegistry();
			$params->set('profile_id', $profileID);
			$payment->gateway_params = $params;

			PP::info()->set('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_SUBSCRIPTION_ACTIVE_AFTER_ONE_DAY', 'success');
		}

		$payment->save();
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered during cron to process recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$profileId = $payment->getGatewayParam('profile_id', null);

		$helper = $this->getHelper();
		$payload = $helper->getRebillPayload($profileId);
		
		$response = $helper->connect('R', $payload, $payment);
		$resultCode = PP::normalizeData($response, 'RESULT', '');

		if ($resultCode != 0) {
			return false;
		}

		$invoice = $payment->getInvoice();
		$transactions = $payment->getTransactions();

		// Duplicate transaction handling does not required since whether existing transaction exist or not as
		// here we are finding all the transaction of payment and checking in the enquiry response about its existence.
		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$val = $transaction->getGatewayTxnId();

				$pnref_array[] = empty($val) ? '' : $val;
			}
		}

		$transactionState = $helper->getRebillState();
		$failedStates = array(PAYPLANS_PAYFLOW_ERROR, PAYPLANS_PAYFLOW_FAILED_TO_SETTLE, PAYPLANS_PAYFLOW_SETLEMENT_INCOMPLETE);

		foreach ($response as $key => $value) {

			if (strpos($key, 'P_PNREF') === false || in_array($value, $pnref_array)) {
				continue;
			}

			$index = substr($key, -1);

			$rowCode = (int) $response['P_RESULT' . $index];
			$rowState = (int) $response['P_TRANSTATE' . $index];
			$rowAmount = (float) $response['P_AMT' . $index];
			$rowTransactionId = (string) $response['P_PNREF' . $index];

			// Prevent future errors by terminating the invoice immediately
			if (in_array($rowState, $failedStates)) {
				$invoice->terminate();
				return array();
			}


			if ($rowCode === 0 && $rowState == $transactionState && $rowAmount > 0) {

				$transTime = PP::normalize($response, 'P_TRANSTIME' . $index, '');
				$transResult = PP::normalize($response, 'P_RESULT' . $index, '');
				$transTranslate = PP::normalize($response, 'P_TRANSLATE' . $index, '');

				$params = new JRegistry();
				$params->set('P_PNREF', $rowTransactionId);
				$params->set('P_TRANSTIME', $transTime);
				$params->set('P_RESULT', $transResult);
				$params->set('P_TRANSLATE', $transTranslate);

				$transaction = PP::createTransaction($invoice, $payment, $rowTransactionId, 0, 0, $response);
				$transaction->amount = $rowAmount;
				$transaction->message = JText::_("COM_PAYPLANS_APP_PAYFLOW_RECURRING_PAYMENT_COMPLETED");

				$state = $transaction->save();

				if (!$state) {
					$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD');
					PP::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
				}

				$payment->save();
			}
		}

		return true;
	}

	/**
	 * Triggered when user terminates their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$params = $payment->getGatewayParams();
		$invoice = $payment->getInvoice();

		$helper = $this->getHelper();
		
		$profileId = $params->get('profile_id');
		$payload = $helper->getRefundPayload($profileId);
		
		$response = $helper->connect('R', $payload, $payment);

		$resultCode = PP::normalize($response, 'RESULT', '');
		$status = PP::normalize($response, 'RESPMSG', '');

		if ($resultCode != 0 && $status != 'Approved') {
			
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_ERROR_IN_RECURRING_PROFILE_CANCELLATION');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

			return false;
		}

		// Create a new transaction for the refund
		$subscriptionId = PP::normalize($response, 'PROFILEID', '');
		$transaction = PP::createTransaction($invoice, $payment, 0, $subscriptionId, 0, $response);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYFLOW_TRANSACTION_RECURRING_CANCEL';
		$transaction->save();

		return parent::onPayplansPaymentTerminate($payment, $controller);
	}
}