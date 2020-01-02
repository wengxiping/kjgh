<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayPlansViewInvoice extends PayPlansAdminView
{
	/**
	 * Renders browser to search for subscription
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '');

		$this->set('callback', $callback);

		$output = parent::display('admin/invoice/dialogs/browse');

		return $this->resolve($output);
	}

	/**
	 * Renders confirmation to mark invoice as paid
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmPaid()
	{
		$output = parent::display('admin/invoice/dialogs/paid');

		return $this->resolve($output);
	}

	/**
	 * Renders the refund form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmRefund()
	{
		$id = $this->input->get('id', 0, 'int');
		$invoice = PP::invoice($id);
		
		// Get the latest transaction that has an amount greater than 0 because then we know,
		// it was used to mark the invoice as paid
		$transaction = $invoice->getLatestTransactionWithAmount();

		if (!$transaction || !$transaction->getId()) {

			// Get the main invoice if exists
			$mainInvoice = $invoice->getMainInvoice();
			$transaction = $mainInvoice->getLatestTransactionWithAmount();

			if (!$transaction || !$transaction->getId()) {
				// Get the main invoice. #743
				$mainInvoice = $invoice->getMainInvoice();

				if ($mainInvoice->getTransactions()) {
					$transactions = $mainInvoice->getTransactions();
				}

				if (!$transaction || !$transaction->getId()) {
					$output = parent::display('admin/invoice/dialogs/refund.error');
					return $this->resolve($output);
				}
			}
		}

		$payment = $transaction->getPayment();
		$paymentApp = $payment->getApp();

		$this->set('paymentApp', $paymentApp);
		$this->set('transaction', $transaction);

		$output = $this->display('admin/invoice/dialogs/refund');

		return $this->resolve($output);
	}
}
