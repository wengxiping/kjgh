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

class PPApp2checkout extends PPAppPayment
{
	/**
	 * Override parent's isApplicable method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName='')
	{
		if ($eventName == 'onPayplansControllerCreation') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}

	/**
	 * It seems like the app is also responsible in collecting it's own params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function collectAppParams($data)
	{
		$app = PP::app()->getAppInstance($data['id']);
		$params = $app->getAppParams();

		if ($app->app_id == 0 || $params->get('activation') == $data['app_params']['activation']) {
			return parent::collectAppParams($data);				
		}

		PP::info()->set(JText::_('COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_CHANGE_MESSAGE'), 'success');
		
		$redirect = JRoute::_('index.php?option=com_payplans&view=app&task=edit&id=' . $data['id']);
		return PP::redirect($redirect);
	}

	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$invoice = $payment->getInvoice();
		
		$helper = $this->getHelper();
		$postUrl = $helper->getPostUrl();
		$cancelUrl = $helper->getCancelUrl();
		$buyer = $invoice->getBuyer();

		$sid = $this->getAppParam('sid', '');

		$this->set('sid', $sid);
		$this->set('postUrl', $postUrl);
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('buyer', $buyer);
		$this->set('cancelUrl', $cancelUrl);

		// For non recurring, we just output the form
		if (!$invoice->isRecurring()) {
			return $this->display('form');
		}

		$counter = $invoice->getCounter();
		
		//  No for now in future we may five option for this
		$time = $helper->getRecurrenceTime($invoice->getExpiration());

		// For recurring subscrition we are using Pass-Through-Products Parameters from 2checkout.com
		
		// 0 is for sequence number of product(starting from 0), product,shipping,tax or coupon
		$this->set('li_0_type', 'product'); 
		$this->set('li_0_name', $invoice->getTitle());
		$this->set('li_0_quantity', 1);
		$this->set('li_0_price', $invoice->getTotal());
		$this->set('li_0_tangible', 'N');
		$this->set('li_0_recurrence', $time['period'].' '.$time['unit']);

		// Forever or # Week | Month | Year – always singular, defaults to Forever.
		$recurrenceCount = $helper->getRecurrenceCount($invoice, $time);
		$this->set('li_0_duration', $recurrenceCount);
		
		if ($invoice->getRecurringType() == PP_PRICE_RECURRING_TRIAL_1) {
			// for start up fee 
			$first_price = $invoice->getTotal();				
			$regular_price = $invoice->getTotal($counter+1);

			// regular  payment amount
			$this->set('li_0_price', $regular_price);
			$this->set('li_0_startup_fee', floatval(floatval($first_price) - floatval($regular_price)));
		}
			
		return $this->display('form');
	}

	/**
	 * When a controller is created
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansControllerCreation($view, $controller, $task, $format)
	{
		if ($view != 'payment' || $task != 'notify') {
			return;
		}

		// vendor_order_id should be present in data posted
		// explode it by , 
		// first will be order key 
		// second will be payment key
		$keys = $this->input->get('vendor_order_id', false, 'default');

		if ($keys == false) {
			return true;
		}
			
		$keys = explode(',', $keys);
		array_shift($keys);

		$this->input->set('payment_key', array_shift($keys));

		return true;
	}

	/**
	 * Triggered upon receiving IPN from 2checkout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		$logs = PPLog::getLog($payment, PPLogger::LEVEL_ERROR);
		$record = array_pop($logs);

		if ($record && !empty($record)) {
			$action = 'error';
		}

		return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
	}

	/**
	 * Triggered when IPN occurs
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentNotify(PPPayment $payment, $data, $controller)
	{
		$invoice = $payment->getInvoice();
		
		$transaction = PP::transaction();
		$transaction->user_id = $payment->getBuyer();
		$transaction->invoice_id = $invoice->getId();
		$transaction->payment_id = $payment->getId();
		$transaction->gateway_subscr_id = PP::normalize($data, 'invoice_id', 0);
		$transaction->gateway_parent_txn = PP::normalize($data, 'sale_id', 0);

		$transactionParams = new JRegistry($data);
		$transaction->params = $transactionParams->toString();

		$helper = $this->getHelper();
		$state = $helper->validate($data);

		// Validation failed and we need to log this somewhere
		if (!$state) {
			$transaction->message = 'COM_PAYPLANS_APP_2CHECKOUT_INVALID_HASH_KEY';
			$transaction->save();

			return array(JText::_('COM_PAYPLANS_APP_2CHECKOUT_INVALID_HASH_KEY'));
		}
		
		// The status is considered refunded
		if ($invoice->isRefunded()) {
			$transaction->message = 'COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_NOTIFICATION_AFTER_REFUND';
			$transaction->save();

			return array(JText::_('COM_PAYPLANS_PAYMENT_APP_2CHECKOUT_NOTIFICATION_AFTER_REFUND'));
		}

		// Ensure that there are no duplicate notifications
		$transactionId = 0;
		$subscriptionId = PP::normalize($data, 'invoice_id', 0);
		$parentTransaction = PP::normalize($data, 'sale_id', 0);

		$transactions = $this->getExistingTransaction($invoice->getId(), $transactionId, $subscriptionId, $parentTransaction);
		$hasDuplicate = $helper->hasDuplicates($invoice, $transactions, $data);

		if ($hasDuplicate) {
			return true;
		}
		
		$errors = $helper->process($payment, $data, $transaction);

		$transaction->save();

		return $errors;
	}
}
