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

class PPAppQuickpay extends PPAppPayment
{
	public function isSupportPaymentCancellation($invoice)
	{
		if ($invoice->isRecurring()) {
			return true;
		}

		return false;
	}

	public function isApplicable($refObject = null, $eventName = '')
	{
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Determines if app supports refunds
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function supportForRefund()
	{
		return true;
	}

	/**
	 * Triggered when controller is created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || $task != 'notify') {
			return true;
		}

		$request = file_get_contents('php://input');
		$data = (array) json_decode($request);

		$orderId = PP::normalize($data, 'order_id', 0);
		$values = explode('_', $orderId, 2);

		$paymentId = $values[0];
		$paymentKey = PP::getKeyFromId($paymentId);

		if (!empty($paymentKey)) {
			$this->input->set('payment_key', $paymentKey);
		}

		return true;
	}

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

		$invoice = $payment->getInvoice();
		$paymentKey = $payment->getKey();

		$helper = $this->getHelper();
		$params = $this->getAppParams();
		
		$amount = $helper->formatAmount($invoice->getTotal());
		$date = JFactory::getDate();

		$payload = array(
			'version' => 'v10',
			'merchant_id' => $helper->getMerchantId(),
			'agreement_id' => $helper->getAgreementId(),
			'order_id' => $payment->getId() . '_' . $date->toUnix(),
			'amount' => $amount,
			'autocapture' => 1,
			'currency' => $invoice->getCurrency('isocode'),
			'continueurl' => $helper->getSuccessUrl($paymentKey),
			'cancelurl' => $helper->getCancelUrl($paymentKey),
			'callbackurl' => $helper->getNotifyUrl()
		);

		if ($invoice->isRecurring()) {
			$payload['type'] = 'subscription';
			$payload['description'] = $invoice->getId();
		}

		$checksum = $helper->generateChecksum($payload, $helper->getApiKey());

		$this->set('payment', $payment);
		$this->set('invoice', $invoice);
		$this->set('checksum', $checksum);
		$this->set('payload', $payload);

		return $this->display('form');
	}

	/**
	 * Triggered after payment is completed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered via IPN
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$rawData = file_get_contents("php://input");
		$response = json_decode($rawData);

		if (!$response) {
			return true;
		}

		$helper = $this->getHelper();
		$valid = $helper->validate($response, $payment);
		if (!$valid) {
			return false;
		}

		$invoice = $payment->getInvoice();
		$gatewayParams = $payment->getGatewayParams();
		

		// Initial recurring subscription
		if ($response->type == 'Subscription') {
			$subscriptionId = PP::normalize($response, 'id', 0);
			$parentId = PP::normalize($response, 'id', 0);

			if ($this->hasDuplicateTransaction($invoice->getId(), 0, $subscriptionId, $parentId, $response, true)) {
				return true;
			}

			$transaction = PP::createTransaction($invoice, $payment, 0, $subscriptionId, 0, $response);

			$operations = $response->operations;
			foreach ($operations as $key) {
				if ($key->qp_status_code == '20000') {
					$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_QUICKPAY_PROFILE_CREATED';
				} else {
					$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_QUICKPAY_PROFILE_CREATION_ERROR';
				}
			}

			$transaction->save();

			$params = new JRegistry();
			$params->set('transaction_id', $response->id);
			$params->set('pending_recur_count', $helper->getRecurrenceCount($invoice));

			$payment->gateway_params = $params->toString();
			$payment->save();

			$amount = $helper->formatAmount($invoice->getTotal());
			$helper->processRecurringPayment($payment, $invoice, $amount);

			return true;
		}

		// Here we assume it is a standard subscription
		$subscriptionId = $gatewayParams->get('transaction_id');
		$operations = $response->operations;
		
		foreach ($operations as $key) {
			// Process refund if there is refund operation
			if ($key->type == 'refund') {
				$transaction = $helper->createRefundTransaction($invoice, $payment, $response, $key->amount);

				$transaction->save();
				return true;
			}
		}

		// Check for duplicate IPN
		$transactionId = $response->id;
		$parentId = PP::normalize($response, 'parent_txn_id', 0);

		if ($this->hasDuplicateTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentId, $response)) {
			return true;
		}

		// Create new transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentId, (array) $response);

		foreach ($response->operations as $operation) {

			if ($operation->qp_status_code == '20000') {
				$transaction->message = 'COM_PAYPLANS_APP_QUICKPAY_TRANSACTION_COMPLETED';
				$transaction->amount = $helper->formatAmount($operation->amount, true);
			} else {
				$transaction->message = 'COM_PAYPLANS_APP_QUICKPAY_TRANSACTION_NO_STATUS';
			}
		}

		$transaction->save();

		return true; 
	}
	
	/**
	 * Triggered during cron to check for recurring subscriptions
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
		$invoiceCounter = $invoiceCount + 1;
		$recurrenceCount = $payment->getGatewayParam('pending_recur_count');

		if ($recurrenceCount > 0) {
			$amount = $helper->formatAmount($invoice->getTotal($invoiceCounter));
			return $helper->processRecurringPayment($payment, $invoice, $amount);
		}
	}

	/**
	 * User terminated subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$gatewayParams = $payment->getGatewayParams();
		$transactionId = $gatewayParams->get('transaction_id',0);

		$payload = array('id' => $transactionId);
		$checksum = $helper->generateChecksum($payload, $helper->getUserApiKey());
		$payload['checksum'] = $checksum;

		$response = $helper->connect($payload, $helper->getEndpointUrl('cancel', $transactionId));
		$responseData = json_decode($response);
		if(is_object($responseData)){
			$responseData = (array)$responseData;
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $responseData);

		if ($responseData['state'] == 'cancelled') {
			
			$transaction->message = 'COM_PAYPLANS_APP_QUICKPAY_CANCEL_SUCCESS';
			$transaction->save();
			
			parent::onPayplansPaymentTerminate($payment, $controller);		
			return true;
		}
		
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_QUICKPAY_CANCEL_ERROR';
		$transaction->save();
					
		$message = JText::_('COM_PAYPLANS_LOGGER_QUICKPAY_ERROR_OCCURED_IN_CANCEL');
		PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $responseData, 'PayplansPaymentFormatter', '', true);
		return false;		
	}

	/**
	 * Triggered when a refund request is made
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refundRequest(PPTransaction $transaction, $refundAmount)
	{
		$helper = $this->getHelper();
		$transactionId = $transaction->getGatewayTxnId();
		$amount = $helper->format($refundAmount);
		$params = $transaction->getParams();

		$payload = array(
			'id' => $transactionId,
			'amount' => $amount
		);

		$checksum = $helper->generateChecksum($payload, $this->getUserApiKey());
		$payload['checksum'] = $checksum;

		$response = $helper->connect($payload, $helper->getEndpointUrl('refund', $transactionId));
		$response = json_decode($response);

		if ($response->state == 'pending' && $response->accepted == true) {
			return true;
		}

		$message = JText::_('COM_PAYPLANS_PAYMENT_APP_QUICKPAY_REFUND_ERROR');
		
		PPLog::log(PPLogger::LEVEL_ERROR, $message, (array) $response);
		return false;
	}

	/**
	 * Check for duplicate IPN transactions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function hasDuplicateTransaction($invoiceId, $transactionId, $subscriptionId, $parentId, $response, $recurring = false)
	{
		$transactions = $this->getExistingTransaction($invoiceId, $transactionId, $subscriptionId, $parentId);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);

				$state = $transaction->getParam('state', '');
				$qpstat = $transaction->getParam('qpstat', '');
				$type = $transaction->getParam('type', '');

				// Recurring subscriptions
				if ($recurring && $qpstat == $response->qpstat && $state == $response->state) {
						return true;
				}

				// Non recurring subscriptions
				if (!$recurring && $response->type == $type && $state == $response->state) {
					return true;
				}
			}
		}

		return false;
	}
}