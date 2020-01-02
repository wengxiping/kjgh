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

class PPAppMonetaweb extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable( $refObject = null, $eventName = '')
	{
		if ($eventName == 'onPayplansControllerCreation' || $eventName == 'onPayplansBeforeStoreIpn') {
			return true;
		}
		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * Render payment page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$invoice = $payment->getInvoice();

		$payload = $helper->getPayload($payment);
		$response = $helper->connect($payload);

		if (isset($response['paymentid'])) {

			$this->set('post_url', $response['hostedpageurl'].'?PaymentID='.$response['paymentid']);
			return $this->display('form');

		} else {

			$redirect = PPR::_('index.php?option=com_payplans&view=payment&task=pay&payment_key=' . $payment->getKey() . '&tmpl=component', false);

			$this->set('code', $response['errorcode']);
			$this->set('errormessage', $response['errormessage']);
			return $this->display('error');
		}
	}

	/**
	 * Payment complete page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered after payment process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{ 
		$helper = $this->getHelper();
		$valid = $helper->validateResponse($data, $payment);

		if (!$valid) {
			return true;
		}
		
		$invoice = $payment->getInvoice();
		$transactionId = PP::normalize($data, 'paymentid', 0);
		$subscriptionId = PP::normalize($data, 'paymentid', 0);

		// Check for duplicate transactions
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, 0);
		if (!empty($transactions)) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction);

				if ($transaction->getParam('result', '') == $data['result']) {
					return true;
				}
			}
		}

		// create transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, 0, $data);

		$transaction->amount = $invoice->getTotal();
		$transaction->message = 'COM_PAYPLANS_APP_MONETAWEB_TRANSACTION_COMPLETED';	
		$transaction->save();

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
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

		if ($data['gateway'] != 'monetaweb') {
			return true;
		}

		$hideData = array('cardcountry', 'cardexpirydate', 'maskedpan');

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
