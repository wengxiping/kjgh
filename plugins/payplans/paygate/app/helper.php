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

class PPHelperPayGate extends PPHelperPayment
{
	/**
	 * Calculate checksum data to validate response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function calculateChecksum($data)
	{
		$data = array(
			'paygateId' => PP::normalize($data, 'PAYGATE_ID', ''),
			'reference' => PP::normalize($data, 'REFERENCE', ''),
			'status' => PP::normalize($data, 'TRANSACTION_STATUS', ''),
			'resultCode' => PP::normalize($data, 'RESULT_CODE', ''),
			'authCode' => PP::normalize($data, 'AUTH_CODE', ''),
			'amount' => PP::normalize($data, 'AMOUNT', 0),
			'resultDesc' => PP::normalize($data, 'RESULT_DESC', ''),
			'transactionId' => PP::normalize($data, 'TRANSACTION_ID', '')
		);


		$riskIndicator = PP::normalize($data, 'RISK_INDICATOR', '');

		if ($riskIndicator) {
			$data['riskIndicator'] = $riskIndicator;
		}
			
		$data['encryptionKey'] = $this->getEncryptionKey();
		
		$hash = $this->hash($data);

		return $hash;
	}

	/**
	 * Formats the amount so that it is compatible with Quickpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount($amount, $reverse = false)
	{
		if ($reverse) {
			$amount = ($amount / 100);

			return $amount;
		}

		// multiply by 100, because payment gateway does not support decimal
		$amount = number_format($amount, 2) * 100;

		return $amount;
	}


	/**
	 * Retrieve the encryption key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEncryptionKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('encryption_key', '');
		}

		return $key;
	}

	/**
	 * Retrieve the encryption key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaygateId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('paygateid', '');
		}

		return $id;
	}

	/**
	 * Method to get the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl(PPInvoice $invoice)
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.paygate.co.za/paywebv2/process.trans';

			if ($invoice->isRecurring()) {
				$url = 'https://www.paygate.co.za/PaySubs/process.trans';
			}
		}

		return $url;
	}

	/**
	 * Method to get the cancel url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReturnUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=paygate&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	
	/**
	 * Given an array of data, md5 it
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hash($data)
	{
		$string = implode('|', $data);
		$hash = md5($string);

		return $hash;
	}

	/**
	 * Process response from paygate
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process(PPPayment $payment, $data, $transaction, $invoice)
	{
		$errors = array();

		$invoice = $payment->getInvoice();
		$checksum = PP::normalize($data, 'CHECKSUM', '');
		$calculatedChecksum = $this->calculateChecksum($data);
		
		// If the calculated checksum does not match the PayGate checksum in the response, then results should be rejected.
		if ($calculatedChecksum != $checksum) {
			$errors[] = JText::_('COM_PAYPLANS_APP_PAYGATE_INVALID_HASH');
		}
		
		// Ensure merchant id is correct
		if ($this->getPaygateId() != $data['PAYGATE_ID']) {
			$errors[] = JText::_('COM_PAYPLANS_APP_PAYGATE_INVALID_MERCHANT_PAYGATEID');
		}

		$status = PP::normalize($data, 'TRANSACTION_STATUS', '');

		if ($status != 1) {
			$errors[] = JText::_('COM_PAYPLANS_APP_PAYGATE_PAYMENT_FAIL');
		}

		$resultCode = PP::normalize($data, 'RESULT_CODE', '');

		// Check Result Sucecss
		if ($resultCode != 990017) {
			$errors[] = JText::_("COM_PAYPLANS_APP_PAYGATE_PAYMENT_FAILED_REASON_CODE_" . $resultCode);
		}
		
		if ($data['RISK_INDICATOR'] != 'AX' ) {
			$errors[] = JText::_("COM_PAYPLANS_APP_PAYGATE_PAYMENT_RISK_INDICATOR");
		}

		// Here we assume that the transaction was successful
		if ($status == 1) {
			$amount = PP::normalize($data, 'AMOUNT', 0);
			$amount = $this->formatAmount($amount, true);

			$transaction->amount = $amount;
		}
				
		return $errors;
	}

}
