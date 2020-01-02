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

class PayPlansViewInvoice extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('orders');
	}
	
	public function display($tpl = null)
	{
		$this->heading('invoice');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_INVOICES'), 'invoice.delete');

		$model = PP::model('invoice');
		$model->initStates();

		$results = $model->getItems();
		$pagination = $model->getPagination();

		$invoices = array();

		if ($results) {
			foreach ($results as $item) {

				$invoice = PP::invoice();
				$invoice->setAfterBindLoad(false);
				$invoice->toggleUseCache();
				$invoice->bind($item);

				$invoice->buyer = $invoice->getBuyer();

				$invoices[] = $invoice;
			}
		}

		// Get states used in this list
		$states = $this->getStates(array('search', 'paid_date', 'total', 'username', 'dateRange', 'plan_id', 'status', 'ordering', 'direction', 'limit'));

		$this->set('invoices', $invoices);
		$this->set('pagination', $pagination);
		$this->set('states', $states);

		return parent::display('invoice/default/default');
	}

	/**
	 * Renders the send e-mail form to the customer
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function emailform()
	{
		$this->heading('Email Invoice');

		JToolbarHelper::apply('invoice.sendEmail', JText::_('Send'));
		JToolbarHelper::cancel();

		$id = $this->input->get('id', 0, 'int');
		$invoice = PP::invoice($id);
		$recipient = $invoice->getBuyer()->email;
		$editor = PP::getEditor();

		$return = $this->input->get('from', '', 'default');
		$return = base64_decode($return);

		$this->set('return', $return);
		$this->set('recipient', $recipient);
		$this->set('invoice', $invoice);
		$this->set('editor', $editor);

		return parent::display('invoice/emailform/default');
	}

	/**
	 * Renders the invoice form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		$this->heading('edit invoice');

		$invoiceId = $this->input->get('id', null, 'int');
		$activeTab = $this->input->get('activeTab', '', 'word');

		// Check if invoiceId is not provided.
		if (!$invoiceId || is_null($invoiceId)) {
			PP::info()->set('COM_PP_ERROR_NO_ID_PROVIDED', PP_MSG_ERROR);
			$this->redirect('/index.php?option=com_payplans&view=invoice');
		}

		JToolbarHelper::apply('invoice.apply'); 
		JToolbarHelper::save('invoice.store');

		$invoice = PP::invoice();
		$invoice->setAfterBindLoad(false);
		$invoice->toggleUseCache();
		$invoice->load($invoiceId);

		$transactions = $invoice->getTransactions();

		if ($invoice->isRefunded()) {
			PP::info()->set('This invoice was <b>refunded</b> and you will not be able to add additional transactions to it. You should create a new invoice for the subscription instead.', 'warning');
		}

		// Allow admin to mark invoice as paid
		if (!$invoice->isPaid() && !$invoice->isRefunded()) {
			JToolbarHelper::custom('invoice.paid', '', '', JText::_('COM_PP_MARK_AS_PAID'), false);
		}

		// Allow admin to refund invoice
		if (($transactions || (!$transactions && $invoice->isRecurring())) && !$invoice->isRefunded() && $invoice->isPaid() && !$invoice->isFree()) {
			$refunds = false;

			if ($invoice->isRefundable()) {
				$refunds = true;

				if ($invoice->isRecurring()) {

					if (!$transactions) {

						// Get the main invoice. #743
						$mainInvoice = $invoice->getMainInvoice();
						$transactions = $mainInvoice->getTransactions();
					}

					$latestTransaction = $transactions[0];

					if ($latestTransaction->getAmount() == floatval(0)) {
						$refunds = false;
					}
				}
			}

			if ($refunds) {
				JToolbarHelper::custom('invoice.refund', 'refund', '', JText::_('COM_PP_REFUND_BUTTON'), false);
			}
		}

		JToolbarHelper::custom('sendInvoiceLink', '', '', 'Send Invoice', false);
		JToolbarHelper::cancel('invoice.cancel');
		
		$logModel = PP::model('Log');
		$options = array('object_id' => $invoice->getId(), 'class' => 'invoice', 'level' => 'all');

		$logs = $logModel->getItemsWithoutState($options);

		$params = $invoice->getParams();

		$this->set('activeTab', $activeTab);
		$this->set('params', $params);
		$this->set('invoice', $invoice);
		$this->set('transactions', $transactions);
		$this->set('logs', $logs);
		
		return parent::display('invoice/form/default');
	}
}