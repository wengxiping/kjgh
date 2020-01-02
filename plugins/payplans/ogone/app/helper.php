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

class PPHelperOgone extends PPHelperPayment
{
	/**
	 * Formats the amount
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

		$amount = (round($amount, 2) * 100);
		return $amount;
	}

	/**
	 * Retrieves the PSP id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPspId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('psp_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the SHA-IN phrase
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSHAIn()
	{
		static $phrase = null;

		if (is_null($phrase)) {
			$phrase = $this->params->get('sha_pass_phrase', '');
		}

		return $phrase;
	}

	/**
	 * Retrieves the SHA-OUT phrase
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSHAOut()
	{
		static $phrase = null;

		if (is_null($phrase)) {
			$phrase = $this->params->get('sha_out_pass_phrase', '');
		}

		return $phrase;
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNotifyUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=notify&gateway=ogone';
		}

		return $url;
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = "https://secure.ogone.com/ncol/prod/orderstandard.asp";

			if ($this->isSandbox()) {
				$url = "https://secure.ogone.com/ncol/test/orderstandard.asp";	
			}
		}

		return $url;
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
	 * Generates the payload for payment request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentRequestPayload(PPPayment $payment)
	{
		$paymentKey = $payment->getKey();
		$invoice = $payment->getInvoice();

		$user = $invoice->getBuyer();
		$email = $user->getEmail();
		$amount = $this->formatAmount($invoice->getTotal());

		$pspId = $this->getPspId();
		$currency = $invoice->getCurrency('isocode');

		$acceptUrl = $this->getNotifyUrl();
		$declineUrl = $this->getNotifyUrl();

		$payload = array(
			'ACCEPTURL' => $acceptUrl,
			'AMOUNT' => $amount,
			'CURRENCY' => $currency,
			'DECLINEURL' => $declineUrl,
			'EMAIL' => $email,
			'ORDERID' => $paymentKey,
			'PSPID' => $pspId
		);

		
		if ($invoice->isRecurring()) {
			$payload = $this->getRecurringPaymentRequestPayload($payload, $invoice, $payment, $paymentKey, $currency);

			// If plan not supported then redirect to the same page
			if (!$payload) {
				PP::info()->set('COM_PAYPLANS_PAYMENT_APP_OGONE_DOES_NOT_SUPPORT_THE_PLAN', 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=checkout&invoice_key=' . $invoice->getKey() . '&tmpl=component', false);
				return PP::redirect($redirect);
			}
		}
		
		$hash = $this->generateSHAIn($payload);

		$payload['SHASIGN'] = $hash;

		return $payload;
	}

	/**
	 * Set other params needed for the payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringPaymentRequestPayload($payload, $invoice, $payment, $paymentKey, $currency)
	{
		$recurringType = $invoice->getRecurringType(true);
		$recurrenceCount = (int) $invoice->getRecurrenceCount();
		$rawTime = 'expiration';
		$amount = $invoice->getTotal();
		
		if ($recurringType == PP_RECURRING && $recurrenceCount != 0) {
			//first payment is direct payment at ogone end and create subscription at ogone end for next payments only
			$recurrenceCount--;
			$expirationRaw = $invoice->getExpiration(PP_PRICE_RECURRING, true);
		}

		if ($recurringType == PP_RECURRING_TRIAL_1) {
			$amount	= $invoice->getTotal($invoice->getCounter()+1);	
			$expirationRaw = $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_1, true);
			$rawTime = 'trial_time_1';
		} 

		if ($recurringType == PP_RECURRING_TRIAL_2) {
			return false;
		}
	
		//calculate period unit and number 
		$expiration = PPHelperPlan::convertIntoTimeArray($invoice->getParam($rawTime, '000000000000'));
		$period = $this->createRecurringPeriodUnitAndNumber($expiration, $payment);
			
		// Get start date
		$now = PP::date();
		$currentDay = $now->format('d');
		$start = $now->addExpiration($invoice->getCurrentExpiration(true));

		// Get end date
		$end = PP::date();

		if ($recurrenceCount != 0) {
			for ($recurrenceCount; $recurrenceCount > 0; $recurrenceCount--) {
				$end->addExpiration($expirationRaw);
			}

			//end date must be set one day after the actual enddate, otherwise ogone 
			//doesn't consider last date
			$end = $end->addExpiration('000001000000');
		} else {
			//set date of after 25 years for lifetime recurring
			$end = $end->addExpiration('250000000000');
		}

		// If expiration time not supported then don't set params and return 
		$subscriptionPeriodNumber = PP::normalize($period, 'subPeriodNumber', '');
		$subscriptionPeriodUnit = PP::normalize($period, 'subPeriodUnit', '');

		if (!$subscriptionPeriodNumber || !$subscriptionPeriodUnit) {
			return false;
		}

		$recurringPayload = array(
			'SUBSCRIPTION_ID' => $invoice->getId(),
			'SUB_AMOUNT' => $this->formatAmount($amount),
			'SUB_CUR' => $currency,
			'SUB_ENDDATE' => $end->format('Y-m-d'),
			'SUB_ORDERID' => $paymentKey,
			'SUB_PERIOD_MOMENT' => $currentDay,
			'SUB_PERIOD_NUMBER' => $subscriptionPeriodNumber,
			'SUB_PERIOD_UNIT' => $subscriptionPeriodUnit,
			'SUB_STARTDATE' => $start->format('Y-m-d'),
			'SUB_STATUS' => '1'
		);

		$payload = array_merge($payload, $recurringPayload);

		return $payload;
	}

	/**
	 * Retrieves the recurrence count
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

		$type = $invoice->getRecurringType(true);

		// Recurrence Count For Regular Recurring Plan
		if ($type == PP_RECURRING) {
			$recurrence_count = $invoice->getRecurrenceCount();
		}
			
		// Recurrence Count For Recurring + Trial 1 Plan
		if ($type == PP_RECURRING_TRIAL_1) {
			$recurrence_count = $invoice->getRecurrenceCount() + 1;
		}
		
		// Recurrence Count For Recurring + Trial 2 Plan
		if ($type == PP_RECURRING_TRIAL_2) {
			$recurrence_count = $invoice->getRecurrenceCount() + 2;
		}
		
		return $recurrence_count;
	}

	/**
	 * Generates the SHA-1 for given parameters
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateSHAIn($data)
	{
		$phrase = $this->getSHAIn();
		$string = '';

		foreach ($data as $key => $value) {
			$string .= $key . '=' . $value . $phrase;
		}

		$hash = sha1($string);
		$hash = strtoupper($hash);

		return $hash;
	}

	/**
	 * Calculate SHA-OUT for given parameters
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateSHAOut($data)
	{
		$phrase = $this->getSHAOut();
		$string = ''; 

		foreach ($data as $key => $value) {
			$string .= $key . '=' . $value . $phrase;
		}

		$hash = sha1($string);
		$hash = strtoupper($hash);
		
		return $hash;
	}

	/**
	 * Set amount when refund notification from ogone 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process_status_7($data, $payment, $transaction, $invoice)
	{
		$transaction->amount = -$data['amount'];
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_OGONE_TRANSACTION_REFUNDED';

		return true;
	}
	
	/**
	 *Decide when payment processing status is SUCCESS
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process_status_9($data, $payment, $transaction, $invoice)
	{
		$amount = PP::normalize($data, 'amount', 0);

		if (!$invoice->isRecurring()) {
			$transaction->amount = $amount;
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_OGONE_TRANSACTION_COMPLETED';
			return true;
		}

		// Here we assume that this is a recurring billing
		$creationStatus = PP::normalize($data, 'creation_status', '');
		$subscriptionId = PP::normalize($data, 'subscription_id', '');
		$orderId = PP::normalize($data, 'orderId', 0);

		if (($creationStatus && $subscriptionId && $creationStatus == 'OK') || $payment->getKey() == $orderId) {
			$transaction->amount = $amount;
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_OGONE_TRANSACTION_COMPLETED';
		}

		return true;
	}
	
	/**
	 *Filter out the parametrs not needed in verification of data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeSHAOutItems($data)
	{   
		//list of SHA-OUT calculation parameters
		$accept = array ('AAVADDRESS','AAVCHECK','AAVZIP','ACCEPTANCE','ALIAS','AMOUNT','BIN','BRAND','CARDNO','CCCTY','CN','COMPLUS','CREATION_STATUS','CURRENCY','CVC','CVCCHECK','DCC_COMMPERCENTAGE','DCC_CONVAMOUNT','DCC_CONVCCY','DCC_EXCHRATE','DCC_EXCHRATESOURCE','DCC_EXCHRATETS','DCC_INDICATOR','DCC_MARGINPERCENTAGE','DCC_VALIDHOURS','DIGESTCARDNO','ECI','ED','ENCCARDNO','IP','IPCTY','NBREMAILUSAGE','NBRIPUSAGE','NBRIPUSAGE_ALLTX','NBRUSAGE','NCERROR','NCERRORCARDNO','NCERRORCN','NCERRORCVC','NCERRORED','ORDERID','PAYID','PM','SCO_CATEGORY','SCORING','STATUS','SUBBRAND','SUBSCRIPTION_ID','TRXDATE','VC');
		
		$source = array();
		foreach ($data as $key => $value) {
			$key = strtoupper($key);
			
			if (!in_array($key, $accept) || (!isset($value) || trim($value) == '')) {
				continue;
			}
			$source[$key] = $value;
		}

		return $source;
	}

	/**
	 * Create period unit and period number parmas required for recurring setup
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createRecurringPeriodUnitAndNumber($expiration, $payment)
	{
		//create period unit and number as supported by ogone
		$count = 0;
		$period = array();
		
		foreach ($expiration as $key=>$value) {
			if ($value == '00') {
				$count++;
				continue;
			}
			//if year is set then do nothing as ogone doesn't support this type of recurring
			if ($key == 'year') {
				$support = false;
				break;
			}
			
			if ($key == 'month') {
				$period['subPeriodUnit'] = 'm';
				$period['subPeriodNumber'] = (int)$value;
				continue;
			}
			
			if ($key == 'day') {
				$period['subPeriodUnit'] = 'd';
				$period['subPeriodNumber'] = (int)$value;
				continue;
			}
		}
		
		//if not a single data in expiration date then generate error log 
		if ($count != 5 || (isset($support) &&  $support == false)) {
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_OGONE_EXPIRATION_NOT_SUPPORTED');
			$error['Payment ID'] = $payment->getId();
			$error['Expiration'] = $expiration;
			$plans = $payment->getPlans();
			$planid	= array_pop($plans);
			$error['Plan Name'] = PP::plan($planid)->getTitle();
			PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $payment, $error, 'PayplansPaymentFormatter', '', true);
			return false;
		}
		return $period;
	}

}