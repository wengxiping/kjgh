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

class PPAppPayzen extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation' || $eventName == 'onPayplansBeforeStoreIpn') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Triggered during controller creation to determine if it should execute any tasks
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || ($task != 'complete') ) {
			return true;
		}
		
		$paymentKey = $this->input->get('vads_order_id', 0);
		
		if (!empty($paymentKey)) {
			$this->input->set('payment_key', $paymentKey);

			return true;
		}

		return true;
	}

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
		$payload = $helper->getPaymentRequestPayload($payment);

		$this->set('formUrl', $helper->getFormUrl());
		$this->set('payload', $payload);
		$this->set('isRecurring', $invoice->isRecurring());
		
		return $this->display('form');
  	}

  	/**
	 * Triggered after payment completion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		$invoice = $payment->getInvoice();
    	
    	$transactionId = PP::normalize($data, 'vads_trans_id', 0);
    	$subscriptionId = PP::normalize($data, 'vads_trans_uuid', 0);

    	// Check for duplicate transactions
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, 0);
			
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				
				if ($transaction->getParam('vads_trans_status', '') == $data['vads_trans_status']) {
					return true;
				}
			}
		}

    	//create new transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $data);		
				
		if ($data['vads_trans_status '] == 'CAPTURED' || $data['vads_trans_status'] == 'AUTHORISED') {

				$transaction->amount = $data['vads_amount'];
				$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYZEN_TRANSACTION_COMPLETED';

		} else {

				$transaction->message = $data['vads_trans_status'];

				$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_PAYZEN_PAYMENT_PROCESS');
				PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data,'PayplansPaymentFormatter', '', true);
		}	
	
		$transaction->save();
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered before saving ipn notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansBeforeStoreIpn($ipn, $data)
	{
		if (!$data) {
			return true;
		}

		if ($data['gateway'] != 'payzen') {
			return true;
		}

		$hideData = array('vads_card_brand', 'vads_card_number', 'vads_expiry_month', 'vads_expiry_year');

		foreach ($data as $key => $value) {
			if (in_array($key, $hideData)) {
				unset($data[$key]);
			}
		}

		$ipn->json = json_encode($data);
		$ipn->query = http_build_query($data);

		return true;
	}

}
