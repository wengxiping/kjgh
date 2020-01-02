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

class PPHelperEbanx extends PPHelperPayment
{
	/**
	 * Connects to external site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $payload)
	{
		// Ensure that integration_key is always sent to Ebanx
		if (!isset($payload['integration_key'])) {
			$payload['integration_key'] = $this->params->get('integration_key', '');
		}
		
		$link = new JURI($url);

		$curl = new JHttpTransportCurl(new JRegistry());
		
		$query = http_build_query($payload);
		$result = $curl->request('POST', $link, $query);

		$response = json_decode($result->body);

		return $response;
	}

	/**
	 * Retrieves the url for Ebanx
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://api.ebanx.com';
			$sandbox = $this->params->get('sandbox', false);

			if ($sandbox) {
				$url = 'https://sandbox.ebanx.com';
			}			
		}

		return $url;
	}

	/**
	 * Retrieves the API url for Ebanx
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRefundUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getApiUrl() . '/ws/refund';
		}

		return $url;
	}

	/**
	 * Retrieves the API url for Ebanx
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRequestUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getApiUrl() . '/ws/request';
		}

		return $url;
	}

	/**
	 * Retrieves the API url for Ebanx
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getQueryUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getApiUrl() . '/ws/query';
		}

		return $url;
	}

	/**
	 * Validate notification response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($response, PPPayment $payment)
	{
		if ($response['payment']->status == 'CO') {
			return true;
		}

		$message = JText::_('COM_PAYPLANS_LOGGER_EBANX_ERROR_OCCURED_IN_PAYMENT_CREATION');
		$error = $response;
		
		PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error), 'PayplansPaymentFormatter', '', true);
		return false;
	}
}
 
