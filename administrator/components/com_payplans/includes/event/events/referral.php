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

class PPEventReferral extends PayPlans
{
	/**
	 * Triggered when an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansInvoiceAfterSave($prev, $new)
	{
		// Do nothing is referral discount is disabled
		if (!$this->config->get('discounts_referral')) {
			return true;
		}

		// Nothing changed
		if ($prev != null && $new->getStatus() == $prev->getStatus()) {
			return true;
		}
		
		// If it isn ot marked as paid, don't do anything
		if (!$new->isPaid()) {
			return true;
		}

		$params = $new->getParams();
		$sharerId = $params->get('referrar_id', 0);
		$amount = $params->get('referrar_amount', 0);

		// Probably it has already been applied before confirmation of payment
		if (!$sharerId || !$amount) {
			return;
		}

		$sharer = PP::user($sharerId);
		
		$lib = PP::referral();
		// Generate coupon codes for the sharer
		$discount = $lib->createCouponCode($new, $sharer, $amount);

		// Notify referrer when a coupon has been generated
		if (!$discount->prodiscount_id) {
			return false;
		}
			
		$result = $lib->notify($new, $sharer, $discount, $amount);

		// Insert into the logs
		$lib->createReferralRecord($new, $sharer, $amount);

		return true;
	}

	/**
	 * Triggered when applying discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansApplyDiscount(PPInvoice $invoice, $code)
	{
		// Since referral already have its own behaviour, we should return nothing here.
	}
}
