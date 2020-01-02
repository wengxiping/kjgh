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

class PP2CheckoutProcessor
{
	private $params = null;

	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * New order created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function order_created($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_ORDER_CREATED_' . JString::strtoupper($data['invoice_status']);

		// after refund is processed 2checkout again send a notification having paid as well as refund variables.
		// so if such notification comes, just make a 0 amount transaction.
		if (isset($data['item_type_2']) && !empty($data['item_type_2'])) {
			return array();
		}

		$activation = $this->params->get('activation', '');

		if ($activation != 'OrderCreation') {
			return array();
		}


		if (in_array($data['invoice_status'], array('approved', 'deposited','pending'))) {
			// pending, declined, approved, deposited
			if ($data['recurring'] == 0) {
				$transaction->amount = $data['item_list_amount_1'];
			}
			
			// change status if its recurring order creation 
			// otherwise do nothing
			if (isset($data['recurring']) && $data['recurring']) {
				$transaction->amount = $data['item_list_amount_1'];
			}
		}
		
		return array();
	}

	/**
	 * Fraud status changed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function fraud_status_changed($payment, $data, $transaction)
	{
		$activation = $this->params->get('activation', '');

		// fail, wait, pass, empty
		$status = PP::normalize($data, 'fraud_status', 'NO_STATUS');

		if ($activation == 'FraudStatus' && $status == 'pass') {

			if ($data['recurring'] == 0) {
				$transaction->amount = $data['item_list_amount_1'];
			}
			
			if (isset($data['recurring']) && $data['recurring']) {
				$transaction->amount = $data['item_list_amount_1'];
			}
		}

		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_FRAUD_STATUS_CHANGED_' . JString::strtoupper($status);

		return array();
	}

	/**
	 * Shipping status modified
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ship_status_changed($payment, $data, $transaction)
	{
		if (!isset($data['ship_status'])) {
			return array();
		}

		// not_shipped, shipped, or empty (if intangible / does not need shipped)
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_SHIP_STATUS_CHANGED_' . JString::strtoupper($data['ship_status']);

		return array();
	}

	/**
	 * Invoice status changed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function invoice_status_changed($payment, $data, $transaction)
	{
		$activation = $this->params->get('activation', '');

		if ($activation == 'OrderCreation') {
			// at the time or order creation, if invoice is in pending status, we activate subscription
			// if that actiavted subscription get declined, make a negative transaction.
			if (strtolower($data['invoice_status']) == 'declined') {

				if ($data['recurring'] == 0) {
					$transaction->amount = -$data['item_list_amount_1'];
				}
				
				if (isset($data['recurring']) && $data['recurring']) {
					$transaction->amount = -$data['item_list_amount_1'];
				}
			}
		}

		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_INVOICE_STATUS_CHANGED_' . JString::strtoupper($data['invoice_status']);

		return array();
	}

	/**
	 * Refund processed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refund_issued($payment, $data, $transaction)
	{
		$transaction->amount = -$data['item_list_amount_1'];
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_REFUND_ISSUED';

		return array();
	}

	/**
	 * Recurring subscription successful
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function recurring_installment_success($payment, $data, $transaction)
	{
		$transaction->amount = $data['item_list_amount_1'];
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_RECURRING_INSTALLMENT_SUCCESS';

		return array();
	}

	/**
	 * Recurring subscription failed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function recurring_installment_failed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_RECURRING_INSTALLMENT_FAILED';

		return array();
	}

	/**
	 * Recurring subscription stopped
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function recurring_stopped($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_RECURRING_STOPPED';

		return array();
	}

	/**
	 * Recurring completed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function recurring_complete($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_RECURRING_COMPLETE';

		return array();
	}

	/**
	 * User restarted recurring subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function recurring_restarted($payment, $data, $transaction)
	{
		// XITODO : need to work
		$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_TRANSACTION_STATUS_RECURRING_RESTARTED';

		return array();
	}
}