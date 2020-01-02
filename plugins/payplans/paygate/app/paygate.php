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

class PPAppPaygate extends PPAppPayment
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
			$data = (array) $data;
		}

		$helper = $this->getHelper();

		$date = PP::date();
		$invoice = $payment->getInvoice();
		$amount = $helper->formatAmount($invoice->getTotal());
		$paymentKey = $payment->getKey();

		$data = array(
			'paygateid' => $helper->getPaygateId(),
			'payment_key' => $paymentKey,
			'amount' => $amount,
			'currency' => $invoice->getCurrency('isocode', 'ZAR'),
			'return_url' => $helper->getReturnUrl($paymentKey),
			'transaction_date' => $date->format('Y-m-d H:i'),
			'encryption_key' => $helper->getEncryptionKey()
		);

		$checksum = $helper->hash($data);
		$postUrl = $helper->getFormUrl($invoice);

		$this->set('invoice', $invoice);
		$this->set('checksum', $checksum);
		$this->set('postUrl', $postUrl);

		foreach ($data as $key => $value) {
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
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();

		// Check for duplicate IPN
		$transactionId = PP::normalize($data, 'TRANSACTION_ID', 0);

		if ($this->hasDuplicate($invoice->getId(), $transactionId, 0, 0, $data)) {
			return true;
		}
		
		// Create new transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $data);

		$errors = $helper->process($payment, $data, $transaction, $invoice);

		$status = PP::normalize($data, 'TRANSACTION_STATUS', '');
		if ($status) {
			$transaction->message = JText::_("COM_PAYPLANS_APP_PAYGATE_TRANSACTION_" . $status);

		} else {
			$transaction->message = JText::_('COM_PAYPLANS_APP_PAYGATE_TRANSACTION_NO_STATUS');
		}
		
		$transaction->save();

		if (!empty($errors)) {

			$message = JText::_('COM_PAYPLANS_APP_PAYGATE_LOGGER_ERROR_IN_PAYMENT_PROCESS');
			$response = array(
				'error_message' => $message,
				'data' => $errors
			);
			
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
			return true;
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}


	/**
	 * Determines if there are duplicate transactions (IPN)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function hasDuplicate($invoiceId, $transactionId, $subscriptionId, $parentId, $data)
	{
		$transactions = $this->getExistingTransaction($invoiceId, $transactionId, $subscriptionId, $parentId);

		if (!$transactions) {
			return false;
		}

		foreach ($transactions as $transaction) {
			$transaction = PP::transaction($transaction->transaction_id);
			$params = $transaction->getParams();

			$status = $params->get('TRANSACTION_STATUS');
			$resultCode = $params->get('RESULT_CODE','');
			$riskIndicator = strtolower($params->get('RISK_INDICATOR'));

			if ($status == $data['TRANSACTION_STATUS'] && $resultCode == $data['RESULT_CODE'] && $riskIndicator == strtolower($data['RISK_INDICATOR'])) {
					return true;
			}
		}

		return false;
	}
}