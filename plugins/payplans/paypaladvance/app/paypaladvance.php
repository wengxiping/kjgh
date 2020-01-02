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

class PPAppPaypaladvance extends PPAppPayment
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
	 * When controller created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || ($task != 'notify')) {
			return true;
		}
			
		$paymentKey = $this->input->get('INVNUM', null);
		
		if (!empty($paymentKey)) {

			$this->input->set('payment_key', $paymentKey);
			return true;
		}
		return true;
	}
	
	/**
	 * Render payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$secureToken = $helper->getSecureToken($payment);
		$invoice = $payment->getInvoice();

		if (!$secureToken) {

			$redirect = PPR::_('index.php?option=com_payplans&view=checkout&invoice_key&invoice_key=' . $invoice->getKey() . '&tmpl=component', false);

			PP::info()->set('Unable to obtain secure token from PayPal Advance', 'error');
			return PP::redirect($redirect);
		}

		$paymentParams = $payment->getParams();
		$paymentParams->set('paypal_advance_token', $secureToken->token);
		$paymentParams->set('paypal_advance_token_id', $secureToken->id);
		$payment->params = $paymentParams->toString();
		
		$payment->save();

		// Next we need to redirect to the payment url
		$redirect = $helper->getPaymentUrl($secureToken->id, $secureToken->token);

		return PP::redirect($redirect);
	}

	/**
	 * Handle paypal response and create transaction accordingly
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'error') {
			return false;
		}
		
		$result = $this->processResponse($payment, $data);
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
	
	/**
	 * Triggered when notification came from paypal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$result = $this->processResponse($payment, $data);

		parent::onPayplansPaymentNotify($payment, $data, $controller);
	}

	/**
	 * Process payment notification , create recurring profile for recurring payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function processResponse(PPPayment $payment, $data)
	{
		$helper = $this->getHelper();

		$invoice = $payment->getInvoice();
		$response = $helper->formatResponse($data);

		// If response is not approved
		if ($response->RESULT != 0 || $response->RESPMSG != 'Approved') {

			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_PAYPAL_ADVANCE_FAILED_RESPONSE_MESSAGE');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, (array) $response, 'PayplansPaymentFormatter', '', true);
			return true;
		}

		$transactionId = PP::normalize($data, 'PNREF', 0);
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);
		
		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);

				if ($transaction->getParam('SECURETOKEN') == $response->SECURETOKEN) {
					return true;
				}
			}
		}

		// Create transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $response);

		if (isset($response->AMT)) {
			$transaction->amount = $response->AMT;
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PAYMENT_COMPLETED_SUCCESSFULLY';
		}

		$transaction->save();

		if ( isset ($response->SECURETOKENID) && isset ($response->SECURETOKEN) ) {
			
			$params = $payment->getParams();
			$secureTokenId = $params->get('paypal_advance_token_id', '');
			$secureToken = $params->get('paypal_advance_token', '');
			
			if ($response->SECURETOKENID == $secureTokenId && $response->SECURETOKEN == $secureToken) {
				
				$params->set('paypal_advance_profile', $response->PNREF);
				$payment->params = $params->toString();
				$payment->save();	
			}
		}

		return true;
	}

}