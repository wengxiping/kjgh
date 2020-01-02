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

class PPHelperMonetaweb extends PPHelperPayment
{
	/**
	 * Retrieve the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiEndpoint()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.monetaonline.it/monetaweb/payment/2/xml';

			if ($this->params->get('sandbox')) {
				$url = 'https://test.monetaonline.it/monetaweb/payment/2/xml';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the Merchat Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the Merchat Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPassword()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('password', '');
		}

		return $id;
	}

	/**
	 * Retrieve success redirection url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl(PPPayment $payment)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?&option=com_payplans&payment_key='.$payment->getKey().'&gateway=monetaweb&view=payment&task=complete&action=cancel';
		}

		return $url;
	}

	/**
	 * Retrieve success redirection url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSuccessUrl(PPPayment $payment)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?&option=com_payplans&payment_key='.$payment->getKey().'&gateway=monetaweb&view=payment&task=complete&action=success';
		}

		return $url;
	}

	/**
	 * Retrieve notify url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNotifyUrl(PPPayment $payment)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?&option=com_payplans&payment_key='.$payment->getKey().'&gateway=monetaweb&view=payment&task=notify';
		}

		return $url;
	}

	/**
	 * Get payment request data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayload(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		$userId = $payment->getBuyer();
		$user = PP::user($userId);

		$language = JFactory::getLanguage();
		$tag = $language->getTag();

		// We need the "gb" from en-GB as PayPal does not require the "en" portion
		$data = explode('-', $tag);
		$lang = $data[0];

		$payload = array(
					'id' => $this->getMerchantId(),
					'password' => $this->getPassword(),
					'operationType' => 'initialize',
					'amount' => $amount,
					'language' => $lang,
					'responseToMerchantUrl' => $this->getNotifyUrl($payment),
					'recoveryUrl' => $this->getSuccessUrl($payment),
					'merchantOrderId' => $payment->getKey(),
					'cardHolderEmail' => $user->getEmail()
					);
		return $payload;
	}

	/**
	 * Connects to PayFlow API
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($payload)
	{
		$query = http_build_query($payload);
	
		$url = $this->getApiEndpoint();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

		$result = curl_exec($ch);
		$response = (array)simplexml_load_string($result);

		return $response;
	}

	/**
	 * Validate response from payment gateway
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validateResponse($response, $payment)
	{
		if ($response['result'] == 'CAPTURED' && $response['responsecode'] == '000') {
			return true;
		} else {

			$message = JText::_('COM_PAYPLANS_LOGGER_MONETAWEB_ERROR_OCCURED_IN_PAYMENT_CREATION');
			PPLog::log(PPLogger::LEVEL_ERROR, urldecode($message), $payment, $response, 'PayplansPaymentFormatter', '', true);
		}

		return false;
	}
}
