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

class PPAppPayuPl extends PPAppPayment
{
	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();
		$helper->loadLibrary();

		$invoice = $payment->getInvoice();
		$payload = $helper->getPaymentRequestPayload($payment);

		$this->set('formUrl', $helper->getFormUrl());
		$this->set('payload', $payload);

		return $this->display('form');
	}

	/**
	 * Triggered after Payment 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
			return true;
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * This method is triggered when PayPal connects to our payment notification page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();

		$helper = $this->getHelper();
		$helper->loadLibrary();

		$response = file_get_contents("php://input");
    	$response = trim($response);

        $result = OpenPayU_Order::consumeNotification($response);

        if (!$result->getResponse()->order->orderId) {
    		$message = JText::_('COM_PAYPLANS_APP_PAYUPL_INVALID_ORDER');
			PPLog::log(PPLogger::LEVEL_ERROR, $message, $payment, $response,'PayplansPaymentFormatter', '', true);
        }
       
        /* Check if OrderId exists in Merchant Service, update Order data by OrderRetrieveRequest */
        $order = OpenPayU_Order::retrieve($result->getResponse()->order->orderId);
        $orderResponse = $order->getResponse()->orders[0];
        $transactionId = $orderResponse->orderId;

    	// Check for duplicate transactions
		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, 0, 0);
		if ($transactions) {
			foreach ($transactions as $transaction) {
				$transaction = PP::transaction($transaction->transaction_id);
				
				if ($transaction->getParam('status', '') == $orderResponse->status) {
					return true;
				}
			}
		}

    	//create new transaction
		$transaction = PP::createTransaction($invoice, $payment, $transactionId, 0, 0, $orderResponse);	

        if ($orderResponse->status == 'COMPLETED'){
        	$transaction->amount = $orderResponse->totalAmount/100;
			$transaction->message = JText::_('COM_PAYPLANS_APP_PAYUPL_TRANSACTION_COMPLETED');   
        } else {
        	$transaction->message = JText::_('COM_PAYPLANS_APP_PAYUPL_TRANSACTION_NOT_COMPLETED');
        }

        $transaction->save();

        return ' No Errors';
	}
}