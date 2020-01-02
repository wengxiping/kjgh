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

interface PayplansIfaceAppDiscount
{
	// all should implement how to apply discount
	//returns true/false or error-string
	public function onPayplansApplyDiscount(PPInvoice $object, $discountCode);

	//Check if given code is allowed on this order, all sort of checking
	// should be done in this function
	public function doCheckAllowed(PPInvoice $object, $discountCode);

	// This function will apply discount on every subscription
	public function doApplyDiscount(PPInvoice $object);

	// Check if coupon is allowed to be used on this subscription
	// Should return true or ErrorMessage
	public function doCheckApplicable($object);

	// Calculate the discount on this price as per your app rules
	public function doCalculateDiscount(PPInvoice $object, $price, $discount);
}
