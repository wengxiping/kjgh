<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPAppEway extends PPAppPayment
{
	/**
	 * Determines if the app supports refund
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function supportForRefund()
	{
		return true;
	}

	/**
	 * Website eway support different time period for trial subscription, so return true
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFirstExpirationtimeApplicable()
	{
		return true;
	}

	/**
	 * Determines if it supports cancellation
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSupportPaymentCancellation($invoice)
	{
		if ($invoice->isRecurring()) {
			return true;
		}
		return false;
	}

	/**
	 * Renders the payment form 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();

		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();
		$paymentKey = $payment->getKey();
		$user = $invoice->getBuyer();
		$formUrl = $helper->getFormUrl($paymentKey);
		$cancelUrl = $helper->getCancelUrl($paymentKey);
		$sandbox = $helper->isSandbox();

		$this->set('user', $user);
		$this->set('sandbox', $sandbox);
		$this->set('cancelUrl', $cancelUrl);
		$this->set('formUrl', $formUrl);
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('amount', $amount);
		$this->set('currency', $invoice->getCurrency());
		
		return $this->display('form');
	}

	/**
	 * Triggered after user completes payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return;
		}

		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		//Create Customer Profile and Customer Payment Profile
		$client = $helper->getSoapClient($invoice);

		if ($client === false) {
			$message = $helper->getError();

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, array(), 'PayplansPaymentFormatter', '', true);

			PP::info()->set($message, 'error');
			$redirect = JRoute::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);
			return PP::redirect($redirect);
		}

		// Check if we need to create a new customer profile
		$gatewayParams = $payment->getGatewayParams();
		$profileId = $gatewayParams->get('profile_id', 0);

		if (!$profileId) {
			$profileId = $helper->createNewCustomer($payment, $invoice, $data, $client);

			if ($profileId === false) {
				PP::info()->set($helper->getError(), 'error');
				$redirect = JRoute::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey() . '&tmpl=component', false);

				return PP::redirect($redirect);
			}
		}

		//Customer profile created successfully now do your process
		$amount = number_format($invoice->getTotal(),2);

		// Free Trial Payment
		if ($amount == '0.00' && $invoice->isRecurring()) {

			$recurrenceCount = $helper->getRecurrenceCount($invoice);
			$helper->processRecurringPayment($payment, $invoice,  $recurrenceCount, $profileId, array());

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}
		
		// After successful profile creation create Transaction Profile
		$response = (array)$helper->initiatePaymentProcess($invoice, $payment, $profileId, $client);
		
		$txnId = PP::normalize($response, 'ewayTrxnNumber', 0);
		$subscrId = $profileId;

		// Check if previous transactions already exists
		$transactions = $this->getExistingTransaction($invoice->getId(), $txnId, $subscrId, 0, $response);

		if ($transactions) {			
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);
				if ($transaction->getParam('ewayTrxnStatus','') == strtolower($response['ewayTrxnStatus'])) {
					return true;
				}
			}
		}
		

		// Connection Failed then show proper error message
		if (strtolower($response['ewayTrxnStatus']) == false) {
			$message = JText::_('COM_PAYPLANS_APP_EWAY_TRANSACTION_ERROR');
			$error = $response['ewayTrxnError'].'('.$response['ewayTrxnNumber'].')';

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

			PP::info()->set(JText::_($error), 'error');
			$redirect = JRoute::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey() . '&tmpl=component', false);

			return PP::redirect($redirect);
		}
		
		//this records the transaction information into payplans so it can activate stuff or not
		if ($invoice->isRecurring()) {
			$recurrenceCount = $helper->getRecurrenceCount($invoice);
			
			$helper->processRecurringPayment($payment, $invoice, $recurrenceCount, $profileId, $response);
		} else {
			$helper->processNonRecurringPayment($payment, $invoice, $response, $profileId);
		}

		$payment->save();

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Initiated during cron to process recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment)
	{
		//this is the previous payment object?
		$invoice = $payment->getInvoice();

		if (!$invoice->isRecurring()) {
			return false;
		}

		$gatewayParams = $payment->getGatewayParams();
		$recurrenceCount = $gatewayParams->get('pending_recur_count');

		if ($recurrenceCount <= 0) {
			return false;
		}

		$helper = $this->getHelper();
		$client = $helper->getSoapClient();

		$profileId = $gatewayParams->get('profile_id', 0);

		$response = (array) $helper->initiatePaymentProcess($invoice, $payment, $profileId, $client);
		$status = PP::normalize($response, 'ewayTrxnStatus', 'false');

		if ($status == 'false') {
			$message = JText::_('COM_PAYPLANS_APP_EWAY_INVALID_TRANSACTION_TYPE_OR_PAYMENT_STATUS');
			$error = $response['ewayTrxnError'].'('.$response['ewayTrxnNumber'].')';

			return PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);			
		}

		$helper->processRecurringPayment($payment, $invoice, $recurrenceCount, $profileId, $response);

		return true;
	}

	/**
	 * Triggered to terminate a payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $invoiceController) 
	{
		parent::onPayplansPaymentTerminate($payment, $invoiceController);

		return true;
	}

	/**
	 * Triggered when refunding a transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refundRequest(PPTransaction $transaction, $amount)
	{
		$helper = $this->getHelper();

		return $this->helper->refund($transaction, $amount);
	}
}
