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

require_once(__DIR__ . '/api/Base.php');
require_once(__DIR__ . '/api/Parameters.php');
require_once(__DIR__ . '/api/Order.php');
require_once(__DIR__ . '/api/Terminal.php');
require_once(__DIR__ . '/api/Customer.php');
require_once(__DIR__ . '/api/RegisterRequest.php');
require_once(__DIR__ . '/api/Environment.php');
require_once(__DIR__ . '/api/Recurring.php');
require_once(__DIR__ . '/api/ProcessRequest.php');
require_once(__DIR__ . '/api/QueryRequest.php');

class PPHelperNets extends PPHelperPayment
{
	/**
	 * Creates a new payment request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createPaymentRequest(PPINvoice $invoice, $redirectUrl, $serviceType = 'B', $firstRecurring = false, $recurringPayload = null)
	{
		$merchantId = $this->getMerchantId();
		$token = $this->getToken();
		$payment = $invoice->getPayment();

		// In nets its required to multiple amount with 100
		$amount = $this->formatAmount($invoice->getTotal());
		$wsdl = $this->getWsdlUrl();
		$payload = $this->getRegisterPayload($serviceType);

		$payload['Recurring'] = $recurringPayload;

		// First recurring payment
		if (!$recurringPayload && $firstRecurring) {
			$time =	$invoice->getExpiration(PP_RECURRING);
			$count = $recurCount = $invoice->getRecurrenceCount();
			$expirationRaw = $invoice->getExpiration(PP_RECURRING, true);

			$frequency = $this->getFrequency($invoice->getExpiration());

			$end = PP::date();

			if ($recurCount != 0) {
				for ($recurCount; $recurCount > 0; $recurCount--) {
					$end = $end->addExpiration($expirationRaw);				
				}
			}

			$endDate = $end->format('Ymd');
			
			$payload['Recurring'] = $this->getRecurringPayload($endDate, $frequency);
		}


		$payload['Environment'] = $this->getEnvironmentPayload();
		$payload['Terminal'] = $this->getTerminalPayload($redirectUrl);
		$payload['Order'] = $this->getOrderPayload($invoice);

		$user = $invoice->getBuyer();
		$payload['Customer'] = new Customer($this->getCustomerPayload($user));

		$request = new RegisterRequest($payload);

		####  ARRAY WITH REGISTER PARAMETERS  ####
		$data = array(
			'token' => $token,
			'merchantId' => $merchantId,
			'request' => $request
		);

		$client = $this->getSoapClient();

		try {
				$response = $client->__call('Register', array('parameters' => $data));
			} catch(SoapFault $e) {
				$errors['code'] = 0;
				$errors['message'] = $e->getMessage();

				PP::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_NETS_LOGGER_ERROR_IN_NETS_PAYMENT_REQUEST'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

				return false;

			}


		// RegisterResult
		return $response->RegisterResult; 
	}

	/**
	 * Formats the amount required by NETS
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

		$amount = (int) ($amount * 100);
		return $amount;
	}

	/**
	 * Get Nets domain
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiDomain()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://epayment.bbs.no';

			if ($this->isSandbox()) {
				$url = 'https://epayment-test.bbs.no';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the merchant id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = trim($this->params->get('merchantId', ''));
		}

		return $id;
	}

	/**
	 * Generates the payload data for customer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomerPayload(PPUser $user)
	{
		$payload = array(
				// Optional parameter (required if DnBNorDirectPayment)
				'Address1' => null,

				// Optional parameters
				'Address2' => null,
				'CompanyName' => null,
				'CompanyRegistrationNumber' => null,
				//'Country' => $user->getCountry(),

				// Optional parameter (required if DnBNorDirectPayment)
				'FirstName' => null,
				'LastName' => null,

				// Optional parameters
				'CustomerNumber' => $user->getId(),
				'Email' => $user->getEmail(),
				'PhoneNumber' => null,

				// Optional parameter (required if DnBNorDirectPayment)
				'Postcode' => null,

				'SocialSecurityNumber' => null,
				'Town' => null
		);

		return $payload;
	}

	/**
	 * Generates the payload data for customer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEnvironmentPayload()
	{
		$data = array(
				// Optional parameter
				'Language' => null,
				
				// Optional parameter
				'OS' => null,

				// Required (for Web Services)
				'WebServicePlatform' => 'PHP5',
		);

		$payload = new Environment($data);
		return $payload;
	}

	/**
	 * Generates the payload data for Order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrderPayload(PPInvoice $invoice)
	{
		$data = array(
				'Amount' => $this->formatAmount($invoice->getTotal()),
				'CurrencyCode' => $invoice->getCurrency('isocode'),
				'Force3DSecure' => null,
				'Goods' => null,
				'OrderNumber' => $invoice->getId(),
				'UpdateStoredPaymentInfo' => null
		);

		$payload = new Order($data);
		
		return $payload;
	}

	/**
	 * Generates the payload data for Recurring object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringPayload($end, $count, $hash = null)
	{
		$data = array(
			//Required (if type R
			'ExpiryDate' => $end,

			// Required (if type R)
			'Frequency' => $count,

			// Optional parameter (unless Pan Hash is supplied, then it is required)
			'Type' => 'R',

			// Optional parameter
			'PanHash' => $hash
		);

		$payload = new Recurring($data);
		return $payload;
	}

	/**
	 * Generates the payload data for QueryRequest object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getQueryRequestPayload($transactionId)
	{
		$data = array(
			'TransactionId' => $transactionId
		);

		$payload = new QueryRequest($data);
		return $payload;
	}

	/**
	 * Generates the payload data for Terminal
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTerminalPayload($redirectUrl)
	{
		$data = array(
				'AutoAuth' => null,
				'PaymentMethodList' => null,
				'Language' => 'en_GB',
				'OrderDescription' => null,
				'RedirectOnError' => null,
				'RedirectUrl' => $redirectUrl
		);

		$payload = new Terminal($data);
		return $payload;
	}

	/**
	 * Generates the payload for creating payment requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRegisterPayload($serviceType)
	{
		$payload = array(
				// Optional parameters
				'AvtaleGiro' => null,
				'CardInfo' => null,
				'Customer' => null,
				'Description' => null,
				'DnBNorDirectPayment' => null,

				// Optional parameter for REST
				'Environment' => null,
				'MicroPayment' => null,
				'Order' => null,
				'Recurring' => null,
				'ServiceType' => $serviceType,
				'Terminal' => null,

				// Optional parameters
				'TransactionId' => null,
				'TransactionReconRef' => null
		);

		return $payload;
	}

	/**
	 * Generates the payload for creating payment requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getProcessPayload($operation, $transactionId)
	{
		$data = array(
			'Description' => null,
			'Operation' => $operation,
			'TransactionAmount' => null,
			'TransactionId' => $transactionId,
			'TransactionReconRef' => null
		);

		$payload = new ProcessRequest($data);

		return $payload;
	}

	/**
	 * Retrieves the terminal url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTerminalUrl($transactionId)
	{
		static $url = null;

		if (is_null($url)) {
			$merchantId = $this->getMerchantId();

			$url = $this->getApiDomain() . '/terminal/default.aspx?merchantId=' . $merchantId . '&transactionId=' . $transactionId;
		}

		return $url;
	}

	/**
	 * Retrieves the SOAP endpoint
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getWsdlUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://epayment.bbs.no/Netaxept.svc?wsdl';

			if ($this->isSandbox()) {
				$url = 'https://epayment-test.bbs.no/netaxept.svc?wsdl';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the secret token set from the back end
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getToken()
	{
		static $token = null;

		if (is_null($token)) {
			$token = trim($this->params->get('token', ''));
		}

		return $token;
	}
	
	/**
	 * Retrieves the cancellation url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the redirection url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRedirectUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root(), '/') . '/index.php?option=com_payplans&view=payment&task=complete&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the SOAP client
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSoapClient()
	{
		$wsdl = $this->getWsdlUrl();

		$client = new SoapClient($wsdl, array(
			'trace' => true,
			'exceptions' => true
		));

		return $client;
	}

	/**
	 * Determines if we are on a sandbox environment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('test', false);
		}

		return $sandbox;
	}

	/**
	 * Process payment through nets api
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function process($type = 'AUTH', $transactionId)
	{
		$payload = $this->getProcessPayload($type, $transactionId);

		$request = array(
			'token' => $this->getToken(),
			'merchantId' => $this->getMerchantId(),
			'request' => $payload
		);
	
		$client = $this->getSoapClient();

		
		try
		{
			$response = $client->__call('Process', array("parameters"=> $request));
		} catch(SoapFault $e) {
			$errors['code'] = 0;
			$errors['message'] = $e->getMessage();

			PP::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_APP_NETS_LOGGER_ERROR_IN_NETS_PAYMENT_REQUEST'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

			return false;

		}


		return $response->ProcessResult;
	}

	/**
	 * Using nets api query requested for transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPanhashRequest($transactionId)
	{
		$payload = $this->getQueryRequestPayload($transactionId);
		
		$request = array(
			'token' => $this->getToken(),
			'merchantId' => $this->getMerchantId(),
			'request' => $payload
		);

		$client = $this->getSoapClient();
		$response = $client->__call('Query' , array("parameters" => $request));
		
		return $response->QueryResult; 
	}
	/**
	 * Formats the recurrence time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFrequency($expTime)
	{
		$expTime['year'] = (int) PP::normalize($expTime, 'year', 0);
		$expTime['month'] = (int) PP::normalize($expTime, 'month', 0);
		$expTime['day'] = (int) PP::normalize($expTime, 'day', 0);
		
		// days, if days are not zero then, convert whole time into days and convert it into weeks 
		$days = $expTime['year'] * 365;
		$days += $expTime['month'] * 30;
		$days += $expTime['day'];

		return $days;
	}

}
