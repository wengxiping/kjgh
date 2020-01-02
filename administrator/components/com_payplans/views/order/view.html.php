<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayPlansViewOrder extends PayPlansAdminView
{
	public function display($tpl = null)
	{
		$this->heading('Orders');

		$model = PP::model('order');
		$model->initStates();

		$result = $model->getItems();

		$orders = array();
		if ($result) {
			foreach ($result as $row) {
				$row->buyer = $row->getBuyer();
				$row->subscription = $row->getSubscription(true);

				$orders[] = $row;
			}
		}

		$pagination = $model->getPagination();

		// Get states used in this list
		$states = $this->getStates(array('search', 'order_id', 'subscription_id', 'buyer_id', 'status', 'created_date', 'modified_date', 'dateRange', 'limit', 'ordering', 'direction'));

		$this->set('orders', $orders);
		$this->set('states', $states);
		$this->set('pagination', $pagination);

		return parent::display('order/default/default');
	}
		
	function edit($tpl=null)
	{
		JToolbarHelper::cancel();

		$model = PP::model('order');

		$itemId = $this->input->get('id', null, 'int');
		$itemId = ($itemId === null) ? $model->getState('id') : $itemId;
		
		$order	= PayplansOrder::getInstance($itemId);
			
		// get all subscription/payment of this order id
		$subsRecord 	= $order->getSubscription();
		$userRecord 	= $order->getBuyer(PP_INSTANCE_REQUIRE);
		$logRecords		= PP::model('log')->loadRecords(array('object_id'=>$itemId, 'class'=>'PayplansOrder'));
		$invRecords		= $order->getInvoices();
		
		// when we are going to create new order then donot perform following check
		// show or hide the recurring order cancellation button
		if($order->getSubscription() && $order->isRecurring()){
			$invoice = $order->getLastMasterInvoice(PP_INSTANCE_REQUIRE);
			if($invoice && ($payment = $invoice->getPayment())){
				$payment = $invoice->getPayment();
				$app = $payment->getApp(PP_INSTANCE_REQUIRE);
				$this->set('show_cancel_option', $app->getAppParam('allow_recurring_cancel', false));
			}
		}

		$txnRecords   = array();
		$invoice_ids  = array();
		foreach ($invRecords as $record){
			$invoice_ids[] = $record->getId();
		}
		
		//fetch all the related transaction records
		if(!empty($invoice_ids)){
			$txnRecords = PP::model('transaction')->loadRecords(array('invoice_id'=> array(array('IN', "(".implode(",", $invoice_ids).")"))));
		}
		
		$form = $order->getModelform()->getForm($order);
		
		$this->set('form',$form);
		$this->set('order', 		  	$order);
		$this->set('user',  			$userRecord);
		$this->set('subscr_record',  $subsRecord);
		$this->set('log_records', 	$logRecords);
		$this->set('invoice_records',$invRecords);
		$this->set('txn_records', 	$txnRecords);

		parent::display('order/form');
		return true;
	}
	
	public function terminate()
	{
		$itemId = $this->getModel()->getState('id');
	
		if($this->confirm == false){
			$this->setTpl(__FUNCTION__.'_confirm');
			$url = 'index.php?option=com_payplans&view=order&task=terminate&confirm=1&order_id='.$itemId;
			
			$this->_setAjaxWinTitle(JText::_('COM_PAYPLANS_ORDER_TERMINATE_CONFIRM_WINDOW_TITLE'));
			$this->_addAjaxWinAction(JText::_('COM_PAYPLANS_ORDER_TERMINATE_CONFRM_WINDOW_YES'), "payplans.ajax.go('".$url."'); ");
			$this->_addAjaxWinAction(JText::_('COM_PAYPLANS_AJAX_CANCEL_BUTTON'),'xi.ui.dialog.close();');
			$this->_setAjaxWinAction();
			$this->_setAjaxWinHeight('auto');
			return true;
		}
		
		$this->setTpl(__FUNCTION__);
		$this->_setAjaxWinTitle(JText::_('COM_PAYPLANS_ORDER_TERMINATE_STATUS_WINDOW_TITLE'));
		$this->_addAjaxWinAction(JText::_('COM_PAYPLANS_ORDER_TERMINATE_STATUS_WINDOW_CLOSE'),'xi.ui.dialog.close(); window.location.reload();');
		$this->_setAjaxWinAction();
		$this->_setAjaxWinHeight('auto');
		
		return true;
	}
	
}

