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

class PPAppPayfast extends PPAppPayment
{
	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();

		$data = $helper->createPaymentRequest($payment, $invoice);
		$postUrl = $helper->getPayfastUrl();

		$this->set('post_url', $postUrl);
		$this->set('data', $data);
		
		return $this->display('form');
	}
	
	/**
	 * Triggered after payment process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		$logs	= PPLog::getLog($payment, PPLogger::LEVEL_ERROR);
		$record = array_pop($logs);		
		if ($record && !empty($record)) {
			$action = 'error';
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
	
	/**
	 * Triggered when notification come from payfast
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();
		$errors = array();
		
		// Check with payfast to see if the IPN is valid
		$valid = $helper->validateIPN($data);

		if (!$valid) {

			$transaction = PP::createTransaction($invoice, $payment, 0, 0, 0, $data);
			$transaction->message = 'COM_PAYPLANS_APP_PAYPAL_INVALID_IPN';
			$transaction->save();

			$message = JText::_('COM_PAYPLANS_LOGGER_PAYMENT_INVALID_IPN');

			PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);
			
			return "INVALID IPN";
		}


		// If same notification came more than once, check if transaction already exists
		// if yes then do nothing and return
		$transactionId = PP::normalize($data, 'pf_payment_id', 0);
		$subscriptionId = PP::normalize($data, 'subscr_id', 0);
		$parentTransactionId = PP::normalize($data, 'parent_txn_id', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentTransactionId);

		if (!empty($transactions)) {			
			foreach ($transactions as $transaction) {
				
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);
				$par = $transaction->getParam('txn_type', '');
				
				if ($transaction->getParam('payment_status','') == $data['payment_status']) {
					return true;
				}
			}
		}
		
		// create transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentTransactionId, $data);
		
		$func_name = isset($data['payment_status']) ? '_on_payment_'.JString::strtolower($data['payment_status']) : 'EMPTY';
		
		if (method_exists($this, $func_name)) {
			$errors = $this->$func_name($payment, $data, $transaction);
		} else {
			$errors[] = JText::_('COM_PAYPLANS_APP_PAYFAST_INVALID_TRANSACTION_TYPE_OR_PAYMENT_STATUS');
		}
		
		//if error present in the transaction then redirect to error page
		if (!empty($errors)) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_PAYFAST_PAYMENT_PROCESS');
			PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $payment, $errors, 'PayplansPaymentFormatter', '', true);
		}
	
		//store the response in the payment AND save the payment
		if (!$transaction->save()) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILED');
			PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);
		}
		
		$payment->save();
		return count($errors) ? implode("\n", $errors) : ' No Errors';
	}
	
	protected function _on_payment_complete($payment, $data, $transaction)
	{
		// Completed: The payment has been completed, and the funds have been
		// added successfully to your account balance.
		$errors = $this->_validateNotification($payment, $data);
		if (empty($errors)) {
			$transaction->amount = $data['amount_gross'];
			$transaction->message = 'COM_PAYPLANS_APP_PAYFAST_TRANSACTION_COMPLETED';
		}
		return $errors;
	}
	
	function _validateNotification(PPPayment $payment, array $data)
	{
		$helper = $this->getHelper();
		$errors = array();

		// find the required data from post-data, and match with payment, reciever email must be same.
		$merchantId = $helper->getMerchantId();
		if ($merchantId != $data['merchant_id']) {
			$errors[] = JText::_('COM_PAYPLANS_INVALID_PAYFAST_MERCHANT_ID');
		} 
		     	
		return $errors;
	}
	
	protected function _on_payment_failed($payment, $data, $transaction)
	{
		$transaction->message = 'COM_PAYPLANS_APP_PAYFAST_TRANSACTION_FAILED';
	}

}
