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

class PPHelperAuthorizeCIM extends PPHelperPayment
{
	/**
	 * Retrieves the Login Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLoginId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('api_login_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the Transaction Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTransactionKey()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('transaction_key', '');
		}

		return $id;
	}

	/**
	 * Retrieve the payload xml contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayloadData()
	{
		static $data = null;

		if (is_null($data)) {
			$data = new stdClass();
			$data->loginId = $this->getLoginId();
			$data->transactionKey = $this->getTransactionKey();
		}

		return $data;
	}

	/**
	 * Retrieve the payload xml contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayloadContents($file, $data)
	{
		ob_start();
		require_once(__DIR__ . '/payloads/' . $file);
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Generates the proper expiration date needed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpirationDate($month, $year)
	{
		$expiration = trim($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));

		return $expiration;
	}

	/**
	 * Method to retrieve the recurrence count for authorize CIM
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

		if (!$recurring) {
			return $count;
		}

		$type = $invoice->getRecurringType();

		// Recurrence Count For Regular Recurring Plan
		if ($type == PP_RECURRING) {
			$count = $invoice->getRecurrenceCount();
		}

		// Recurrence Count For Recurring + Trial 1 Plan
		if ($type == PP_RECURRING_TRIAL_1) {
			$count = $invoice->getRecurrenceCount() + 1;
		}

		// Recurrence Count For Recurring + Trial 2 Plan
		if ($type == PP_RECURRING_TRIAL_2) {
			$count = $invoice->getRecurrenceCount() + 2;
		}

		return $count;
	}

	/**
	 * Given the response, try to get the transaction params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTransactionParams($response)
	{
		$items = $response->results;

		$params = new JRegistry();

		foreach ($items as $key => $value) {
			$params->set('param' . $key, $value);
		}

		return $params;
	}

	/**
	 * Creates a new customer on Authorize.net
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createCustomer($payment, $invoice, $data)
	{
		$date = JFactory::getDate();

		$payloadData = $this->getPayloadData();

		$payloadData->paymentKey = $payment->getKey();
		$payloadData->title = JText::sprintf('%1$s Time:(%2$s)', $invoice->getTitle(), $date->toSql());
		$payloadData->post = $data;
		$payloadData->expiration = $this->getExpirationDate($data['exp_month'], $data['exp_year']);

		$payload = $this->getPayloadContents('create.customer.php', $payloadData);
		$response = $this->process($payload, 'create', $payment);
		
		return $response;
	}

	/**
	 * Create a new customer transaction on Authorize.Net
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createTransaction(PPInvoice $invoice, PPPayment $payment, $amount, $customerProfileId, $customerPaymentId, $connection_faliure = 0, $auth_faliure = 0)
	{
		$payloadData = $this->getPayloadData();
		$payloadData->amount = $amount;
		$payloadData->customerProfileId = $customerProfileId;
		$payloadData->customerPaymentId = $customerPaymentId;
		$payloadData->invoiceId = $invoice->getId();
		$payloadData->invoiceTitle = $invoice->getTitle();
		
		$payload = $this->getPayloadContents('create.transaction.php', $payloadData);
		$response = $this->process($payload, 'transaction', $payment);

		return $response;
	}

	/**
	 * Retrieves data about customer from Authorize CIM
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomer($customerProfileId, PPPayment $payment)
	{
		$payloadData = $this->getPayloadData();
		$payloadData->customerProfileId = $customerProfileId;

		$payload = $this->getPayloadContents('get.customer.php', $payloadData);
		$response = $this->process($payload, 'get', $payment);

		return $response;
	}

	/**
	 * Deletes customer data from Authorize CIM
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteCustomer($payment, $customerProfileId, $customerPaymentId)
	{
		$payloadData = $this->getPayloadData();
		$payloadData->customerProfileId = $customerProfileId;
		$payloadData->customerPaymentId = $customerPaymentId;

		$payload = $this->getPayloadContents('delete.customer.php', $payloadData);
		$response = $this->process($payload, 'delete', $payment);
		
		return $response;
	}
	

	/**
	 * Retrieves the URL for API end point
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$domain = $this->params->get('sandbox', false) ? 'apitest' : 'api';
			$url = 'https://' . $domain . '.authorize.net/xml/v1/request.api';
		}
		
		return $url;
	}

	/**
	 * Connects to the remote API services
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process($xml = '', $method = '', $payment)
	{
		$url = $this->getApiUrl();

		$headers = array(
			'Content-Type: text/xml'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		$response = curl_exec($ch);
		
		$result = new stdClass();
		$result->resultCode = 'ConnectionFailed';

		if (!$response) {
			$result->code = curl_errno($ch);
			$result->text = curl_error($ch);

			return $result;
		}


		$response = str_replace('xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $response);
		$xml = new SimpleXMLElement($response);

		$result->code = (string) $xml->messages->message->code;
		$result->text = (string) $xml->messages->message->text;
		$result->resultCode = (string) $xml->messages->resultCode;

		if ($method == 'create') {
			$result->profileId = (int) $xml->customerProfileId;
			$result->paymentProfileId = (int) $xml->customerPaymentProfileIdList->numericString;
		}

		if ($method == 'get') {
			$result->profileId = (int) $xml->profile->customerProfileId;
			$result->paymentProfileId = (int) $xml->profile->paymentProfiles->customerPaymentProfileId;
			$result->results = explode(',', $xml->directResponse);				
		}

		if ($method == 'transaction') {
			$result->results = explode(',', $xml->directResponse);				
		}

		curl_close($ch);
		unset($ch);
			
		return $result;
	}

	/**
	 * Process recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringPayments(PPPayment $payment, PPInvoice $invoice, $amount, $recurrenceCount, $customerProfileId, $customerPaymentId, $params = null, $resultCode = '')
	{
		$pendingRecurrenceCount = $recurrenceCount - 1;
		$message = 'COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_CIM_PAYMENT_COMPLETED_SUCCESSFULLY';

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->amount = $amount;
		$transaction->gateway_txn_id = $params ? $params->get('param6', 0) : 0;
		$transaction->gateway_subscr_id = $customerProfileId;
		$transaction->gateway_parent_txn = $customerPaymentId;
		$transaction->message = $message;
		$transaction->params = $params ? $params->toString() : '';
		$transaction->save();

		// IMPORTANT: If payment arrives after certain failures then reset the failure counter
		$gatewayParams = $payment->getGatewayParams();

		$gatewayParams->set('pending_recur_count', $pendingRecurrenceCount);
		$gatewayParams->set('profile_id', $customerProfileId);
		$gatewayParams->set('connection_faliure_attempt', 0);
		$gatewayParams->set('auth_faliure_attempt', 0);

		$payment->gateway_params = $gatewayParams->toString();
		$payment->save();
	}
}