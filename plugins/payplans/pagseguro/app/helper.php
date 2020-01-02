<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPHelperPagseguro extends PPHelperPayment
{

	/**
	 * Load Pagseguro library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadLibrary()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$lib = __DIR__ . '/lib/PagSeguroLibrary.php';

			include_once($lib);

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Retrieves the merchant id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchant()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('merchant', '');
		}
		return $id;
	}

	/**
	 * Retrieves the token
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getToken()
	{
		static $code = null;

		if (is_null($code)) {
			$code = $this->params->get('token', '');
		}
		return $code;
	}

	/**
	 * Retrieves the redirection url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRedirectUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=pagseguro&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the cancellation url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=pagseguro&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $paymentKey;
		}

		return $url;
	}


	/**
	 * Get parameters from xml
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTransactionArray($transactionXML)
	{
		$transaction['code'] = $transactionXML->getCode();
		$transaction['email'] = $transactionXML->getSender() ? $transactionXML->getSender()->getEmail() : "null";
		$transaction['date'] = $transactionXML->getDate();
		$transaction['reference'] = $transactionXML->getReference();
		$transaction['status'] = $transactionXML->getStatus() ? $transactionXML->getStatus()->getTypeFromValue().($transactionXML->getStatus()->getValue()) : "null";
		$transaction['itemsCount'] = is_array($transactionXML->getItems()) ? count($transactionXML->getItems()) : "null";
		
		$transaction['xml_string'] = serialize($transactionXML);

		return $transaction;
	}

}