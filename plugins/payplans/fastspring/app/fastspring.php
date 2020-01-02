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

class PPAppFastspring extends PPAppPayment
{	
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Render Payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{	
		$id = $this->input->get('id', 0, 'int');
		$invoice = $payment->getInvoice();
		$invoiceKey = $invoice->getKey();
		$redirect = PPR::_('index.php?option=com_payplans&view=thanks&invoice_key=' . $invoiceKey . '&tmpl=component', false);	

		// When there is an "id" in the request, we assume that their payment has succeed
		if ($id) {
			return PP::redirect($redirect);
		}

		if (is_object($data)) {
			$data = (array)$data;
		}

		$storeFrontUrl = $this->getAppParam('storefront_url',false);
		$accessKey = $this->getAppParam('access_key',false);
		$productId = $this->getPlanMapping($invoice, $payment);

		// If there is no mapping of the plan, prevent the order from happening since it isn't mapped to any products
		if (!$productId) {
			PP::info()->set('COM_PP_FASTSPRING_PLAN_NOT_ASSOCIATED', 'error');

			$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);
			return PP::redirect($redirect);
		}
		
		$this->set('storeFrontUrl', $storeFrontUrl);
		$this->set('accessKey', $accessKey);
		$this->set('productId', $productId);
		$this->set('payment', $payment);
		$this->set('invoice', $invoice);
		$this->set('redirect', $redirect);

		return $this->display('form');
	}

	/**
	 * Mapping of Plan with fastspring plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getPlanMapping(PPInvoice $invoice, PPPayment $payment)
	{
		$mapping = $this->getAppParam('plan_product_mapping');
		$plan = $invoice->getPlan();
		$productId = false;

		foreach ($mapping as $key => $map) {
			if ($map[0] == $plan->getId()) {
				$productId = $map[1];
				break;
			}
		}

		return $productId;
	}
	
	/**
	 * When controller called
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{		
		if ($view != 'payment' || $task != 'notify') {
			return true;
		}

		$contents = file_get_contents('php://input');
		$obj = json_decode($contents);

		$data = array_pop($obj->events);
		$paymentData = $data->data->tags;
		$paymentKey = $paymentData->key;

		// When we have the payment key, we'll need to set it in the request
		if ($paymentKey) {
			$this->input->set('payment_key', $paymentKey);
			$this->input->set('ipn_data', $contents);
		}

		return true;
	}
	
	/**
	 * Triggered when notification came from fastspring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();

		$contents = file_get_contents('php://input');
		$obj = json_decode($contents);
		
		$ipnData = array_pop($obj->events);
		$paymentData = $ipnData->data;
		
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();

		$gatewayTransactionId = isset($paymentData->order) ? $paymentData->order : '';
		$transaction->gateway_txn_id = $gatewayTransactionId;

		$subscriptionId = isset($paymentData->reference) ? $paymentData->reference : '';
		$transaction->gateway_subscr_id = $subscriptionId;

		$transaction->gateway_parent_txn = 0;
		
		$transactionParams = new JRegistry($ipnData);
		$transaction->params = $transactionParams->toString();

		$amount = 0;
		$message = 'COM_PP_PAYMENT_APP_FASTSPRING_TRANSACTION_NOT_COMPLETED';

		if ($paymentData->completed) {
			$amount = $invoice->getTotal();
			$message = 'COM_PP_PAYMENT_APP_FASTSPRING_TRANSACTION_COMPLETED';
		}

		$transaction->amount = $amount;
		$transaction->message = JText::_($message);

		$state = $transaction->save();

		if (!$state) {
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD'), $payment, $data, 'PayplansPaymentFormatter', '', true);

			return false;
		}

		$payment->save();

		return true;
	}
}
