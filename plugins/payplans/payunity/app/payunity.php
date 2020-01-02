<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPAppPayUnity extends PPAppPayment
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

		$checkoutId = $helper->getCheckoutId($payment);
		$scriptUrl = $helper->getScriptUrl($checkoutId);
		$postUrl = $helper->getPostUrl($payment->getKey());

		$this->set('checkoutId', $checkoutId);
		$this->set('postUrl', $postUrl);
		$this->set('src', $scriptUrl);

		return $this->display('form');
	}

	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if($action == 'cancel'){
			return true;
		}

		$invoice = $payment->getInvoice();

		$helper = $this->getHelper();
		$response = $helper->processResponse($payment, $data);

		$pattern  = '/^(000\.000\.|000\.100\.1|000\.[36])/';

		if (!preg_match($pattern, $response->result->code)) {

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUNITY_ERROR'), $payment, $data, 'PayplansPaymentFormatter', '',true);
			return true;
		} 

		//do check for valid payments.
		list($isValidResp, $message) = $helper->checkResponse($payment, $response);
		
		if (!$isValidResp) {

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUNITY_INVALID_RESPONSE'), $payment, (array) $data, 'PayplansPaymentFormatter', '',true);
			return true;
		}

		$transactionId = $response->id;
		$subscriptionId = $response->registrationId;
		//Duplicate Transactions Checking
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, 0);
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction);
				$statusCode = $transaction->getParam('result', '')->code;

				if($statusCode == $response->result->code){
					return true;
				}
			}
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $response);
		$transaction->amount = $response->amount;
		$transaction->message = 'COM_PAYPLANS_APP_PAYUNITY_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();

		if ($invoice->isRecurring()) {

			$recurrenceCount = $helper->getRecurrenceCount($invoice);

			$gatewayParams = $payment->getGatewayParams();
			$gatewayParams->set('pending_recur_count', $recurrenceCount);
			$gatewayParams->set('reference_id', $response->registrationId);

			$payment->gateway_params = $gatewayParams->toString();
			$payment->save();

		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);

	}

	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$helper = $this->getHelper();
		$response = $helper->processRecurringPayment($payment, $invoiceCount);

		$pattern = '/^(000\.000\.|000\.100\.1|000\.[36])/';
		
		if (!preg_match($pattern, $response->result->code)) {

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUNITY_ERROR_RECURRING_PAYMENT'), $payment, $response->result->code, 'PayplansPaymentFormatter', '',true);

			return false;
		} 

		$transaction = PP::createTransaction($invoice, $payment, $response->id, $sourceOrder, $response);
		$transaction->amount = $response->amount;
		$transaction->message = 'COM_PAYPLANS_APP_PAYUNITY_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();

		$recurrenceCount = $payment->getGatewayParam('pending_recur_count');
		$recurrenceCount = $recurrence_count  - 1;

		$gatewayParams = $payment->getGatewayParams();
		$gatewayParams->set('pending_recur_count', $recurrenceCount);

		$payment->gateway_params = $gatewayParams->toString();
		$payment->save();		
	}

}
 
