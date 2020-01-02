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

class PPAppPayuTurkey extends PPAppPayment
{
	/**
	 * Determines if payment cancellation is supported
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
		$paymentKey = $payment->getKey();
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		$helper = $this->getHelper();
		$publicKey = $helper->getPublicKey();
		$postUrl = $helper->getPostUrl($paymentKey);
		$sandbox = $helper->isSandbox();

		$this->set('sandbox', $sandbox);
		$this->set('payment', $payment);
		$this->set('invoice', $invoice);
		$this->set('amount', $amount);
		$this->set('paymentKey', $paymentKey);
		$this->set('publicKey',	$publicKey);
		$this->set('postUrl', $postUrl);

		return $this->display('form');
	}

	/**
	 * Triggered after user completes payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{	
		if ($action == 'cancel') {
			return true;
		}

		// For 3D Secure cards
		$refNumber = PP::normalize($data, 'REFNO', '');

		if ($refNumber) {
			$invoice = $payment->getInvoice();
			$helper = $this->getHelper();
			$state = $helper->createTransaction($invoice, $payment, $data);

			if ($state == true) {
				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $payment->getKey(), false);
				return PP::redirect($redirect);
			}

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		if ($action == 'process') {
			$helper = $this->getHelper();

			$response = $helper->processPayment($payment, $data);

			// Payment was not successful
			if (!($response instanceof PayU\Alu\Response)) {
				$redirect = 'index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&error_code=' . $result['error_code'] . '&error_message=' . urlencode($result['error_message']);
				$redirect = PPR::_($redirect);

				return PP::redirect($redirect);				
			}

			$transactionId = $response->getRefno();
			
			// if same notification then epg gateway id is same as previous one then check if transaction already exists ,if yes then do nothing and return
			$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);
			
			if ($transactions) {
				return true;
			}

			$state = $helper->createTransaction($invoice, $payment, $response);

			if (!$state) {
				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $payment->getKey());
				return PP::redirect($redirect);
			}

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $payment->getKey());

			return PP::redirect($redirect);
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * When user terminates their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $invoiceController) 
	{
		return parent::onPayplansPaymentTerminate($payment, $invoiceController);
	}

	/**
	 * Triggered during cron for recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();
		
		if (!$invoice->isRecurring()) {
			return false;
		}

		$lifetime = false;
		$lifetime = ($invoice->getRecurrenceCount() == 0)? true : false;
		$invoice_count = $invoiceCount +1;
		$recurrence_count = $payment->getGatewayParam('pending_recur_count');
			
		if ($recurrence_count > 0 || $lifetime) {
			$helper = $this->getHelper();
			$response = $helper->processRecurringPayment($payment, array('process_payment' => true), $invoice_count);

			// Error when trying to rebill
			if (!($response instanceof PayU\Alu\Response)) {
				return false;
			}

			$transactionId = $response->getRefno(); 

			// Check for duplicate transactions
			$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);

			if ($transactions) {
				return true;
			}

			$gatewayParams = $payment->getGatewayParams();
			$token = $gatewayParams->get('TOKEN_HASH');

			$status = $response->getStatus();
			$transaction = PP::createTransaction($invoice, $payment, $transactionId, $token, $token, $response->getResponseParams());
			
			// Transaction failed
			if ($status !== 'SUCCESS') {
				$transaction->amount = 0;
				$transaction->message = JText::_('COM_PAYPLANS_APP_PAYUTURKEY_TRANSACTION_NOT_COMPLETED');
				$transaction->save();
				return false;		
			}

			// Here we assume that the transaction was successful
			$transaction->amount = $response->getAmount();
			$transaction->message = JText::_('COM_PAYPLANS_APP_PAYUTURKEY_TRANSACTION_COMPLETED');
			$transaction->save();

			// IMP: if payment arrives after certain failures then reset the failure counter
			if ($recurrence_count != 0) {
				$recurrence_count = $recurrence_count - 1;
				$gatewayParams->set('pending_recur_count', $recurrence_count);
			}

			$gatewayParams->set('TOKEN_HASH', $token);
			$payment->gateway_params = $gatewayParams->toString();
			$payment->save();

			return true;
		}
					
		return true;
	}
}