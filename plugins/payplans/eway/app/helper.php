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

class PPHelperEway extends PPHelperPayment
{
	/**
	 * Retrieves the customer id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomerId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('ewayCustomrerID', '');
		}

		return $id;
	}

	/**
	 * Retrieves the eWay username
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUsername()
	{
		static $username = null;

		if (is_null($username)) {
			$username = $this->params->get('ewayUsername', '');
		}

		return $username;
	}

	/**
	 * Retrieves the eWay password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('ewayPassword', '');
		}

		return $password;
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
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&gateway=eway&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Method to get the form's url
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
	 * Method to get the WSDL endpoint
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiEndpoint()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.eway.com.au/gateway/ManagedPaymentService/managedCreditCardPayment.asmx?WSDL';

			if ($this->isSandbox()) {
				$url = 'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedCreditCardPayment.asmx?WSDL';
			}
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
	 * Creates a new SOAP client
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSoapClient()
	{
		static $client = null;

		if (is_null($client)) {
			$client = false;

			if (!class_exists('SoapClient')) {
				$this->setError('SOAP Library is not present with the current PHP Setup');
			
				return $client;
			}

			$url = $this->getApiEndpoint();

			try {
				$client = new SoapClient($this->getApiEndpoint(), array("exceptions" => 1));
			} catch (SoapFault $e) {
				$this->setError(JText::_('COM_PAYPLANS_APP_EWAY_SOAP_CONNECTION_ERROR'));
				return $client;
			}

			$params = array(
				'eWAYCustomerID' => $this->getCustomerId(),
				'Username' => $this->getUsername(),
				'Password' => $this->getPassword()
			);

			$header = new SoapHeader('https://www.eway.com.au/gateway/managedpayment', 'eWAYHeader', $params);
			$client->__setSoapHeaders(array($header));
		}

		return $client;
	}

	/**
	 * Ensure that the amount value is in accordance with the eWay specs
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
	 * Creates a new customer on eWay's server
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createNewCustomer(PPPayment $payment, PPInvoice $invoice, $data, $client)
	{
		$payload = array();
		$payload['Title'] = PP::normalize($data, 'name_title', '');
		$payload['FirstName'] = PP::normalize($data, 'first_name', '');
		$payload['LastName'] = PP::normalize($data, 'last_name', '');
		$payload['Address'] = PP::normalize($data, 'address', '');
		$payload['Suburb'] = '';
		$payload['State'] = PP::normalize($data, 'state', '');
		$payload['Company'] = PP::normalize($data, 'company', '');
		$payload['PostCode'] = PP::normalize($data, 'zip', '');
		$payload['Country'] = strtolower(PP::normalize($data, 'country', ''));
		$payload['Email'] = PP::normalize($data, 'email', '');
		$payload['Phone'] = PP::normalize($data, 'phone', '');
		$payload['Mobile'] = PP::normalize($data, 'mobile', '');
		$payload['CustomerRef'] = $invoice->getBuyer()->getId();
		$payload['JobDesc'] = '';
		$payload['Comments'] = '';
		$payload['URL'] = '';
		$payload['CCNumber'] = PP::normalizeCardNumber(PP::normalize($data, 'card_num', ''));
		$payload['CCNameOnCard'] = PP::normalize($data, 'card_name', '');
		$payload['CCExpiryMonth'] = PP::normalize($data, 'exp_month', '');
		$payload['CCExpiryYear'] = PP::normalize($data, 'exp_year', '');

		try {
			$result = $client->CreateCustomer($payload);

		} catch (SoapFault $e) {
			$message = JText::_('COM_PAYPLANS_APP_EWAY_CREATE_CUSTOMER_ERROR');

			$error = array(
				'error_code' => $e->faultcode,
				'error_message' => $e->faultstring
			);

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $error, 'PayplansPaymentFormatter', '', true);

			$this->setError($message);
			return false;
		}

		return $result->CreateCustomerResult;
	}

	/**
	 * Get Curl request content
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function get_url_contents($url)
	{
		$crl = curl_init();
		$timeout = 5;

		curl_setopt ($crl, CURLOPT_URL,$url);
		curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$ret = curl_exec($crl);
		curl_close($crl);

		return $ret;
	}

	/**
	 * Get Response Code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRespnseCode($response)
	{
		//sets the error codes and message
		$this->_responsecodes = new stdClass();

		$this->_responsecodes->deny = array("01"=>"Refer to Issuer","02"=>"Refer to Issuer, special","03"=>"No Merchant","04"=>"Pick Up Card","05"=>"Do Not Honour","06"=>"Error",
			"07"=>"Pick Up Card, Special","08"=>"Honour With Identification","09"=>"Request In Progress","10"=>"Approved For Partial Amount","12"=>"Invalid Transaction",
			"13"=>"Invalid Amount","14"=>"Invalid Card Number","15"=>"No Issuer","19"=>"Re-enter Last Transaction","21"=>"No Action Taken",
			"22"=>"Suspected Malfunction","23"=>"Unacceptable Transaction Fee","25"=>"Unable to Locate Record On File","30"=>"Format Error","31"=>"Bank Not Supported By Switch",
			"33"=>"Expired Card, Capture","34"=>"Suspected Fraud, Retain Card","35"=>"Card Acceptor, Contact Acquirer, Retain Card","36"=>"Restricted Card, Retain Card",
			"37"=>"Contact Acquirer Security Department, Retain Card","38"=>"PIN Tries Exceeded, Capture","39"=>"No Credit Account","40"=>"Function Not Supported",
			"41"=>"Lost Card","42"=>"No Universal Account","43"=>"Stolen Card","44"=>"No Investment Account","51"=>"Insufficient Funds","52"=>"No Cheque Account",
			"53"=>"No Savings Account","54"=>"Expired Card","55"=>"Incorrect PIN","56"=>"No Card Record","57"=>"Function Not Permitted to Cardholder","58"=>"Function Not Permitted to Terminal",
			"59"=>"Suspected Fraud","60"=>"Acceptor Contact Acquirer","61"=>"Exceeds Withdrawal Limit","62"=>"Restricted Card","63"=>"Security Violation",
			"64"=>"Original Amount Incorrect","66"=>"Acceptor Contact Acquirer, Security","67"=>"Capture Card","75"=>"PIN Tries Exceeded","82"=>"CVV Validation Error",
			"90"=>"Cutoff In Progress","91"=>"Card Issuer Unavailable","92"=>"Unable To Route Transaction","93"=>"Cannot Complete, Violation Of The Law",
			"94"=>"Duplicate Transaction","96"=>"System Error");

		$this->_responsecodes->accept = array("00"=>"Transaction Approved","11"=>"Approved, VIP","16"=>"Approved, Update Track 3");

		if (strpos($response["EWAYTRXNERROR"],',')) {
			$pieces = explode(',',$response["EWAYTRXNERROR"]);
			$response["EWAYTRXNERROR"] = $pieces[0];
		}

		$return = array();
		$return['deny']     = false;
		$return['accept']   = false;
		$return['message']  = '';

		//if($data["EWAYTRXNERROR"] == 00 || $data["EWAYTRXNERROR"] == 11 || $data["EWAYTRXNERROR"] == 16){
		if (array_key_exists($response["EWAYTRXNERROR"],$this->_responsecodes->accept)) {
			$return['accept'] = true;
			$return['deny'] = false;
			$return['message'] = $this->_responsecodes->accept[$response["EWAYTRXNERROR"]];

		}

		if(array_key_exists($response["EWAYTRXNERROR"],$this->_responsecodes->deny)) {
			$return['deny'] = true;
			$return['accept'] = false;
			$return['message'] = $this->_responsecodes->deny[$response["EWAYTRXNERROR"]];
		}
			
		return $return;
	}

	/**
	 * Get Recurrence count value
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
	 * Get Recurrence Time value
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceTime($expTime)
	{
		$expTime['year'] = isset($expTime['year']) ? intval($expTime['year']) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month']) : 0;
		$expTime['day'] = isset($expTime['day']) ? intval($expTime['day']) : 0;
		$expTime['hour'] = isset($expTime['hour']) ? intval($expTime['hour']) : 0;

		// if only hours are set then return days as it is
		if(!empty($expTime['hour'])){
			$hours = $expTime['hour'];

			if(!empty($expTime['day'])){
				$hours += $expTime['day'] * 24;

				if(!empty($expTime['month'])){
					$hours += $expTime['month'] * 30 * 24;

					if(!empty($expTime['year'])){
						$hours += $expTime['year'] * 365 * 24;
					}
				}
			}
			return array('period' => $hours, 'unit' => 'Hour', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'));
		}


		// if only days are set then return days as it is
		if(!empty($expTime['day'])){
			$days = $expTime['day'];

			if(!empty($expTime['month'])){
				$days += $expTime['month'] * 30;

				if(!empty($expTime['year'])){
					$days += $expTime['year'] * 365;
				}
			}
			return array('period' => $days, 'unit' => 'Day', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'));
		}

		// if months are set
		if(!empty($expTime['month'])){
			$month = $expTime['month'];

			if(!empty($expTime['year'])){
				$month += $expTime['year'] * 12 ;
			}

			return array('period' => $month, 'unit' => 'Month', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'));
		}

		// years
		if(!empty($expTime['year'])){
			return array('period' => $expTime['year'], 'unit' => 'Year', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'));
		}

		return false;
	}

	/**
	 * Initiate Payment Process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initiatePaymentProcess(PPInvoice $invoice, PPPayment $payment, $profileId, $client)
	{
		$params = array();
		$params['managedCustomerID'] = (double)$profileId;
		$params['amount'] = (number_format($invoice->getTotal(),2,'.','')*100);
		$params['invoiceReference'] = $invoice->getId();
		$params['invoiceDescription'] = $invoice->getTitle();

		try {
			$result = $client->ProcessPayment($params);
		} catch (SoapFault $e) {

			$message = JText::_('COM_PAYPLANS_APP_EWAY_TRANSACTION_ERROR');
			$error = array();              
			$error['error_code'] = $e->faultcode;
			$error['error_message'] = $e->faultstring;

			PP::log(PPLogger::LEVEL_ERROR, $message, $payment, $error, 'PayplansPaymentFormatter', '', true);

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&error_code=' . $error['error_code'] . '&error_msg=' . urlencode($error['error_message']), false);

			return PP::redirect($redirect);
		}

		//ewayTrxnError,ewayTrxnStatus,ewayTrxnNumber,ewayReturnAmount,ewayAuthCode
		if($result){
			return $result->ewayResponse;
		}

		return false;
	}

	/**
	 * Capture Fixed Payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processNonRecurringPayment(PPPayment $payment, PPInvoice $invoice, $data = array(), $customerProfileId)
	{
		$amount = number_format($invoice->getTotal(), 2);

		$txnId = PP::normalize($data, 'ewayTrxnNumber', 0);

		// Create transaction
		$transaction = PP::createTransaction($invoice, $payment, $txnId, $customerProfileId, 0, $data);

		$transaction->amount = $amount;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_EWAY_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();

		return true;
	}

	/**
	 * Capture Recurring Payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringPayment(PPPayment $payment, PPInvoice $invoice, $recurrenceCount, $customerProfileId, $data = array())
	{
		$recurrenceCount = $recurrenceCount - 1;
		$amount = number_format($invoice->getTotal(), 2);

		$txnId = PP::normalize($data, 'ewayTrxnNumber', 0);

		// Create Transaction
		$transaction = PP::createTransaction($invoice, $payment, $txnId, $customerProfileId, 0, $data);
		$transaction->amount = $amount;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_EWAY_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();

		$gatewayParams = $payment->getGatewayParams();
		$gatewayParams->set('pending_recur_count', $recurrenceCount);
		$gatewayParams->set('profile_id', $customerProfileId);
					
		$payment->gateway_params = $gatewayParams->toString();
		$payment->save();

		return true;
	}

	/**
	 * Refunds a transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refund(PPTransaction $transaction, $amount)
	{
		$client = $this->getSoapClient();
		$invoice = $transaction->getInvoice();

		$profileId = $transaction->getGatewaySubscriptionId();
		$amount = ($this->formatAmount($amount)) * 100;

		$gatewayTransactionId = $transaction->getGatewayTxnId();
		$password = $this->getEwayUserPassword($invoice);  

		$params = "<ewaygateway><ewayCustomerID>{$profileId}</ewayCustomerID><ewayOriginalTrxnNumber>{$gatewayTransactionId}</ewayOriginalTrxnNumber>"
					."<ewayTotalAmount>{$amount}</ewayTotalAmount><ewayCardExpiryMonth></ewayCardExpiryMonth>"
					."<ewayCardExpiryYear></ewayCardExpiryYear><ewayOption1></ewayOption1><ewayOption2></ewayOption2><ewayOption3></ewayOption3>"
					."<ewayRefundPassword>{$password}</ewayRefundPassword></ewaygateway>";

		$response = $client->__doRequest($params,"https://www.eway.com.au/gateway/xmlpaymentrefund.asp","POST","1.2");
		$response = simplexml_load_string($response);

		//refund response transaction status is true then terminate the order
		if(strtolower($response['ewayTrxnStatus']) == True){
			
			$payment    = $transaction->getPayment();
			
			if ($payment) {
				$transactionId = PP::normalize($response, 'ewayTrxnNumber', 0);
				$newtransaction = PP::createTransaction($invoice, $payment, $transactionId, $profileId, 0, $response);

				$negativeAmt = -($response['ewayReturnAmount']/100);
				$newtransaction->amount = $negativeAmt;
				$newtransaction->message = 'COM_PAYPLANS_APP_EWAY_TRANSACTION_REFUNDED';
				$newtransaction->save();

				return true; 
			}
		}
		
		return false;
	}

	/**
	 * Get Eway Password
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEwayUserPassword(PPInvoice $invoice)
    {
    	$buyer = $invoice->getBuyer(true);

        $db = PP::db();

		$query = 'SELECT `password` FROM `#__users`';
		$query .= ' WHERE `username` != ' . $db->Quote($buyer->getUsername());

		$db->setQuery($query);
        $password = $db->loadResult();

        if ($password) {
            $parts = explode(':', $password);
            $crypt = $parts[0];
            $salt = @$parts[1];
           
            return JUserHelper::getCryptedPassword($crypt, $salt);
        }

        return false;
    }

}