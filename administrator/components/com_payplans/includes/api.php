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

require_once(__DIR__ . '/payplans.php');

class PayPlansApi
{
	/**
	 * Get PayPlans User object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getUser($userId)
	{
		return PP::user($userId);
	}
	
	/**
	 * Get all Available Plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getPlans($filter = array('published'=> 1))
	{
		return PP::model('plan')->loadRecords($filter);
	}
	
	/**
	 * Get Plan Object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getPlan($planId)
	{
		return PP::plan($planId);
	}
	
	
	/**
	 * Get All Available Plan Groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getGroups($filter = array('published'=> 1))
	{
		return PP::model('group')->loadRecords($filter);
	}
	
	/**
	 * Get Group Object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getGroup($groupId)
	{
		return PP::group($groupId);
	}
	
	/**
	 * Create new plan object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function createPlan()
	{
		return PP::plan(0);
	}
	
	
	/**
	 * Get all orders available in the system, By default returns all 
	 * orders, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getOrders($filter = array())
	{		
		return  PP::model('order')->loadRecords($filter);
	}
	
	/**
	 * Get Payplans Oder
	 * orders, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getOrder($orderId)
	{
		return PP::order($orderId);
	}
	
	/**
	 * Creates a new Order object and return it.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function createOrder()
	{
		return PP::order(0);
	}
	
	/**
	 * Get all subscriptions available in the system, By default returns all 
	 * subscriptions, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getSubscriptions($filter = array())
	{
		return PP::model('subscription')->loadRecords($filter);
	}
	
	/**
	 * Get the subscription object of given ID
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getSubscription($subscriptionId)
	{
		return PP::subscription($subscriptionId);
	}
	
	/**
	 * Creates a new Subscription object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function createSubscription()
	{
		return PP::subscription(0);
	}
	
	/**
	 * Get all payments available in the system, By default returns all 
	 * payments, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getPayments($filter = array())
	{
		return PP::model('payment')->loadRecords($filter);
	}
	
	/**
	 * Get payment object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getPayment($paymentId)
	{
		return PP::payment($paymentId);
	}
	
	/**
	 * @return stdClass Object
	 * If you update configuration here, it will NOT be saved 
	 * into database. The updated configuration will only work 
	 * for current execution cycle
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getConfig()
	{
		return PP::config();
	}
	
	/**
	 * Check If given user have subscription to given plan.
	 * 
	 * @param $userid : user id
	 * @param $planid : the plan to check against
	 * @param $staus  : Status can be one of below 3. 
	 *        PP_SUBSCRIPTION_ACTIVE
	 *        PP_SUBSCRIPTION_HOLD
	 *        PP_SUBSCRIPTION_EXPIRED
	 *        
	 * Imp: By default it checks subscription status = active 
	 *
	 * @return : false
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static function haveSubscription($userId, $planId, $status = PP_SUBSCRIPTION_ACTIVE)
	{
		$subscriptions = PP::model('subscription')->loadRecords(array('user_id' => $userId, 'plan_id' => $planId, 'status'=>$status));
								
		return count($subscriptions);
	}

	/**
	 * Get all invoices available in the system, By default returns all 
	 * invoices, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static function getInvoices($filter = array())
	{
		return PP::model('invoice')->loadRecords($filter);
	}
	
	/**
	  * Get Invoice object from invoice id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getInvoice($invoiceId)
	{
		return PP::invoice($invoiceId);
	}
	
	/**
	 * Get all transactions available in the system, By default returns all 
	 * transaction, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getTransactions($filter = array())
	{
		return PP::model('transaction')->loadRecords($filter);
	}
	
	/**
	 * Get Transaction object from transaction id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	static public function getTransaction($transactionId)
	{
		return PP::transaction($transactionId);
	}
	
	/**
	 * Gets the instance of PayplansModifier with the provided modifier identifier
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModifier($modifierId)
	{
		return PP::modifier($modifierId);
	}
	
	/**
	 * Get all modifiers available in the system, By default returns all 
	 * modifiers, you can change filter to get different subsets.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModifiers($filter = array())
	{
		return PP::model('modifier')->loadRecords($filter);
	}
} 