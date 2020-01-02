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

class PPHelperCompay extends PPHelperPayment
{
	/**
	 * Formats amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount($amount)
	{
		$amount = number_format($amount, 2);
		return $amount;
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
			$id = $this->params->get('client_id', '');
		}
		return $id;
	}

	/**
	 * Retrieves the merchant id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStoreKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('store_key', '');
		}
		return $key;
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
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=compay&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.compayodeme.com/fim/comPayGate';

			if ($this->isSandbox()) {
				$url = 'https://cptest.asseco-see.com.tr/fim/comPayGate';
			}
		}

		return $url;
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
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=compay&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Initiate Payment Request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createPaymentRequest(PPPayment $payment, PPInvoice $invoice)
	{	
		$merchantId = $this->getMerchantId();
		$paymentKey = $payment->getKey();
		$buyer = $invoice->getBuyer();
		
		$redirectUrl = $this->getRedirectUrl($paymentKey);
		$cancelUrl = $this->getCancelUrl($paymentKey);
		
		$payload = array(
			'clientid' => $merchantId,
			'amount' => $invoice->getTotal(),
			'okurl' => $redirectUrl,
			'failUrl' => $cancelUrl,
			'TranType' => 'ComPayPayment',
			'Instalment' => '',
			'callbackUrl' => $redirectUrl,
			'shopurl' => $redirectUrl,
			'currency' => '949',
			'orderId' => $paymentKey,
			'productType' => 'B',
			'hashAlgorithm' => 'ver3',
			'lang' => 'en',
			'BillToName' => $buyer->getUsername(),
			'BillToCompany' => $buyer->getUsername()
		);

		$hash = $this->generateHash($payload);

		$payload['HASH'] = $hash;

		return $payload;
	}

	/**
	 * Given a set of data, try to compute the hash values for it
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateHash($data)
	{
		$tmp = array();

		foreach ($data as $key => $value) {
			array_push($tmp, $key);
		}
			
		natcasesort($tmp);

		$string = "";	
		foreach ($tmp as $key) {
			$value = $data[$key];
			$value = str_replace("|", "\\|", str_replace("\\", "\\\\", $value));

			$key = strtolower($key);

			if ($key != "hash" && $key != "encoding") {
				$string = $string . $value . "|";
			}
		}

		$storeKey = $this->getStoreKey();
		$escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));	
		$string = $string . $escapedStoreKey;
		
		$calculatedHashValue = hash('sha512', $string);
		$hash = base64_encode(pack('H*',$calculatedHashValue));

		return $hash;
	}


	/**
	 * Determines if it is sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', '');
		}

		return $sandbox;
	}
}