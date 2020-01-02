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

class PPHelperPayfast extends PPHelperPayment
{
	/**
	 * Get Merchant Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('merchantid');
		}
		
		return $id;
	}

	/**
	 * Get Merchant Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('merchantkey');
		}
		
		return $key;
	}

	/**
	 * Determines if it is running in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox') ? true : false;
		}

		return $sandbox;
	}

	/**
	 * Determines if server behind proxy server
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isProxyServer()
	{
		static $server = null;

		if (is_null($server)) {
			$server = $this->params->get('proxy_server') ? true : false;
		}

		return $server;
	}

	/**
	 * Determines if it is running in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPassphrase()
	{
		static $passphrase = null;

		if (is_null($passphrase)) {
			$passphrase = $this->params->get('passphrase');
		}

		return $passphrase;
	}

	/**
	 * Prepares the callback urls
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public  function getCallbackUrls(PPPayment $payment)
	{
		static $callbacks = null;

		if (is_null($callbacks)) {
			$config = PP::config();
			$root = JURI::root();
			
			$callbacks = array(
				'return' => $root . 'index.php?option=com_payplans&gateway=payfast&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'cancel' => $root . 'index.php?option=com_payplans&gateway=payfast&view=payment&task=complete&action=cancel&payment_key=' . $payment->getKey(),
				'notify' => $root . 'index.php?option=com_payplans&gateway=payfast&view=payment&task=notify&payment_key=' . $payment->getKey()
			);
		}
		
		return $callbacks;
	}

	/**
	 * Retrieves the url for payfast
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayfastUrl()
	{
		static $url = null;

		if (is_null($url)) {
			
			$url = 'https://www.payfast.co.za/eng/process';

			if ($this->isSandbox()) {
				$url = 'https://sandbox.payfast.co.za/eng/process';
			}
		}

		return $url;
	} 

	/**
	 * Retrieves the url for payfast
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayfastIpnUrl()
	{
		static $url = null;

		if (is_null($url)) {

			$url = 'www.payfast.co.za';

			if ($this->isSandbox()) {
				$url = 'sandbox.payfast.co.za';
			}
		}

		return $url;
	}

	/**
	 * Retrieve recurring details given the expiration time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringFrequency($expTime)
	{
		$frequency = 0;

		if (isset($expTime['month'])) {

			$month = $expTime['month'];
			if ($month == 1) {
				$frequency = 3;
			} 

			if ($month == 4) {
				$frequency = 4;
			} 

			if ($month == 6) {
				$frequency = 5;
			}
		}

		if (isset($expTime['year'])) {

			if ($expTime['year'] == 1) {
				$frequency = 6;
			}
		}

		return $frequency;
	}

	/**
	 * Initiate Payment Request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createPaymentRequest(PPPayment $payment, PPInvoice $invoice)
	{	
		// Build the callback urls
		$callbacks = $this->getCallbackUrls($payment);

		$payload = array(
			'merchant_id' => $this->getMerchantId(),
			'merchant_key' => $this->getMerchantKey(),
			'return_url' => $callbacks['return'],
			'cancel_url' => $callbacks['cancel'],
			'notify_url' => $callbacks['notify'],
			'm_payment_id' => $invoice->getKey(),
			'amount' => $invoice->getTotal(),
			'item_name' => $invoice->getTitle()
		);

		if ($invoice->isRecurring()) {
			$expTime = $invoice->getExpiration(PP_RECURRING);

			$payload['subscription_type'] = 1;
			$payload['frequency'] = $this->getRecurringFrequency($expTime);
			$payload['cycles'] = $invoice->getRecurrenceCount();
		}

		$hash = $this->generateHash($payload);
		
		$payload['signature'] = $hash;

		return $payload;
	}

	/**
	 * Generate signature from data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateHash($data)
	{
		$sigString = '';

		foreach ($data as $key => $val) {
			if ($val != '' && $key != 'submit' && $key != 'passphrase') {
				$sigString .= $key .'='. urlencode(stripslashes(trim($val))) .'&';
			}
		}
		// Remove last ampersand
		$getString = substr( $sigString, 0, -1 );

		$passphrase = $this->getPassphrase();
		if ($passphrase) {
			$getString = $getString. '&passphrase=' . urlencode($passphrase);
		}

		return md5($getString);
	}

	/**
	 * Validates IPN data from payfast
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validateIPN($data)
	{
		$proxyServer = $this->isProxyServer(); 
		$passphrase = $this->getPassphrase();

		$url = $this->getPayfastIpnUrl();

		if ($proxyServer) {
			// Variable initialization
			$validHosts = array(
								'www.payfast.co.za',
								'sandbox.payfast.co.za',
								'w1w.payfast.co.za',
								'w2w.payfast.co.za',
							 );
	
			$validIps = array();
			foreach ($validHosts as $pfHostname) {
				$ips = gethostbynamel( $pfHostname );
				if ($ips) {
					$validIps = array_merge( $validIps, $ips );
				}
			}
	
			// Remove duplicates
			$validIps = array_unique( $validIps );
			if (!in_array( $_SERVER['REMOTE_ADDR'], $validIps)) { 
				return false;
			}
		}

		$returnString = '';
		$disallowedKeys = array('option', 'task', 'view', 'layout', 'gateway', 'id', 'Itemid', 'payment_key', 'signature');

		foreach ($data as $key => $val ) {
			if (in_array($key, $disallowedKeys)) {
				continue;
			}
			$returnString .= $key . '=' . urlencode($val) . '&';
		}

		$returnString = substr($returnString, 0, -1);
		$pfTempParamString = $returnString;

		if ($passphrase) {
			$pfTempParamString = $pfTempParamString. '&passphrase=' . urlencode( $passphrase);
		}

		if (md5($pfTempParamString) != $data['signature']) {
			return false;
		}

		// Variable initialization
		$url = 'https://'. $url .'/eng/query/validate';

		// Create default cURL object
		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );      
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1 );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $returnString );

		$response = curl_exec( $ch );
		curl_close($ch);

		$lines = explode( "\r\n", $response );
		$verifyResult = trim( $lines[0] );

		if (strcasecmp( $verifyResult, 'VALID' ) != 0) {
			return false;
		}
			
		return true;
	 }
}
