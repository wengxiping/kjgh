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

class PPHelperPin extends PPHelperPayment
{
	/**
	 * Formats amount to suit pin payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatAmount($amount, $reverse = false)
	{
		if ($reverse) {
			$amount = ($amount / 100);
			return $amount;
		}

		$amount = ($amount * 100);

		return $amount;
	}

	/**
	 * Connects to the PIN api service
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $payload)
	{
		$secretKey = $this->getSecretKey();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($payload)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ':');
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		if ($this->isSandbox()) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);
		
		$response = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($response, true);

		return $result;
	}

	/**
	 * Retrieves the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = PPR::_('index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $paymentKey);
		}

		return $url;
	}

	/**
	 * Retrieves the API url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUrl($type = 'charges')
	{
		static $domain = null;

		if (is_null($domain)) {
			$domain = 'https://api.pin.net.au';

			if ($this->isSandbox()) {
				$domain = 'https://test-api.pin.net.au';
			}
		}

		$url = $domain . '/1/' . $type;

		return $url;
	}

	/**
	 * Retrieve the secret key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSecretKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('secret_key', '');

			if ($this->isSandbox()) {
				$key = $this->params->get('sandbox_secret_key', '');
			}
		}

		return $key;
	}

	/**
	 * Determines if it is sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', '');
		}

		return $sandbox;
	}

	/**
	 * Generates the payload for creating customers on PIN Payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomerPayload($data)
	{
		$payload = array(
			'card' => array(
				'name' => PP::normalize($data, 'card_name', ''),
				'number' => PP::normalize($data, 'card_num', ''),
				'cvc' => PP::normalize($data, 'card_code', ''),
				'expiry_month' => PP::normalize($data, 'exp_month', ''),
				'expiry_year' => PP::normalize($data, 'exp_year', ''),
				'address_line1' => PP::normalize($data, 'address', ''),
				'address_line2' => '',
				'address_city' => PP::normalize($data, 'city', ''),
				'address_state' => PP::normalize($data, 'state', ''),
				'address_postcode' => PP::normalize($data, 'zip', ''),
				'address_country' => PP::normalize($data, 'country', '')
			),
			'email' => PP::normalize($data, 'email', '')
		);

		return $payload;
	}

	/**
	 * Generates the payload for charging customers
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getChargePayload($amount, $currency, $title, $email, $ip, $customerToken)
	{
		$payload = array(
			'amount' => $amount,
			'currency' => $currency,
			'description' => urldecode($title),
			'email' => $email,
			'ip_address' => $ip,
			'customer_token' => $customerToken
		);

		return $payload;
	}	

	/**
	 * Create a new customer on PIN Payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createCustomer(PPInvoice $invoice, PPPayment $payment, $payload)
	{
		$user = $invoice->getBuyer(true);
		$url = $this->getApiUrl('customers');

		$response = $this->connect($url, $payload);
		$error = PP::normalize($response, 'error', '');

		if ($error) {
			return $this->createErrorLog($invoice, $payment, $user->getId(), $response, 'COM_PAYPLANS_PAYMENT_APP_PIN_LOGGER_CUSTOMER_CREATION_ERROR');
		}

		// Create a new transaction
		$transaction = PP::createTransaction($invoice, $payment, 0, $response['response']['token'], 0, $response);
		$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_PIN_TRANSACTION_CUSTOMER_CREATED_SUCCESSFULLY';

		// IMP: user can provide different email address during payment processing. Store that email address and use that for further payment processing
		$email = PP::normalize($response['response'], 'email', '');
		$token = PP::normalize($response['response'], 'token', '');

		$params = $payment->getGatewayParams();
		$params->set('customer_email', $email);
		$params->set('customer_token', $token);

		$payment->gateway_params = $params->toString();
		$payment->save();
		
		return $token;
	}

	/**
	 * Creates a new charge transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createCharge(PPInvoice $invoice, PPPayment $payment, $payload, $token, $invoice_count = 1)
	{
		$url = $this->getApiUrl('charges');
		$response = $this->connect($url, $payload);
		$error = PP::normalize($response, 'error', '');

		if ($error) {
			$user = $invoice->getBuyer(true);
			return $this->createErrorLog($invoice, $payment, $user->getId(), $response, 'COM_PAYPLANS_PAYMENT_APP_PIN_LOGGER_DO_CHARGE_ERROR');
		}

		return $response;
	}

	/**
	 * Retrieve the recurrence count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();
		
		if (!$invoice->isRecurring()) {
			return 0;
		}

		if (intval($count) === 0) {
			return 9999;
		}
		
		$type = $invoice->getRecurringType(true);

		// Recurrence Count For Regular Recurring Plan
		if ($type == PP_RECURRING) {
			$recurrence_count = $invoice->getRecurrenceCount();
		}

		// Recurrence Count For Recurring + Trial 1 Plan
		if ($type == PP_RECURRING_TRIAL_1) {
			$recurrence_count = $invoice->getRecurrenceCount() + 1;
		}
		
		// Recurrence Count For Recurring + Trial 2 Plan
		if ($type == PP_RECURRING_TRIAL_2) {
			$recurrence_count = $invoice->getRecurrenceCount() + 2;
		}

		return $recurrence_count;
	}

	/**
	 * Creates a new error log
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createErrorLog(PPInvoice $invoice, PPPayment $payment, $userId, $response, $message)
	{
		$errors = array(
			'error_code' => PP::normalize($response, 'error', ''),
			'error_desc' => PP::normalize($response, 'error_description', ''),
			'message' => ''
		);


		$messages = PP::normalize($response, 'messages', array());

		if ($messages) {
			$errors['message'] = implode(",", $messages);
		}


		PPLog::log(PPLogger::LEVEL_ERROR, $errors['message'], $payment, $errors, 'PayplansPaymentFormatter', '', true);

		$transaction = PP::createTransaction($invoice, $payment, 0, 0, 0, $response);
		$transaction->save();

		return $errors;
	}
}
