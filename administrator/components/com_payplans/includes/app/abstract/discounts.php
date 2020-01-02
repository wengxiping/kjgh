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

require_once(PP_LIB . '/app/app.php');

abstract class PPAppDiscounts extends PPApp implements PayplansIfaceAppDiscount
{

	// All discount app should have some common params
	// Like :
	// publish_start
	// publish_end

	/**
	 * Determine if we need to implement if plugin is applicable or not
	 *
	 * All discount app should have some common params
	 * Like :
	 *   publish_start
	 *   publish_end
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($refObject = null, $eventName='')
	{
		//if coupon is published as per dates
		$publish_start = $this->getAppParam('publish_start', '');
		$publish_end = $this->getAppParam('publish_end','');

		$now = PP::date();
		if ($publish_start != '') {
			$start = new JDate($publish_start);

			if ($start->toUnix() > $now->toUnix()) {
				return false;
			}
		}

		if ($publish_end != '') {
			$end = new JDate($publish_end);

			if ($end->toUnix() < $now->toUnix()) {
				//also disable the discount
				$this->published = false;
				$this->save();
				return false;
			}
		}

		return parent::_isApplicable($refObject);
	}


	/**
	 * Simply Checks, if disocunt-app is attached to given subscription
	 * Tips : Avoid overriding this function
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function doCheckApplicable($object)
	{
		//Check if not applicable on given subscription
		if ($this->getParam('applyAll',false)) {
			return true;
		}

		$plans = $object->getPlans();
		$subPlan = array_shift($plans);
		return in_array($subPlan,$this->getPlans());
	}

	/**
	 * Iteratively apply discount
	 * Tips : Avoid overriding this function
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function doApplyDiscount(PPInvoice $object)
	{
		// apply dicount on invoice
		$discountUsed = false;
		$price 		= $object->getSubTotal() + $object->getDiscountable();
		$discount 	= $object->getDiscount();

		if ($this->doCheckApplicable($object, $price, $discount) ==false) {
			return $discountUsed;
		}

		list($amount, $isPercentage) = $this->doCalculateDiscount($object, $price, $discount);

		$modifier = PayplansModifier::getInstance();
		$modifier->set('message', JText::_('COM_PAYPLANS_APP_BASIC_DISCOUNT_MESSAGE'))
				 ->set('invoice_id', $object->getId())
				 ->set('user_id', $object->getBuyer())
				 ->set('type', $this->getType())
				 ->set('amount', -$amount) // Discount should be negative
				 ->set('reference', $this->getAppParam('coupon_code', ''))
				 ->set('percentage', $isPercentage ? true : false)
				 ->set('frequency', $this->getAppParam('onlyFirstRecurringDiscount', false) ? PP_MODIFIER_FREQUENCY_ONE_TIME : PP_MODIFIER_FREQUENCY_EACH_TIME);

		/**
		 * V.V.IMP : this is very impotant for applying discount in which serial
		 * @see PayplansModifier
		*/
		$serial = ($isPercentage === true)
							? PP_MODIFIER_PERCENT_DISCOUNT
							: PP_MODIFIER_FIXED_DISCOUNT;

		// XITODO : add error checking
		$modifier->set('serial', $serial)->save();

		// refresh the object after applying discount
		$object->refresh();

		if ($this->getAppParam('onlyFirstRecurringDiscount', false) && $object->isRecurring() == PP_RECURRING) {
				$params = $object->getParams()->toArray();
				$object->setParam('expirationtype', 'recurring_trial_1');
				$object->setParam('recurrence_count', ($params['recurrence_count']> 0 ) ? $params['recurrence_count']-1 : 0);
				$object->setParam('trial_price_1', $params['price']);
				$object->setParam('trial_time_1', $params['expiration']);
		}

		$object->save();
		return true;
	}
}
