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

class PPAppSmoovPay extends PPAppPayment
{
	/**
	 * Render Payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$params = $this->getAppParams();
		$merchantId = $params->get('merchant_id', '');

		$postUrl = $helper->getPostUrl();
		$cancelUrl = $helper->getCancelUrl($payment->getKey());
		$successUrl = $helper->getSuccessUrl($payment->getKey());

		$payload = array($payment->getKey(), $invoice->getTotal(), $invoice->getCurrency('isocode', 'USD'));
		$signature = $helper->sign($payload);

		$this->set('postUrl', $postUrl);
		$this->set('payment', $payment);
		$this->set('invoice', $invoice);
		$this->set('merchantId', $merchantId);
		$this->set('cancelUrl', $cancelUrl);
		$this->set('successUrl', $successUrl);
		$this->set('signature', $signature);

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
		// User cancelled payment
		if ($action == 'cancel') {
			return true;
		}

		$responseCode = PP::normalize($data, 'response_code', '');

		if ($responseCode != 1) {
			$action = 'error';

			PPLog::log(PPLogger::LEVEL_ERROR, "Error", $payment, $data, 'PayplansPaymentFormatter');
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}
		
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		list($valid, $message) = $helper->validate($data, $invoice);

		if (!$valid) {
			$action	= 'error';

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter');

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}
		
		// Ensure that there are no duplicates
		$transactions = $this->getExistingTransaction($invoice->getId(), $data['reference_code'], 0, 0);
		
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);

				if ($transaction->getParam('response_code', '') == $data['response_code']) {
					return true;
				}
			}
		}
		
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->amount = $data['total_amount'];
		$transaction->message = JText::_('COM_PP_PAYMENT_COMPLETED_SUCCESSFULLY');
		$transaction->gateway_txn_id = PP::normalize($data, 'reference_code', 0);
		$transaction->gateway_subscr_id = 0;
		$transaction->gateway_parent_txn = 0;
		
		$params = new JRegistry($data);
		$transaction->params = $params->toString();
		$transaction->save();
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
}