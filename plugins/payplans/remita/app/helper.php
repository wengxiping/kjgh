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

class PPHelperRemita extends PPHelperPayment
{
	/**
	 * Performs outgoing connection
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$result	= curl_exec($ch);
		curl_close($ch);

		$response = json_decode($result, true);

		return $response;
	}

	/**
	 * Retrieves the API Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('api_key', '');
		}

		return $key;
	}

	/**
	 * Retrieves the response url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStatusUrl($orderId, $hash)
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'http://www.remita.net/remita/ecomm';

			$sandbox = $this->params->get('sandbox', false);

			if ($sandbox) {
				$url = 'http://www.remitademo.net/remita/ecomm';
			}

			$url .= '/' . $this->getMerchantId() . '/' . $orderId . '/' . $hash . '/orderstatus.reg';
		}

		return $url;
	}

	/**
	 * Retrieves the merchant id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('merchant_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the service type id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getServiceId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('service_type_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the response url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResponseUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=remita&view=payment&task=complete&action=success&payment_key=' . $paymentKey; 
		}

		return $url;
	}

	/**
	 * Retrieves the response url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'http://www.remita.net/remita/ecomm/init.reg';

			$sandbox = $this->params->get('sandbox', false);

			if ($sandbox) {
				$url = 'http://www.remitademo.net/remita/ecomm/init.reg';
			}
		}

		return $url;
	}

	/**
	 * Signs the payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function signRequest($paymentKey, $total, $responseUrl)
	{
		$string = $this->getMerchantId() . $this->getServiceId() . $paymentKey . $total . $responseUrl . $this->getApiKey();
		$hash = hash('sha512', $string);

		return $hash;
	}

	/**
	 * Signs the payload for order status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function signStatus($orderId)
	{
		$merchantId = $this->getMerchantId();
		$apiKey = $this->getApiKey();

		$payload = array($orderId, $apiKey, $merchantId);
		
		$hash = implode('', $payload);
		$hash = hash('sha512', $hash);

		return $hash;
	}
}
