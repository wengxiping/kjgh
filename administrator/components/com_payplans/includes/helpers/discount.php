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

jimport('joomla.filesystem.file');

require_once(JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php');

class PPHelperDiscount
{
	/**
	 * Check whether any discount is applicable on this invoice or not
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function getApplicableDiscounts($invoice, $filters)
	{
		$model = PP::model('Discount');
		$discounts = $model->loadRecords($filters);

		if (!$discounts) {
			return array();
		}

		$items = array();

		foreach ($discounts as $discountId => $value) {

			$discount = PP::Discount($discountId);

			//if discount coupon is not published or not within the applicable dates
			//then unset that discount
			if (!self::checkForApplicableDates($discount)) {
				continue;
			}

			//if not a core discount then check for the applicable planids
			if (!$discount->isCoreDiscount()) {

				$plans = $invoice->getPlans();
				$subPlan = $plans;

				if (is_array($plans)) {
					// get the first plan
					$subPlan = array_shift($plans);
				}

				if (is_object($subPlan)) {
					$subPlan = $subPlan->getId();
				}

				$discount_plans = $discount->getPlans();

				if (!in_array($subPlan,$discount_plans)) {
					continue;
				}
			}

			$items[] = $discount;
		}

		return $items;
	}

	/**
	 * Check whether the date is within the applicable dates limit or not
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function checkForApplicableDates(PPDiscount $discount)
	{
		//if coupon is published as per dates
		$publish_start = $discount->getStartDate();
		$publish_end = $discount->getEndDate();

		if (!$publish_start || $publish_start == '0000-00-00 00:00:00') {
			return true;
		}

		$now = PP::date();
		$startDate = PP::date($publish_start);

		if ($startDate->toUnix() > $now->toUnix()) {
			return false;
		}

		if (!$publish_end || $publish_end == '0000-00-00 00:00:00') {
			return true;
		}

		$endDate = PP::date($publish_end);
		
		// Disable the discount if end date reached
		if ($endDate->toUnix() < $now->toUnix()) {
			$discount->setPublished(0);
			$discount->save();
			return false;
		}

		return true;
	}
}
