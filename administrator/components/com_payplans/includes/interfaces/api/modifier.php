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
 * These functions are listed for Modifier object
 * @author JPayplans
 * @since 2.0
 */

class PayplansIfaceApiModifier
{
	/**
	 * Gets the Amount of Modifier
	 * @return float Modifier amount.
	 */
	public function getAmount();	

	
	/**
	 * Checks the Modifier amount is fixed or in percentage.
	 * @return boolean true/false  True when amount is in percentage else False.
	 */
	public function isPercentage();
	
	
	/**
	 * Type of a modifier is known as a serial.
	 * 
	 * Discountable Modifier means any addition or substraction before applying discount/tax
	 * FIXED_DISCOUNTABLE = 10, PERCENT_DISCOUNTABLE = 15
	 * 
	 * Discount Modifier means discount on invocie
	 * FIXED_DISCOUNT = 20, PERCENT_DISCOUNT = 25
	 *  
	 * Tax Modifier means tax on invoice
	 * FIXED_TAX = 30, PERCENT_TAX = 35
	 * 
	 * NON-TAXABLE Modifier means any addition or substraction after applying discount/tax
	 * PP_MODIFIER_FIXED_NON_TAXABLE = 40, PERCENT_NON_TAXABLE = 45
	 * 
	 * @return integer Constant value of the serial.
	 */	
	public function getSerial();
	
		
	/**
	 * Gets string which specifies how much time the modifier can be used.
	 * Like FREQUENCY_ONE_TIME and FREQUENCY_EACH_TIME
	 * Modifier with frequency of FREQUENCY_ONE_TIME will be applicable only one time whereas 
	 * FREQUENCY_EACH_TIME indicates that the modifier will be applicable every time.
	 *
	 * @return string One Time for FREQUENCY_ONE_TIME and Each Time for FREQUENCY_EACH_TIME
	 */
	public function getFrequency();
	
	
	/**
	 * Gets Type of modifier
	 * Type of the modifier indicates the Name of App or any other resource by which the modifier was created.
	 *
	 * @return String Name of the app or other resource which has created the modifier. 
	 */
	public function getType();
	
	
	
	/**
	 * Gets a message in string format.
	 * @return String Message attached with the modifier.
	 */
	public function getMessage();
	
	
	/**
	 * Gets the reference.
	 * In case of Discount, Coupon code is treated as Reference.
	 * In case of Upgrade, Old Invoice_key is treated as Reference.
	 * @return String Reference code by which modifier has been applied.
	 */
	public function getReference();
}