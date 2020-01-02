<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPHelperPayUnity extends PPHelperPayment
{
	/**
	 * Determines if it is running in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox') ? true : false;
		}

		return $sandbox;
	}

	/**
	 * Retrieves the main url for payunity
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDomain()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://oppwa.com';
			$sandbox = $this->isSandbox();

			if ($sandbox) {
				$url = 'https://test.oppwa.com';
			}
		}

		return $url;		
	}

	/**
	 * Retrieves the script url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCheckoutUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getDomain() . '/v1/checkouts';
		}

		return $url;
	}

	/**
	 * Retrieve the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=payunity&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}
		return $url;
	}
	
	/**
	 * Retrieves the script url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getScriptUrl($checkoutId)
	{
		static $url = null;

		if (is_null($url)) {
			$url = $this->getDomain() . '/v1/paymentWidgets.js?checkoutId=' . $checkoutId;
		}

		return $url;
	}

	/**
	 * Retrieves the script url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getChannelId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('channel_id');
		}

		return $id;
	}

	/**
	 * Retrieves the script url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('user_id');
		}

		return $id;
	}

	/**
	 * Retrieves the script url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantPassword()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('user_pwd');
		}

		return $id;
	}

	/**
	 * Proper way of getting the recurring count. If unlimited, authorize.net requires it to be 9999
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();
		if (intval($count) === 0){
			return 9999;
		}

		$recurring = $invoice->isRecurring();

		// Recurrence Count For Regular Recurring Plan
		if($recurring == PP_RECURRING){
			$count = $invoice->getRecurrenceCount();
		}

		// Recurrence Count For Recurring + Trial 1 Plan
		if($recurring == PP_RECURRING_TRIAL_1){
			$count = $invoice->getRecurrenceCount() + 1;
		}

		// Recurrence Count For Recurring + Trial 2 Plan
		if($recurring == PP_RECURRING_TRIAL_2){
			$count = $invoice->getRecurrenceCount() + 2;
		}
				
		return $count;
	}


	/**
	 * Request checkout id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCheckoutId(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();
		$customer = $invoice->getBuyer(true);

		$requestUrl = $this->getCheckoutUrl();

		$payload = array(
			'authentication.userId' => $this->getMerchantId(),
			'authentication.password' => $this->getMerchantPassword(),
			'authentication.entityId' => $this->getChannelId(),
			'amount' => $invoice->getTotal(),
			'currency' => $invoice->getCurrency('isocode','EUR'),
			'paymentType' => 'DB',
			'merchantTransactionId' => $payment->getKey(),
			'customer.email' => $customer->getEmail(),
			'customer.givenName' => $customer->getName()
		);

		if ($invoice->isRecurring()) {
			$payload['recurringType'] = 'INITIAL';
			$payload['createRegistration'] = 'true';
		}

		$sslPear = true;
		if ($this->isSandbox()) {
            $sslPear = false;
        } 

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslPear);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$result = curl_exec($ch);
		
		if (curl_errno($ch)) {
			return curl_error($ch);
		}

		curl_close($ch);

		$response = json_decode($result);
		return $response->id;
	}

	/**
	 * Request checkout id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processResponse(PPPayment $payment, $data)
	{
		$resourcePath = PP::normalize($data, 'resourcePath', 0);
		$merchantId  = $this->getMerchantId();
		$merchantPwd = $this->getMerchantPassword();
		$channelId = $this->getChannelId();
		$requestUrl = $this->getDomain().$resourcePath;
		$requestUrl .= "?authentication.userId=".$merchantId."&authentication.password=".$merchantPwd."&authentication.entityId=".$channelId;

		$sslPear = true;
		if ($this->isSandbox()) {
            $sslPear = false;
        } 

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslPear);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			return curl_error($ch);
		}

		curl_close($ch);

		$response = json_decode($result);

		return $response;
	}

	/**
	 * Validate response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkResponse(PPPayment $payment, $response) 
	{
		$invoice = $payment->getInvoice();
		$amount  = $invoice->getTotal();

		if ($amount == $response->amount && $payment->getKey() == $response->merchantTransactionId && $response->paymentType =='DB') {
			return array(true, JText::_('COM_PAYPLANS_APP_PAYUNITY_NO_ERROR'));
		} 
		
		return array(false, JText::_('COM_PAYPLANS_APP_PAYUNITY_RESPONSE_ERROR'));
	}

	/**
	 * Request checkout id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();

		$payload = array(
			'authentication.userId' => $this->getMerchantId(),
			'authentication.password' => $this->getMerchantPassword(),
			'authentication.entityId' => $this->getChannelId(),
			'amount' => $invoice->getTotal(),
			'currency' => $invoice->getCurrency('isocode','EUR'),
			'paymentType' => 'DB',
			'recurringType' => 'REPEATED'
		);

		$sourceOrder = $payment->getGatewayParam('reference_id');
		$lifetime = ($invoice->getRecurrenceCount() == 0)? true : false;
		$invoiceCount  = $invoiceCount +1;

		$recurrenceCount = $payment->getGatewayParam('pending_recur_count');

		if ($recurrenceCount > 0 || $lifetime) {

			$url = $this->getDomain();
			$url = $url . "v1/registrations/" .$sourceOrder."/payments";

			$sslPear = true;
			if ($this->isSandbox()) {
	            $sslPear = false;
	        }

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslPear);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);
			
			if (curl_errno($ch)) {
				return curl_error($ch);
			}

			curl_close($ch);

			$response = json_decode($result);
			return $response;
		}
	}

}