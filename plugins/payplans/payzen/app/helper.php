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

class PPHelperPayzen extends PPHelperPayment
{
	/**
	 * Retrieves the Shop id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getShopId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('shop_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the Currency Code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrencyCode()
	{
		static $code = null;

		if (is_null($code)) {
			$code = $this->params->get('currency_code', '');
		}

		return $code;
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

	/** 
	 * Retrive Transaction Mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCtxMode()
	{
		static $mode = null;

		if (is_null($mode)) {
			$mode = 'PRODUCTION';

			if ($this->params->get('sandbox', '')) {
				$mode = 'TEST';
			}
		}

		return $mode;
	}

	/** 
	 * Retrive Certificate
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCertificate()
	{
		static $certificate = null;

		if (is_null($certificate)) {
			$certificate = $this->params->get('certificate', '');
		}

		return $certificate;
	}

	/**
	 * Retrieves the payment form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = "https://secure.payzen.eu/vads-payment/";
		}

		return $url;
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
		$date = new DateTime('now', new DateTimeZone('UTC'));

		$payload = array(
			'vads_site_id' => $this->getShopId(),
			'vads_ctx_mode' => $this->getCtxMode(),
			'vads_trans_id' => rand(000000 , 899999),
			'vads_order_id' => $payment->getKey(),
			'vads_trans_date' => $date->format('YmdHis'),
			'vads_amount' => round($invoice->getTotal()),
			'vads_currency' => $this->getCurrencyCode(),
			'vads_version' => 'V2',
			'vads_return_mode' => 'POST',
			'vads_payment_config' => 'SINGLE',
			'vads_action_mode' => 'INTERACTIVE'
		);

		if ($invoice->isRecurring()) {
			$payload = $this->getRecurringPaymentRequestPayload($payment, $invoice, $payload);		
		} else {
			$payload['vads_page_action'] = 'PAYMENT';
		} 	

		$payload['signature'] = $this->createSignature($payload);

		return $payload;
	}

	/**
	 * Generates the signature
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFrequency($expTime)
	{
		$expTime['year'] = PP::normalize($expTime, 'year', 0);
		$expTime['month'] = PP::normalize($expTime, 'month', 0);
		$expTime['day'] = PP::normalize($expTime,'day', 0);

		$frequency = "";
		if ($expTime['day']) {
			$frequency = "DAILY";
		} 

		if ($expTime['month']) {
			$frequency = "MONTHLY";
		}

		if ($expTime['year']) {
			$frequency = "YEARLY";
		}

		return $frequency;
	}

	/**
	 * Set other params needed for the payload for recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringPaymentRequestPayload($payment, $invoice, $payload)
	{
		$userId = $payment->getBuyer();
		$user = PP::user($userId);

		$expTime = $invoice->getExpiration(PP_RECURRING);
		$frequency = $this->getFrequency($expTime);

		$recurrenceCount = $invoice->getRecurrenceCount();
		$paymentConfig = "RRULE:FREQ=".$frequency.";count=".$recurrenceCount;

		$date = new DateTime('now', new DateTimeZone('UTC'));

		$recurringPayload = array(
			'vads_page_action' => 'REGISTER_PAY_SUBSCRIBE',
			'vads_cust_email' => $user->getEmail(),
			'vads_sub_amount' => round($invoice->getTotal()),
			'vads_sub_currency' => $this->getCurrencyCode(),
			'vads_sub_desc' => $paymentConfig,
			'vads_sub_effect_date' => $date->format('Ymd')
		);

		$payload = array_merge($payload, $recurringPayload);
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
  		$key = $this->getCertificate();
  		$contenuSignature = "" ;

	    ksort($data);
        foreach ($data as $nom => $valeu) { 

	        if (substr($nom,0,5)=='vads_') { 
	            $contenuSignature .= $valeu."+";
	        }
	    }

		$contenuSignature .= $key;
		$signature = sha1($contenuSignature);

		return $signature ;
  	}

}

