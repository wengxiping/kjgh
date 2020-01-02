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

require_once(__DIR__ . '/helper.php');

class PPAppAlipay extends PPAppPayment
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
	 * Called when controller created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || ($task != 'notify' && $task != 'complete')) {
			return;
		}

		$key = $this->input->get('out_trade_no', false);

		if ($key) {
			$this->input->set('payment_key', $key);
		}

		return true;
	}

	/**
	 * Render Payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		if (is_object($data)) {
			$data = (array)$data;
		}
			
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$options = array(			
			"service" => "create_direct_pay_by_user",
			"payment_type" => "1",
			"notify_url" => $helper->getUrl('index.php?option=com_payplans&gateway=alipay&view=payment&task=notify'),
			"return_url" => $helper->getUrl('index.php?option=com_payplans&view=payment&task=complete'),
			"out_trade_no" => $payment->getKey(),
			"subject" => $invoice->getTitle(),
			"total_fee" => $invoice->getTotal(),
			"body" => $invoice->getTitle(),
			"anti_phishing_key" => '',
			"exter_invoke_ip" => '',
			"_input_charset" => 'utf-8'
		);

		$response = $helper->createSubmitAction($options);

		$this->set('response', $response);
		return $this->display('form');
	}

	/**
	 * Triggered when notification came from alipay
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();
		
		// get the transaction instace of lib
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_txn_id = PP::normalize($data, 'trade_no', 0);
		$transaction->gateway_subscr_id = PP::normalize($data, 'out_trade_no', 0);

		$transactionParams = new JRegistry($data);
		$transaction->params = $transactionParams->toString();

		$helper = $this->getHelper();
		$response = $helper->createNotifyAction();

		// Response failed
		if (!$response) {
			$transaction->message = 'COM_PAYPLANS_APP_ALIPAY_FAIL';
			$transaction->save();

			return array(JText::_('COM_PAYPLANS_APP_ALIPAY_FAIL'));
		}


		// Ensure that there are no duplicate IPN
		$transactionId = 0;
		$subscriptionId = PP::normalize($data, 'out_trade_no', 0);
		
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, 0);
		
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				$transactionParams = $transaction->getParams();

				if ($transactionParams->get('trade_no', '') == $data['trade_no']) {
					return true;
				}
			}
		}

		$status = PP::normalize($data, 'trade_status', '');

		if ($helper->isSuccess($status)) {
			$transaction->amount = PP::normalize($data, 'total_fee', 0);
			$transaction->message = 'COM_PAYPLANS_APP_ALIPAY_SUCCESS';
		}

		$transaction->save();
	}
	
	/**
	 * Triggered after payment process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		$logs = PPLog::getLog($payment, PPLogger::LEVEL_ERROR);
		$record = array_pop($logs);
			
		if ($record && !empty($record)) {
			$action = 'error';
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

}
