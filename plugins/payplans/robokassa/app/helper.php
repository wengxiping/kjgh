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

define('PP_ROBOKASSA_STATE_CODE', 5);

class PPHelperRobokassa extends PPHelperPayment
{
	/**
	 * Formats XML into an array
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function converXmlToArray($xml)
	{
		$data = array();

		foreach ($xml as $key => $value) {
			
			if (is_array($value)) {
				foreach ($value as $key1 => $value1) {
					if (is_array($value1)) {
						foreach ($value1 as $key2 => $value2) {
							$data[$key."_".$key2] = $value2;
						}
					} else {
						$data[$key."_".$key1] = $value1;	
					}
				}
			} else {
				$data[$key] = $value;
			}
		}
			
		return $data;
	}

	/**
	 * Retrieves the payment submission url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://auth.robokassa.ru/Merchant/Index.aspx';

			if ($this->isSandbox()) {
				$url = 'https://test.robokassa.ru/Index.aspx';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the payment response url
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApiUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$url = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx/OpState?';

			if ($this->isSandbox()) {
				$url = 'https://test.robokassa.ru/Webservice/Service.asmx/OpState?';
			}
		}

		return $url;
	}

	/**
	 * Retrieves the Merchant Login Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantLogin()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->params->get('merchantLogin', 0);
		}

		return $id;
	}

	/**
	 * Retrieves the Merchant Password1
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantPass1()
	{
		static $pwd = null;

		if (is_null($pwd)) {
			$pwd = $this->params->get('merchantPassword1', 0);
		}

		return $pwd;
	}

	/**
	 * Retrieves the Merchant Password2
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMerchantPass2()
	{
		static $pwd = null;

		if (is_null($pwd)) {
			$pwd = $this->params->get('merchantPassword2', 0);
		}

		return $pwd;
	}

	/**
	 * Determines if it is running in sandbox mode
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSandbox()
	{
		static $sandbox = null;

		if (is_null($sandbox)) {
			$sandbox = $this->params->get('sandbox') ? true : false;
		}

		return $sandbox;
	}

	/**
	 * This function will return the payment status of each invoiceId.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentStatus($invoiceId) 
	{
		$login = $this->getMerchantLogin();
		$password = $this->getMerchantPass2();

		$signature = md5($login . ':' . $invoiceId . ':' . $password);

		$payload = array(
			'MerchantLogin' => $login,
			'InvoiceID' => $invoiceId,
			'Signature' => $signature
		);
			
		if ($this->isSandbox()) {
			$payload['StateCode'] = PP_ROBOKASSA_STATE_CODE;
		}

		$url = $this->getApiUrl();
		
		$link = new JURI($url);
		$curl = new JHttpTransportCurl(new JRegistry());

		$response = $curl->request('POST', $link, http_build_query($payload));
		$xml = simplexml_load_string($response->body);

		$response = json_decode(json_encode($xml), true);
		$response = $this->converXmlToArray($response);
		
		return $response;
	}

	/**
	 * Generate a new invoice id each time it is required by Robokassa
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSignature($total, $uid, $paymentKey)
	{
		$total = number_format($total, 2);

		$payload = array(
			$this->getMerchantLogin(),
			$total,
			$uid,
			$this->getMerchantPass1(),
			'Shp_paymentKey=' . $paymentKey
		);

		$signature = implode(':', $payload);
		$signature = md5($signature);

		return $signature;
	}

	/**
	 * Generate a new inv id each time. It is required by Robokassa
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getInvId()
	{
		$tmp = substr(time() * rand(), 0, 7);

		$model = PP::model('Transaction');
		$options = array(
			'gateway_txn_id' => $tmp
		);
		
		$result = $model->loadRecords($options);

		if (!$result) {
			return $tmp;
		}

		$tmp = $this->getUniqueInvoiceId();
	}

	/**
	 * Performs a CRC validation for Robokassa
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function validate($data)
	{
		$pass2 = $this->getMerchantPass2();
		$crc = strtoupper($data['SignatureValue']);
		$outSum = $data['OutSum'];
		$InvId = $data['InvId'];
		$paymentKey = $data['Shp_paymentKey'];
		$myCrc = strtoupper(md5("$outSum:$InvId:$pass2:Shp_paymentKey=$paymentKey"));

		if ($myCrc != $crc) {
			return false;
		}

		return true;
	}
}