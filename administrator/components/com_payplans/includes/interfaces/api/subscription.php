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

interface PayplansIfaceApiSubscription
{
	
	/**
	 * @example  
	 * 	PayplansSubscription::getInstance(5)->isActive();
	 * 
	 * @return boolean 
	 * 		Subscription is Active : True 
	 * 		else : False
	 */
	public function isActive();
	
	
	/**
	 * Setup the subscription object for given plan
	 * It will Update -
	 * 	1. Price  = Equal to Plan price
	 * 	2. Discount = reset to Zero 
	 *  3. Status = None
	 *  4. Subscription Date = current date
	 *  5. Expiration Date	 = current date + expiration time of plan
	 */
	public function setPlan($plan);
	
	/**
	 * @return mixed Userid or instance of PayplansUser attached with the subscription
	 */
	public function getBuyer();
	
	/**
	 * Update the PayplansSubscription object
	 * It will update
	 * 1. buyer Id = equal to buyer id of the order
	 * 2. order id = equal to the id of the $order
	 * @return object PayplansSubscription Instance of PayplansSubscription
	 */
	public function setOrder(PPOrder $order);

	/**
	 * @return mixed Instance of PayplansOrder or Orderid this subscription is linked with
	 * @param boolean $requireInstance If True return PayplansOrder instance else return order_id
	 */
	public function getOrder();
	
	/**
	 * Gets the expiration time of the subscription
	 * 
	 * @param integer $for  An integer constant indicating expiration type 
	 */
	public function getExpiration($for = PAYPLANS_SUBSCRIPTION_FIXED);
	
	/**
	 * Gets the recurrence count of the subscription
	 */
	public function getRecurrenceCount();
	
	/**
	 * Refund the subscription
	 * Mark the subscription status to refund and save
	 * 
	 */
	public function refund();
	
	/**
	 * Gets the expiration type of the subscription
	 * 
	 * @return  string 
	 */
	public function getExpirationType();
	
	/**
	 * Is subscriotion reccuring ?
	 */
	public function isRecurring();
	
	/**
	 * Renew the subscription
	 * 
	 * Activate the subscription and add the given 
	 * expiration time to the existing expiration time of the subscription.
	 * Extend the sibscription is already active
	 * 
	 * 
	 * @param string $expiration  12 digits numeric string 
	 * each 2 digits denotes the value for year, month, day, minute, 
	 * hour and second in the same sequence, starting from year(starting 2 digits indicate year)
	 */
	public function renew($expiration);
	
	/**
	 * Sets the buyer for the subscription
	 * @param  integer $userId  UserId to which the subscription will be attached
	 */
	public function setBuyer($userId=0);
	
	/**
	 * Gets the status of the subscription
	 */
	public function getStatus();
	
	/**
	 * Gets the total amount of the subscription
	 * Subscription total is exclusive of tax and discount.
	 */
	public function getTotal();
	
	/**
	 * returns float Price of subscription
	 * @param integer $type
	 * if type is not set then return the regular/normal price
	 * if it is set to RECURRING_TRIAL_1 then return first trial price 
	 * if it is set to RECURRING_TRIAL_2 then return second trial price 
	 */
	public function getPrice($type = PAYPLANS_SUBSCRIPTION_FIXED);
	
	/**
	 * Gets the title of the subscription
	 * Subscription title is the title of the plan
	 */
	public function getTitle();
}