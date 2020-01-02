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

define('PAYPAL_ADVANCE_PILOT_TEST_URL', "https://pilot-payflowpro.paypal.com");
define('PAYPAL_ADVANCE_PILOT_LIVE_URL', "https://payflowpro.paypal.com");
define('PAYPAL_ADVANCE_PAYMENT_URL', "https://payflowlink.paypal.com");

class PPHelperPaypalAdvance extends PPHelperPayment
{
	/**
	 * Determines if it is in testing mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', false);
		}

		return $sandbox;
	}

	/**
	 * Retrieves the Partenr 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantPartner()
	{
		static $partner = null;

		if (is_null($partner)) {
			$partner = $this->params->get('partner', '');
		}

		return $partner;
	}

	/**
	 * Retrieves the User
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantUser()
	{
		static $user = null;

		if (is_null($user)) {
			$user = $this->params->get('user', '');
		}

		return $user;
	}

	/**
	 * Retrieves the Password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('password', '');
		}

		return $password;
	}

	/**
	 * Retrieves the Password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantVendor()
	{
		static $vendor = null;

		if (is_null($vendor)) {
			$vendor = $this->params->get('vendor', '');
		}

		return $vendor;
	}

	/**
	 * Creates a new connection to paypal advance
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($payload)
	{
		$url = PAYPAL_ADVANCE_PILOT_LIVE_URL;

		if ($this->isSandbox()) {
			$uri = PAYPAL_ADVANCE_PILOT_TEST_URL;
		}
		
		$uri = new JURI($url);

		try {
			
			// Convert the post data into a string
			$query = http_build_query($payload);

			$curl = new JHttpTransportCurl(new JRegistry());
			$response = $curl->request('post', $uri, $query);

			return $response;

		} catch (Exception $e) {
			throw new Exception($e);
		}
	}

	/**
	 * Transforn response into array
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatResponse($response) 
	{
		if (is_array($response)) {
			return (object) $response;
		}
		
		//Reponse is like eg. x=x&y=y&z=z, so we need to process the response details.
		$response = explode("&", $response);
		$processedResponse = new stdClass();

		foreach ($response as $key => $value) {
			$tmp = explode("=", $value);

			if (sizeof($tmp) > 1) {
				$key = $tmp[0];
				$processedResponse->$key = $tmp[1];
			}
		}

		return $processedResponse;
	}

	/**
	 * Generates standard payload data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayloadData()
	{
		static $data = null;

		if (is_null($data)) {

			$data = array(
				'PARTNER' => $this->getMerchantPartner(),
				'VENDOR' => $this->getMerchantVendor(),
				'USER' => $this->getMerchantUser(),
				'PWD' => $this->getMerchantPassword()
			);
		}

		return $data;
	}

	/**
	 * Retrieves the payment url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentUrl($tokenId, $token)
	{
		$mode = 'LIVE';
		if ($this->isSandbox()) {
			$mode = 'TEST';
		}

		$url = PAYPAL_ADVANCE_PAYMENT_URL . '?MODE=' . $mode . '&SECURETOKENID=' . $tokenId . '&SECURETOKEN=' . $token;
		
		return $url;
	}

	/**
	 * Generate security token for payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSecureToken(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();
		$currency = $invoice->getCurrency('isocode'); 
		
		$sercurityToken = 
		$payload = $this->getPayloadData();
		
		$payload['TRXTYPE'] = "S";
		$payload['AMT'] = $amount;
		$payload['CURRENCY'] = $invoice->getCurrency('isocode');
		$payload['INVNUM'] = $payment->getKey();
		$payload['CREATESECURETOKEN'] = 'Y';
		$payload['SECURETOKENID'] = $this->getSecureTokenId($payment);

		try {
			$response = $this->connect($payload);
		} catch(Exception $e) {

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_ADVANCE_CURL_FAILED'), $payment, array($e->getMessage()), 'PayplansPaymentFormatter', '', true);
			return false;
		}

		if (!$response) {
			return false;
		}

		if (!$response->body) {
			PP::Log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_ADVANCE_EMPTY_RESPONSE'), $payment, $response, 'PayplansPaymentFormatter', '', true);

			return false;
		}

		$response = $this->formatResponse($response->body);

		if (!isset($response->RESULT)) {
			$message = 'Invalid response message from PayPal Advance';
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, (array) $response, 'PayplansPaymentFormatter', '', true);

			return false;
		}

		if ($response->RESULT != 0) {
			$message = JText::sprintf('Invalid response message from PayPal Advance. Received: %1$s', $response->RESPMSG);
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, (array) $response, 'PayplansPaymentFormatter', '', true);

			return false;
		}
	
		$token = new stdClass();
		$token->id = $response->SECURETOKENID;
		$token->token = $response->SECURETOKEN;

		return $token;
	}

	/**
	 * It will generate the 32 Alphanumeric string that will be utilize as secure token
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSecureTokenId(PPPayment $payment)
	{
		$paymentKey = $payment->getKey();
		$encryptor = PP::encryptor(true);
		$encryptor->setSourceLength(32);
		
		$token = $encryptor->encrypt($paymentKey);

		return $token; 
	}
}