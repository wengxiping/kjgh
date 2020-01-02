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

class PPAppPin extends PPAppPayment
{
	/**
	 * Determines if this payment supports cancellation
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
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$paymentKey	= $payment->getKey();
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();
		$currency = $invoice->getCurrency('isocode');
		$buyer = $invoice->getBuyer();
		$email = $buyer->getEmail();
		

		$helper = $this->getHelper();
		$formUrl = $helper->getFormUrl($paymentKey);
		$sandbox = $helper->isSandbox();

		$this->set('sandbox', $sandbox);
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('amount', $amount);
		$this->set('currency', $currency);
		$this->set('paymentKey', $paymentKey);
		$this->set('email', $email);
		$this->set('formUrl', $formUrl);

		return $this->display('form');
	}

	/**
	 * Triggered after user completed payment
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

		$payload = $helper->getCustomerPayload($data);
		$token = $helper->createCustomer($invoice, $payment, $payload);
		
		// If there occur any error then array will appear in $token else $token will be a string....
		if (is_array($token)) {
			PP::info()->set($token['error_desc'], 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
			return PP::redirect($redirect);
		}
		
		$recurrence = 0;
		$amount = $helper->formatAmount($invoice->getTotal());

		if ($invoice->isRecurring()) {
			$recurrence = $helper->getRecurrenceCount($invoice);
		}

		$buyer = $invoice->getBuyer(true);
		$email = $buyer->getEmail();

		$payload = $helper->getChargePayload($amount, $invoice->getCurrency('isocode'), $invoice->getTitle(), $email, @$_SERVER['REMOTE_ADDR'], $token);
		$result = $helper->createCharge($invoice, $payment, $payload, $token, $recurrence);
		$response = PP::normalize($result, 'response', '');

		// If there occur any error then array will appear in $token else $token will be a string....
		if (is_array($response) && isset($response['error'])) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_PIN_LOGGER_DO_CHARGE_ERROR');
			PP::info()->set($message, 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
			return PP::redirect($redirect);
		}

		// Check for duplicate transaction
		$transactionId = PP::normalize($response, 'token', '');
		$subscriptionId = $token;

		if ($this->hasDuplicate($invoice->getId(), $transactionId, $subscriptionId, 0, $response)) {
			return true;
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $response);
		$transaction->amount = $helper->formatAmount($response['amount'], true);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PIN_TRANSACTION_COMPLETED_SUCCESSFULLY';
		$transaction->save();
		
		if ($invoice->isRecurring()) {
			$recurrence = $recurrence - 1;

			$gatewayParams = $payment->getGatewayParams();
			$gatewayParams->set('pending_recur_count', $recurrence);

			$payment->gateway_params = $gatewayParams->toString();
			$payment->save();
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
	
	/**
	 * Executed during cron
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();
		$invoice_count = $invoiceCount + 1;
		
		if (!$invoice->isRecurring()) {
			return;
		}

		$params = $payment->getGatewayParams();
		$recurrence = $params->get('pending_recur_count', 0);
		$email = $params->get('customer_email', '');
		$token = $params->get('customer_token', '');

		if (!$email || !$token) {
			$error = array(
				'code' => '',
				'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PIN_LOGGER_CHARGES_DATA_MISSING')
			);

			PPLog::log(PPLogger::LEVEL_ERROR, $error['message'], $payment, $error, 'PayplansPaymentFormatter', '', true);
			return true;
		}

		// Nothing to rebill anymore
		if ($recurrence <= 0) {
			return;
		}


		$helper = $this->getHelper();

		$amount = $helper->formatAmount($invoice->getTotal());
		$currency = $invoice->getCurrency('isocode');

		$payload = $helper->getChargePayload($amount, $currency, $invoice->getTitle(), $email, @$_SERVER['REMOTE_ADDR'], $token);			
		$result = $helper->createCharge($invoice, $payment, $payload, $token, $recurrence);

		$response = PP::normalize($result, 'response', '');

		// If there occur any error then array will appear in $token else $token will be a string....
		if (is_array($response) && isset($response['error'])) {
			return false;
		}

		// Check for duplicate transaction
		$transactionId = PP::normalize($response, 'token', '');
		$subscriptionId = $token;

		if ($this->hasDuplicate($invoice->getId(), $transactionId, $subscriptionId, 0, $response)) {
			return true;
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $response);
		$transaction->amount = $helper->formatAmount($response['amount'], true);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PIN_TRANSACTION_COMPLETED_SUCCESSFULLY';
		$transaction->save();
		
		$recurrence = $recurrence - 1;

		$gatewayParams = $payment->getGatewayParams();
		$gatewayParams->set('pending_recur_count', $recurrence);

		$payment->gateway_params = $gatewayParams->toString();
		$payment->save();

		return true;
	}

	/**
	 * Upon termination of payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller) 
	{
		parent::onPayplansPaymentTerminate($payment, $controller);
		
		return true;
	}

	/**
	 * Determines if there are duplicate IPN
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function hasDuplicate($invoiceId, $transactionId, $subscriptionId, $parentId, $response)
	{
		$transactions = $this->getExistingTransaction($invoiceId, $transactionId, $subscriptionId, $parentId);

		if (!$transactions) {
			return false;
		}


		foreach ($transactions as $transaction) {
			$transaction = PP::transaction($transaction->transaction_id);
			$params = $transaction->getParams();
			$paramsResponse = $params->get('response', new stdClass());
			
			if (strtolower($paramsResponse->status_message) == strtolower($response['status_message'])) {
				return true;
			}
		}

		return false;
	}
}
