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

class PPAppCompay extends PPAppPayment
{
	/**
	 * Render Payment Page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		if (is_object($data)) {
			$data = (array)$data;
		}

		$invoice = $payment->getInvoice();
		
		$helper = $this->getHelper();
		$data = $helper->createPaymentRequest($payment, $invoice);
		$formUrl = $helper->getFormUrl();

		$this->set('data', $data);
		$this->set('formUrl', $formUrl);
		
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
		if ($action == 'cancel'){
			return true;
		}

		$transactionId = PP::normalize($data, 'provisionTransactionId', 0);
		if (!$transactionId) {
			return true;
		}

		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$amount = $helper->formatAmount($invoice->getTotal());

		// Check for duplicate transactions
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				$params = $transaction->getParams();

				// @TODO: Where is this $orderStatus coming from?
				if ($params->get('order_status') === $orderStatus) {
					return true;
				}
			}
		}

		$hash = $helper->generateHash($data);

		$transactionId = PP::normalize($data, 'provisionTransactionId', 0);
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $data);

		if ($hash != $data['HASH']) {
			$transaction->message = JText::_('COM_PAYPLANS_PAYMENT_APP_COMPAY_TRANSACTION_INVALID_HASH');

			$transaction->save();

			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_COMPAY_PAYMENT_PROCESS');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		$status = PP::normalize($data, 'acqResponseDetail', '');

		$transaction->message = JText::_('COM_PAYPLANS_PAYMENT_APP_COMPAY_TRANSACTION_FAILED');

		if ($status == 'Success') {
			$transaction->amount = PP::normalize($data, 'amount', 0);
			$transaction->message = JText::_('COM_PAYPLANS_PAYMENT_APP_COMPAY_TRANSACTION_COMPLETED');
		}

		$state = $transaction->save();

		if (!$state) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);
		}

		$payment->save();
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
}
