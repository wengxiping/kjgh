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

require_once(__DIR__ . '/lib/payuturkey.php');

class PPHelperPayuTurkey extends PPHelperPayment
{
	public function getMerchantKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('merchant_key', '');
		}
		return $key;
	}

	public function getPostUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&action=process&payment_key=' . $paymentKey;
		}
		return $url;
	}

	public function getPublicKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('public_key', '');
		}
		return $key;
	}

	public function getSecretKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('secret_key', '');
		}
		return $key;
	}

	public function createTransaction(PPInvoice $invoice, PPPayment $payment, $data)
	{
		if (is_array($data)) {
			$hash = PP::normalize($data, 'HASH', '');
			$status = PP::normalize($data, 'STATUS', '');
			$transactionId = PP::normalize($data, 'REFNO', '');
			$amount = PP::normalize($data, 'AMOUNT', 0);
			$transactionParams = $data;
		}

		if (is_object($data)) {
			$hash = $data->getTokenHash();
			$status = $data->getStatus();
			$transactionId = $data->getRefno();
			$amount = $response->getAmount();
			$transactionParams = $data->getResponseParams();
		}
		
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $hash, 0, $transactionParams);

		if ($status != 'SUCCESS') {
			$transaction->amount = 0;
			$transaction->message = 'COM_PAYPLANS_APP_PAYUTURKEY_TRANSACTION_NOT_COMPLETED';
			$transaction->save();

			$transactionKey = PP::getKeyFromId($transaction->getId());
			$invoiceKey = PP::getKeyFromId($invoice->getId());
			
			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_PAYMENT_PROCESS'), $payment, $data, 'PayplansPaymentFormatter', '', true);

			return false;
		}

		// Here we assume that the transaction was successful
		$transaction->amount = $amount;
		$transaction->message = 'COM_PAYPLANS_APP_PAYUTURKEY_TRANSACTION_COMPLETED';
		$transaction->save();

		$recurrenceCount = 0;

		if ($invoice->isRecurring()) {
			$recurrenceCount = $invoice->getRecurrenceCount();	

			if ($invoice->getRecurringType(true) == PP_RECURRING_TRIAL_1) {
				$recurrenceCount++;
			}

			if ($invoice->getRecurringType(true) == PP_RECURRING_TRIAL_2) {
				$recurrenceCount = $recurrenceCount + 2;
			}
		}

		$gatewayParams = $payment->getGatewayParams();
		$gatewayParams->set('pending_recur_count', $recurrenceCount);
		$gatewayParams->set('TOKEN_HASH', $hash);

		$payment->gateway_params = $gatewayParams->toString();
		return $payment->save();
	}

	public function createMerchantConfig()
	{
		$merchantKey = $this->getMerchantKey();
		$secretKey = $this->getSecretKey();

		$merchant = new \PayU\Alu\MerchantConfig($merchantKey, $secretKey, 'TR');

		return $merchant;
	}

	public function createNewOrder(PPPayment $payment, $installment = false)
	{
		$reference = time() . "_" . $payment->getKey();

		$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $payment->getKey();

		$order = new \PayU\Alu\Order();		
		$order->withBackRef($url)
			->withOrderRef($reference)
			->withCurrency('TRY')
			->withOrderDate(gmdate('Y-m-d H:i:s'))
			->withOrderTimeout(1000)
			->withPayMethod('CCVISAMC');

		// Recurring subscriptions
		if ($installment) {
			$order->withInstallmentsNumber($installment);
		}

		return $order;
	}

	public function createNewBilling($user)
	{
		$billing = new \PayU\Alu\Billing();
		$billing->withAddressLine1('')
			->withAddressLine2('')
			->withCity('City')
			->withCountryCode('TR')
			->withEmail($user->getEmail())
			->withFirstName($user->getEmail())
			->withLastName($user->getEmail())
			->withPhoneNumber('1234567895')
			->withIdentityCardNumber('');

		return $billing;
	}

	public function createNewProduct(PPPayment $payment, PPInvoice $invoice, $amount)
	{
		$product = new \PayU\Alu\Product();

		$product->withCode($payment->getKey())
			->withName($invoice->getTitle())
			->withPrice($amount)
			->withVAT(0.0)
			->withQuantity(1);

		return $product;
	}

	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox', false);
		}
		return $sandbox;
	}

	public function processPayment(PPPayment $payment, $data, $invoiceCount = 0)
	{
		$invoice = $payment->getInvoice();
		$amount = $invoice->getTotal($invoiceCount);
		$buyer = $payment->getBuyer(true);
		
		if ($amount == 0) {
			$result = array(
				'failure_code' => 0,
				'failure_message' => JText::_('Invalid amount provided. Amount should be greater than 0')
			);

			return $result;
		}

		$errors['error_code']	  = "";
		$errors['error_message']  = "";

		// Create charge for customer
		$merchant = $this->createMerchantConfig();
		$user = new \PayU\Alu\User('127.0.0.1');
		
		$order = $this->createNewOrder($payment);
		$product = $this->createNewProduct($payment, $invoice, $amount);

		$order->addProduct($product);
			
		// Create new billing address
		$billing = $this->createNewBilling($buyer);

		// Create new delivery address
		$delivery = new \PayU\Alu\Delivery();

		$card = new \PayU\Alu\Card(PP::normalize($data, 'credit-card', ''), PP::normalize($data, 'card-expiry-month', ''), PP::normalize($data, 'card-expiry-year', ''), PP::normalize($data, 'cvc-length', ''), PP::normalize($data, 'card-owner', ''));
		$card->enableTokenCreation();
			
		$request = new \PayU\Alu\Request($merchant, $order, $billing, $delivery, $user);
		$request->setCard($card);

		$client = new \PayU\Alu\Client($merchant);

		try {
			$response = $client->pay($request);

			// Determines if we need to redirect to 3ds secure site
			if ($response->isThreeDs()) {
				return PP::redirect($response->getThreeDsUrl());
			}

			return $response;

		} catch (ConnectionException $exception) {

			$username = $buyer->getUsername();
			$userId = $buyer->getId();
			
			// It is needed to create a log for wrong response
			$result = array(
				'error_code' => $exception->getCode(),
				'error_message' => JText::sprintf('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_PAYMENT_PROCESS_DETAILS', $exception->getMessage(), $username, $userId, $invoice->getKey())
			);

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_RESPONSE_INVALID'), $payment, $result, 'PayplansPaymentFormatter', '', true);
			
			$result['error_message'] = $exception->getMessage();

			return $result;
			
		} catch (ClientException $exception) {
			$username = $buyer->getUsername();
			$userId = $buyer->getId();
			
			// It is needed to create a log for wrong response
			$result = array(
				'error_code' => $exception->getCode(),
				'error_message' => JText::sprintf('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_PAYMENT_PROCESS_DETAILS', $exception->getMessage(), $username, $userId, $invoice->getKey())
			);

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_RESPONSE_INVALID'), $payment, $result, 'PayplansPaymentFormatter', '', true);
			
			$result['error_message'] = $exception->getMessage();

			return $result;
		}

		return true;
	}

	/**
	 * Process recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRecurringPayment(PPPayment $payment, $data, $invoiceCount = 0)
	{
		$invoice = $payment->getInvoice();
		$invoice_count = $invoiceCount;
		$recurrenceCount = $payment->getGatewayParam('pending_recur_count');
		$amount = $invoice->getTotal($invoice_count);
		$buyer = $payment->getBuyer(true);

		$merchantConfig = $this->createMerchantConfig();
		$user = new \PayU\Alu\User('127.0.0.1');

		$order = $this->createNewOrder($payment, $invoice_count);
		$product = $this->createNewProduct($payment, $invoice, $amount);

		$order->addProduct($product);

		// Create new billing address
		$billing = $this->createNewBilling($buyer);

		// Create new delivery address
		$delivery = new \PayU\Alu\Delivery();

		// Reuse card stored in system
		$token = $payment->getGatewayParam('TOKEN_HASH');
		$cardToken = new \PayU\Alu\CardToken($token);

		$request = new \PayU\Alu\Request($merchantConfig, $order, $billing, $delivery, $user);
		$request->setCardToken($cardToken);

		$client = new \PayU\Alu\Client($merchantConfig);

		try {

			$response = $client->pay($request);

			if ($response->isThreeDs()) {
				$redirect = $response->getThreeDsUrl();

				return PP::redirect($redirect);
			}

			return $response;

		} catch (ConnectionException $exception) {
			$username = $buyer->getUsername();
			$userId = $buyer->getId();
			
			$result = array(
				'error_code' => $exception->getCode(),
				'error_message' => JText::sprintf('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_PAYMENT_PROCESS_DETAILS', $exception->getMessage(), $username, $userId, $invoice->getKey())
			);

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('Invaid Payuturkey'), $payment, $result, 'PayplansPaymentFormatter', '', true);
			$result['error_message'] = $exception->getMessage();

			return $result;
			
		} catch (ClientException $exception) {
			$username = $buyer->getUsername();
			$userId = $buyer->getId();
			
			$result = array(
				'error_code' => $exception->getCode(),
				'error_message' => JText::sprintf('COM_PAYPLANS_APP_PAYUTURKEY_LOGGER_ERROR_IN_PAYUTURKEY_PAYMENT_PROCESS_DETAILS', $exception->getMessage(), $username, $userId, $invoice->getKey())
			);

			PPLog::log(PPLogger::LEVEL_ERROR, JText::_('Invaid Payuturkey'), $payment, $result, 'PayplansPaymentFormatter', '', true);
			$result['error_message'] = $exception->getMessage();

			return $result;
		}
	}
}