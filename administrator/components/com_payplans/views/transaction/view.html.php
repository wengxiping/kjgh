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

class PayplansViewTransaction extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		$this->heading('Transactions');

		$model = PP::model('Transaction');
		$model->initStates();
		
		$pagination = $model->getPagination();

		$rows = $model->getItems();
		$transactions = array();
		
		JToolbarHelper::addNew();

		if ($rows) {
			foreach ($rows as $row) {
				$transaction = PP::transaction($row);
				$transaction->payment = PP::payment($row->payment_id); 				
				$transaction->buyer = $transaction->getBuyer();

				$transactions[] = $transaction;
			}
		}

		// Get states used in this list
		$states = $this->getStates(array('search', 'created_date', 'amount', 'username', 'invoice_id', 'app_id', 'ordering', 'direction', 'limit', 'dateRange'));

		$this->set('states', $states);
		$this->set('transactions', $transactions);
		$this->set('pagination', $pagination);

		return parent::display('transaction/default/default');
	}

	/**
	 * Renders the invoice form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		$id = $this->input->get('id', 0, 'int');

		$transaction = PP::transaction($id);
		$transaction->toggleUseCache();
		
		$purchaser = $transaction->getBuyer();
			
		$this->heading('Manage Transaction');

		if ($transaction->getId()) {
			$invoice = $transaction->getInvoice();
		}

		$invoiceId = $this->input->get('invoice_id', 0, 'int');
		$paymentId = 0;
	
		$from = $this->input->get('from', '', 'default');

		if ($invoiceId) {
			JToolbarHelper::apply('transaction.apply');

			// Don't show save and close if coming from another page
			if (!$from) {
				JToolbarHelper::save('transaction.save');
			}

			$invoice = PP::invoice($invoiceId);
			$purchaser = $invoice->getBuyer();
			$payment = $invoice->getPayment();

			if ($payment) {
				$paymentId = $payment->getId();
			}
		}

		if (!$from) {
			JToolbarHelper::cancel('cancel', 'Close');
		}

		// Get payment data
		$payment = $transaction->getPayment();
		$gateway = false;

		$transactionParams = '';

		if ($payment->getId() && ($payment instanceof PPPayment)) {
			$gateway = $payment->getApp();

			if ($gateway) {
				$transactionParams = $gateway->onPayplansTransactionRecord($transaction);
				$transactionParams = JString::trim($transactionParams);
			}
		}

		$activeTab = $this->input->get('activeTab', '', 'default');
		$namespace = 'transaction/form/default';

		if (!$transaction->getId()) {
			$namespace = 'transaction/form/new';
		}

		if ($from) {
			$from = rtrim(JURI::root(), '/') . base64_decode($from);
		}

		$this->set('from', $from);
		$this->set('paymentId', $paymentId);
		$this->set('invoice', $invoice);
		$this->set('purchaser', $purchaser);
		$this->set('gateway', $gateway);
		$this->set('transaction', $transaction);
		$this->set('activeTab', $activeTab);
		$this->set('payment', $payment);
		$this->set('transactionParams', $transactionParams);
		
		return parent::display($namespace);
	}
}