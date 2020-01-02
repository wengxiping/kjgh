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

class PPHelper2Checkout extends PPHelperPayment
{
	/**
	 * Retrieve the submission url for 2checkout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPostUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://www.2checkout.com/checkout/spurchase';

			if ($this->params->get('sandbox', false)) {
				$url = 'https://sandbox.2checkout.com/checkout/purchase';
			}

			if (!$this->params->get('sandbox', false) && $this->params->get('alternate_url', false)) {
				$url = 'https://www2.2checkout.com/checkout/spurchase';
			}
		}
		return $url;
	}

	/**
	 * Retrieves the return url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&gateway=2checkout&action=cancel';
		}

		return $url;
	}

	/**
	 * Formats the recurrence time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceTime($expTime)
	{
		$expTime['year'] = (int) PP::normalize($expTime, 'year', 0);
		$expTime['month'] = (int) PP::normalize($expTime, 'month', 0);
		$expTime['day'] = (int) PP::normalize($expTime, 'day', 0);
		
		// days, if days are not zero then, convert whole time into days and convert it into weeks 
		if (!empty($expTime['day'])) {

			$days = $expTime['year'] * 365;
			$days += $expTime['month'] * 30;
			$days += $expTime['day'];
			
			$weeks = intval($days/7, 10);
			
			return array('period' => $weeks, 'unit' => 'Week', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_RECURRING_MESSAGE'));
		}
		
		// if months are not empty 
		if (!empty($expTime['month'])) {
			$months = $expTime['year'] * 12;
			$months += $expTime['month'];
			
			return array('period' => $months, 'unit' => 'Month', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_RECURRING_MESSAGE'));
		}
		
		// if years are not empty 
		if (!empty($expTime['year'])) {			
			return array('period' => $expTime['year'], 'unit' => 'Year', 'frequency' => JText::_('COM_PAYPLANS_RECURRENCE_FREQUENCY_GREATER_THAN_ONE'),
									'message' => JText::_('COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_RECURRING_MESSAGE'));
		}
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
			$processor = new PP2CheckoutProcessor($this->params);
		}

		return $processor;
	}

	/**
	 * Formats the recurrence count to ensure that it is compatible with 2checkout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount(PPInvoice $invoice, $time)
	{
		$count = $invoice->getRecurrenceCount();

		if (intval($count) === 0) {
			return 'FOREVER';
		}
		
		$duration = $count * $time['period'];
		
		return $duration.' '.$time['unit'];
	}

	/**
	 * Determines if there are duplicate IPN notifications
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasDuplicates(PPInvoice $invoice, $transactions, $data)
	{
		if (!$transactions) {
			return false;
		}

		$messageId = PP::normalize($data, 'message_id', '');

		foreach ($transactions as $record) {
			$transaction = PP::transaction($record->transaction_id);
			$params = $transaction->getParams();

			if ($params->get('message_id', '') === $messageId && !isset($data['item_type_2'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determines which processes should be triggered
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process($payment, $data, $transaction)
	{
		$method = false;

		if (isset($data['message_type'])) {
			$method = JString::strtolower($data['message_type']);
		}

		$processor = $this->getProcessor();

		if (!$method || !method_exists($processor, $method)) {
			$errors = array(JText::_('COM_PAYPLANS_APP_2CHECKOUT_INVALID_MESSAGE_TYPE'));

			return $errors;
		}

		$errors = $processor->$method($payment, $data, $transaction);

		return $errors;
	}

	/**
	 * Validates the IPN request from 2checkout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($data)
	{
		$postKey = PP::normalize($data, 'key', '');
		$secretWord = $this->params->get('secret_word');
		$sid = $this->params->get('sid');
		$total = PP::normalize($data, 'total', '');

		$stringToHash = $secretWord . $sid . PP::normalize($data, 'order_number', '') . $total;

		if ($this->params->get('sandbox', false)) {
			$stringToHash = $secretWord . $sid . "1" . $total;
		}

		// If notification came from INS then need to do following else, in normal case it 
		if (isset($data['message_type'])) {
			$postKey = PP::normalize($data, 'md5_hash', '');
			$saleId = PP::normalize($data, 'sale_id', '');
			$invoiceId = PP::normalize($data, 'invoice_id', '');

			$stringToHash = $saleId . $sid . $invoiceId . $secretWord;
		}
		
		$key = strtoupper(md5($stringToHash));
		$valid = (strcmp($key, $postKey) == 0);

		return $valid;
	}
}