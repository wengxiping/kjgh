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

class PPAppPaypalPro extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName = '')
	{
		if ($eventName == 'onPayplansControllerCreation' || $eventName == 'onPayplansBeforeStoreIpn') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Website PayPal pro support different time period for trial subscription, so return true
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFirstExpirationtimeApplicable()
	{
		return true;
	}

	/**
	 * Determines if paypal pro supports payment cancellation
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
	 * Triggered during controller creation to determine if it should execute any tasks
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || ($task != 'notify')) {
			return true;
		}
		
		//in case of recurring payment we get "rp_invoice_id" which contains payment key
		$paymentKey = $this->input->get('rp_invoice_id', null, 'default');

		if ($paymentKey) {
			$this->input->set('payment_key', $paymentKey);

			return true;
		}
		
		// In case of non-recurring payment we get invoice key and payment key in custom variable.
		$custom = $this->input->get('custom', null, 'default');

		if ($custom) {
			$custom = explode('-', $custom);
			$invoiceKey = $custom['0'];
			$paymentKey = $custom['1'];

			if ($invoiceKey == false || $paymentKey == false) {
				return true;
			}

			$this->input->set('invoice_key', $invoiceKey);
			$this->input->set('payment_key', $paymentKey);
		}

		return true;
	}

	/**
	 * Triggered when rendering payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();

		$invoice = $payment->getInvoice();
		$amount  = $invoice->getTotal();
		$paymentKey = $payment->getKey();
		
		$postUrl = $helper->getPostUrl($paymentKey);
		$cancelUrl = $helper->getCancelUrl($paymentKey);

		$countries = $helper->getCountries();
		$params = $this->getAppParams();

		$this->set('payment', $payment);
		$this->set('params', $params);
		$this->set('amount', $amount);
		$this->set('invoice', $invoice);
		$this->set('postUrl', $postUrl);
		$this->set('cancelUrl', $cancelUrl);
		$this->set('paymentKey', $paymentKey);
		$this->set('countries', $countries);
		
		return $this->display('form');
	}

	/**
	 * Triggered after payment completion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
		}
			
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$expMonth = PP::normalize($data, 'exp_month', '');
		$expMonth = str_pad($expMonth, 2, '0', STR_PAD_LEFT);
		$expYear = PP::normalize($data, 'exp_year', '');
		$expDate = urlencode($expMonth . $expYear);

		$payloadData = array(
			'PAYMENTACTION' => urlencode('Sale'),
			'FIRSTNAME' => urlencode(PP::normalize($data, 'first_name', '')),
			'LASTNAME' => urlencode(PP::normalize($data, 'last_name', '')),
			'EMAIL' => urlencode(PP::normalize($data, 'last_name', '')),
			'CREDITCARDTYPE' => urlencode(PP::normalize($data, 'cc_type', '')),
			'ACCT' => urlencode(PP::normalizeCardNumber(PP::normalize($data, 'card_num', ''))),
			'CVV2' => urlencode(PP::normalize($data, 'card_code', '')),
			'EXPDATE' => $expDate,
			'STREET' => urlencode(PP::normalize($data, 'address', '')),
			'CITY' => urlencode(PP::normalize($data, 'city', '')),
			'STATE' => urlencode(PP::normalize($data, 'state', '')),
			'ZIP' => urlencode(PP::normalize($data, 'zip', '')),
			'COUNTRYCODE' => urlencode(PP::normalize($data, 'country', '')),
			'IPADDRESS' => urlencode(@$_SERVER['REMOTE_ADDR']),
			'CUSTOM' => $invoice->getKey() . '-' . $payment->getKey(),
			'CURRENCYCODE' => urlencode($invoice->getCurrency('isocode')),
			'bn' => urlencode(PP::normalize($data, 'bn', '')),
			'NOTIFYURL' => $helper->getNotifyUrl()
		);

		// For non recurring items
		if (!$invoice->isRecurring()) {
			$payloadData['AMT'] = urlencode($invoice->getTotal());

			$response = $helper->connect('DoDirectPayment', $payloadData, $payment);

			if ($response && isset($response['ACK']) && strtoupper($response['ACK']) == 'FAILURE') {
				$error = array(
					'error_message' => JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_DIRECT_PAYMENT_NOT_COMPLETED'),
					'response' => $response
				);

				$errorCode = $response['L_ERRORCODE0'];
				$message = urldecode($response['L_LONGMESSAGE0']);

				PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $error, 'PayplansPaymentFormatter', '', true);

				PP::info()->set(JText::_($message) . ' (' . $errorCode . ')', 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
				return PP::redirect($redirect);
			}
		}

		// For recurring, we need to create their recurring profile
		if ($invoice->isRecurring()) {
			$response = $helper->createRecurringProfile($invoice, $payment, $payloadData);

			if ($response['ACK'] == 'SUCCESS' || $response['ACK'] == 'SUCCESSWITHWARNING') {
				$gatewayParams = $payment->getGatewayParams();
				$gatewayParams->set('profile_id', urldecode($response['PROFILEID']));

				$payment->gateway_params = $gatewayParams->toString();
				$payment->save();
			}

			if ($response['ACK'] == 'FAILURE') {
					
				$errorCode = $response['L_ERRORCODE0'];
				$message = urldecode($response['L_LONGMESSAGE0']);
				$error = array(
					'error_message' => JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_RECURRING_PROFILE_REJECTED'),
					'response' => $response
				);

				PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);

				PP::info()->set($message . ' (' . $errorCode . ')', 'error');

				$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey(), false);
				return PP::redirect($redirect);
			}
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered during payment notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$errors = array();
		$invoice = $payment->getInvoice();
		
		$transactionId = PP::normalize($data, 'txn_id', 0);
		$subscriptionId = PP::normalize($data, 'recurring_payment_id', 0);
		$parentTransaction = PP::normalize($data, 'parent_txn_id', 0);

		// Check for duplicate IPNs
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentTransaction);

		if (!empty($transactions)) {
			$recurring = $invoice->isRecurring();

			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction);

				if (!$recurring) {
					$status = $transaction->getParam('payment_status', '');
					
					if ($status == $data['payment_status']) {
						return true;
					}
				}

				$type = $transaction->getParam('txn_type', '');

				if ($type == $data['txn_type']) {
					return true;
				}
			}
		}

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $data);

		$helper = $this->getHelper();
		$errors = $helper->process($payment, $transaction, $data);

		//if error present in the transaction then redirect to error page
		if ($errors) {
			$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_PAYPAL_PRO_PAYMENT_PROCESS');
			$response = array(
				'error_message' => array_shift($errors),
				'response' => $data
			);

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
		}
	
		// Store the response in the payment AND save the payment
		$transaction->save();
		$payment->save();
		
		return count($errors) ? implode("\n", $errors) : ' No Errors';
	}

	/**
	 * Triggered when user cancels their subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();
		$profileId = $payment->getGatewayParam('profile_id', 0);
		
		$payloadData = array(
			'PROFILEID' => $profileId,
			'ACTION' => 'Cancel'
		);

		$response = $helper->connect('ManageRecurringPaymentsProfileStatus', $payloadData, $payment);

		if ($response['ACK'] == 'FAILURE') {
			$error = array(
				'error_message' => JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_ERROR_IN_RECURRING_PROFILE_CANCELLATION'),
				'response' => $response
			);			
			$message = urldecode($response['L_LONGMESSAGE0']);

			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $error, 'PayplansPaymentFormatter', '', true);
			return false;
			// return $this->_render('cancel_error');
		}

		if ($response['ACK'] == 'SUCCESS' || $response['ACK'] == 'SUCCESSWITHWARNING') {
			$transaction = PP::createTransaction($invoice, $payment, 0, $response['PROFILEID']);
			$transaction->save();
			
			parent::onPayplansPaymentTerminate($payment, $controller);
			return true;
		}

		return false;
	}

	/**
	 * Triggered before saving ipn notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansBeforeStoreIpn($ipn, $data)
	{	
		if (!$data) {
			return true;
		}

		if ($data['gateway'] != 'paypalpro') {
			return true;
		}

		$hideData = array('first_name', 'last_name', 'residence_country');

		foreach ($data as $key => $value) {
			if (in_array($key, $hideData)) {
				unset($data[$key]);
			}
		}

		$ipn->json = json_encode($data);
		$ipn->query = http_build_query($data);

		return true;
	}
}