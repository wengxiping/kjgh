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

class PPHelperPaypal extends PPHelperPayment
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
	 * Prepares the callback urls
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantEmail()
	{
		static $email = null;

		if (is_null($email)) {
			$email = $this->params->get('merchant_email');

			if ($this->isSandbox()) {
				$email = $this->params->get('sandbox_merchant_email');
			}
		}
		
		return $email;
	}

	/**
	 * Determines if it is failed recurring payment should re-attempt
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isReattemptFailedRecurring()
	{
		static $reattempt = null;

		if (is_null($reattempt)) {
			$reattempt = $this->params->get('faliure_attempt') ? true : false;
		}

		return $reattempt;
	}

	/**
	 * Prepares the callback urls
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCallbackUrls(PPPayment $payment)
	{
		static $callbacks = null;

		if (is_null($callbacks)) {
			$config = PP::config();
			$root = JURI::root();

			$cancelURL = PPR::external('index.php?option=com_payplans&gateway=paypal&view=payment&task=complete&action=cancel&payment_key=' . $payment->getKey(), false);

			$callbacks = array(
				'return' => $root . 'index.php?option=com_payplans&gateway=paypal&view=payment&task=complete&action=success&payment_key=' . $payment->getKey(),
				'cancel' => $cancelURL,
				'notify' => $root . 'index.php?option=com_payplans&gateway=paypal&view=payment&task=notify'
			);
		}
		
		return $callbacks;
	}

	/**
	 * Retrieves the url for paypal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaypalUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$language = JFactory::getLanguage();
			$tag = $language->getTag();

			// We need the "gb" from en-GB as PayPal does not require the "en" portion
			$data = explode('-', $tag);
			$locale = $data[1];


			$url = 'https://www.paypal.com';

			if ($this->isSandbox()) {
				// $url = 'https://ipnpb.sandbox.paypal.com';
				$url = 'https://www.sandbox.paypal.com';
			}

			$url .= '/cgi-bin/webscr?lc=' . $locale;
		}

		return $url;
	}

	/**
	 * Retrieves the url for paypal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaypalIpnUrl($testMode = false)
	{
		static $url = null;

		if (is_null($url)) {
			$language = JFactory::getLanguage();
			$tag = $language->getTag();

			// We need the "gb" from en-GB as PayPal does not require the "en" portion
			$data = explode('-', $tag);
			$locale = $data[1];


			$url = 'https://www.paypal.com';

			if ($testMode) {
				$url = 'https://ipnpb.sandbox.paypal.com';
			}

			$url .= '/cgi-bin/webscr?lc=' . $locale;
		}

		return $url;
	}

	/**
	 * Retrieve recurring details given the expiration time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceTime($expTime)
	{
		$expTime['year'] = isset($expTime['year']) ? intval($expTime['year']) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month']) : 0;
		$expTime['day'] = isset($expTime['day']) ? intval($expTime['day']) : 0;;
		
		// years
		if (!empty($expTime['year'])) {
			
			if ($expTime['year'] >= 5) {
				return array('period' => 5, 'unit' => 'Y', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
			}
			
			if ($expTime['year'] >= 2) {
				return array('period' => $expTime['year'], 'unit' => 'Y', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
			}
			
			// if months is set then return years * 12 + months
			if (isset($expTime['month']) && $expTime['month']) {
				return array('period' => $expTime['year'] * 12 + $expTime['month'], 'unit' => 'M', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
			}				
			
			return array('period' => $expTime['year'], 'unit' => 'Y', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
		}
		
		// if months are set
		if (!empty($expTime['month'])) {
			
			// if days are empty
			if (empty($expTime['day'])) {
				return array('period' => $expTime['month'], 'unit' => 'M', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
			}
			
			// if total days are less or equlas to 90, then return days
			//  IMP : ASSUMPTION : 1 month = 30 days
			$days = $expTime['month'] * 30;

			if (($days + $expTime['day']) <= 90) {
				return array('period' => $days + $expTime['day'], 'unit' => 'D', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
			}
			
			// other wise convert it into weeks
			return array('period' => intval(($days + $expTime['day'])/7, 10), 'unit' => 'W', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
		}
		
		// if only days are set then return days as it is
		if (!empty($expTime['day'])) {
			return array('period' => intval($expTime['day'], 10), 'unit' => 'D', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
										'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_RECURRING_MESSAGE'));
		}
		
		// XITODO : what to do if not able to convert it
		return false;
	}

	/**
	 * Validates IPN data from PayPal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validateIPN($data, PPPayment $payment, PPInvoice $invoice, $testMode = false)
	{
		$url = self::getPaypalIpnUrl($testMode);
		$request = 'cmd=_notify-validate';

		if ($data) {

			// These keys from the POST request needs to be excluded since PayPal IPN didn't send them to us in the first place.
			$disallowedKeys = array('option', 'task', 'view', 'layout', 'gateway', 'id', 'Itemid', 'payment_key');
			foreach ($data as $key => $value) {
				if (in_array($key, $disallowedKeys)) {
					continue;
				}

				$request .= '&' . $key . '=' . urlencode(stripslashes($value));
			}
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		// curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$result = curl_exec($ch);
		curl_close($ch);

		if (strcmp($result, 'VERIFIED') === 0) {
			return true;
		}

		return false;
	 }
}

class PPValidationPaypal
{
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * Canceled_Reversal: 
	 * A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was
	 * reversed have been returned to you.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPaymentCanceledReversal($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_CANCELED_REVERSAL';

		return array();
	}

	/**
	 * A German ELV payment is made using Express Checkout. Probably we don't need it
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentCreated($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_CREATED';

		return array();
	}

	/**
	 * Denied: 
	 * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the
	 * pending_reason variable or the Fraud_Management_Filters_x variable.
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentDenied($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_DENIED';

		return array();
	}

	/**
	 * This authorization has expired and cannot be captured.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPaymentExpired($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_EXPIRED';

		return array();
	}

	/**
	 * The payment has failed. This happens only if the payment was made from your customer’s bank account.
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentFailed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_FAILED';

		return array();		
	}

	/**
	 * The payment is pending. See pending_reason for more information.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPaymentPending($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_PENDING';

		return array();
	}

	/**
	 * Refunded: 
	 * You refunded the payment.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPaymentRefunded($payment, $data, $transaction)
	{
		// @TODO: Configurtion is there to ask from admin What to do on partial refund
		$transaction->amount = $data['mc_gross'];
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_REFUNDED';

		return array();
	}

	/**
	 * Reversed: 
	 * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and
	 * returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentReversed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_REVERSED';

		return array();	
	}
	
	/**
	 * Processed: 
	 * A payment has been accepted. 
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentProcessed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_PROCESSED';

		return array();	
	}

	/**
	 * This authorization has been voided.
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPaymentVoided($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_VOIDED';

		return array();	
	}

	/**
	 * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPaymentCompleted(PPPayment $payment, $data, PPTransaction $transaction)
	{
		$errors = $this->validateNotification($payment, $data);

		if (empty($errors)) {
			$transaction->amount = $data['mc_gross'];
			$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_COMPLETED';
		}

		return $errors;
	}

	/**
	 * Process recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcessSubscr_payment($payment, $data, $transaction)
	{
		// @TODO: cros check subscr_id
		$errors = $this->validateNotification($payment, $data);

		$method = 'onPayment' . strtolower($data['payment_status']);
		$result = $this->$method($payment, $data, $transaction);
		$errors = array_merge($errors, $result);

		return $errors;
	}

	/**
	 * Process new subscription sign up
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcessSubscr_signup($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_SUBSCR_SIGNUP';
	
		//if free trail then change the invoice status to paid
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();

		return array();
	}
	
	/**
	 * Process subscription cancellation
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcessSubscr_cancel($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_SUBSCR_CANCEL';

		// Terminate the invoice
		$invoice = $payment->getInvoice();
		$invoice->terminate();

		return array();
	}

	/**
	 * Process subscription modification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcessSubscr_modify($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_SUBSCR_MODIFY';

		return array();
	}

	/**
	 * Process failed subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcessSubscr_failed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_SUBSCR_FAILED';
		
		return array();
	}

	/**
	 * Subscription expired on paypal
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcessSubscr_eot($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_SUBSCR_EOT';
		
		return array();
	}

	/**
	 * 
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcessNew_case($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_NEW_CASE';

		return array();
	}

	/**
	 * Validate IPN notifications for Buy Now, Donation, or Auction Smart Logos button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validateNotification(PPPayment $payment, $data)
	{
		// find the required data from post-data, and match with payment
		// check reciever email must be same.
		$email = $this->params->get('sandbox') ? $this->params->get('sandbox_merchant_email') : $this->params->get('merchant_email');
		$errors = array();

		if ($email != urldecode($data['business'])) {
			$errors[] = JText::_('COM_PAYPLANS_INVALID_PAYPAL_RECEIVER_EMAIL');
		}
		
		return $errors;
	}
}