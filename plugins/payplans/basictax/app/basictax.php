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

// require_once(__DIR__ . '/formatter.php');

class PPAppBasictax extends PPApp
{
	//inherited properties
	protected $_location	= __FILE__;


	// 	Entry Function
	public function onPayplansApplyTax(PPInvoice $invoice, $countryCode, $purpose = 0, $vatno = false)
	{
		$applyTo = $this->getAppParam('tax_country', array());

		// make sure its array
		$applyTo =  PP::makeArray($applyTo);

		if (!in_array(PP_CONST_ALL,$applyTo)) {

			// not applicable
			if (in_array($countryCode,$applyTo) == false){
				return NULL;
			}
		}

		$tax = new stdClass();
		$tax->rate = floatval($this->getAppParam('tax_rate', 0));
		$tax->title = $this->title;
		return $tax;
	}
}
