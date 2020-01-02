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

class PPHelperPaypalProProcessor
{
	private $params = null;

	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * A reversal has been canceled. For example, you won a dispute with the customer, 
	 * and the funds for the transaction that was reversed have been returned to you.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_canceled_reversal(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_COMPLETED';

		return array();
	}

	/**
	 * The payment has been completed, and the funds have been added successfully to your account balance.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_completed(PPPayment $payment, $transaction, $data)
	{
		$errors = $this->validate($payment, $data);
		
		if (!$errors) {
			$transaction->amount = PP::normalize($data, 'mc_gross', 0);
			$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_COMPLETED';
		}

		return $errors;
	}

	/**
	 * A German ELV payment is made using Express Checkout.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_created(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_CREATED';

		return array();
	}

	/**
	 * This authorization has expired and cannot be captured.
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayment_expired(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_EXPIRED';

		return array();
	}

	/**
	 * The payment has failed. This happens only if the payment was
	 * made from your customer’s bank account.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_failed(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_FAILED';

		return array();		
	}

	/**
	 * The payment is pending. See pending_reason for more information.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_pending(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_PENDING';

		return array();
	}

	/**
	 * Merchant refunded the payment.
	 * What to do on partial refund check the stored amount against the payment amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_refunded(PPPayment $payment, $transaction, $data)
	{
		$transaction->amount = -$data['mc_gross'];
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_REFUNDED';

		return array();
	}

	/**
	 * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and
	 * returned to the buyer. The reason for the reversal is specified in the
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayment_reversed(PPPayment $payment, $transaction, $data)
	{
		$transaction->amount = -$data['mc_gross'];
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_REVERSED';

		return array();	
	}

	/**
	 * A payment has been accepted.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayment_processed(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_PROCESSED';

		return array();	
	}

	/**
	 * Recurring payment skipped; it will be retried up to a total of 3 times, 5 days apart
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcess_recurring_payment_skipped(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_PAYMENT_SKIPPED';

		return array();
	}

	/**
	 * Payment profile created successfully
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcess_recurring_payment_profile_created(PPPayment $payment, $transaction, $data)
	{
		$transaction->amount = 0;
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_TRANSACTION_PROFILE_CREATED';

		return array();
	}

	/**
	 * Recurring payment completed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcess_recurring_payment(PPPayment $payment, $transaction, $data)
	{
		$transaction->amount = PP::normalize($data, 'mc_gross', 0);
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_COMPLETED';

		return array();
	}

	/**
	 * Cancelled recurring payment profile
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcess_recurring_payment_profile_cancel(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_RECURRING_PROFILE_CANCEL';

		return array();
	}

	/**
	 * Recurring payment profile modified
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcess_recurring_payment_profile_modified(PPPayment $payment, $transaction, $data)
	{
		// XITODO : what to do here
	}

	/**
	 * Recurring payment failed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onProcess_recurring_payment_failed(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_RECURRING_PAYMENT_FAILED';

		return array();
	}

	/**
	 * Recurring payment expired
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onProcess_recurring_payment_expired(PPPayment $payment, $transaction, $data)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_PRO_TRANSACTION_RECURRING_PAYMENT_EXPIRED';

		return array();
	}

	/**
	 * Ensure that the IPN sent is a valid IPN
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function validate(PPPayment $payment, $data)
	{
		$errors = array();

		// Find the required data from post-data, and match with payment. Check reciever email must be same.
		$merchantEmail = $this->params->get('merchantEmail', '');
		$receiverEmail = PP::normalize($data, 'receiver_email', '');
		$paymentKey = PP::normalize($data, 'payment_key', '');

		if ($merchantEmail != $receiverEmail) {
			$errors[] = JText::_('COM_PAYPLANS_INVALID_PAYPAL_PRO_RECEIVER_EMAIL');
		}

		// Ensure that the payment key matches
		if ($payment->getKey() != $paymentKey) {
			$errors[] = JText::_('COM_PAYPLANS_INVALID_PAYPAL_PRO_PAYMENT_KEY');
		}

		return $errors;
	}
}