<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();

/**
 * These functions are listed for Order object
 * @author bhavya
 *
 */
interface PayplansIfaceApiOrder
{	
	/**
	 * @return mixed  UserId of the order or Instance of PayplansUser for UserId
	 * @param boolean $requireinstance True then return user instance else UserId
	 * else return userid 
	 **/
	public function getBuyer($requireinstance=false);
	
	/**
	 * @return integer Value of the status of order
	 * available order status are  
	 * PP_NONE, PP_ORDER_CONFIRMED,PP_ORDER_PAID,
	 * PP_ORDER_COMPLETE, PP_ORDER_HOLD, PP_ORDER_EXPIRED
	 */
	public function getStatus();
	
	/**
	 * Gets the subscription attached with the current order
	 * 
 	 * @return object PayplansSubscription Instance of PayplansSubscription
 	 */
 	public function getSubscription();
 	
 	/**
 	 * Gets the total of the order
 	 * Order total is exclusive of all discounts and taxes 
 	 * 
 	 * @return float  Total amount of the order
 	 */
 	public function getTotal();
 	
 	/**
 	 * Gets order creation date
 	 * 
 	 * @return object XiDate  Instance of XiDate
 	 */
 	public function getCreatedDate();
 	
 	/**
	 * Gets the invoices attached on the order
	 * If status is null then return all the attached invoices
	 * 
	 * Available invoice status are : NONE, INVOICE_CONFIRMED, INVOICE_PAID, INVOICE_REFUNDED, INVOICE_WALLET_RECHARGE
	 * @param mixed $status  Status of the invoice to be get, or array of status
	 * 
	 * @return array  Array of PayplansInvoice
	 */
	public function getInvoices($status = null);
	
	/**
	 * Gets the invoice attached on the order with specified counter
	 * @param integer $counter  Counter of the invoice to be get
	 * @return  mixed  Object if invoice with the specified counter exists else retuen false
	 */
	public function getInvoice($counter = null);
	
	/**
	 * Create a new invoice on the order
	 * 
	 * @return object PayplansInvoice Instance of PayplansInvoice
	 */
	public function createInvoice();
	
	/**
	 * Is recurring Order?
	 * @return integer when subscription attached with the order is of recurring/recurring+trial type else return False
	 */
	public function isRecurring();
	
	/**
	 * Renew the subscription 
	 * Extend the subscription time to the specified time
	 * 
	 * @param string $expiration  12 digits numeric string each 2 digits denotes the value for year,
	 *  month, day, minute, hour and second in the same sequence, starting from year(starting 2 digits indicate year)
	 *  
	 *  @return object PayplansOrder Instance of PayplansOrder
	 */
	function renewSubscription($expiration);

	/**
	 * Refund the subscription amount and mark order on Hold
	 * @return object PayplansOrder  Instance of PayplansOrder
	 */
	public function refund();
	
	/**
	 * Terminate/cancel the order
	 * 
	 * Termination is applicable on recurring orders only.
	 *
	 * @return array  boolean values indicating the output returned from event trigger
	 */
	public function terminate();
	
	/**
	 * Gets the count of the completed recurrence cycle
	 * @return integer  Number indicating completed recurring cycle
	 */
	public function getRecurringInvoiceCount();
 	
	
}