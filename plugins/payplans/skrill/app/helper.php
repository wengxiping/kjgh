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

class PPHelperSkrill extends PPHelperPayment
{
	protected $_location = __FILE__;
	
	private $txnStates = array(
							'PROCESSED' => 2, 
							'PENDING' => 0,
							'CANCELLED' => -1,
							'FAILED' => -2,
							'CHARGEBACK' => -3
						);

	// Transaction states
	const PROCESSED = 2;
	const PENDING = 0;
	const CANCELLED = -1;
	const FAILED = -2;
	const CHARGEBACK = -3;	

	private $languages = array('da','de','en','fo','fr','kl','it','no','nl','pl','ru','sv');

	/**
	 * Calculates the ending date given the expiration time
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function calculateEndDate($invoice, $expiration)
	{
		$startDate = PP::date('now');
		$endDate = $startDate;
		
		$recurrence_count  = $invoice->getRecurrenceCount();
		// IMP : add 10 years when recurrence count is 0
		if(intval($recurrence_count) ===0){
			$endDate = $endDate->addExpiration('100000000000');
			return $endDate->toFormat('%d/%m/%Y');
		}
		
		
		for($i=0; $i< $recurrence_count; $i++){
			$endDate   = $endDate->addExpiration($expiration);
		}
		
		return $endDate->toFormat('%d/%m/%Y');
	}

	public function getSupportedLanguages()
	{
		return $this->languages;
	}

	/**
	 * Retrieves language code that should be used for the site
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getLanguageCode()
	{
		static $code = null;

		if (is_null($code)) {
			$language = JFactory::getLanguage();
			$language = explode('-', $language->getTag());
			$languageCode = $language[0];
			
			// Default
			$code = 'en';

			if (in_array($languageCode, $this->languages)) {
				$code = $languageCode;
			}
		}
		
		return $code;
	}

	/**
	 * Ensure that the amount is formatted accordingly to Skrill's format
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getAmount($total)
	{
		$amount = number_format($total, 2);

		return $amount;
	}

	/**
	 * Generates the callback urls that should be used with Skrill
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getCallbackUrls(PPPayment $payment)
	{
		static $urls = array();

		if (!$urls) {
			$url = rtrim(JURI::root(), '/');
			$key = $payment->getKey();

			$urls['notify'] = $url . '/index.php?option=com_payplans&gateway=skrill&view=payment&task=notify';
			$urls['return'] = $url . '/index.php?option=com_payplans&gateway=skrill&view=payment&task=complete&action=success&payment_key=' . $key;
			$urls['cancel'] = $url . '/index.php?option=com_payplans&gateway=skrill&view=payment&task=complete&action=cancel&payment_key=' . $key;
		}

		return $urls;
	}

	/**
	 * Sets and format the recurrence time
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getRecurrenceTime($expiration)
	{
		$expiration['year'] = intval(PP::normalize($expiration, 'year', 0));
		$expiration['month'] = intval(PP::normalize($expiration, 'month', 0));
		$expiration['day'] = intval(PP::normalize($expiration, 'day', 0));
		
		// if only days are set then return days as it is
		if (!empty($expiration['day'])) {
			$days = $expiration['day'];
			
			if (!empty($expiration['month'])) {
				$days += $expiration['month'] * 30;

				if (!empty($expiration['year'])) {
					$days += $expiration['year'] * 365;
				}
			}
			
			return array(
					'period' => $days, 
					'unit' => 'day', 
					'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
					'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_MONEYBOOKERS_RECURRING_MESSAGE')
			);
		}
		
		// if months are set
		if (!empty($expiration['month'])) {
			$month = $expiration['month'];
			
			if (!empty($expiration['year'])) {
				$month += $expiration['year'] * 12;
			}
			
			return array(
					'period' => $month, 
					'unit' => 'month', 
					'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
					'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_MONEYBOOKERS_RECURRING_MESSAGE')
			);
		}
		
		// years
		if (!empty($expiration['year'])) {
			return array(
				'period' => $expiration['year'], 
				'unit' => 'year', 
				'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
				'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_MONEYBOOKERS_RECURRING_MESSAGE')
			);
		}

		return false;
	}

	/**
	 * Retrieves the transaction message based on the current status
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function getTransactionMessage($status)
	{
		$message = 'COM_PP_SKRILL_TRANSACTION_' . array_search($status, $this->txnStates);

		$message = JText::_($message);

		return $message;
	}

	/**
	 * Given the status of the request, determine if it should be processed as payment notification
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function isPaymentNotification($status)
	{
		$states = array(self::PROCESSED, self::CHARGEBACK);

		if (in_array($status, $states)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the transaction status is failed
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function isTransactionFailed($status)
	{
		return self::FAILED === $status;
	}

	/**
	 * Computes and verify the hash
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public function verifySignature($data)
	{
		$signature = PP::normalize($data, 'md5sig', '');

		if (!$signature) {
			return false;
		}

		$secretWord = $this->params->get('merchantsecretword');
		$secretWordHashed = strtoupper(md5($secretWord));

		$merchantId = PP::normalize($data, 'merchant_id', '');
		$transactionId = PP::normalize($data, 'transaction_id', '');
		$amount = PP::normalize($data, 'mb_amount', '');
		$currency = PP::normalize($data, 'mb_currency', 0);
		$status = PP::normalize($data, 'status', '');

		$hash = $merchantId . $transactionId . $secretWordHashed . $amount . $currency . $status;
		$hash = JString::strtoupper(md5($hash));

		// Ensure that the signature matches the hash
		if ($signature == $hash) {
			return true;
		}

		return false;
	}
}