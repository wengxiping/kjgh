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

interface PPApiInvoiceInterface
{
	/**
	 * return the total amount of invoice
	 * Invoice total is inclusive of discount and tax and other kind of amount modification
	 * 
	 * @param  integer $number  Invoice number(counter) to get total of 
	 * @return float Value of the invoice total
	 * @since 2.0
	 */
	public function getTotal($number = 0);
	
	/**
	 * The subtotal amount of invoice
	 * This is exclusive of discount and tax
	 * 
	 * @return float  Value of the subtotal
	 * @since 2.0
	 */
	public function getSubtotal();
	
	/**
	 * returns the discount amount applied on invoice
	 * 
	 * @return float  Value of the discount
	 * @since 2.0
	 */
	public function getDiscount();
	
	/**
	 * returns the tax amount applied on invoice
	 * 
	 * @return float  Valus of the tax applied on the invoice
	 * @since 2.0
	 */
	public function getTaxAmount();
	
	/**
	 * returns the current status of invoice
	 * 
	 * @return integer Constant @type PayplansStatus
	 * @since 2.0
	 */
	public function getStatus();
	
	/**
	 * Gets the buyer of the order
	 * 
	 * @param boolean $requireinstance  If True return PayplansUser instance else Userid 
	 * 
	 * @return mixed  Userid or PayplansUser attached with the order
	 */
	public function getBuyer();
	
	/**
	 * Gets the expiration time of the invoice
	 * 
	 * Invoice has expiration time of its own.
	 * Initially its copied from subscription parameter and can be changed later 
	 * 
	 * @param integer $for  An integer constant indicating expiration type 
	 * 
	 * @return array  An array containing expiration values as string for year|month|day|hours|minute|seconds, two digit each. 
	 * 				  equals to 12 digit
	 */
	public function getExpiration($for = PAYPLANS_SUBSCRIPTION_FIXED, $raw = false);
	
	/**
	 * Gets the expiration type of the invoice
	 * @return string fixed / recurring / recurring_trial_1 / recurring_trial_2 / forever
	 */
	public function getExpirationType();
	
	/**
	 * Gets the recurrence count of the invoice
	 *    i.e. How many times payment need to be done.
		 *    Special Case : 0 = Lifetime
	 *
	 * @return integer  Value of the recurrence count
	 */
	public function getRecurrenceCount();
	
	/**
	 * Gets the type of the object which has created the invoice
	 * 
	 * @return string
	 */
	public function getObjectType();
	
	/**
	 * Gets the object id of the invoice
	 * 
	 * Object id is the identifier which has created invoice
	 * 
	 * @return integer
	 */
	public function getObjectId();
	
	/**
	 * Gets the regular amount including tax and discount
	 * 
	 * In terms of recurring, Regular amount is the one 
	 * which will be charged regularly after all the applicable trials
	 *  
	 * @return float  Value of the regular amount
	 */
	public function getRegularAmount();
	
	/**
	 * Confirm the invoice and create payment
	 * @param integer $appId  Payment gateway app id for payment creation
	 * 
	 * @return object PayplansInvoice  Instance of PayplansInvoice
	 */
	public function confirm($appId);
	
	/**
	 * returns the array of PayplansModifier
	 * @param  array $filters  Optional parameter to get the selected modifiers
	 * @return array  Array of objects of PayplansModifier type 
	 */
	public function getModifiers($filters = array());
	
	/**
	 * Gets the payment attached to the invoice
	 * 
	 * @return object PayplansPayment Instance of PayplansPayment  
	 */
	public function getPayment();
	

	
	/**
	 * Gets the wallet record attached to the invoice
	 * 
	 * @return object PayplansTransaction Instance of PayplansWallet
	 */
	public function getTransactions();
	
	/**
	 * Create payment on the invoice
	 * 
	 * @param  integer  $appId  App identifier (payment gateway app) for creating payment
	 * @return object PayplansPayment Instance of PayplansPayment
	 */
	public function createPayment($appId);
	
	/**
	 * Is invoice reccuring ?
	 * 
	 * @return mixed  Integer constant if invoice is of recurring type else False
	 */
	public function isRecurring();
	
	/**
	 * add modifier by considering the given params
	 * 1. Create Modifier as per params
		 * 2. Attach to current invoice.
	 * 
	 * @param $params : object of stdClass
	 * 
	 * @return  object PayplansModifier  Instance of PayplansModifier applied on the invoice 
	 */
	function addModifier($params = '');
	
	/**
	 * Adds a transaction on the invoice
	 * 
	 * 1. Finds payment gateway for invoice (default to admin payment)
	 * 2. Adds a transaction on the payment record
	 * 
	 * @param $parameters  object of stdClass
	 * 
	 * @return object PayplansTransaction  Instance of the PayplansTransaction type added on the current invoice 
	 */
	public function addTransaction($parameters='');
	
	/**
	 * return the counter of current invoice
	 * @return integer Counter of the invoice
	 */
	public function getCounter();
	
	/**
	 * The amount of invoice, on this amount discount will be applied.
	 * e.g. Plan amount + Any amount for addons (+/-)
	 * 
	 * @return double
	 * @since 2.1
	 */
	public function getDiscountable();
	
	/**
	 * returns the change in subtotal and total after applying tax
	 * it can be positive or negative
	 * @return double
	 */
	public function getNontaxableAmount();
	
	
	/**
	 * Gets the title of the invoice set in the parameters
	 * 
	 * @return string  Title of the invoice
	 * 
	 * @since 2.1
	 */
	public function getTitle();
	
	/**
	 * Terminate the invoice
	 * 
	 * Order is terminated by executing terminate on invoice object
	 *  Related Payment App is asked to terminate the recurring payments, if any.
	 * 
	 * @return array  boolean values indicating the output returned from event trigger
	 */
	public function terminate();
	
	
}

