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

class PPHelperSaferPay extends PPHelperPayment
{
	/**
	 * Connects to SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $payload) 
	{  
		$ch = curl_init($url);

		$headers = array(
			"Content-type: application/json",
			"Accept: application/json"
		);

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$username = trim($this->getApiUsername());
		$password = trim($this->getApiPassword());

		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
		
		$response = curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($status != 200) {
			return false;
		}

		curl_close($ch);

		$response = json_decode($response, true);

		return $response;
	}

	/**
	 * Formats the amount required by SaferPay
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

		$amount = number_format($amount) * 100;

		return $amount;
	}
	
	/**
	 * Retrieves the API Username for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUsername()
	{
		static $username = null;

		if (is_null($username)) {
			$username = $this->params->get('username', '');
		}

		return $username;
	}

	/**
	 * Retrieves the API Username for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('password', '');
		}

		return $password;
	}

	/**
	 * Retrieves the Customer ID for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomerId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('CustomerId', '');
		}

		return $id;
	}

	/**
	 * Retrieves the Terminal ID for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTerminalId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('TerminalId', '');
		}

		return $id;
	}

	/**
	 * Retrieves the domain name used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUrlDomain()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.saferpay.com';

			if ($this->isSandbox()) {
				$url = 'https://test.saferpay.com';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the Payment url for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getUrlDomain() . '/api/Payment/v1/PaymentPage/Initialize';
		}

		return $url;
	}

	/**
	 * Retrieves the payment capture url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentCaptureUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getUrlDomain() . '/api/Payment/v1/Transaction/Capture';
		}

		return $url;
	}

	/**
	 * Retrieves the url for SaferPay in exchanging token for transaction details
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResponseUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getUrlDomain() . '/api/Payment/v1/PaymentPage/Assert';
		}

		return $url;
	}

	/**
	 * Retrieves the cancellation url for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=saferpay&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}
	
	/**
	 * Retrieves the return url for SaferPay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReturnUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=saferpay&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the payment request payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentRequestPayload(PPInvoice $invoice, PPPayment $payment)
	{
		$amount = $this->formatAmount($invoice->getTotal());
		
		$customerId = $this->getCustomerId();
		$terminalId = $this->getTerminalId();

		$paymentKey = $payment->getKey();

		$returnUrl = $this->getReturnUrl($paymentKey);
		$cancelUrl = $this->getCancelUrl($paymentKey);

		$payload = array(
			'RequestHeader' => array(
				'SpecVersion' => '1.6',
				'CustomerId' => $customerId,
				'RequestId' => $invoice->getKey(),
				'RetryIndicator' => 0
			),
			'TerminalId' => $terminalId,
			'Payment' => array(
				'Amount' => array(
					'Value' => $amount,
					'CurrencyCode' => $invoice->getCurrency('isocode', 'CHF')
				),
				'OrderId' => $invoice->getKey(),
				'Description' => $invoice->getTitle()
			),
			'ReturnUrls' => array(
				'Success' => $returnUrl,
				'Fail' => $cancelUrl
			)
		);

		return $payload;
	}

	/**
	 * Retrieves the payment request payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResponsePayload(PPInvoice $invoice, $token)
	{
		$customerId = $this->getCustomerId();
		$terminalId = $this->getTerminalId();

		$payload = array(
			'RequestHeader' => array(
				'SpecVersion' => '1.6',
				'CustomerId' => $customerId,
				'RequestId' => $invoice->getKey(),
				'RetryIndicator' => 0
			),
			'Token' => $token
		);

		return $payload;
	}

	/**
	 * Retrieves the payment request payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentCapturePayload(PPInvoice $invoice, $transactionId)
	{
		$customerId = $this->getCustomerId();

		$payload = array(
			'RequestHeader' => array(
				'SpecVersion' => '1.6',
				'CustomerId' => $customerId,
				'RequestId' => $invoice->getKey(),
				'RetryIndicator' => 0
			),
			'TransactionReference' => array(
				'TransactionId' => $transactionId
			)
		);

		return $payload;
	}

	/**
	 * Determines if it is currently in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', false) ? true : false;
		}

		return $sandbox;
	}
}
