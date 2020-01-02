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

class PayplansControllerOrder extends PayplansController
{
	protected	$_defaultOrderingDirection = 'DESC';

	public function __construct()
	{
		parent::__construct();
		
		$this->registerTask('remove', 'delete');
		$this->registerTask('add', 'edit');
		$this->registerTask('close', 'cancel');

	}
	
	/**
	 * Creates a new invoice for an order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createInvoice()
	{
		$id = $this->input->get('id', 0, 'int');

		$order = PP::order($id);
		$subscription = $order->getSubscription();
		$invoice = $order->createInvoice();
		$invoice->confirm(0);
		$invoice->save();

		$this->info->set('New invoice created for the order', 'success');

		return $this->redirectToView('subscription', 'form', 'id=' . $subscription->getId() . '&active=invoices');
	}

	public function delete()
	{
		$id = $this->input->get('id', 0, 'int');

		dump($id);
	}

	public function terminate()
	{
		//load order record
		$orderId = $this->getModel()->getId();
		
		XiError::assert($orderId, JText::_('COM_PAYPLANS_ERROR_INVALID_ORDER_ID'));
		$order = PayplansOrder::getInstance($orderId);	
		
		if(!$order->isRecurring()){
			return true;
		}
		$invoice = $order->getInvoice();
		$payment = $invoice->getPayment();
		
		XiError::assert($payment, JText::_('COM_PAYPLANS_ERROR_INVALID_PAYMENT'));

		// if not confirm then set confirm = false on its view
		if(JRequest::getVar('confirm', false) == false){
			$this->getview()->set('confirm', false);
			return true;
		}
		// set confirm = true on its view
		$this->getview()->set('confirm', true);
		
		$appCancelHtml = $order->terminate();
		// assign appCompleteHtml to view // XITODO : clean it
		$this->getView()->assign('appCancelHtml', $appCancelHtml);

		return true;
	}	

}

