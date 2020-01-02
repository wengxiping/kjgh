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

class PPEventOrder extends PayPlans
{
	/**
	 * Internal trigger after a transaction is stored
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansTransactionAfterSave($previous, $current)
	{
		// the transaction is not of any order's invoice then return 
		$invoice = $current->getInvoice();

		// do not mark subscription when the transaction and invoice is of 0 amount.
		// For eg:- in case of offline payment or when we want to dump the notification from payment gateway.
		//In these case transaction amount is of 0 amount bt actual amount will be of more then 0.
		if (floatval($current->getAmount()) == 0 && floatval($invoice->getTotal()) != 0) {
			return true;
		}

		// When the wallet amount is consumed then blank invoice object will be created
		// so no need to proceed further.
		if (!$invoice || !$invoice->getId()) {
		   return true;
		}

		$buyer = $invoice->getBuyer();

		// Purchaser must be a valid user
		if (!$buyer->getId()) {
			return true;
		}

		// Get the order
		$order = $invoice->getReferenceObject();

		if (!is_a($order, 'PPOrder')) {
			return true;
		}
		
		// When refund is made externally then mark invoice as refunded
		if (floatval($current->getAmount()) < floatval(0)) {
			return self::_processRefund($current, $order);
		}
		
		// First check for whether its required to create new invoice or not
		$invoiceRequired = self::invoiceRequired($invoice, $order);

		if ($invoiceRequired === false) {
			return true;
		}
		
		// When transaction with greater than zero. Create invoice if order is not expired
		return $this->processNewTransaction($invoice, $order, $current, $current->getAmount());
	}
	
	/**
	 * Internal method to process a new transaction
	 *
	 * @since	4.0.0
	 * @access	protected
	 */
	protected function processNewTransaction($invoice, PPOrder $order, $transaction, $amount)
	{
		// When order is expired or hold then do nothing and return
		if ($order->isExpired() || $order->isHold() || $order->isCancelled()) {
			return true;
		}

		$subscription = $order->getSubscription();

		// If order does not have any subscription, then do nothing
		if (!($subscription instanceof PPSubscription)) {
			return false;
		}		

		// Process invoice for the transaction
		$invoice = self::processInvoice($order, $invoice, $transaction);

		if (!($invoice instanceof PPInvoice)) {
			return false;
		}				

		$expiration = $invoice->getCurrentExpiration(true);

		if ($expiration == '000000000000' && $invoice->isRecurring()) {
			$invoiceParams = $invoice->table->getParams();
			$expType = $invoiceParams->get('expirationtype', '');

			$expTime = 'expiration';
			if ($expType === PP_RECURRING_TRIAL_1 || $expType === PP_RECURRING_TRIAL_2) {
				$expTime = 'trial_time_1';
			}

			$expiration = $invoiceParams->get($expTime, '');
		}
		
		$order->renewSubscription($expiration);
		$order->status = PP_ORDER_COMPLETE;
		$order->save();

		return true;
	}
	
