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

define('PAYPLANS_PAYFLOW_SETTLEMENT_PENDING', 6);
define('PAYPLANS_PAYFLOW_SETTLEMENT_SUCCESS', 8);
define('PAYPLANS_PAYFLOW_ERROR',1);
define('PAYPLANS_PAYFLOW_FAILED_TO_SETTLE',11);
define('PAYPLANS_PAYFLOW_SETLEMENT_INCOMPLETE', 14);

class PPHelperPayflow extends PPHelperPayment
{
	/**
	 * Retrieves the vendor name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserId()
	{
		static $user = null;

		if (is_null($user)) {
			$user = $this->params->get('user', $this->getVendorId());
		}

		return $user;
	}

	/**
	 * Retrieves the vendor name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPartner()
	{
		static $partner = null;

		if (is_null($partner)) {
			$partner = $this->params->get('partner', '');
		}

		return $partner;
	}

	/**
	 * Retrieves the vendor name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('password', '');
		}

		return $password;
	}

	/**
	 * Retrieves the vendor name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getVendorId()
	{
		static $vendor = null;

		if (is_null($vendor)) {
			$vendor = $this->params->get('vendor', '');
		}

		return $vendor;
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
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=payflow&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Method to get the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Determines if we are on a sandbox environment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandboxTesting', false);
		}

		return $sandbox;
	}

	/**
	 * Connects to PayFlow API
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($transactionType, $payload, PPPayment $payment)
	{
		$payload = array_merge(array(
			'TRXTYPE' => $transactionType,
			'PARTNER' => $this->getPartner(),
			'VENDOR' => $this->getVendorId(),
			'USER' => $this->getUserId(),
			'PWD' => $this->getPassword()
		), $payload);

		$query = http_build_query($payload);
	
		$url = $this->getApiEndpoint();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

		$result = curl_exec($ch);
		
		if (!$result) {
			$error = curl_error($ch).'('.curl_errno($ch).')';
			
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_FAILED_MESSAGE'), $payment, array($error));
			return false;
		}

		// Extract the response details.
		parse_str($result, $response);
		
		$resultCode = PP::normalize($response, 'RESULT', null);

		if (!$response || is_null($resultCode)) {
			$error = "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.";
			$message = "$methodName_ ".JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_FAILED_MESSAGE');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error));
		}
	
		return $response;
	}

	/**
	 * Creates a new transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createNewTransaction(PPInvoice $invoice, PPPayment $payment, $response, $type = 'fixed')
	{
		// when profile creation of fixed payment is there then unique key is stored in PNREF and in case of
		// recurring, it is in RPREF.
		$transactionId = PP::normalize($response, 'PNREF', 0);

		if ($type == 'recurring') {
			$transactionId = PP::normalize($response, 'RPREF', 0);
		}

		$resultCode = PP::normalize($response, 'RESULT', '');
		$message = PP::normalize($response, 'RESPMSG', '');

		// Payment request failed
		if ($resultCode != 0 || $message != 'Approved') {
			$errorMessage = JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_FIXED_PAYMENT_NOT_COMPLETED');

			$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $response);
			$transaction->amount = 0;
			$transaction->message = $errorMessage;
			$transaction->save();

			$this->setError($errorMessage . ' (' . $resultCode . ')');
			return false;
		}

		// Otherwise, we assume that the transaction was approved
		if ($resultCode == 0 && $message == 'Approved') {
			$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $response);
			$transaction->amount = PP::normalize($response, 'AMT', 0);
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYFLOW_PAYMENT_COMPLETED_SUCCESSFULLY';

			$state = $transaction->save();

			if (!$state) {
				$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD');
				PP::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
			}
		}

		return true;
	}

	/**
	 * Create a new recurring profile with PayFlow
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createNewRecurringProfile(PPInvoice $invoice, PPPayment $payment, $payload)
	{
		$counter = $invoice->getCounter();
		$regularAmount = $invoice->getTotal($counter + 1);

		$now = PP::date();

		$recurringType = $invoice->getRecurringType(true);

		// In case of 0 price of trial set optional transaction as A(Authorization) else
		// set it to S(Sales)
		if ($recurringType == PP_RECURRING_TRIAL_1) {

			$amount = $invoice->getTotal();
			$optionalTransaction = 'S';
			
			if ($amount == '0.00') {
				$optionalTransaction = 'A';
			}

			$payload['OPTIONALTRX'] = $optionalTransaction;
			$payload['OPTIONALTRXAMT'] = urlencode($amount);
			
            $expTime = $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_1, true);
			$now = $now->addExpiration($expTime, true);
		}

		if ($recurringType != PP_RECURRING_TRIAL_1)  {
			$now = $now->addExpiration('000001000000');
		}

		$expirationTime = $invoice->getExpiration(PP_PRICE_RECURRING);
		$expirationTime = $this->getRecurrenceTime($expirationTime);
	
		$billingPeriod = $expirationTime['period'];
		$billingCycle = $invoice->getRecurrenceCount();

		$firstName = PP::normalize($payload, 'FIRSTNAME', '');

		$payload = array_merge(array(
			'TENDER' => 'C',
			'ACTION' => 'A',
			'PROFILENAME' => urlencode($firstName),
			'AMT' => urlencode($regularAmount),
			'START' => urlencode($now->format('mdY')),
			'PAYPERIOD' => urlencode($billingPeriod),
			'TERM' => urlencode($billingCycle)
		), $payload);
		
		$response = $this->connect('R', $payload, $payment);

		$resultCode = PP::normalize($response, 'RESULT', '');
		$resultMessage = PP::normalize($response, 'RESPMSG', '');
		$transactionId = PP::normalize($response, 'PROFILEID', 0);

		if ($resultCode != 0 || $resultMessage != 'Approved' || !$transactionId) {
			$error = JText::_('COM_PAYPLANS_APP_PAYFLOW_RECURRING_PROFILE_REJECTED');
			PPLog::log(PPLogger::LEVEL_ERROR, urldecode($resultMessage), $payment, $response, 'PayplansPaymentFormatter', '', true);

			$this->setError($resultMessage . ' (' . $resultCode . ')');
			return false;
		}

		return $response;
	}

	/**
	 * Retrieves the API endpoint
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiEndpoint()
	{
		static $url = null;

		if (is_null($url)) {
			$url = "https://payflowpro.paypal.com";
			
			if ($this->isSandbox()) {
				$url = "https://pilot-payflowpro.paypal.com";
			}
		}
		return $url;
	}

	/**
	 * Generates the payload based on the data provided
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPayload($data)
	{
		$expDate = PP::normalize($data, 'EXPDATE', '');
		$expYear = PP::normalize($data, 'EXPYEAR', '');
		$expiryDate = PP::normalizeCardExpiry($expDate, $expYear);

		$custIP = PP::normalize($data, 'CUSTIP', '');
		$cardType = PP::normalize($data, 'card_type', '');

		$acct = PP::normalize($data, 'ACCT', '');
		$acct = PP::normalizeCardNumber($acct);

		$cvv2 = PP::normalize($data, 'CVV2', '');
		$custom = PP::normalize($data, 'CUSTOM', '');

		$payload = array(
			'CUSTIP' => urlencode($custIP),
			'CARDTYPE' => urlencode($cardType),
			'ACCT' => urlencode($acct),
			'EXPDATE' => urlencode($expiryDate),
			'CVV2' => urlencode($cvv2),
			'CUSTOM' => urlencode($custom),
			'VERBOSITY' => 'HIGH'
		);
		
		return $payload;
	}

	/**
	 * Retrieves the payload for refunds
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRefundPayload($profileId)
	{
		$payload = array(
			'TENDER' => 'C',
			'ACTION' => 'C',
			'ORIGPROFILEID' => $profileId
		);

		return $payload;
	}

	/**
	 * Given the standard payload data, insert the necessary payload data for recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStandardPayload(PPInvoice $invoice, $data)
	{
		$payload = $this->getPayload($data);

		$data['CURRENCYCODE'] = $invoice->getCurrency('isocode');

		// Items to scan from the data
		$items = array('BILLTOFIRSTNAME', 'BILLTOLASTNAME', 'BILLTOSTREET', 'BILLTOCITY', 'BILLTOSTATE', 'BILLTOZIP', 'BILLTOCOUNTRY', 'CURRENCYCODE', 'BILLTOPHONENUM', 'BILLTOEMAIL');

		foreach ($items as $key) {
			$payload[$key] = PP::normalize($data, $key, '');
		}

		$payload['BILLTOFIRSTNAME'] = urlencode($payload['BILLTOFIRSTNAME']);
		$payload['BILLTOLASTNAME'] = urlencode($payload['BILLTOLASTNAME']);
		$payload['BILLTOSTREET'] = urlencode($payload['BILLTOSTREET']);
		$payload['BILLTOCITY'] = urlencode($payload['BILLTOCITY']);
		$payload['BILLTOSTATE'] = urlencode($payload['BILLTOSTATE']);
		$payload['BILLTOZIP'] = urlencode($payload['BILLTOZIP']);
		$payload['BILLTOCOUNTRY'] = urlencode($payload['BILLTOCOUNTRY']);
		$payload['CURRENCYCODE'] = urlencode($payload['CURRENCYCODE']);
		$payload['BILLTOPHONENUM'] = urldecode($payload['BILLTOPHONENUM']);
		$payload['BILLTOEMAIL'] = urldecode($payload['BILLTOEMAIL']);

		// Append other data
		$payload['AMT'] = urlencode($invoice->getTotal());
		$payload['TENDER'] = 'C';

		return $payload;
	}

	/**
	 * Given the standard payload data, insert the necessary payload data for recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringPayload(PPInvoice $invoice, $data)
	{
		$payload = $this->getPayload($data);

		$firstName = PP::normalize($data, 'BILLTOFIRSTNAME', '');
		$lastName = PP::normalize($data, 'BILLTOLASTNAME', '');
		$street = PP::normalize($data, 'BILLTOSTREET', '');
		$city = PP::normalize($data, 'BILLTOCITY', '');
		$state = PP::normalize($data, 'BILLTOSTATE', '');
		$zip = PP::normalize($data, 'BILLTOZIP', '');
		$country = PP::normalize($data, 'BILLTOCOUNTRY', '');
		$currencyCode = $invoice->getCurrency('isocode');
		$phoneNum = PP::normalize($data, 'BILLTOPHONENUM', '');
		$email = PP::normalize($data, 'BILLTOEMAIL', '');

		$billDetails = array(
			'FIRSTNAME' => urlencode($firstName),
			'LASTNAME' => urlencode($lastName),
			'STREET' => urlencode($street),
			'CITY' => urlencode($city),
			'STATE' => urlencode($state),
			'ZIP' => urlencode($zip),
			'COUNTRY' => urlencode($country),
			'CURRENCYCODE' => urlencode($currencyCode),
			'PHONENUM' => urldecode($phoneNum),
			'EMAIL' => urldecode($email)
		);

		$payload = array_merge($payload, $billDetails);

		return $payload;
	}

	/**
	 * Given the standard payload data, insert the necessary payload data for recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRebillPayload($profileId)
	{
		$payload = array(
			'ACTION' => 'I',
			'PAYMENTHISTORY' => 'Y',
			'PROFILEID' => $profileId
		);

		return $payload;
	}

	/**
	 * Retrieves the state of the rebill
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRebillState()
	{
		static $state = null;

		if (is_null($state)) {
			$state = PAYPLANS_PAYFLOW_SETTLEMENT_SUCCESS;

			if ($this->isSandbox()) {
				$state = PAYPLANS_PAYFLOW_SETTLEMENT_PENDING;
			}
		}

		return $state;
	}

	public function getRecurrenceTime($expTime)
	{
		$expTime['year'] = isset($expTime['year']) 	? intval($expTime['year'], 10) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month'], 10) : 0;
		$expTime['day'] = isset($expTime['day']) 	? intval($expTime['day'], 10)  : 0;
		
		// if years are not empty 
		if (!empty($expTime['year'])) {	
		
			$year = $expTime['year']; 
			if ($year >= 1) {
				$payPeriod = 'YEAR';
			}	

			return array('period' => $payPeriod, 'unit' => 'Year', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_YEAR_MESSAGE'));
		}
		
		// if months are not empty 
		if (!empty($expTime['month'])) {
			$months = $expTime['month'];
		   	if ($months == 1) {
				$payPeriod = 'MONT';
			}

			if ($months >= 3 && $months < 6) {
				$payPeriod = 'QTER';
			}

			if ($months >= 6) {
				$payPeriod = 'SMYR';
			}

			return array('period' => $payPeriod, 'unit' => 'Month', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_MONTH_MESSAGE'));
		}
		
		
		if (!empty($expTime['day'])) {
			$days  = $expTime['day'];
			$weeks = intval($days/7, 10);
			
		if ($weeks == 0) {
			$payPeriod = 'DAYS';
		}

		if ($weeks == 1) {
			$payPeriod = 'WEEK';
		}

		if($weeks == 2 || $weeks == 3) {
			$payPeriod = 'BIWK';
		}

		if($weeks == 4) {
			$payPeriod = 'FRWK';
		}	
		
		return array('period' => $payPeriod, 'unit' => 'Week', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_DAY_MESSAGE'));
		}
		
		return false;
	}
}