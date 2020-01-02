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

class PPHelperBitpay extends PPHelperPayment
{
	/**
	 * Create invoice for bitpay payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createInvoice($post, $payment, $invoice)
	{
		$url = $this->getBitPayUrl();
		$response = $this->connect($url, $post);

		if (isset($response->url) && $response->url) {
			return $response;			
		}

		return false;
	}

	/**
	 * Confirms the payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirm($data)
	{
		$url = $this->getBitPayUrl() . $data->id;
		$response = $this->connect($url);

		return $response;
	}

	/**
	 * Creates a new connection to bitpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $post = null)
	{
		$curl = curl_init($url);
		
		$username = base64_encode($this->getMerchantId());
		$length = 0;

		if ($post) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
			
			$length = strlen($post);
		}

		$header = array(
			"Content-Type: application/json",
			"Content-Length: $length",
			"Authorization: Basic $username",
		);


		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_CAINFO, PP_CACERT);
		
		$responseString = curl_exec($curl);

		$response = json_decode($responseString);

		if ($responseString == false || isset($response->error)) {
			$response = curl_error($curl);
		}

		curl_close($curl);
		
		return $response;
	}

	/**
	 * Retrieves the notification url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNotifyUrl()
	{
		// For debugging
		// $url = 'http://d1750f15.ngrok.io/';
		// return $url;

		$url = JURI::root();

		return $url;
	}

	/**
	 * Retrieves the bitpay url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBitPayUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$sandbox = $this->params->get('sandbox', false);
			$url = 'https://bitpay.com/api/invoice/';

			if ($sandbox) {
				$url = 'https://test.bitpay.com/api/invoice/';
			}
		}

		return $url;
	}

	/**
	 * Retrieve the merchant id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $merchantId = null;

		if (is_null($merchantId)) {
			$merchantId = trim($this->params->get('api_key', ''));
		}

		return $merchantId;
	}

	/**
	 * Logic to handle new subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processNew($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_NEW';
	}

	/**
	 * Logic to handle paid notifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPaid($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_PAID';
		$transaction->amount = $data->price;
	}

	/**
	 * Logic to handle confirmed notifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processConfirmed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_CONFIRMED';
		$transaction->amount = $data->price;
	}

	/**
	 * Logic to handle completed payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processComplete($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_COMPLETE';
	}

	/**
	 * Logic to handle expired payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processExpired($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_EXPIRED';
	}

	/**
	 * Logic to handle incomplete payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processIncomplete($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_BITPAY_INCOMPLETE';
	}

	/**
	 * Validates a notification data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($data)
	{
		$merchantId = $this->getMerchantId();
		$result = json_decode($data);
		
		if (is_string($result) || (isset($result->error))) {
			return false;
		}

		// Ensure that the hash matches
		$posData = json_decode($result->posData);

		if ($posData->hash != crypt($posData->invoice_key, $merchantId)) {
			return false;
		}

		$result->posData = $posData;

		return $result;
	}
}