	/**
	 * Create new invoice if previous invoice is on paid or refunded status. 
	 * Mark invoice as paid if amount is available
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected static function processInvoice($order, $invoice, $transaction)
	{	
		// if invoice status is paid/refunded then we need to get total of next invoice
		// otherwise use total of current invoice
		$total = $invoice->getTotal();

		if ($invoice->isPaid() || $invoice->isRefunded()) {
			$invoiceCount = $order->getRecurringInvoiceCount();
			$total = $invoice->getTotal($invoiceCount+1);
			$user = $invoice->getBuyer();
		
			$newInvoice = $order->createInvoice();
		
			if (!($newInvoice instanceof PPInvoice)) {
				return false;
			}
		} else{
			$newInvoice = $invoice;
		}

		// Do nothing if invoice amount and transaction amount is not same.
		$total = $newInvoice->getTotal();

		if (floatval($total) != $transaction->getAmount()) {
			return true;
		}

		$date = PP::date();

		$newInvoice->status = PP_INVOICE_PAID;
		$newInvoice->paid_date = $date;
		$newInvoice->save();

		return $newInvoice;
	}

	/**
	 * Triggered when an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansInvoiceAfterSave($previousInvoice, $newInvoice)
	{
		// Get the order from the invoice
		$order = $newInvoice->getReferenceObject();

		if (!is_a($order, 'PPOrder')) {
			return true;
		}

		if ($newInvoice->isConfirmed() && ($previousInvoice == null  || ($previousInvoice->getStatus() != $newInvoice->getStatus()))) {

			$order = $newInvoice->getReferenceObject();
			
			if (!$order->isCompleted()) {
				$order->status = PP_ORDER_CONFIRMED;
				return $order->save();
			}
		}

		// When invoice is refunded then mark related order and subscription as hold
		if (($previousInvoice != null) && ($previousInvoice->isPaid()) && ($newInvoice->isRefunded())) {
			return $order->refund();
		}
		
		// Creating a new invoice for the first time. If it is not for first time then do nothing
		if ($previousInvoice != null) {
			return true;
		}

		// Get the first invoice
		$invoice = $order->getInvoice(1);

		// If the first invoice has the same id as the new invoice, we shouldn't be doing anything
		if (!$invoice || $newInvoice->getId() == $invoice->getId()) {
			return true;
		}
		
		// Get all modifires
		$modifiers = $invoice->getModifiers();
		
		// if any modifier is for each time then apply it
		PPHelperModifier::applyConditionally($newInvoice, $invoice, $modifiers);

		// Trigger an event after invoice paid for renewal
		if ($newInvoice->isRenewalInvoice()) {

			$order = $invoice->getReferenceObject();
			$subscription = $order->getSubscription();

			$args = array($subscription, $newInvoice);
			PPEvent::trigger('onPayplansSubscriptionRenewalComplete', $args, '', $subscription);
		}
		
		$newInvoice->refresh()->save();
	}
	
	/**
	 * Process refund for an order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected static function _processRefund($transaction, $order)
	{
		// If order status already hold then do nothing 
		// In case when transaction save after setting message then this process repeats and mark all invoices paid (only last invoice should be paid)
		if ($order->isHold()) {
			return true;	
		}

		$invoiceToRefund = false;
		
		if (!($transaction instanceof PPTransaction)) {
			return true;
		}

		$transactionAmount = abs($transaction->getAmount());
		
		// When invoice amount is same as that of txn then mark the invoice refunded
		// otherwise mark the last invoice of the order as refunded 
		$invoices = array_reverse($order->getInvoices(PP_INVOICE_PAID));
		
		if (!$invoices) {
			return true;
		}
		
		foreach ($invoices as $invoice) {
			$invoiceAmount = $invoice->getTotal();
			
			if (floatval($invoiceAmount) == floatval($transactionAmount)) {
				$invoiceToRefund = true;
				break;
			}
		}
		
		if ($invoiceToRefund === false) {
			$invoice = array_shift($invoices);
		}
			
		$invoice->status = PP_INVOICE_REFUNDED;

		// Save the invoice
		$invoice->save();

		$transactionMessage = $transaction->getMessage();

		// Insert a message for the transaction if nothing is provided
		if (empty($transactionMessage)) {
			$transaction->message = JText::_('COM_PAYPLANS_TRANSACTION_TRANSACTION_MADE_FOR_REFUND');
			
			$transaction->save();
		}
		
		return true;
	}
	
	/**
	 * Triggered after a payment is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansPaymentAfterSave($previousPayment = null, PPPayment $newPayment)
	{
		// When creating invoice for the first time. If it is not for first time then do nothing
		if ($previousPayment != null) {
			return true;
		}	
		
		$invoice = $newPayment->getInvoice();
		$order = $invoice->getReferenceObject();

		if (!is_a($order, 'PPOrder')) {
			return true;
		}
		
		$order->setParam('last_master_invoice_id', $invoice->getId());
		
		return $order->save();
	}

	/**
	 * Triggered when a new payment request is initiated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansNewPaymentRequest($subscription)
	{
		//set the inprocess variable on new subscription instance so that 
		//in between subscription is saved it does not create a loop
		if(!isset($subscription->inprocess)){
			$order = $subscription->getOrder();
			$invoice = $order->getLastMasterInvoice();		

			$totalPaidInvoices = count(($order->getInvoices(PP_INVOICE_PAID, true)));

			// do nothing when invoice is not recurring
			if(!($invoice instanceof PPInvoice)|| !($invoice->isRecurring())){
				return true;
			}
			
			//first check for whether its required to create new invoice or not.
			$invoice = $order->getLastMasterInvoice();
			
			if (self::invoiceRequired($invoice, $order) === false) {
				return true;
			}
			
			$subscription->inprocess = true;

			//when order is cancel or expired then do not request for  payment
			if(!in_array($order->getStatus(), array(PP_ORDER_CANCEL, PP_ORDER_EXPIRED))
			&& !$invoice->requestPayment($totalPaidInvoices)){
				
				return false;
			}

			//unset the variable after processing is done
			unset($subscription->inprocess);
		}
	}
	
	/**
	 * Triggered before an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onPayplansInvoiceBeforeSave($prev, $new)
	{
		// Change the invoice serial here
		if (isset($prev) && in_array($prev->getStatus(), array(PP_INVOICE_CONFIRMED, PP_NONE)) && $new->getStatus() == PP_INVOICE_PAID) {
			$new->setNewSerial();
		}
		
		if (!(isset($prev) && $prev->getStatus() == PP_NONE && $new->getStatus() == PP_INVOICE_CONFIRMED)) {
			return true;
		}
		
		//create error log if plan is recurring and regular amount is zero after applying 100% discount
		if( $new->isRecurring() && floatval(0) == floatval($new->getRegularAmount() )){
			$message = JText::_('COM_PAYPLANS_LOG_100_PERCENT_DISCOUNT_ON_EACH_RECURRING');
			$error['Invoice ID'] = $new->getId();
			$error['Error'] = JText::_('COM_PAYPLANS_LOG_ERROR_ON_100_PERCENT_EACH_RECURRING_DISCOUNT');
			PPLog::log(PPLogger::LEVEL_INFO, $message, 'PayplansInvoice',$error);
		}
	}
	
	/**
	 * Checks for invoice counter
	 *
	 * @since	4.0.0
	 * @access	protected
	 */
	protected static function checkInvoiceCounter($order, $invoice)
	{
		// If total invoices(paid+refunded) are not equal to the last invoice counter then create log and return
		$invoices = $order->getInvoices(array(PP_INVOICE_PAID, PP_INVOICE_REFUNDED), true);

		$totalInvoices = count($invoices);
		sort($invoices);
		$lastInvoice = array_pop($invoices);
	
		$invoiceCount = 0;
		if($lastInvoice){
			$invoiceCount = $lastInvoice->getCounter();
		}

		if ($invoiceCount < $totalInvoices) {
			$subscription = $order->getSubscription();
			$message = JText::_('COM_PP_INVOICE_LOG_COUNTER_MISMATCH');
			$error['Subscription Id'] = $subscription->getId();
			$error['Actual Counter'] = $totalInvoices+1;
			$error['Expected Counter'] = $invoiceCount+1;
			
			PP::logger()->log(PPLogger::LEVEL_INFO, $message, '', 'SYSTEM', $error, '', '', true);
		}
		
		return true;
	}

	/**
	 * Determines if invoice is required
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected static function invoiceRequired($invoice, $order)
	{
		if (!$invoice->isPaid() && !$invoice->isRefunded()) {
			return true;
		}

		self::checkInvoiceCounter($order, $invoice);
		
		$recurrenceCount = $invoice->getRecurrenceCount();
		$recurring = $invoice->isRecurring();
		$invoiceCount = $order->getRecurringInvoiceCount();
		
		if (!$invoice->isRecurring()) {
			return false;
		}

		// For 0 reccurrence count, always create new invoice
		if ($recurrenceCount == 0) {
			return true;
		}
			
		$recurringType = $invoice->getRecurringType();

		if ($recurringType == PP_RECURRING && $invoiceCount >= $recurrenceCount) {
			return false;
		}
		
		if ($recurringType == PP_RECURRING_TRIAL_1 && $invoiceCount >= ($recurrenceCount + 1)) {
			return false;
		}
		
		if ($recurringType == PP_RECURRING_TRIAL_2 && $invoiceCount >= ($recurrenceCount + 2)) {
			return false;
		}
		
		return true;

	}
}
