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

class PPAppPaysiteCash extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}


	/**
	 * This method is triggered when paypal notifies us
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		$tasks = array('notify', 'complete', 'cancel');

		if ($view != 'payment' || !in_array($task, $tasks) ) {
			return true;
		}

		$paymentKey = $this->input->get('ref', false);

		if ($paymentKey) {
			$this->input->set('payment_key', $paymentKey, 'POST');
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

		return $this->display('form');
  	}

  	/**
	 * Log after a payment is received
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{			
		if ($action == 'error') {
			return true;
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * This method is triggered when PayPal connects to our payment notification page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();
		$helper = $this->getHelper();	

		// If same notification came more than once, check if transaction already exists
		// if yes then do nothing and return

		$transactionId = PP::normalize($data, 'id_trans', 0);
		$subscriptionId = PP::normalize($data, 'num_abo', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, 0);

		if (!empty($transactions)) {			
			foreach ($transactions as $transaction) {

				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);

				if ($transaction->getParam('etat','') == $data['etat']) {
					return true;
				}
			}
		}

		// create transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $data);

		if ($data['etat'] == 'ok') {

			$transaction->amount = $data['montant_sent'];
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYSITE_CASH_TRANSACTION_COMPLETED';

		} else {
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYSITE_CASH_TRANSACTION_FAILED';
		}

		//store the response in the payment AND save the payment
		if (!$transaction->save()) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILED');
			PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);
		}

		// send payment confirmation email
		$helper->sendConfirmationEmail($invoice, $data);

		$payment->save();
		return true;
	}
}

