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

class PPAppBraintree extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Retrieves the verification library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getVerificationLibrary()
	{
		$params = $this->getAppParams();

		$lib = new PPValidationBraintree($params);

		return $lib;
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

		$gateway = $this->input->get('gateway', null);

		if ($gateway != 'braintree') {
			return true;
		}

		$btChallenge = $_POST['bt_challenge'];
		if (!empty($btChallenge)) {

			$helper = $this->getHelper();
			$briantreeGateway = $helper->loadConfig();

			echo $briantreeGateway->briantreeGateway()->verify($btChallenge);
			exit();
		}

		$btSignature = $_POST['bt_signature'];
		$btPayload = $_POST['bt_payload'];
		if (!empty($btSignature) && !empty($btPayload)) {
			
			$helper = $this->getHelper();
			$briantreeGateway = $helper->loadConfig();

			$webhookNotification = $briantreeGateway->webhookNotification()->parse($btSignature, $btPayload);

			if (!empty($webhookNotification->subscription->id)) {

				$transactions = PP::model('transaction')->loadRecords(array('gateway_subscr_id' => $webhookNotification->subscription->id));
				if (!empty($transactions)) {

					$transaction = array_shift($transactions);
					//$paymentKey = PP::getIdFromInput($transaction->payment_id);		
					$paymentKey = PP::getKeyFromId($transaction->payment_id);
					
					if (!empty($paymentKey)) {
						$this->input->set('payment_key', $paymentKey, 'POST');
						$this->webhookNotification = $webhookNotification;
						return true;
					}
				}

			}
		}

		return true;
	}

	/**
	 * Render the payment input form
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
		$amount = $invoice->getTotal();
		$paymentKey = $payment->getKey();

		$helper = $this->getHelper();
		$helper->loadConfig();
		$cancelUrl = $helper->getCancelUrl($paymentKey);

		$token = Braintree_ClientToken::generate();

		$this->set('amount', $amount);
		$this->set('invoice', $invoice);
		$this->set('post_url', PPR::_("index.php?option=com_payplans&view=payment&task=complete&payment_key=".$paymentKey));
		$this->set('cancel_url', $cancelUrl);
		$this->set('payment_key', $paymentKey);
		$this->set('currency', $invoice->getCurrency('isocode'));
		$this->set('token', $token);

		$formType = 'form';
		if ($helper->isSCA()) {
			$formType = 'form_3ds';
		}

		return $this->display($formType);
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
		
		$helper = $this->getHelper();
		$briantreeGateway = $helper->loadConfig();

		$invoice = $payment->getInvoice();
		$invoiceAmount = $invoice->getTotal();
		$secondaryMerchantId = $helper->getSecondaryMerchantId();
		$isRecurring = $invoice->isRecurring();

		if ($isRecurring) {
			$this->_createCustomer($invoice, $payment, $data);
			$this->_createSubscription($invoice, $payment, $data);
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}

		$options = array(
			'amount' => $invoiceAmount,
			'paymentMethodNonce' => $data['payment_method_nonce'],
			'options' => array(
				'submitForSettlement' => true
			)
		);

		if ($secondaryMerchantId) {
			$options['merchantAccountId'] = $secondaryMerchantId;
		}

		$result = $briantreeGateway->transaction()->sale($options);

		if (!($result instanceof Braintree_Result_Successful)) {
			$message = JText::_('COM_PAYPLANS_LOGGER_BRAINTREE_ERROR_IN_TRANSACTION');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $result->__toString(), 'PayplansPaymentFormatter', '', true);

			PP::info()->set($result->message, 'error');
			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey());
			return PP::redirect($redirect);
		}
		
		$amount = $result->transaction->amount;
		
		// do this for normal payment
		// save transaction for normal payment
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->amount = $amount;
		$transaction->gateway_txn_id = $result->transaction->id;
		$transaction->gateway_subscr_id = 0;
		$transaction->gateway_parent_txn = 0;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_BRAINTREE_PAYMENT_COMPLETED_SUCCESSFULLY';

		$transactionParams = new JRegistry($result);
		$transaction->params = $transactionParams->toString();
		$transaction->save();

		$payment->save();			

		return parent::onPayplansPaymentAfter($payment, $action, $result->__toString(), $controller);
	}

	/**
	 * Trigger when notification came
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		if (!isset($this->webhookNotification)) {
			return true;
		}

		$invoice = $payment->getInvoice();		
		$btTransactions = $this->webhookNotification->subscription->transactions;
		$btTransaction = array_shift($btTransactions);
		$amount = $btTransaction->amount;

		// if same notification came more than one time
		// check if transaction already exists
		// if yes then do nothing and return
		$txn_id = $btTransaction->id;
		$subscr_id = $this->webhookNotification->subscription->id;
		$parent_txn = 0;

		$transactions = $this->_getExistingTransaction($invoice->getId(), $txn_id, $subscr_id, $parent_txn);
		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id, null, $transaction);
				if (!empty($transaction)) {
					return true;
				}    
			}
		}

		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_txn_id = $result->transaction->id;
		$transaction->gateway_subscr_id = 0;
		$transaction->gateway_parent_txn = 0;
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_BRAINTREE_PAYMENT_COMPLETED_SUCCESSFULLY';

		$transactionParams = new JRegistry($result);
		$transaction->params = $transactionParams->toString();

		$recurringCallback = 'onProcess' . ucfirst($this->webhookNotification->kind);
		$lib = $this->getVerificationLibrary();

		// Recurring subscriptions
		if ($recurringCallback && method_exists($lib, $recurringCallback)) {
			$lib->$recurringCallback($payment, $amount, $transaction);			
		}

		$transaction->save();
		$payment->save();
		
		return ' No Errors';
	}

	protected function _createCustomer($invoice, $payment, $data)
	{
		$helper = $this->getHelper();
		$helper->loadConfig();

		$result = $helper->createCustomer($invoice, $payment, $data);

		if ($result->success) {					
			$message = JText::_('COM_PAYPLANS_LOGGER_BRAINTREE_CUSTOMER_CREATED');
			PPLog::log(PPLogger::LEVEL_INFO, $message, $payment, $result->__toString(), 'PayplansPaymentFormatter');
	
		} else {
			$message = '';
			foreach ($result->errors->deepAll() AS $error) {
				$message .=  $error->code . ": " . $error->message . "\n";
			}

			$logmessage = JText::_('COM_PAYPLANS_LOGGER_BRAINTREE_ERROR_IN_CUSTOMER_CREATION');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $result->__toString(), 'PayplansPaymentFormatter', '', true);

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey());
			PP::info()->set($message, 'error');
			return PP::redirect($redirect);
		}		
	}

	protected function _createSubscription($invoice, $payment, $data)
	{
		$helper = $this->getHelper();
		$helper->loadConfig();

		$customerId = $payment->getGatewayParams()->get('customer_id');
		$paymentToken = $payment->getGatewayParams()->get('payment_token');
		$secondaryMerchantId = $this->getAppParam('secondary_merchant_id', '');

		$subArgs = array(
				  'paymentMethodToken' => $paymentToken,
				  'planId' => $this->getAppParam('plan_id', ''),
				  'price'  => $invoice->getTotal()
				);

		if ($secondaryMerchantId) {
			$subArgs['merchantAccountId'] = $secondaryMerchantId;
		}

		$recurring = $invoice->isRecurring();
		if ($recurring) {

			$recurringType = $invoice->getRecurringType();
			if ($recurringType == PP_RECURRING_TRIAL_1) {

				$expTime = $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_1);
				$expTime = $helper->getRecurrenceTime($expTime);

				$subArgs['trialPeriod'] = true; 
				$subArgs['trialDuration'] = $expTime['period'];
				$subArgs['trialDurationUnit'] = $expTime['unit'];
			}
		} 
		else {			
			$subArgs['trialPeriod'] = false; 
			$subArgs['trialDuration'] = 0;
		}

		$recCount = $invoice->getRecurrenceCount();
		if ($recCount) {
			$subArgs['neverExpires'] = false;
			$subArgs['numberOfBillingCycles'] = $recCount;
		}
		else {
			$subArgs['neverExpires'] = true;
		}	

		$result = $helper->createSubscription($invoice, $payment, $subArgs);

		if ($result instanceof Braintree_Result_Error) {

			$message = '';
			foreach ($result->errors->deepAll() AS $error) {
				$message .=  $error->code . ": " . $error->message . "\n";
			}

			$logmessage = JText::_('COM_PAYPLANS_LOGGER_BRAINTREE_ERROR_IN_SUBSCRIPTION_CREATION');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $result->__toString(), 'PayplansPaymentFormatter', '', true);

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key='.$payment->getKey());
			PP::info()->set($message, 'error');
			return PP::redirect($redirect);
		}

		return true;
	}

	//function after payment is done
	public function refundRequest(PPTransaction $transaction, $refund_amount)
	{
		$helper = $this->getHelper();
		$briantreeGateway = $helper->loadConfig();
		
		$txnId = $transaction->getGatewayTxnId();
		$amount = ($refund_amount*100);

		$bt_transaction = $briantreeGateway->transaction()->find($txnId);
		$status = strtolower($bt_transaction->status);

		if (in_array($status, array('settled', 'settling'))) {
			$result = $briantreeGateway->transaction()->refund($txnId);

		} else{

			$result = $briantreeGateway->transaction()->void($txnId);
		}

		if ($result->success == true) {		

			$payment = $transaction->getPayment();

			$refundTransaction = PP::transaction();
			$refundTransaction->user_id = $transaction->getBuyer()->getId();
			$refundTransaction->invoice_id = $transaction->getInvoice()->getId();
			$refundTransaction->payment_id = $payment->getId();

			$refundTransaction->gateway_txn_id = $result->transaction->id;
			$refundTransaction->gateway_subscr_id = 0;
			$refundTransaction->gateway_parent_txn = 0;

			$transactionParams = new JRegistry($result);
			$refundTransaction->params = $transactionParams->toString();

			$negativeAmount = -($refund_amount);
			$refundTransaction->amount = $negativeAmount;

			$refundTransaction->message = 'COM_PAYPLANS_APP_BRAINTREE_TRANSACTION_REFUNDED';
			$refundTransaction->save();
		
			return true;
		}
		else {

			$user = $transaction->getBuyer();
			$username = $user->getUsername();
			$userId	= $user->getId();
			$invoice = $transaction->getInvoice();

			$message = '';
			foreach ($result->errors->deepAll() AS $error) {
				$message .=  $error->code . ": " . $error->message . "\n";
			}
			//It is needed to create a log for wrong response
			$errors['error_code'] = '404';
			$errors['error_message'] = sprintf(JText::_('COM_PAYPLANS_APP_BRAINTREE_LOGGER_ERROR_IN_PAYMENT_PROCESS_DETAILS'),$message,$username,$userId,$invoice->getKey());
			
			$message = JText::_('COM_PAYPLANS_APP_BRAINTREE_LOGGER_ERROR_IN_STRIPE_RESPONSE_INVALID');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $transaction, $errors, 'PayplansPaymentFormatter', '', true);
			
			return false;
		}		
		
		return false;
	}
	
	public function supportForRefund()
	{
		return true;
	}

	public function isSupportPaymentCancellation($invoice)
	{
		if ($invoice->isRecurring()) {
			return true;
		}
		return false;
	}

	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{		
		$invoice = $payment->getInvoice();
		$customerId = $payment->getGatewayParam('customer_id');
		$subscriptionId = $payment->getGatewayParam('subscription_id');

		$transaction = PP::createTransaction($invoice, $payment, $customerId, $subscriptionId, 0, null);
		
		$helper = $this->getHelper();
		$briantreeGateway = $helper->loadConfig();

		$result = $briantreeGateway->subscription()->cancel($subscriptionId);		
		
		if ($result->success === true) {

			$response = $briantreeGateway->customer()->delete($customerId);
			
			if ($response->success === true) {

				$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CANCEL_SUCCESS';
				$transaction->save();

				$gatewayParams = $payment->getGatewayParams();
				$gatewayParams->set('customer_id', $customerId);

				$payment->gateway_params = $gatewayParams->toString();
				$payment->save();

				parent::onPayplansPaymentTerminate($payment, $controller);
				return true;
			}
		}

		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_BRAINTREE_CANCEL_ERROR';
		$transaction->save();

		return false;
	}
}


