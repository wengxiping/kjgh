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

class PPAppPaywithpoints extends PPAppPayment
{
	/**
	 * Determines if the payment method is applicable
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject = null, $eventName = '')
	{
		$user = PP::user();
		$points = $this->helper->getPoints($user);

		if ($points === false) {
			return false;
		}

		return true;
	}

	/**
	 * Renders payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentForm(PPPayment $payment, $data = null)
	{
		$helper = $this->getHelper();

		$invoice = $payment->getInvoice();
		$buyer = $invoice->getBuyer();
		$amount = $invoice->getTotal();
		
		$points = $helper->getPoints($buyer);
		$cost = $helper->getPointsCost();
		$sufficient = $helper->hasSufficientPoints($buyer);

		$this->set('amount', $amount);
		$this->set('cost', $cost);
		$this->set('invoice', $invoice);
		$this->set('payment', $payment);
		$this->set('points', $points);
		$this->set('sufficient', $sufficient);

		return $this->display('form');
	}
	
	/**
	 * Triggered after a user completes payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentAfter(PPPayment $payment, &$action, &$data, $controller)
	{
		if ($action == 'cancel') {
		   return;
		}

		if ($action == 'success') {
			$helper = $this->getHelper();
			$invoice = $payment->getInvoice();
			$buyer = $invoice->getBuyer();
			$sufficient = $helper->hasSufficientPoints($buyer);

			if (!$sufficient) {
				$errors = array(
					'error_message' => 'COM_PAYPLANS_PAY_WITH_POINTS_INSUFFICIENT_POINTS',
					'response' => $data
				);

				PPLog::log(PPLogger::LEVEL_ERROR, JText::_('COM_PAYPLANS_LOGGER_ERROR_IN_PAY_WITH_POINTS_PAYMENT_PROCESS'), $payment, $errors, 'PayplansPaymentFormatter', '', true);

				$action = 'error';
				return parent::onPayplansPaymentAfter($payment, $action, $data, $controller);
			}

			$helper->process($payment, $data);

			return true;
		}
	}
	
	/**
	 * Triggered during cronjob to process expired payments
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processPayment(PPPayment $payment, $invoiceCount)
	{
		$invoice = $payment->getInvoice();

		if (!$invoice->isRecurring()) {
			return false;
		}

		$invoice_count = $invoiceCount + 1;
		$recurrence_count = $payment->getGatewayParam('pending_recur_count');

		// Nothing to rebill as we have completed
		if ($recurrence_count <= 0) {
			return false;
		}

		$helper = $this->getHelper();
		$buyer = $invoice->getBuyer();

		// Insufficient points
		if (!$helper->hasSufficientPoints($buyer)) {
			return false;
		}

		$pendingRecurrence = $helper->getRecurrenceCount($invoice) - $invoice_count;

		$helper->process($payment, $data, $pendingRecurrence);

		return true;
	}

	/**
	 * Triggered when user terminates payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansPaymentTerminate(PPPayment $payment, $controller)
	{
		return true;
	}
}