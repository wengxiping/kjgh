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

class PayplansViewPayment extends PayPlansAdminView
{
	/**
	 * Internal usage to view payments (for support only)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->heading('Payments');

		$model = PP::model('Payment');
		$model->initStates();

		$rows = $model->getItems();
		$pagination = $model->getPagination();
		$payments = array();

		if ($rows) {
			foreach ($rows as $row) {
				$payment = PP::payment($row);
				$payment->gateway = $payment->getApp();

				$payments[] = $payment;
			}
		}

		// Get states used in this list
		$states = $this->getStates(array('search', 'paid_date', 'app_id', 'status', 'ordering', 'direction', 'limit'));

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		$this->set('payments', $payments);
		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('filter_order', $ordering);
		$this->set('filter_order_dir', $model->getState('filter_order_dir'));
		$this->set('limitstart', $model->getState('limitstart'));
		$this->set('states', $states);

		return parent::display('payment/default/default');
	}

	/**
	 * Renders the edit payment form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		JToolbarHelper::cancel();

		$this->heading('Editing Payment');

		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			die('Invalid request, without a payment id. If you want to create a new payment, do it under transactions');
		}

		$activeTab = $this->input->get('activeTab', '', 'default');
		$payment = PP::payment($id);
		$payment->gateway = $payment->getApp();
		$payment->purchaser = $payment->getBuyer(true);

		// Get payment transactions
		$model = PP::model('Transaction');
		$transactions = $model->loadRecords(array('payment_id' => $payment->getId()));

		if ($transactions) {
			foreach ($transactions as &$transaction) {
				$transaction = PP::transaction($transaction);
			}
		}

		$logModel = PP::model('Log');
		$logs = $logModel->getItemsWithoutState(array('object_id' => $payment->getId(), 'class' => 'PPPayment'));

		$this->set('logs', $logs);
		$this->set('transactions', $transactions);
		$this->set('payment', $payment);
		$this->set('activeTab', $activeTab);

		return parent::display('payment/form/default');
	}
}