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

class PPAppRemita extends PPAppPayment
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

		if (is_object($data)) {
			$data = (array) $data;
		}

		$params = $this->getAppParams();
		$postUrl = $helper->getPostUrl();

		$merchantId = $helper->getMerchantId();
		$serviceTypeId = $helper->getServiceId();

		$total = $invoice->getTotal();
		$paymentKey = $payment->getKey();
		$responseUrl = $helper->getResponseUrl($paymentKey);

		$hash = $helper->signRequest($paymentKey, $total, $responseUrl);
		$sandbox = $helper->isSandbox();

		$this->set('payment', $payment);
		$this->set('sandbox', $sandbox);
		$this->set('merchantId', $merchantId);
		$this->set('serviceTypeId', $serviceTypeId);
		$this->set('params', $params);
		$this->set('postUrl', $postUrl);
		$this->set('hash', $hash);
		$this->set('total', $total);
		$this->set('paymentKey', $paymentKey);
		$this->set('responseUrl', $responseUrl);
		$this->set('sandbox', $sandbox);

		return $this->display('form');
	}

	/**
	 * Triggered after user clicks on complete payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}

		$invoice = $payment->getInvoice();
		$amount = number_format($invoice->getTotal(), 2);
		
		$orderId = PP::normalize($data, 'orderID', 0);

		if (!$orderId) {
			$message = JText::_('COM_PP_LOGGER_ERROR_IN_REMITA_PAYMENT_PROCESS');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		$helper = $this->getHelper();

		$hash = $helper->signStatus($orderId);
		$statusUrl = $helper->getStatusUrl($orderId, $hash);

		// Connect to remita
		$response = $helper->connect($statusUrl);

		$status = PP::normalize($response, 'status', false);

		$transactionId = PP::normalize($response, 'RRR', 0);
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $response);		

		// Successful transaction
		if ($status == '01' || $status == '00') {
			$transaction->amount = PP::normalize($response, 'amount', 0);
			$transaction->message = JText::_('COM_PP_PAYMENT_APP_REMITA_TRANSACTION_COMPLETED');
		} else {
			$transaction->message = JText::_('COM_PP_PAYMENT_APP_REMITA_TRANSACTION_FAILED');
		}
		
		$transaction->save();
		$payment->save();
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
}