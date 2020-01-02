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

class PPHelperPayuPl extends PPHelperPayment
{
	/**
	 * Load stripe's library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadLibrary()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$lib = __DIR__ . '/lib/openpayu.php';

			include_once($lib);

			OpenPayU_Configuration::setEnvironment('secure');
			OpenPayU_Configuration::setMerchantPosId($this->getPosId());
    		OpenPayU_Configuration::setSignatureKey($this->getSecretKey());

			$loaded = true;
		}

		return $loaded;
	}

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
			$url = 'https://secure.payu.com/api/v2_1/orders';
		}

		return $url;
	}

	/**
	 * Retrieve the POS Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPosId()
	{
		static $posId = null;

		if (is_null($posId)) {
			$posId = $this->params->get('pos_id', '');
		}
		return $posId;
	}

	/**
	 * Retrieve the Secret Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSecretKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('secret_key', '');
		}
		return $key;
	}

	/**
	 * Prepares the callback urls
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCallbackUrls(PPPayment $payment)
	{
		static $callbacks = null;

		if (is_null($callbacks)) {
			$config = PP::config();
			$root = JURI::root();

			$callbacks = array(
				'return' => $root . 'index.php?option=com_payplans&gateway=payupl&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'notify' => $root . 'index.php?option=com_payplans&gateway=payupl&view=payment&task=notify&payment_key=' . $payment->getKey()
			);
		}
		
		return $callbacks;
	}


	/**
	 * Generates the payload for payment request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentRequestPayload(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();
		$description = $invoice->getTitle();
		$callBacks = $this->getCallbackUrls($payment);

		$payload['notifyUrl'] = $callBacks['notify'];
		$payload['continueUrl'] = $callBacks['return'];
		$payload['customerIp'] = $_SERVER['REMOTE_ADDR'];
		$payload['merchantPosId'] = $this->getPosId();
		$payload['description'] = $invoice->getTitle();
		$payload['currencyCode'] = $invoice->getCurrency('isocode', 'PLN');
		$payload['totalAmount'] = $invoice->getTotal()*100;
		$payload['extOrderId'] = $payment->getKey();
		$payload['products'][0]['name'] = $invoice->getTitle();
		$payload['products'][0]['unitPrice'] = $invoice->getTotal()*100;
		$payload['products'][0]['quantity'] = 1;
	
		$payload['signature'] = $this->createSignature($payload);
		return $payload;
	}

	/**
	 * Generates the signature
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createSignature($data = array())
  	{
  		$formFieldValuesAsArray = array();
        $htmlFormFields = OpenPayU_Util::convertArrayToHtmlForm($data, '', $formFieldValuesAsArray);

        $signature = OpenPayU_Util::generateSignData(
            $formFieldValuesAsArray,
            OpenPayU_Configuration::getHashAlgorithm(),
            OpenPayU_Configuration::getMerchantPosId(),
            OpenPayU_Configuration::getSignatureKey()
        );

        return $signature;
  	}
}
