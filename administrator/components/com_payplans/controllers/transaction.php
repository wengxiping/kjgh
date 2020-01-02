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

class PayplansControllerTransaction extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
	}

	/**
	 * Saves a new transaction
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$params = $this->input->get('params', array(), 'array');
		$from = $this->input->get('from', '', 'default');

		$invoiceId = PP::normalize($params, 'invoice_id', 0);
		$invoice = PP::invoice($invoiceId);

		$paymentId = PP::normalize($params, 'payment_id', 0);
		$payment = PP::payment($paymentId);

		$transactionId = PP::normalize($params, 'gateway_txn_id', 0);
		$subscriptionId = PP::normalize($params, 'gateway_subscr_id', 0);
		$parentId = PP::normalize($params, 'gateway_parent_txn', 0);

		$transaction = PP::createTransaction($invoice, $payment, $transactionId, $subscriptionId, $parentId);
		$transaction->amount = PP::normalize($params, 'amount', 0);

		$transaction->save();

		$this->info->set('COM_PP_TRANSACTION_SAVED_SUCCESSFULLY');

		$task = $this->getTask();

		if ($from) {
			$from = base64_decode($from);
			return $this->app->redirect($from);
		}

		if ($task == 'apply') {
			return $this->redirectToView('transaction', 'form', 'id=' . $transaction->getId());
		}

		return $this->redirectToView('transaction');
	}
}