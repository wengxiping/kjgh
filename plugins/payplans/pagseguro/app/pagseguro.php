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

class PPAppPagseguro extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function isApplicable($refObject = null, $eventName='')
	{
		// return true for event onPayplansControllerCreation
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}
		
		return parent::isApplicable($refObject, $eventName);
	}
	
	/**
	 * When controller created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || $task != 'notify') {
			return true;
		}

		$code = $this->input->get('notificationCode', null, 'default');
		$type = $this->input->get('notificationType', null, 'default');

		if (!$code && !$type) {
			return true;
		}
		
		// load the library file
		$helper = $this->getHelper();
		$helper->loadLibrary();

		$merchant = $helper->getMerchant();
		$token = $helper->getToken();

		if ($code && $type) {
			$notificationType = new NotificationType($type);
			$strType = $notificationType->getTypeFromValue();
			
			switch ($strType) {
				case 'TRANSACTION':
					/*
					* #### Crendencials ##### 
					* Substitute the parameters below with your credentials (e-mail and token)
					*/
					$credentials = new AccountCredentials($merchant, $token);
					try {
						$transaction = NotificationService::checkTransaction($credentials, $code);
					} catch (PagSeguroServiceException $e) {
						//the token is not valid
						PP::logger()->log(PPLogger::LEVEL_ERROR, $e->getMessage(), $this, array($e->getMessage()), 'PayplansPaymentFormatter', '', true);
						return true;
					}
					break;
					
				default:
					$message = JText::_("COM_PAYPLANS_APP_PAGSEGURO_UNKNOWN_NOTIFICATION_TYPE"). ' : '.$notificationType->getValue();
					PP::logger()->log(PPLogger::LEVEL_ERROR, $message, $this, array($message),'PayplansPaymentFormatter', '', true);

					return true;		
			}
		}
		
		$reference = explode('-',$transaction->getReference());

		// V V IMP : assign this so that it can be used later
		$this->_transactionXML = $transaction;
		$paymentKey = $reference['1'];
		if ($paymentKey == false) {
			return true;
		}

		$this->input->set('payment_key', $paymentKey, 'POST');
		return true;
	}
	
	/**
	 * Render's payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice();
		
		// load the library file
		$helper = $this->getHelper();
		$helper->loadLibrary();
		
		// Instantiate a new payment request
		$paymentRequest = new PaymentRequest();
		
		$paymentRequest->setCurrency($invoice->getcurrency('isocode'));
		$paymentRequest->addItem($invoice->getKey(), $invoice->getTitle(), 1, $invoice->getTotal());
		$paymentRequest->setReference($invoice->getKey().'-'.$payment->getKey());
		
		
		$url = $helper->getRedirectUrl($payment->getKey());
		$merchant = $helper->getMerchant();
		$token = $helper->getToken();
		
		$paymentRequest->setRedirectUrl($url);

		try {
			/*
			* #### Crendentials ##### 
			* Substitute the parameters below with your credentials (e-mail and token)
			* You can also get your credentails from a config file. See an example:
			* $credentials = PagSeguroConfig::getAccountCredentials();
			*/			
			$credentials = new AccountCredentials($merchant, $token);
			
			// Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
			$url = $paymentRequest->register($credentials);

			$this->set('postUrl', $url);
			return $this->display('form');
			
		} catch (PagSeguroServiceException $e) {
			$message = $e->getMessage();
			$this->set('errors', array($message));

			return $this->display('post_error');
		}
	}

	/**
	 * Triggered after payment process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{		
		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}
	
	/**
	 * Triggered when notification come from pagseguro
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();

		// load the library file
		$helper = $this->getHelper();
		$helper->loadLibrary();
		
		$errors = array();
		$amount = $this->_transactionXML->getGrossAmount();

		// if same notification came more than one time
		// Check if transaction already exists,if yes then do nothing and return
		$transactionId = $this->_transactionXML->getCode() != null ? $this->_transactionXML->getCode() : 0; 
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);

		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				$params = $transaction->getParams();

				if (strtolower($params->getParam('status','')) == strtolower($this->_transactionXML->getStatus()->getValue())) {
						return true;
				}
			}
		}

		 // get the transaction instance of lib
		$data = $helper->getTransactionArray($this->_transactionXML);
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $data);
		
		switch (intval($this->_transactionXML->getStatus()->getValue())) {

			case 1:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_INITIATED';
				$amount = 0;
				break;
		
			case 2:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_IN_ANALYSIS';
				$amount = 0;
				break;
						
			case 5:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_IN_DISPUTE';
				$amount = 0;
				break;
				
			case 6:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_RETURNED';
				$amount = -$amount;
				break;
		
			case 7:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_CANCELLED';
				$amount = 0;
				break;
		
			case 3:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_COMPLETE';
				break;
				
			default:
				$message = 'COM_PAYPLANS_APP_PAGSEGURO_TRANSACTION_PAYMENT_UNKNOWN_STATUS';
				$amount = 0;
				break;
		}	

		$transaction->amount = $amount;
		$transaction->message = $message;
		$transaction->save();	

		return true;
	}
}
