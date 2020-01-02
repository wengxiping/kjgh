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

class PPHelperPaysitecash extends PPHelperPayment
{
	/**
	 * Retrieves the API Key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('api_key', '');
		}

		return $key;
	}

	/**
	 * Retrieves the Site ID
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSiteId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('site_id', '');
		}

		return $id;
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
	 * Retrieves the payment form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = "https://billing.paysite-cash.biz";
		}

		return $url;
	}

	/**
	 * Generates the payload for payment request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentRequestPayload(PPPayment $payment)
	{
		$invoice = $payment->getInvoice();
		$user = $invoice->getBuyer(true);

		$test = 0;
		if ($this->isSandbox()) {
			$test = 1;
		}

		$payload = array(
			'site' => $this->getSiteId(),
			'montant' => $invoice->getTotal(),
			'devise' => $invoice->getCurrency('isocode'),
			'test' => $test,
			'ref' => $payment->getKey(),
			'email' => $user->getEmail(),
			'description' => $invoice->getTitle(),
			'debug' => 1
		); 

		if ($invoice->isRecurring()) {
			$payload = $this->getRecurringPaymentRequestPayload($payment, $invoice, $payload);
		}

		return $payload;
	}

	/**
	 * Generates the signature
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFrequency($expTime)
	{
		$expTime['year'] = isset($expTime['year']) ? intval($expTime['year']) : 0;
		$expTime['month'] = isset($expTime['month']) ? intval($expTime['month']) : 0;
		$expTime['day'] = isset($expTime['day']) ? intval($expTime['day']) : 0;;

		$frequency = "";
		if ($expTime['day']) {
			$frequency = $expTime['day']."j";
		} 

		if ($expTime['month']) {
			$frequency = $expTime['month']."m";
		}

		return $frequency;
	}

	/**
	 * Set other params needed for the payload for recurring payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurringPaymentRequestPayload($payment, $invoice, $payload)
	{
		$userId = $payment->getBuyer();
		$user = PP::user($userId);

		$expTime = $invoice->getExpiration(PP_RECURRING);
		$frequency = $this->getFrequency($expTime);

		$counter = $invoice->getCounter();
		$recurrenceCount = $invoice->getRecurrenceCount();

		$recurringPayload = array(
			'subscription' => 1,
			'periode' => $frequency,
			'nb_redebit' => $recurrenceCount
		);

		if ($invoice->getRecurringType() == PP_PRICE_RECURRING_TRIAL_1) {

			$expTrialtime = $invoice->getExpiration(PP_PRICE_RECURRING_TRIAL_1);
			$frequency = $this->getFrequency($expTrialtime);

			$recurringPayload['periode2'] = $frequency;
			$recurringPayload['montant2'] = $invoice->getTotal($counter+1);
		}

		$recurringPayload['key'] = $this->getApiKey();

		$payload = array_merge($payload, $recurringPayload);
		return $payload;
	}

	/**
	 * Sent Confirmation Email on payment completion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sendConfirmationEmail($invoice, $data) 
	{
		if ($data['etat'] != 'ok') {
			return false;
		}

		$user = $invoice->getBuyer(true);
		$subject = JText::sprintf('COM_PP_PAYMENT_APP_PAYSITE_CASH_PAYMMENT_CONFIRM_EMAIL_SUBJECT', $data['id_trans']);
		$recipient = $user->getEmail();

		$contents = JText::sprintf('COM_PP_PAYMENT_APP_PAYSITE_CASH_PAYMMENT_CONFIRM_EMAIL_CONTENT', $data['id_trans']);

		$mailer = PP::mailer();
		$mailer->send($recipient, $subject, 'emails/custom/blank', array('contents' => $contents));
		return true;
	}
}
