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

class PPHelperWirecard extends PPHelperPayment
{
	/**
	 * Connects to wirecard toolbax for recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function connect($url, $payload)
	{
		$link = new JURI($url);
		$curl = new JHttpTransportCurl(new JRegistry());

		$query = http_build_query($payload);
		$curl->request('POST', $link, $query);

		$response = json_decode($response->body);
		return $response;
	}

	/**
	 * Retrieves the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://checkout.wirecard.com/page/init.php';
		}

		return $url;
	}

	/**
	 * Retrieves the form url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getToolkitUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://checkout.wirecard.com/page/toolkit.php';
		}

		return $url;
	}

	/**
	 * Retrieves the customer id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCustomerId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('customer_id', '');
		}

		return $id;
	}

	/**
	 * Retrieves the secret key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSecretKey()
	{
		static $key = null;

		if (is_null($key)) {
			$key = $this->params->get('secret', '');
		}

		return $key;
	}

	/**
	 * Retrieves the secret key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getToolkitPassword()
	{
		static $password = null;

		if (is_null($password)) {
			$password = $this->params->get('toolkit_password', '');
		}

		return $password;
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSuccessUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root() . '/') . '/index.php?option=com_payplans&gateway=wirecard&view=payment&task=complete&action=success&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFailUrl($paymentKey)
	{
		static $url = null;

		if (is_null($url)) {
			$url = rtrim(JURI::root() . '/') . '/index.php?option=com_payplans&gateway=wirecard&view=payment&task=complete&action=cancel&payment_key=' . $paymentKey;
		}

		return $url;
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCancelUrl($paymentKey)
	{
		return $this->getFailUrl($paymentKey);
	}

	/**
	 * Retrieves the success url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getServiceUrl($paymentKey)
	{
		return $this->getFailUrl($paymentKey);
	}

	/**
	 * Signs a payload
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sign($payload)
	{
		$string = implode('', $payload);
		$fingerprint = hash_hmac('sha512', $string, $this->getSecretKey());

		return $fingerprint;
	}

	/**
	 * Retrieves the recurrence count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecurrenceCount(PPInvoice $invoice)
	{
		$count = $invoice->getRecurrenceCount();

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
	 * Validates IPN response 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate(PPPayment $payment, $data)
	{
		$responseFingerprint = PP::normalize($data, 'responseFingerprint');

		if (!$responseFingerprint) {
			return array(false, JText::_('Error in payment response or response not valid'));
		}

		$secretKey = $this->getSecretKey();
		$responseFingerprintOrder = PP::normalize($data, 'responseFingerprintOrder', '');

		$order = explode(',', $responseFingerprintOrder);
		$seeds = array();

		if (in_array('paymentState', $order) && in_array('secret', $order)) {

			foreach ($order as $name) {
				$value = PP::normalize($data, $name, '');

				$seeds[$name] = $value;

				if (strcmp($name, 'secret') == 0) {
					$seeds[$name] = $secretKey;
				}
			}

			$fingerprint = $this->sign($seeds);

			if (strcmp($fingerprint, $responseFingerprint) == 0) {
				return array(true, 'No Error');
			}
		}
		return array(false, 'Invalid Signature from Wirecard');
	}
}