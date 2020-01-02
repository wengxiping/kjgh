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

class PPAppNets extends PPAppPayment
{	

	/**
	 * Recurring Cancellation Added
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
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansSubscriptionBeforeSave
		if ($eventName == 'onPayplansSubscriptionBeforeSave') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}
		
	/**
	 * Render Payment Page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		//if some error occured when click on buy but response is not successful then show error msg
		$error_code = $this->input->get('error_code');
		$error_msg = $this->input->get('error_msg', '', 'string');

		if (isset($error_code) && isset($error_msg)) {
			$invoice = $payment->getInvoice();

			$theme = PP::themes();
			$theme->set('error_code', $error_code);
			$theme->set('error_msg', $error_msg);
			$theme->set('invoice', $invoice);
			return $theme->output('apps:/nets/error');
		}

		$helper = $this->getHelper();

		$invoice = $payment->getInvoice();
		$paymentKey = $payment->getKey();
		$redirectUrl = $helper->getRedirectUrl($paymentKey);
		
		$response = $helper->createPaymentRequest($invoice, $redirectUrl, 'B', $invoice->isRecurring());

		$terminalUrl = $helper->getTerminalUrl($response->TransactionId);
		$cancelUrl = $helper->getCancelUrl($paymentKey);

		$this->set('cancelUrl', $cancelUrl);
		$this->set('terminalUrl', $terminalUrl);

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
		if ($action == 'cancel') {
			return true;
		}

		$responseCode = trim($data['responseCode']);

		if ($responseCode != 'OK') {

			$message = JText::_('COM_PAYPLANS_LOGGER_NETS_ERROR');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $data, 'PayplansPaymentFormatter', '', true);

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&error_code='.$responseCode.'&error_msg='.$message.'&payment_key=' . $payment->getKey(), false);

			return PP::redirect($redirect);
		}
		
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();
		
		// Update the operation
		$operation = $invoice->isRecurring() ? 'SALE' : 'AUTH';

		$response = $helper->process($operation, $data['transactionId']);

		if (!is_array($response)) {
			$response = (array) $response;
		}

		$transactionId = PP::normalize($data, 'transactionId', '');

		if ($invoice->isRecurring()) {
			
			$result = $helper->processPanhashRequest($transactionId);

			$params = new JRegistry();
			$params->set('PanHash', $result->CardInformation->PanHash);

			$payment->gateway_params = $params->toString();
			$payment->save();
		}

		$responseCode = trim($response['ResponseCode']);

		if ($responseCode != 'OK') {

			$message = JText::_('COM_PAYPLANS_LOGGER_NETS_ERROR');
			
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter');

			$errors['error_code'] = 0;
			$errors['error_message'] = $message;

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&error_code=' . $errors['error_code'] . '&error_msg=' . urlencode($errors['error_message']), false);

			return PP::redirect($redirect);
		}

		// Here we assume the response was success. Check for duplicate transactions to avoid duplicate IPN requests
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $transactionId, 0);
		
		if ($transactions) {
			return true;
		}
		
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $transactionId, 0, $response);
		$transaction->amount = $amount;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_NETS_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
	
	/**
	 * Triggered on cron when recurring payment requested
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		// Check for failure attempt
		$failureCount = $payment->getParams()->get('failure_attempt');
		if($failureCount && $failureCount == 3){
			return false;   // do nothing if faulire attempt are 3
		}

		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal();
		$panHash = $payment->getGatewayParams()->get('PanHash', '');

		if (!$panHash) {
			$panHash = $payment->getParams()->get('PanHash', '');
		}

		$recurrencCount  	= $invoice->getRecurrenceCount();

		//calculate end date
		$endDate = PP::date();
		$endDate = $endDate->format('Ymd');			

		$frequency = $helper->getFrequency($invoice->getExpiration());
				
		$payload = $helper->getRecurringPayload($endDate, $frequency, $panHash);

		// Register with type 'C' for next payment
		$response = $helper->createPaymentRequest($invoice, null, 'C', false, $payload);
		if (!$response) {
			return false;
		}

		$transactionId = PP::normalize($response, 'TransactionId', '');

		if (!$transactionId) {
			return false;
		}

		$response = $helper->process('SALE', $transactionId);

		if (!$response || trim($response->ResponseCode) != "OK") {

			// to check failure attempt
			if ($failureCount != 0) {
				$failureCount++;
			} else {
				$failureCount = 1;
			}

			$params = new JRegistry();
			$params->set('failure_attempt', $failureCount);

			$payment->params = $params->toString();
			$payment->save();

			return false;
		}
		
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $transactionId, 0, $response);
		$transaction->amount = $amount;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_NETS_PAYMENT_COMPLETED_SUCCESSFULLY';
		$transaction->save();
	}
	
	/**
	 * Triggered when payment cancelled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{		
		parent::onPayplansPaymentTerminate($payment, $controller);
	}
}
