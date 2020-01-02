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

class PPHelperPayUMoney extends PPHelperPayment
{
	/**
	 * Retrieve the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://secure.payu.in/_payment';

			if ($this->params->get('sandbox')) {
				$url = 'https://test.payu.in/_payment';
			}
		}

		return $url;
	}

	/**
	 * Generate the hash
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getHash($transactionKey, $amount, $productInfo, $userName, $email, $userId, $status = null, $reverse = false)
	{
		$key = $this->params->get('merchant_key', '');
		$salt = $this->params->get('salt', '');

		// Do not change the ordering of these items
		$data = array(
			$key,
			$transactionKey,
			$amount,
			$productInfo,
			$userName,
			$email,
			$userId
		);

		if (!$reverse) {
			$string = implode('|', $data);
			$string .= '||||||||||' . $salt;

			$hash = strtolower(hash('sha512', $string));
			return $hash;
		}

		// Reversing the hash
		$payload = array_reverse($data);
		$string = implode('|', $payload);
		$string = $salt . '|' . $status . '||||||||||' . $string;
		$hash = strtolower(hash('sha512', $string));
		return $hash;
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
			$url = rtrim(JURI::root(), '/') . '/index.php?&option=com_payplans&payment_key='.$payment->getKey().'&gateway=payumoney&view=payment&task=complete&action=cancel';
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
			$url = rtrim(JURI::root(), '/') . '/index.php?&option=com_payplans&payment_key='.$payment->getKey().'&gateway=payumoney&view=payment&task=complete&action=success';
		}

		return $url;
	}

	/**
	 * Validate notification response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($data, PPInvoice $invoice)
	{
		if (!$data) {
			return array(false, JText::_('Error in payment response or response not valid'));
		}

		$salt = $this->params->get('salt', '');
		$status = PP::normalize($data, 'status', '');

		// IMPORTANT! Do not change the ordering of the following payload.
		$payload = array(
			'userId' => PP::normalize($data, 'udf1', ''),
			'email' => PP::normalize($data, 'email', ''),
			'userName' => PP::normalize($data, 'firstname', ''),
			'productInfo' => PP::normalize($data, 'productinfo', ''),
			'amount' => PP::normalize($data, 'amount', ''),
			'transactionId' => PP::normalize($data, 'txnid', ''),
			'key' => PP::normalize($data, 'key', '')
		);

		$payloadString = implode('|', $payload);

		$string = $salt . '|' . $status . '||||||||||' . $payloadString;
		$generatedHash = strtolower(hash('sha512', $string));

		$hash = PP::normalize($data, 'hash', '');

		// signature hash does not matched
		if ($generatedHash !== $hash) {
			return array(false, JText::_('Hash did not match'));
		}

		return array(true, JText::_('No Error'));
	}
}
