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

class PPHelperAuthorize extends PPHelperPayment
{
	/**
	 * Proper way of getting the recurring count. If unlimited, authorize.net requires it to be 9999
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();

		// To submit a subscription with no end date (an ongoing subscription), the field needs to be submitted as 9999
		if (intval($count) === 0) {
			return 9999;   
		}
		
		return $count;
	}

	/**
	 * Formats and return the appropriate recurring time for authorize.net recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getRecurrenceTime($expTime)
	{
		$expTime['year'] = isset($expTime['year']) ? intval($expTime['year']) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month']) : 0;
		$expTime['day'] = isset($expTime['day']) ? intval($expTime['day']) : 0;;
		
		// years
		if (!empty($expTime['year'])) {		
			return array('period' => 12, 'unit' => 'months', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'));
		}
		
		// if months are set
		if (!empty($expTime['month'])) {
			
			// if days are not empty
			if (!empty($expTime['day']) && ( ($expTime['month'] * 30 + $expTime['day']) <= 365) ) {
				return array('period' => $expTime['month'] * 30 + $expTime['day'], 'unit' => 'days', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_RECURRING_MESSAGE'));
			}
			
			return array('period' => $expTime['month'], 'unit' => 'months', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_RECURRING_MESSAGE'));
		}
		
		// if only days are set then return days as it is
		if (!empty($expTime['day'])) {
			return array('period' => intval($expTime['day'], 10), 'unit' => 'days', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_AUTHORIZE_RECURRING_MESSAGE'));
		}
		
		// XITODO : what to do if not able to convert it
		return false;
	}

	/**
	 * Processes IPN requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function processIPN(PPTransaction &$transaction, $data, PPPayment $payment)
	{
		$input = JFactory::getApplication()->input;

		$errors = array();

		// Get the response code. 1 is success, 2 is decline, 3 is error
		$code = $input->get('x_response_code', '', 'int');
	 
		// Get the reason code. 8 is expired card.
		$reason = $input->get('x_response_reason_code', '', 'int');

		$message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_ERROR';

		// Approved transaction
		if ($code == 1) {
			$transaction->amount = $data['x_amount'];
			$message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_COMPLETED';
		}

		// Declined transaction
		if ($code == 2) {
			$message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_DECLINED';
		}

		// An expired card
		if ($code == 3 && $reason == 8) {
			$message = 'COM_PAYPLANS_APP_AUTHORIZE_TRANSACTION_EXPIRED_CARD';
		}

		if ($code != 1 && $code != 2 && $code != 3) {
			$errors[] = JText::_('COM_PAYPLANS_INVALID_AUTHORIZE_PAYMENT_STATUS');
		}

		$transaction->message = $message;

		if (!empty($errors)) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_AUTHORIZE_PAYMENT_PROCESS'), $payment, $errors, 'PayplansPaymentFormatter','', true);
		}
	
		return $errors;
	}

	/**
	 * Determines if it is currently in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', false) ? true : false;
		}

		return $sandbox;
	}

	/**
	 * Renders the authorize.net library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadLibrary()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			require_once(__DIR__ . '/library/library.php');

			$params = $this->params;

			if (!defined('AUTHORIZENET_API_LOGIN_ID')) {
				define('AUTHORIZENET_API_LOGIN_ID', $params->get('api_login_id'));
			}

			if (!defined('AUTHORIZENET_TRANSACTION_KEY')) {
				define('AUTHORIZENET_TRANSACTION_KEY', $params->get('transaction_key'));
			}

			if (!defined('AUTHORIZENET_SANDBOX')) {
				define('AUTHORIZENET_SANDBOX', $params->get('sandbox', false));
			}

			if (!defined('TEST_REQUEST')) {
				define('TEST_REQUEST', false);
			}

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Prepares transaction data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTransactionData(PPPayment $payment, PPInvoice $invoice, $data)
	{
		$transaction = new stdClass();
		$transaction->amount = number_format($invoice->getTotal(), 2);
		$transaction->card_num = trim(str_ireplace(' ', '', $data['x_card_num']));
		$transaction->exp_date = trim($data['exp_year'] . '-' . str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT));
		$transaction->card_code = PP::normalize($data, 'x_card_code', '');
		$transaction->first_name = PP::normalize($data, 'x_first_name', '');
		$transaction->last_name = PP::normalize($data, 'x_last_name', '');
		$transaction->address = PP::normalize($data, 'x_address', '');
		$transaction->city = PP::normalize($data, 'x_city', '');
		$transaction->state = PP::normalize($data, 'x_state', '');
		$transaction->country = PP::normalize($data, 'x_country', '');
		$transaction->zip = PP::normalize($data, 'x_zip', '');
		$transaction->email = PP::normalize($data, 'x_email', '');
		$transaction->phone = PP::normalize($data, 'x_phone', '');
		$transaction->invoice_num = $payment->getKey();
		$transaction->description = $invoice->getTitle();

		return $transaction;
	}

	/**
	 * Prepares transaction data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionData(PPPayment $payment, PPInvoice $invoice, $transactionData)
	{
		$this->loadLibrary();

		$durationInDays = $invoice->getExpiration();
		$time = $this->getRecurrenceTime($invoice->getExpiration());
		$interval = $time['period'];
		$unit = $time['unit'];

		$recurringType = $invoice->getRecurringType(true);
		if ($recurringType == PP_RECURRING_TRIAL_1) {
			$amount = $invoice->getTotal($invoice->getCounter() + 1);
			$subscription->trialOccurrences = 1;
			$subscription->trialAmount = $invoice->getTotal();
		}

		$subscription = new AuthorizeNet_Subscription();

		$subscription->name = $invoice->getTitle();
		$subscription->amount = $invoice->getTotal();;
		$subscription->creditCardCardNumber = $transactionData->card_num;
		$subscription->creditCardExpirationDate = $transactionData->exp_date;
		$subscription->creditCardCardCode = $transactionData->card_code;
		$subscription->billToFirstName = $transactionData->first_name;
		$subscription->billToLastName = $transactionData->last_name;
		$subscription->billToAddress = $transactionData->address;
		$subscription->billToCity = $transactionData->city;
		$subscription->billToState = $transactionData->state;;
		$subscription->billToCountry = $transactionData->country;
		$subscription->billToZip = $transactionData->zip;
		$subscription->customerEmail = $transactionData->email;
		$subscription->customerPhoneNumber = $transactionData->phone;
		$subscription->orderDescription = $invoice->getTitle();
	 
		// Set the billing cycle for every three months
		$subscription->intervalLength = $interval;
		$subscription->intervalUnit = $unit;
		
		// start subscription from now
		$startDate = JFactory::getDate();
		$subscription->startDate = $startDate->format('Y-m-d');
		$subscription->totalOccurrences = $this->getRecurrenceCount($invoice);
	 
		// payment key is invoice key here
		$subscription->orderInvoiceNumber = 'PK_' . $payment->getKey();

		return $subscription;
	}
}