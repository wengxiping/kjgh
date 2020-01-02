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

class PPAppStripe extends PPAppPayment
{
	/**
	 * This option determines if the app supports refund requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function supportForRefund()
	{
		return true;
	}

	/**
	 * Recurring cancellation supported
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
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		//if some error occured when click on buy but response is not successful then show error msg
		$error_code = $this->input->get('error_code');
		$error_msg = $this->input->get('error_msg', '', 'string');
		$error_html = '';

		if (isset($error_code) && isset($error_msg)) {
			$invoice = $payment->getInvoice();

			$theme = PP::themes();
			$theme->set('error_code', $error_code);
			$theme->set('error_msg', $error_msg);
			$theme->set('invoice', $invoice);
			return $theme->output('apps:/stripe/buying_error');
		}

		$publicKey = $this->getAppParam('public_key', '');
		$sandbox = $this->getAppParam('sandbox', false);
		$storeName = $this->getAppParam('popup_store_name', '');
		$populateEmail = $this->getAppParam('auto_fill_email', false);
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$amount = $helper->getAmount($invoice->getTotal());
		$currency = $invoice->getCurrency('isocode');
		$cancelLink = PPR::_("index.php?option=com_payplans&view=payment&task=complete&action=cancel&payment_key=" . $payment->getKey() . '&tmpl=component');

		$this->set('amount', $amount);
		$this->set('populateEmail', $populateEmail);
		$this->set('cancelLink', $cancelLink);
		$this->set('storeName', $storeName);
		$this->set('sandbox', $sandbox);
		$this->set('payment', $payment);
		$this->set('publicKey', $publicKey);
		$this->set('currency', $currency);

		$type = $this->getAppParam('form_type', 'form');

		if ($this->getAppParam('enable_sca')) {

			// set form type
			$type = 'form_sca';

			$this->helper->loadLibrary();

			$intent = $this->helper->createPaymentIntent($amount, $currency, $payment);
			$this->set('paymentIntentSecret', $intent->client_secret);
		}

		return $this->display($type);
	}

	/**
	 * Trigger after payment COM_PAYPLANS_APP_STRIPE_LOGGER_ERROR_IN_STRIPE_PAYMENT_PROCESS_DETAILS
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}

		if ($action == 'process') {

			if ($this->getAppParam('enable_sca')) {
				$state = $this->stripeScaProcessPayment($payment, $data);
			} else {
				$state = $this->stripeProcessPayment($payment, $data);
			}

			if ($state) {
				$redirect = PPR::_('index.php?option=com_payplans&view=payment&layout=complete&payment_key='.$payment->getKey() . '&action=success', false);

				return PP::redirect($redirect);
			}
		}
		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Process payments with Stripe
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function stripeProcessPayment(&$payment, $data, $invoiceCount = 0)
	{
		$this->helper->loadLibrary();

		$invoice = $payment->getInvoice();
		
		// This need to be done because stripe accept payment only in cents
		$amount = $this->helper->getAmount($invoice->getTotal($invoiceCount));

		if (!isset($data['stripeToken']) && !isset($data['process_payment'])) {
			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&error_msg=Invalid_Token&payment_key=' . $payment->getKey(), false);

			return PP::redirect($redirect);
		}

		$user = PP::user((int) $payment->getBuyer());

		// Check if there is a customer id
		$customerId = $payment->getGatewayParams()->get('stripe_customer', '');

		// Retrieve the customer information
		if ($customerId) {
			$customer = \Stripe\Customer::retrieve($customerId);
		}

		// If the account doesn't exist, create a new account
		if (!$customerId) {

			try {
				$customer = $this->helper->createCustomer($user, $data['stripeToken'], $payment);
			} catch(Exception $e) {

				if ($invoiceCount) {
					$errors['error_code'] = $e->getCode();
					$errors['error_message'] = $e->getMessage();

					PP::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_STRIPE_LOGGER_ERROR_IN_STRIPE_RESPONSE_INVALID'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

					return false;

				}

				PP::info()->set($e->getMessage(), 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);

				return PP::redirect($redirect);
			}
		}

		// IMPORTANT
		// Stripe does not support free trials directly but provides a work around for trials.
		// Additional transaction can be created, we need to check whether the plan has (1 free trial + recurring or 2 free trial + recurring)
		
		// If the amount is 0 and the invoice does not have free trials, something is not right
		if ($amount == 0 && !$invoice->hasRecurringWithFreeTrials()) {
			die('Invalid amount');
		}

		// Probably this is free trials
		if ($amount == 0 && $invoice->hasRecurringWithFreeTrials()) {
			$transaction = $this->helper->addFreeTrialSupport($payment, $customer);

			return true;
		}

		$errors = array(
			// Standard bills
			'error_code' => '',
			'error_message' => ''
		);

		try {
			$options = array(
				'amount' => $amount,
				'currency' => $invoice->getCurrency('isocode'),
				'customer' => $customer,
				'description' => $payment->getKey()
			);

			$response = \Stripe\Charge::create($options);


			if ($response->paid) {
				$gatewayParams = $payment->getGatewayParams();

				// Decrease recurrence count when it is marked as paid
				$recurrenceCount = $gatewayParams->get('pending_recur_count');

				if ($recurrenceCount != 0) {
					$recurrenceCount--;
					$gatewayParams->set('pending_recur_count', $recurrenceCount);
					$payment->gateway_params = $gatewayParams->toString();
					$payment->save();
				}

				$response = (is_object($response)) ? $response->__toArray() : $response;

				$gatewayTransactionId = PP::normalize($response, 'id', 0);

				// Check if previous transactions already exists
				$transactions = $this->getExistingTransaction($invoice->getId(), $gatewayTransactionId, 0, 0);

				if ($transactions) {
					return true;
				}

				$result = $this->helper->processPayment($payment, $response, $customer);

				return $result;
			}

			$response = (is_object($response)) ? $response->__toArray() : $response;
			$errors['error_code'] = $response['failure_code'];
			$errors['error_message']  = $response['failure_message'];

			return $errors;

		} catch(Exception $e) {

			// If some exception is occured then create an log and handle it 
			$username = $user->getUsername();
			$userId	= $user->getId();

			//It is needed to create a log for wrong response
			$errors['error_code'] = $e->getCode();
			$errors['error_message'] = sprintf(JText::_('COM_PAYPLANS_APP_STRIPE_LOGGER_ERROR_IN_STRIPE_PAYMENT_PROCESS_DETAILS'), $e->getMessage(), $username, $userId, $invoice->getKey());
			
			PP::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_STRIPE_LOGGER_ERROR_IN_STRIPE_RESPONSE_INVALID'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

			if ($invoiceCount) {
				return false;
			}

			$errors['error_message'] = $e->getMessage();

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&error_code=' . $errors['error_code'] . '&error_msg=' . urlencode($errors['error_message']), false);

			return PP::redirect($redirect);
		}
	}

	/**
	 * Process payments with Stripe SCA
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function stripeScaProcessPayment(&$payment, $data, $invoiceCount = 0)
	{
		$this->helper->loadLibrary();

		$invoice = $payment->getInvoice();

		if (!isset($data['dataSecret']) && !isset($data['process_payment'])) {
			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&error_msg=Invalid_Token&payment_key=' . $payment->getKey(), false);

			return PP::redirect($redirect);
		}

		$user = $invoice->getBuyer();
		
		// Retrieve Payment Intent
		$paymentIntentId = $payment->getGatewayParams()->get('payment_intent_id', '');
		$customerId = $payment->getGatewayParams()->get('stripe_customer', '');

		$intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

		if (!$customerId) {
			try {

			$customer = $this->helper->createSCACustomer($user, $intent, $payment);
			$customerId = $customer->id;

			} catch (Exception $e) {

				if ($invoiceCount) {
					$errors['error_code'] = $e->getCode();
					$errors['error_message'] = $e->getMessage();

					PP::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_STRIPE_LOGGER_ERROR_IN_STRIPE_RESPONSE_INVALID'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

					return false;

				}

				PP::info()->set($e->getMessage(), 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);

				return PP::redirect($redirect);
			}
		}

		if ($invoice->isRecurring) {
			$paymentMethod = \Stripe\PaymentMethod::retrieve($intent->payment_method);
			$paymentMethod->attach(['customer' => $customerId]);
		}

		$paymentIntent = (is_object($intent)) ? $intent->__toArray() : $intent;
		return $this->helper->processSCAPayment($payment, $paymentIntent,  $customerId);
		
	}

	/**
	 * Initiated during cron to process recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();

		// If it isn't recurring, ignore this altogether
		if (!$invoice->isRecurring()) {
			return;
		}

		$lifetime = false;
		$lifetime = ($invoice->getRecurrenceCount() == 0)? true : false;
		
		$counter = $invoiceCount +1;

		$gatewayParams = $payment->getGatewayParams();
		$recurrenceCount = $gatewayParams->get('pending_recur_count');

		if ($recurrenceCount > 0 || $lifetime) {
			if ($this->getAppParam('enable_sca')) {
				$this->stripeProcessRecurringPayment($payment, array('process_payment' => true), $counter);
			} else {
				$this->stripeProcessPayment($payment, array('process_payment' => true), $counter);
			}
		}
	}

	/**
	 * Triggered when refunding a transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function refundRequest(PPTransaction $transaction, $amount)
	{
		$this->helper->loadLibrary();
		
		$gatewayTransactionId = $transaction->getGatewayTxnId();
		$amount = $this->helper->getAmount($amount);

		return $this->helper->refund($transaction, $amount);
	}
	
	/**
	 * Triggered to terminate a payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $invoiceController) 
	{
		parent::onPayplansPaymentTerminate($payment, $invoiceController);

		return true;
	}

	/**
	 * Render actions for a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionActions($subscription)
	{
		if (!$subscription->isRecurring()) {
			return;
		}

		// To prevent multiple click events
		$uid = uniqid();

		$this->set('uid', $uid);
		$this->set('subscription', $subscription);
		$this->set('appId', $this->getId());

		$output = $this->display('button');

		return $output;
	}

	/**
	* Process Recurring Payments
	*
	* @since 4.0.0
	* @access public
	*/
	public function stripeProcessRecurringPayment(&$payment, $data, $invoiceCount = 0)
	{
		$this->helper->loadLibrary();

		$invoice = $payment->getInvoice();

		// This need to be done because stripe accept payment only in cents
		$amount = $this->helper->getAmount($invoice->getTotal($invoiceCount));


		if (!isset($data['dataSecret']) && !isset($data['process_payment'])) {
			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&error_msg=Invalid_Token&payment_key=' . $payment->getKey(), false);

			return PP::redirect($redirect);
		}

		$user = $invoice->getBuyer();
		
		// Retrieve Payment Intent
		$paymentIntentId = $payment->getGatewayParams()->get('payment_intent_id', '');

		$intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

		$customerId = $payment->getGatewayParams()->get('stripe_customer', '');
		$paymentMethodId = $payment->getGatewayParams()->get('payment_method_id', '');

		$this->helper->loadLibrary();

		$paymentIntent = \Stripe\PaymentIntent::create([
						    'amount' => $amount,
						    'currency' => $invoice->getCurrency('isocode'),
						    'payment_method_types' => ['card'],
						    'customer' => $customerId,
						    'payment_method' => $paymentMethodId,
						    'off_session' => true,
						    'confirm' => true,
						]);

		$paymentIntent = (is_object($intent)) ? $intent->__toArray() : $intent;
		return $this->helper->processSCAPayment($payment, $paymentIntent,  $customerId);

	}
}
