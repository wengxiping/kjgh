<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	18 February 2013
 * @file name	:	helpers/finance.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class FinanceHelper {
	
	//get the last plan's subscr details
	public static function getLastSubscription($userid = null){
		$db = JFactory::getDbo();
	
		$query = "SELECT MAX(id) FROM #__jblance_plan_subscr WHERE approved=1 AND user_id=".$db->quote($userid);
		$db->setQuery($query);
		$id_max = $db->loadResult();
	
		$query = "SELECT * FROM #__jblance_plan_subscr WHERE id=".$db->quote($id_max);
		$db->setQuery($query);
		$last_subscr = $db->loadObject();
	
		return $last_subscr;
	}
	
	//update Project Left column
	function updateProjectLeft($userid){
		$db = JFactory::getDbo();
		
		$last_subscr = $this->getLastSubscription($userid);
		if($last_subscr->projects_allowed > 0){
			$query = "UPDATE #__jblance_plan_subscr SET projects_left=projects_left-1 WHERE id=".$db->quote($last_subscr->id);
			$db->setQuery($query);
			if(!$db->execute())
				throw new Exception();
		}
	}
	
	//update bids Left column
	function updateBidsLeft($userid){
		$db = JFactory::getDbo();
		
		$last_subscr = $this->getLastSubscription($userid);
		if($last_subscr->bids_allowed > 0){
			$query = "UPDATE #__jblance_plan_subscr SET bids_left=bids_left-1 WHERE id=".$db->quote($last_subscr->id);
			$db->setQuery($query);
			if(!$db->execute())
				throw new Exception();
		}
	}
	
	//check if the user has paid
	function hasPaid($prj_svc_id, $type){
		$db = JFactory::getDbo();
		$query = "SELECT e.* FROM #__jblance_escrow e ".
				 "WHERE e.status='COM_JBLANCE_ACCEPTED' AND e.type=".$db->quote($type)." AND e.project_id=".$db->quote($prj_svc_id);
		$db->setQuery($query);//echo $query;
		if($db->loadObject())
			return 1;
		else
			return 0;
	}
	
	//get total amount paid
	function getTotalAmountPaid($prj_svc_id, $type){
		$db = JFactory::getDbo();
		$query = "SELECT SUM(e.amount) FROM #__jblance_escrow e ".
				 "WHERE e.type=".$db->quote($type)." AND e.project_id=".$db->quote($prj_svc_id);
		$db->setQuery($query);echo $query;
		return $db->loadResult();
	}
	
	//
	/**
	 * Get the details of payment from the invoice number
	 * 
	 * @param int $id Row id of deposit or subscription table
	 * @param string $buy Either deposit or plan
	 * @return array $details Array of payment details
	 */
	function getPaymentDetailsFromInvoice($invoice_num){
	
		$db 		= JFactory::getDbo();
		$details 	= array();
		
		$identify 	= JblanceHelper::identifyDepositOrPlan($invoice_num);	//get the type from invoice number
		$buy 		= $identify['type'];
		$id 		= $identify['id'];
		
		if($buy == 'deposit'){
			$row 	= JTable::getInstance('deposit', 'Table');
			$row->load($id);
	
			$amount 	= $row->total;
			$taxrate 	= 0;
			$totamt		= $amount;
			$orderid 	= $row->id;
			$itemname 	= JText::_('COM_JBLANCE_DEPOSIT_FUNDS');
			$item_num 	= $row->id;	// id of the fund deposit
			$invoiceNo 	= $row->invoiceNo;	// invoice number of the payment
			$user_id 	= $row->user_id;
		}
		elseif($buy == 'plan'){
			$row 	= JTable::getInstance('plansubscr', 'Table');
			$row->load($id);
	
			$query = "SELECT * FROM #__jblance_plan WHERE id = ".$db->quote($row->plan_id);
			$db->setQuery($query);
			$plan = $db->loadObject();
	
			$amount 	= $row->price;
			$taxrate 	= $row->tax_percent;
			$totamt 	= (float)($amount + $amount * ($taxrate/100));	$totamt = round($totamt, 2);
			$orderid 	= $row->id;
			$itemname 	= JText::_('COM_JBLANCE_BUY_SUBSCR').' - '.$plan->name;
			$item_num 	= $row->id;	// subscr id of the plan purchased
			$invoiceNo 	= $row->invoiceNo;	// invoice number of the payment
			$user_id 	= $row->user_id;
		}
		$details['amount'] 	  = $totamt;
		$details['taxrate']   = $taxrate;
		$details['orderid']   = $orderid;
		$details['itemname']  = $itemname;
		$details['item_num']  = $item_num;
		$details['invoiceNo'] = $invoiceNo;
		$details['user_id']   = $user_id;
		return $details;
	}
}
