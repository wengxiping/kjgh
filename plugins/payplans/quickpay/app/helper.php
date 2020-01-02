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

class PPHelperQuickpay extends PPHelperPayment
{
	/**
	 * Creates an outgoing connection to quickpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($payload, $url)
	{
		$headers = array(
			"Authorization: Basic " . base64_encode(":".$this->getUserApiKey()),
			"Accept-Version: v10"
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		$response = curl_exec($ch);

		return $response;
	}

	/**
	 * Creates a new refund transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createRefundTransaction(PPInvoice $invoice, PPPayment $payment, $amount)
	{
		$gatewayParams = $payment->getGatewayParams();
		$subscriptionId = $gatewayParams->get('transaction_id');

		$transactionId = $response->id;
		$parentId = $response->id;

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentId, (array) $response);
		$transaction->amount = -($amount / 100);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_QUICKPAY_REFUND_SUCCESS';

		return $transaction;
	}

	/**
	 * Formats the amount so that it is compatible with Quickpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount($amount, $reverse = false)
	{
		if ($reverse) {
			$amount = ($amount / 100);

			return $amount;
		}

		//IMP: transaction amount must be in its smallest unit
		$amount = number_format($amount, 2, '.', '') * 100;

		return $amount;
	}

	/**
	 * Retrieve the API Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserApiKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('apiuserkey', '');
		}

		return $key;
	}

	/**
	 * Retrieve the API endpoint url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEndpointUrl($type, $transactionId)
	{
		if ($type == 'cancel') {
			return "https://api.quickpay.net//subscriptions/" . $transactionId . "/cancel?synchronized";
		}


		if ($type == 'refund') {
			return "https://api.quickpay.net//payments/" . $transactionId . "/refund";
		}

		if ($type == 'recurring') {
			return "https://api.quickpay.net//subscriptions/" . $transactionId . "/recurring";
		}
	}

	/**
	 * Retrieve the API Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('payment_key', '');
		}

		return $key;
	}

	/**
	 * Retrieve the API Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAgreementId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('agreement_id', '');
		}

		return $id;
	}

	/**
	 * Retrieve the API Key
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
	 * Method to get the cancel url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=quickpay&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Method to get the IPN url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNotifyUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=quickpay&view=payment&task=notify';
		}

		return $url;
	}

	/**
	 * Method to get the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSuccessUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=quickpay&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Generates the recurrence count given the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();
		
		if (intval($count) === 0) {
			return 9999;
		}
		
		if (!$invoice->isRecurring()) {
			return 0;
		}

		$type = $invoice->getRecurringType(true);

		// Recurrence Count For Regular Recurring Plan
		if ($type == PP_RECURRING){
			$count = $invoice->getRecurrenceCount();
		}

		// Recurrence Count For Recurring + Trial 1 Plan
		if ($type == PP_RECURRING_TRIAL_1){
			$count = $invoice->getRecurrenceCount() + 1;
		}

		// Recurrence Count For Recurring + Trial 2 Plan
		if ($type == PP_RECURRING_TRIAL_2){
			$count = $invoice->getRecurrenceCount() + 2;
		}

		return $count;
	}

	/**
	 * Generates  A MD5 checksum to ensure data integrity. 
	 * See http://tech.quickpay.net/payments/form/ for more information.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateChecksum($payload, $key)
	{
		ksort($payload);

		$string = implode(" ", $payload);

		return hash_hmac("sha256", $string, $key);

	}

	/**
	 * Processes recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringPayment(PPPayment $payment, $invoice, $amount)
	{
		$deductCount = 1;
		$gatewayParams = $payment->getGatewayParams();

		// Send request to quickpay to make a withdrawal from subscription
		$response = $this->withdrawFromSubscription($payment, $invoice, $amount);

		// As one payment has been made so update pending_recur_count parameter of the payment
		$pendingRecurringCount = ($gatewayParams->get('pending_recur_count') - $deductCount);
		$gatewayParams->set('pending_recur_count', $pendingRecurringCount);

		$payment->gateway_params = $gatewayParams->toString();
		return $payment->save();
	}

	/**
	 * Validates the response provided by Quickpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($response, PPPayment $payment)
	{
		if ($response->state == 'active' || $response->state == 'processed') {
			return true;
		}

		if ($response->state == 'pending') {
			return false;
		}

		$message = JText::_('COM_PAYPLANS_LOGGER_QUICKPAY_ERROR_OCCURED_IN_PROFILE_CREATION');
		PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array($response), 'PayplansPaymentFormatter', '', true);

		return false;
	}

	/**
	 * Withdraw subscription from quickpay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function withdrawFromSubscription(PPPayment $payment, PPInvoice $invoice, $amount)
	{
		$gatewayParams = $payment->getGatewayParams();

		$date = JFactory::getDate();

		$payload = array(
			'order_id' => $payment->getId() . '_' . $date->toUnix(),
			'amount' => $amount,
			'id' => $gatewayParams->get('transaction_id'),
			'auto_capture' => 1
		);
		
		$checksum = $this->generateChecksum($payload, $this->getUserApiKey());
		$payload['checksum'] = $checksum;

		return $this->connect($payload, $this->getEndpointUrl('recurring', $payload['id']));
	}
}