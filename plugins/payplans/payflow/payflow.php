<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);
require_once(__DIR__ . '/app/helper.php');

class plgPayplansPayflow extends PPPlugins
{
	/**
	 * Executed during cronjob
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansCron()
	{
		// Get payflow apps
		$apps = $this->getAvailableApps();

		if (!$apps) {
			return;
		}

		// Get a list of app ids
		$appIds = PP::getIds($apps);

		$payments = $this->getPayments($appIds);

		if (!$payments) {
			return;
		}

		foreach ($payments as $payment) {

			$app = PP::app()->getAppInstance($payment->app_id);
			$params = $app->getAppParams();

			$state = PAYPLANS_PAYFLOW_SETTLEMENT_SUCCESS;

			if ($params->get('sandboxTesting')) {
				$state = PAYPLANS_PAYFLOW_SETTLEMENT_PENDING;
			}

			$payment = PP::payment($payment);
			$profileId = $payment->getGatewayParam('profile_id');

			// Ensure that we have the profile id
			if (!$profileId) {
				continue;
			}

			$invoice = $payment->getInvoice();
			$count = $invoice->getCounter();
			$total = $invoice->getTotal($count);

			if ($count != 1 || !$invoice->isRecurring() || !$invoice->isConfirmed()) {
				continue;
			}

			$response = $this->executeApi($profileId, $params);
			$transactions = $this->getExistingTransactions($invoice->getId(), $response['P_PNREF1'], 0, 0);

			if ($transactions) {
				foreach ($transactions as $transaction) {
					$transaction = PP::transaction($transaction);

					if ($transaction->get('gateway_txn_id') == $response['P_PNREF1']) {
						return true;
					}
				}
			}

			if ($response['P_RESULT1'] == 0 && isset($response['P_TRANSTATE1']) && $response['P_TRANSTATE1'] == $state && $response['P_AMT1'] == $total) {
				$transaction = PP::createTransaction($invoice, $payment, $response['P_PNREF1'], 0, 0, $response);
				$transaction->amount = $response['P_AMT1'];
				$transaction->message = JText::_("COM_PAYPLANS_APP_PAYFLOW_RECURRING_PAYMENT_COMPLETED");

				$state = $transaction->save();

				if (!$state) {
					$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD');
					PP::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
				}

				$payment->save();

			} elseif (isset($response['P_RESULT1']) && $response['P_RESULT1'] != 0) {
				
				$transaction = PP::createTransaction($invoice, $payment, 0, 0, 0, $response);
				$transaction->amount = 0;
				$transaction->message = JText::_("COM_PAYPLANS_APP_PAYFLOW_RECURRING_PAYMENT_NOT_COMPLETED");
				
				$state = $transaction->save();

				if (!$state) {
					$message = JText::_('COM_PAYPLANS_LOGGER_ERROR_TRANSACTION_SAVE_FAILD');
					PP::log(PPLogger::LEVEL_ERROR, $message, $payment, $response, 'PayplansPaymentFormatter', '', true);
				}
			}
		}
	}

	/**
	 * Connects to PayFlow API
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function executeApi($profileId, $params)
	{ 
		$API_Vendor = $params->get('vendor');
		$API_User = empty($params->get('user'))? $API_Vendor : $params->get('user');
		$API_Partner = $params->get('partner');
		$API_Password = $params->get('password');
		$API_Endpoint = "https://payflowpro.paypal.com";
		
		if ($params->get('sandboxTesting')) {
			$API_Endpoint ="https://pilot-payflowpro.paypal.com";
		}

		$version = urlencode('51.0');
	
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		$nvpstr = "TRXTYPE=R&PARTNER=$API_Partner&VENDOR=$API_Vendor&USER=$API_User&PWD=$API_Password";
		$nvpstr .= "&ACTION=I&PAYMENTHISTORY=Y&ORIGPROFILEID=$profileId";
		
		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpstr);
		
		$httpResponse = curl_exec($ch);

		if (JDEBUG) {
			file_put_contents(JPATH_SITE.DS.'tmp'.DS.'inquiry'.time(), var_export($httpResponse,true), FILE_APPEND);
		}
		
		if (!$httpResponse) {
			$error = curl_error($ch).'('.curl_errno($ch).')';
			$message = JText::_('COM_PAYPLANS_PAYMENT_APP_PAYFLOW_PRO_FAILED_MESSAGE');
			PP::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error), 'PayplansPaymentFormatter', '', true);
		}

		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
		$httpParsedResponseAr = array();

		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if (sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
	
		if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('RESULT', $httpParsedResponseAr)) {
			$error = "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.";
			$message = "$methodName_ ".JText::_('COM_PAYPLANS_APP_PAYPAL_PRO_FAILED_MESSAGE');
			PP::log(PPLogger::LEVEL_ERROR, $message, $payment, array($error), 'PayplansPaymentFormatter', '', true);
		}
	
		return $httpParsedResponseAr;	
	}

	/**
	 * Retrieves a list of payments associated with any payflow app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getPayments($appIds = array())
	{
		$now = PP::date();
		$prev = PP::date();
		$prev->subtractExpiration('000003000000');

		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_payment');
		$query[] = 'WHERE ' . $db->qn('app_id') . ' IN(' . implode(',', $appIds) . ')';
		$query[] = 'AND ' . $db->qn('modified_date') . ' <= ' . $db->Quote($now->toSql());
		$query[] = 'AND ' . $db->qn('modified_date') . ' >= ' . $db->Quote($prev->toSql());

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves existing transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function getExistingTransactions($invoiceId, $transactionId, $subscriptionId, $parentTransaction)
	{
		// if all arguments are empty or then return exists
		if (empty($transactionId) && empty($subscriptionId) && empty($parentTransaction)) {
			return true;
		}

		$model = PP::model('Transaction');
		$options = array(
			'invoice_id' => $invoiceId,
			'gateway_txn_id' => $transactionId,
			'gateway_subscr_id' => $subscriptionId,
			'gateway_parent_txn' => $parentTransaction
		);

		$result = $model->loadRecords($options);

		return $result;
	}	
}
