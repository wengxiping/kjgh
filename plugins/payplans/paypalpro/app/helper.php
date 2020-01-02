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

class PPHelperPaypalPro extends PPHelperPayment
{
	/**
	 * Determines if it is in testing mode
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
	 * Retrieves the Merchant Email 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantEmail()
	{
		static $email = null;

		if (is_null($email)) {
			$email = $this->params->get('merchantEmail', '');
		}

		return $email;
	}

	/**
	 * Retrieves the Api Username 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUsername()
	{
		static $user = null;

		if (is_null($user)) {
			$user = $this->params->get('apiUsername', '');
		}

		return $user;
	}

	/**
	 * Retrieves the Api Password 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('apiPassword', '');
		}

		return $password;
	}

	/**
	 * Retrieves the Api Signature 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiSignature()
	{
		static $signature = null;

		if (is_null($signature)) {
			$signature = $this->params->get('apiSignature', '');
		}

		return $signature;
	}

	/**
	 * Creates a connection to Paypal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($method, $payload, PPPayment $payment) 
	{
		$payload['METHOD'] = $method;
		$payload['VERSION'] = urlencode('51.0');
		$payload['USER'] = $this->getApiUsername();
		$payload['PWD'] = $this->getApiPassword();
		$payload['SIGNATURE'] = $this->getApiSignature();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getApiEndpoint());
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
	
		// Set the request as a POST FIELD for curl.
		$query = http_build_query($payload);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec($ch);
	
		if(!$result) {
			$message = $method . ' ' . JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_FAILED_MESSAGE');
			$error = curl_error($ch) . ' (' . curl_errno($ch) . ')';

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error), 'PayplansPaymentFormatter', '', true);

			return false;
		}

		// Extract the response details.
		$response = $this->formatResponse($result);

		// Paypal returns errors
		if (!$response || !isset($response['ACK'])) {
			$error = 'Invalid HTTP Response for POST Request (' . $query . ') to ' . $this->getApiEndpoint();
			$message = $method . ' ' . JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_FAILED_MESSAGE');

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error), 'PayplansPaymentFormatter', '', true);

			return false;
		}
	
		// Ensure that the ACK response is always uppercase
		if (isset($response['ACK'])) {
			$response['ACK'] = strtoupper($response['ACK']);
		}

		return $response;
	}

	/**
	 * Creates a new recurring profile on PayPal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createRecurringProfile($invoice, $payment, $payloadData)
	{
		$expirationTime = $invoice->getExpiration();
		$expirationTime = $this->getRecurrenceTime($expirationTime);

		// Current date
		$date = JFactory::getDate();

		// Default Amount
		$amount = $invoice->getTotal();

		$payloadData['PROFILEREFERENCE'] = urlencode($payment->getKey());
		$payloadData['PROFILESTARTDATE'] = urlencode($date->format("Y-m-dTH:i:s", null, true));
		$payloadData['BILLINGPERIOD'] = urlencode($expirationTime['unit']);
		$payloadData['BILLINGFREQUENCY'] = urlencode($expirationTime['period']);
		$payloadData['TOTALBILLINGCYCLES'] = urlencode($invoice->getRecurrenceCount());
		$payloadData['DESC'] = urlencode($invoice->getTitle());
		

		// For recurring with first trial
		if ($invoice->getRecurringType() == PP_RECURRING_TRIAL_1) {

			$trialTime = $invoice->getExpiration(PP_RECURRING_TRIAL_1);
			$trialTime = $this->getRecurrenceTime($trialTime);
			$payloadData['TRIALBILLINGPERIOD'] = urlencode($trialTime['unit']);
			$payloadData['TRIALBILLINGFREQUENCY'] = urlencode($trialTime['period']);
			$payloadData['TRIALAMT'] = urlencode($invoice->getTotal());
			$payloadData['TRIALTOTALBILLINGCYCLES'] = urlencode(1);

			// Update the amount
			$amount = $invoice->getTotal($invoice->getCounter() + 1);
		}
		
		$payloadData['AMT'] = urlencode($amount);

		$response = $this->connect('CreateRecurringPaymentsProfile', $payloadData, $payment);
		return $response;

	}

	/**
	 * Format response given by PayPal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatResponse($responseString)
	{
		parse_str($responseString, $response);
		
		return $response;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiEndpoint()
	{
		static $url = null;

		if (is_null($url)) {
			$url = "https://api-3t.paypal.com/nvp";
			
			if ($this->isSandbox()) {
				$url = 'https://api-3t.sandbox.paypal.com/nvp';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($key)
	{
		static $url = null;

		if (is_null($url)) {
			$url = PPR::_("index.php?option=com_payplans&gateway=paypalpro&view=payment&task=complete&action=cancel&payment_key=" . $key . '&tmpl=component');
		}

		return $url;
	}

	/**
	 * Retrieves a list of supported countries
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCountries()
	{
		static $countries = null;

		if (is_null($countries)) {
			$countries = array();

			// Reference:-https://www.x.com/developers/community/blogs/dan_pro_vt/pro/virtual-terminal-updates-supported-country-list
			$notSupported = array('AF','DZ','AO','AQ','BY','BO','BV','BI','KH','CM','CF','TD','CX','CU','TP','GQ','ER','ET','GT','GN','GW','HT','IQ','KE','LB','LR','NP','NG','PK','PG','ST','SL','SO','LK','SD','TJ','YE','ZW','XE');

			$options = array(
				'isocode2' => array(array('NOT IN', '("'.implode('","', $notSupported).'")'))
			);

			$model = PP::model('Country');
			$items = $model->loadRecords($options);

			if ($items) {
				foreach ($items as $item) {
					$country = new stdClass();
					$country->value = $item->isocode2;
					$country->title = PPFormats::country($item);

					$countries[] = $country;
				}
			}
		}

		return $countries;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl($key)
	{
		static $url = null;

		if (is_null($url)) {
			$url = PPR::_("index.php?option=com_payplans&view=payment&task=complete&action=success&payment_key=" . $key);
		}

		return $url;
	}

	/**
	 * Retrieves the processor library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getProcessor()
	{
		static $processor = null;

		if (is_null($processor)) {
			require_once(__DIR__ . '/processor.php');

			$processor = new PPHelperPaypalProProcessor($this->params);
		}

		return $processor;
	}

	/**
	 * Retrieves the post url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNotifyUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=notify&gateway=paypalpro';
		}

		return $url;
	}

	public function getRecurrenceTime($expTime)
	{ 
		$expTime['year'] = isset($expTime['year']) ? intval($expTime['year']) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month']) : 0;
		$expTime['day'] = isset($expTime['day']) ? intval($expTime['day']) : 0;
		
		// if only days are set then return days as it is
		if(!empty($expTime['day'])){
			$days = $expTime['day'];
			
			if(!empty($expTime['month'])){
				$days += $expTime['month'] * 30;

				if(!empty($expTime['year'])){
					$days += $expTime['year'] * 365;
				}
			}
			return array('period' => $days, 'unit' => 'Day', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_PRO_RECURRING_MESSAGE'));
		}
		
		// if months are set
		if(!empty($expTime['month'])){
			$month = $expTime['month'];
			
			if(!empty($expTime['year'])){
				$month += $expTime['year'] * 12 ;
			}
			
			return array('period' => $month, 'unit' => 'Month', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_PRO_RECURRING_MESSAGE'));
		}
		
		// years
		if(!empty($expTime['year'])){		
			return array('period' => $expTime['year'], 'unit' => 'Year', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_PRO_RECURRING_MESSAGE'));
		}
		
		return false;
	}

	/**
	 * Process payment notifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process(PPPayment $payment, PPTransaction $transaction, $data)
	{
		$processor = $this->getProcessor();
		$errors = JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_INVALID_TRANSACTION_TYPE_OR_PAYMENT_STATUS');
		$recurringMethod = PP::normalize($data, 'txn_type', false);
		$nonRecurringMethod = PP::normalize($data, 'payment_status', false);
		
		if ($recurringMethod) {
			$recurringMethod = 'onProcess_' . strtolower($recurringMethod);

			if (method_exists($processor, $recurringMethod)) {
				$errors = $processor->$recurringMethod($payment, $transaction, $data);
			}
		}

		if ($nonRecurringMethod) {
			$nonRecurringMethod = 'onPayment_' . strtolower($nonRecurringMethod);

			if (method_exists($processor, $nonRecurringMethod)) {
				$errors = $processor->$nonRecurringMethod($payment, $transaction, $data);
			}
		}

		return $errors;
	}
}