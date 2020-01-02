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

class PPHelperEpay extends PPHelperPayment
{
	/**
	 * Get Merchant Number
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantNumber()
	{
		static $merchantNumber = null;

		if (is_null($merchantNumber)) {
			$merchantNumber = $this->params->get('merchant_id');
		}
		
		return $merchantNumber;
	}

	/**
	 * Get Api Password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiPassword()
	{
		static $apiPassword = null;

		if (is_null($apiPassword)) {
			$apiPassword = $this->params->get('api_password');
		}
		
		return $apiPassword;
	}

	/**
	 * Formats the amount for ePay since it only supports cents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount($amount, $reverse = false)
	{
		if ($reverse) {
			return ($amount / 100);
		}

		return ($amount * 100);
	}

	/**
	 * Retrieves the form's post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/Default.aspx';
		}

		return $url;
	}

	/**
	 * Retrieves the form's post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSuccessUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?payment_key=' . $paymentKey . '&option=com_payplans&gateway=epay&view=payment&task=complete&action=success';
		}

		return $url;
	}

	/**
	 * Retrieves the form's post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?payment_key=' . $paymentKey . '&option=com_payplans&gateway=epay&view=payment&task=complete&action=cancel';
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
		$user = $invoice->getBuyer(true);
		$paymentKey = $payment->getKey();

		$payload = array(
			'merchantNumber' => $this->getMerchantNumber(),
			'orderId' => $paymentKey,
			'amount' => $this->formatAmount($invoice->getTotal()),
			'description' => $invoice->getTitle(),
			'accepturl' => $this->getSuccessUrl($paymentKey),
			'cancelurl' => $this->getCancelUrl($paymentKey),
			'windowstate' => '3',
			'currency' => $invoice->getCurrency('isocode','DKK'),
			'subscription' => ($invoice->isRecurring() ? 1 : 0),
			'instantcapture' => '1',
			'instantcallback' => '1',
			'ownreceipt' => '1'
		); 

		return $payload;
	}

	/**
	 * Get the Reccurence Count
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

		$recurring = $invoice->isRecurring();
		if ($recurring) {

			// Recurrence Count For Regular Recurring Plan
			if ($recurring == PP_RECURRING) {
				$recurrenceCount = $invoice->getRecurrenceCount();
			}
			// Recurrence Count For Recurring + Trial 1 Plan
			if ($recurring == PP_RECURRING_TRIAL_1) {
				$recurrenceCount = $invoice->getRecurrenceCount() + 1;
			}
			// Recurrence Count For Recurring + Trial 2 Plan
			if ($recurring == PP_RECURRING_TRIAL_2) {
				$recurrenceCount = $invoice->getRecurrenceCount() + 2;
			}
		}
		return $recurrenceCount;
	}

	/**
	* Process Recurring Payment
	* @since 4.0.0
	* @access public
	*/
	public function processRecurringPayment(PPPayment $payment, $invoiceCount = 0)
	{
		$invoice = $payment->getInvoice();
		$subscriptionId = $payment->getGatewayParam('subscriptionid');

		$amount = $invoice->getTotal($invoiceCount)*100;
		$recurrenceCount = $payment->getGatewayParam('pending_recur_count');

		$epayParams = array();
		$epayParams['merchantnumber'] = $this->getMerchantNumber();
		$epayParams['subscriptionid'] = $subscriptionId;
		$epayParams['orderid'] = time();
		$epayParams['amount'] = $amount;
		$epayParams['fraud'] = "0";
		$epayParams['currency'] = "208"/*$invoice->getCurrency('isocode','DKK')*/;
		$epayParams['instantcapture'] = "1";
		$epayParams['transactionid'] = "-1";
		$epayParams['pbsresponse'] = "-1";
		$epayParams['epayresponse'] = "-1";

		$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/subscription.asmx?WSDL');

		$result = $client->authorize($epayParams);

		if (!$result->authorizeResult) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_EPAY_ERROR_RECURRING_PAYMENT'), $payment, $result, 'PayplansPaymentFormatter');
			return false;					
		}

		// Create Transaction
		$transaction = PP::createTransaction($invoice, $payment, $result->transactionid, $subscriptionId, 0, $result);

		$transaction->amount = $this->formatAmount($amount, true);
		$transaction->message = JText::_('COM_PAYPLANS_APP_EPAY_PAYMENT_COMPLETED_SUCCESSFULLY');
		$transaction->save();

		$recurrenceCount = $recurrenceCount -1;

		$params = new JRegistry();
		$params->set('pending_recur_count', $recurrenceCount);
		$params->set('subscriptionid', $subscriptionId);
		$payment->gateway_params = $params->toString();
		$payment->save();

		return true;
	}

	/**
	* Process Cancellation of Recurring Subscription
	* @since 4.0.0
	* @access public
	*/
	public function processTerminatSubscription(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();

		$subscriptionId = $payment->getGatewayParam('subscriptionid');

		// Create Transaction 
		$epayParams = array();
		$epayParams['merchantnumber'] = $this->getMerchantNumber();
		$epayParams['pwd'] = $this->getApiPassword();
		$epayParams['subscriptionid'] = $subscriptionId;
		$epayParams['epayresponse'] = "-1";
		$epayParams['pbsresponse'] = "-1";

		$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/subscription.asmx?WSDL');

		$result = $client->deletesubscription($epayParams);

		return $result;
	}

	/**
	* Process Refund Request
	* @since 4.0.0
	* @access public
	*/
	public function processRefund(PPTransaction $transaction, $amount)
	{
		$invoice = $transaction->getInvoice();
		$payment = $transaction->getPayment();

		$txnId = $transaction->getGatewayTxnId();
		$amount = ($amount*100);

		$epayParams = array();
		$epayParams['merchantnumber'] = $this->getMerchantNumber();
		$epayParams['transactionid'] = $txnId;
		$epayParams['amount'] = $amount;
		$epayParams['pbsresponse'] = "-1";
		$epayParams['epayresponse'] = "-1";

		$client = new SoapClient('https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL');

		$result = $client->credit($epayParams);

		if ($result->creditResult != true) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_EPAY_TRANSACTION_REFUND_ERROR');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $result, 'PayplansPaymentFormatter','', true);

			return false;	
		} 

		$transaction = PP::createTransaction($invoice, $payment, $txnId, 0, 0, $result);
		$transaction->amount = -($refundAmount);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_EPAY_TRANSACTION_REFUNDED';
		$transaction->save();
		
		return true;
	}
}