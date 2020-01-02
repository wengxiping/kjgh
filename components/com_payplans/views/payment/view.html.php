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

PP::import('site:/views/views');

class PayPlansViewPayment extends PayPlansSiteView
{
	/**
	 * Renders the payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// // Session expired or not
		// if ($this->_checkSessionExpiry()==false){
		// 	return false;
		// }

		// Get the payment
		$paymentKey = $this->input->get('payment_key', '', 'default');
		$paymentId = (int) PP::encryptor()->decrypt($paymentKey);

		$payment = PP::payment($paymentId);

		// If payment is not valid, redirect the user back to the plans page
		if (!$paymentId || !$payment->getId()) {
			$this->info->set('COM_PAYPLANS_ERROR_INVALID_PAYMENT_ID');
			return $this->redirectToView('plan');
		}	

		// Trigger the payment apps to render the output
		$args = array(&$payment);
		$result = PP::event()->trigger('onPayplansPaymentForm', $args, 'payment', $payment);

		$invoice = $payment->getInvoice();
		$plan = $invoice->getPlan();
		$modifiers = $invoice->getModifiers();

		$this->set('modifiers', $modifiers);
		$this->set('plan', $plan);
		$this->set('invoice', $invoice);
		$this->set('result', $result);

		return parent::display('site/payment/default/default');
	}

	/**
	 * @legacy (Standard IPN which merchant is redirecting viewer)
	 *
	 * Processes the complete payment task from the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function complete()
	{
		$paymentId = $this->getKey('payment_key');
		$payment = PP::payment($paymentId);

		if (!$paymentId || !$payment->getId()) {
			throw new Exception(JText::_('COM_PAYPLANS_ERROR_INVALID_PAYMENT_ID'));
		}

		// Set template success, so application can change it if required.
		$action = $this->input->get('action', 'success', 'word');
		$post = $this->input->request->getArray();

		// Only activate this for debug only
		// PP::ipn()->log($payment->getId(), $post);

		// Trigger apps, so they can perform post payment work
		$args = array($payment, &$action, &$post, $this);
		$html = PP::event()->trigger('onPayplansPaymentAfter', $args, 'payment', $payment);
		
		$invoice = $payment->getInvoice();
		$plan = $invoice->getPlan();

		// If everything is success then redirect to thanks page
		if ($action === 'success') {
			$subs = $invoice->getSubscription();

			// We reload the subscription so that we get the latest data
			$subscription = PP::subscription($subs->getId());
			$subscription->processModeration();

			$url = PPR::_('index.php?option=com_payplans&view=thanks&invoice_key=' . $invoice->getKey() . '&tmpl=component', false);
			return $this->redirect($url);
		}

		$allowedActions = array('cancel', 'error');

		if (!in_array($action, $allowedActions)) {
			throw new Exception('Unknown action provided');
		}

		$this->set('html', $html);
		$this->set('plan', $plan);
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);

		$namespace = 'site/payment/' . strtolower($action) . '/default';

		return parent::display($namespace);
	}

	/**
	 * @legacy (IPN sent from vendors)
	 *
	 * This is where most of the IPN comes from payment providers
	 * It is notification of payment recieved from Banks/Paypal
	 * that some one has made payment, so we should process it and
	 * update the status of payment
	 * 
	 * Important: 
	 * App must decide how to find payment key.
	 * App must work on onPayplansControllerCreation
	 * If payment key is not known.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function notify()
	{
		$post = $this->input->request->getArray();
		$paymentId = $this->getKey('payment_key');

		if (!$paymentId) {
			throw new Exception('Payment ID is not provided in request');
		}

		$payment = PP::payment($paymentId);

		PP::ipn()->log($payment->getId(), $post);

		$args = array($payment, $post, $this);
		$results = PP::event()->trigger('onPayplansPaymentNotify', $args, 'payment', $payment);

		foreach ($results as $result) {
			
			if ($result === false) {
				// some problem here
			}

			// echo the output
			if ($result !== true) {
				echo $result;
			}
		}

		// no need to generate payment view, its already done via app
		return false;
	}
}