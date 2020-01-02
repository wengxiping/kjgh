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

class PPAppPayUMoney extends PPAppPayment
{
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
		$user = $invoice->getBuyer();
		
		$transactionId = $payment->getKey();
		$amount = $invoice->getTotal();
		$productInfo = $invoice->getTitle();
		$userName = $user->getUserName();
		$email = $user->getEmail();
		$userId = $user->getId();

		// Generate the hash based on the query
		$hash = $helper->getHash($transactionId, $amount, $productInfo, $userName, $email, $userId);

		// Prepare urls
		$formUrl = $helper->getFormUrl();
		$successUrl = $helper->getSuccessUrl($payment);
		$cancelUrl = $helper->getCancelUrl($payment);

		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('params', $this->getAppParams());
		$this->set('formUrl', $formUrl);
		$this->set('transactionId', $transactionId);
		$this->set('productInfo', $productInfo);
		$this->set('amount', $amount);
		$this->set('userName', $userName);
		$this->set('email', $email);
		$this->set('surl',	$successUrl);
		$this->set('furl', $cancelUrl);
		$this->set('curl', $cancelUrl);
		$this->set('userId', $userId);
		$this->set('hash', $hash);
		
		return $this->display('form');
	}

	/**
	 * Triggered after Payment 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}

		$status = PP::normalize($data, 'status', '');

		if ($status != 'success') {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('Error'), $payment, $data, 'PayplansPaymentFormatter');

			return parent::onPayplansPaymentAfter($payment, 'error', $data, $controller);			
		}

		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		
		list($status, $message) = $helper->validate($data, $invoice);

		if (!$status) {
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter');

			return parent::onPayplansPaymentAfter($payment, 'error', $data, $controller);
		}

		// Check for duplicate transactions
		$payuId = PP::normalize($data, 'payuMoneyId', 0);
		$transactions = $this->getExistingTransaction($invoice->getId(), $payuId, 0, 0);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				
				if ($transaction->getParam('mihpayid', '') == $data['mihpayid']) {
					return true;
				}
			}
		}

		$transactionId = PP::normalize($data, 'payuMoneyId', 0);
		
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $data);
		$transaction->amount = PP::normalize($data, 'amount', 0);
		$transaction->message = JText::_('COM_PAYPLANS_APP_PAYUMONEY_PAYMENT_COMPLETED_SUCCESSFULLY');
		$transaction->save();

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
}