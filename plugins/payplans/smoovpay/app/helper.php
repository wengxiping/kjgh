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

class PPHelperSmoovPay extends PPHelperPayment
{
	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://secure.smoovpay.com/access';
			$sandbox = $this->params->get('sandbox', false);

			if ($sandbox) {
				$url = 'https://staging-secure.smoovpay.com/access';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			// Special case: in smoovpay we need to send payment key as first argument in success/cancel url.
			$url = rtrim(JURI::root(), '/') . '/index.php?payment_key=' . $paymentKey . '&option=com_payplans&gateway=smoovpay&view=payment&task=complete&action=cancel';
		}

		return $url;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSuccessUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			// Special case: in smoovpay we need to send payment key as first argument in success/cancel url.
			$url = rtrim(JURI::root(), '/') . '/index.php?payment_key=' . $paymentKey . '&option=com_payplans&gateway=smoovpay&view=payment&task=complete&action=success';
		}

		return $url;
	}

	/**
	 * Signs the payload by generating a signature
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sign($payload)
	{
		$key = $this->params->get('secret_hash', 0);
		$merchantId = $this->params->get('merchant_id', '');

		$string = $key . $merchantId . 'pay' . implode('', $payload);
		
		// Ensure that the string is in UTF-8 encoding
		$string = mb_convert_encoding($string, 'UTF-8');
		$signature = sha1($string, false);

		return $signature;
	}

	/**
	 * Validates a payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($data, PPInvoice $invoice) 
	{
		if (!$data) {
			return array(false, JText::_('Invalid response from SmoovPay'));	
		}

		$payload = array(
			$this->params->get('secret_hash', 0),
			PP::normalize($data, 'merchant', ''),
			PP::normalize($data, 'ref_id', ''),
			PP::normalize($data, 'reference_code', ''),
			PP::normalize($data, 'response_code', ''),
			PP::normalize($data, 'currency', ''),
			PP::normalize($data, 'total_amount', '')
		);

		$string = implode('', $payload);
		$string = mb_convert_encoding($string, 'UTF-8');
		$signature = sha1($string, false);

		// Ensure that the signature matches
		if ($signature !== PP::normalize($data, 'signature', '')) {
			return array(false, JText::_('Received signature does not match with original signature'));
		}

		// Ensure that the merchant matches correctly
		if (PP::normalize($data, 'merchant', '') != $this->params->get('merchant_id', '')) {
			return array(false, JText::_('Merchant e-mail address does not match'));
		}

		// Ensure amount paid is correct
		if (PP::normalize($data, 'total_amount', 0) != $invoice->getTotal()) {
			return array(false, JText::_('Amount total did not match in response'));
		}

		// Ensure amount paid is in correct currency
		if (PP::normalize($data, 'currency', 0) != $invoice->getCurrency('isocode', 'USD')) {
			return array(false, JText::_('Amount total did not match in response'));
		}

		//Check for currency
		if ($invoice->getCurrency('isocode','SGD') != PP::normalize($data, 'currency', 0)) {
			return array(false, JText::_('Currency does not match with the initial currency'));
		}

		return array(true, JText::_('No Error'));
	}
}