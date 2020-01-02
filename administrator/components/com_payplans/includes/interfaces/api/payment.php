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

interface PPApiPaymentInterface
{	
	/**
	 * @return buyer(user) linked with the current payment
	 * if $instance is PP_INSTANCE_REQUIRE then return user instance
	 * else return userid 
	 */
	public function getBuyer($instance=false);
	
	/**
	 * @return created date of payment
	 */
	public function getCreatedDate();
	
	/**
	 * @return date when payment is last modified
	 */
	public function getModifiedDate();
	
	/**
	 * @return payment app this payment has made from
	 * if $requireInstance is PP_INSTANCE_REQUIRE then return instance of payment app
	 * else payment app id
	 */
	public function getApp();

	/**
	 * Gets the transaction attached with the payment
	 * @return array  Array of transaction object (PayplansTransaction)
	 */
	public function getTransactions();
	
	/**
	 * Gets the invoice linked with the current payment
	 * @param  boolean  $requireInstance  Optional parameter to get the object (PayplansInvoice)
	 * @return mixed  InvoiceId or object of PayplansInvoice for InvoiceId
	 */
	public function getInvoice();
	
	/**
	 * Gets the gateway params of the payment
	 * Gateway params are payment gateway specific parameters 
	 * like pending recurrence cycle to process, subscribe id etc 
	 * @return  object XiParameter
	 */
	public function getGatewayParams();
}