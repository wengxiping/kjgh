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

class PPAppWirecard extends PPAppPayment
{
	/**
	 * Render Payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice();
		
		$helper = $this->getHelper();
		$formUrl = $helper->getFormUrl();
		$toolkitPassword = $helper->getToolkitPassword();
		$orderDescription = $invoice->getTitle();
		$paymentKey = $payment->getKey();

		// Urls
		$successUrl = $helper->getSuccessUrl($paymentKey);
		$cancelUrl = $helper->getCancelUrl($paymentKey);
		$serviceUrl = $helper->getServiceUrl($paymentKey);
		$failureUrl = $helper->getFailUrl($paymentKey);

		$autoDeposit = 'no';

		$transactionIdentifier = $invoice->isRecurring()?'INITIAL':'SINGLE';

		$payload = array(
			'secretKey' => $helper->getSecretKey(),
			'customerId' => $helper->getCustomerId(),
			'amount' => $invoice->getTotal(),
			'currency' => $invoice->getCurrency('isocode', 'USD'),
			'language' => 'en',
			'paymentType' => 'SELECT',
			'orderDescription' => $invoice->getTitle(),
			'successUrl' => $successUrl,
			'cancelUrl' => $cancelUrl,
			'serviceUrl' => $serviceUrl,
			'failureUrl' => $failureUrl,
			'orderReference' => $paymentKey,
			'customerStatement' => $invoice->getTitle(),
			'autoDeposit' => 'no',
			'transactionIdentifier' => $invoice->isRecurring() ? 'INITIAL' : 'SINGLE',
			'requestFingerprintOrder' => 'secret,customerId,amount,currency,language,paymentType,orderDescription,successUrl,cancelUrl,serviceUrl,failureUrl,orderReference,customerStatement,autoDeposit,transactionIdentifier,requestFingerprintOrder'
		);

		$fingerprint = $helper->sign($payload);

		$this->set('formUrl', $formUrl);
		$this->set('cancelUrl', $cancelUrl);
		$this->set('failureUrl', $failureUrl);
		$this->set('serviceUrl', $serviceUrl);
		$this->set('fingerprint', $fingerprint);

		foreach ($payload as $key => $value) {
			$this->set($key, $value);
		}
		
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

		$status = PP::normalize($data, 'paymentState', '');

		if ($status != 'SUCCESS') {
			$message = JText::_('COM_PAYPLANS_APP_WIRECARD_ERROR');
			$action = 'error';
			
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter');
			
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);	
		}

		$helper = $this->getHelper();
		list($valid, $message) = $helper->validate($payment, $data);

		if (!$valid) {
			$action	= 'error';
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter');
			
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}
		
		$invoice = $payment->getInvoice();
		
		// Check for duplicate IPN
		$transactionId = PP::normalize($data, 'gatewayReferenceNumber', '');
		$parentId = PP::normalize($data, 'orderNumber', '');

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, $parentId);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);

				$params = $transaction->getParams();
				$transactionReference = $params->get('orderNumber', '');
				$transactionStatus = $params->get('paymentState', '');

				if ($transactionReference == $data['orderNumber'] && $transactionStatus == $data['paymentState']) {
					return true;
				}
			}
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, $parentId, $data);
		$transaction->amount = PP::normalize($data, 'amount', 0);
		$transaction->message = JText::_('COM_PAYPLANS_APP_WIRECARD_PAYMENT_COMPLETED_SUCCESSFULLY');
		$transaction->save();

		if ($invoice->isRecurring()) {
			$recurrence_count = $helper->getRecurrenceCount($invoice);

			$params = $payment->getGatewayParams();
			$params->set('pending_recur_count', $recurrence_count);
			$params->set('source_order_number', $parentId);

			$payment->gateway_params = $params->toString();
			$payment->save();
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered when recurring payment requested 
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
		$gatewayParams = $payment->getGatewayParams();

		$payload = array(
			'customerId' => $helper->getCustomerId(),
			'toolkitPassword' => $helper->getToolkitPassword(),
			'secret' => $helper->getSecretKey(),
			'command' => 'recurPayment',
			'language' => 'en',
			'orderNumber' => $payment->getId(),
			'sourceOrderNumber' => $gatewayParams->get('source_order_number'),
			'autoDeposit' => 'no',
			'orderDescription' => $invoice->getTitle(),
			'amount' => $invoice->getTotal(),
			'currency' => $invoice->getCurrency('isocode', 'USD'),
			'orderReference' => $payment->getKey(),
			'customerStatement' => $invoice->getTitle(),
			'requestFingerprintOrder' => 'customerId,toolkitPassword,secret,command,language,orderNumber,sourceOrderNumber,autoDeposit,orderDescription,amount,currency,orderReference,customerStatement,requestFingerprintOrder'
		);

		$fingerprint = $helper->sign($payload);

		$lifetime = ($invoice->getRecurrenceCount() == 0)? true : false;
		$invoice_count = $invoiceCount +1;
		$recurrence_count = $payment->getGatewayParam('pending_recur_count');
		
		if ($recurrence_count > 0 || $lifetime) {
			$url = $helper->getToolkitUrl();

			$payload['requestFingerprint'] = $fingerprint;
			$response = $helper->connect($url, $payload);
			$status = PP::normalize($data, 'status', '');

			if ($status != 0) {
				$message = JText::_('COM_PAYPLANS_APP_WIRECARD_ERROR_RECURRING_PAYMENT');
				PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter');
				return false;
			}
			
			$transactionId = $orderReference . '_recurrence_' . time();
			$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0);
			$transaction->amount = $amount;
			$transaction->message = JText::_('COM_PAYPLANS_APP_WIRECARD_PAYMENT_COMPLETED_SUCCESSFULLY');
			$transaction->save();
			
			$recurrence_count = $recurrence_count -1;
			$payment->getGatewayParams()->set('pending_recur_count',$recurrence_count);
			$payment->save();
		}
	}	
}