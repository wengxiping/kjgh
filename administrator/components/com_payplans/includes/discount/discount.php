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

require_once(PP_LIB . '/abstract.php');

class PPDiscount extends PPAbstract
{
	public static function factory($id)
	{
		return new self($id);
	}

	// 	not for table fields
	public function reset($config = array())
	{
		$this->table->prodiscount_id = 0;
		$this->table->title = null;
		$this->table->coupon_code = null;
		$this->table->core_discount = 1;
		$this->table->coupon_amount = 0.0000;
		$this->table->coupon_type = null;
		$this->table->start_date = PP::date('0000:00:00 00:00:00');
		$this->table->end_date = PP::date('0000:00:00 00:00:00');
		$this->table->plans = array();
		$this->table->published = 1;
		$this->table->params = new JRegistry();

		return $this;
	}

	/**
	 * Given the invoice, calculate the total discount for an invoice with the current discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalDiscount(PPInvoice $invoice)
	{
		$price = $invoice->getSubTotal() + $invoice->getDiscountable();

		if ($price <= 0) {
			return 0;
		}

		$amount = $this->getCouponAmount();

		// For renewals discount
		if ($this->isForRenewals()) {
			$order = $invoice->getOrder();

			$subscription = $order->getSubscription();
			$amount = $this->getRenewDiscountAmount($subscription);
		}

		return $amount;
	}

	/**
	 * Determines if a coupon code can be applied on a plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isInvoiceApplicable(PPInvoice $invoice, $discountCode = '')
	{
		// check for discount date range
		$applicable = PPHelperDiscount::checkForApplicableDates($this);
		if (!$applicable) {
			return false;
		}

		// For non core discounts, need to check if the plan matches the allowed plans
		if (!$this->isCoreDiscount()) {
			$plans = $this->getPlans();
			$plan = $invoice->getPlan();

			if (!in_array($plan->getId(), $plans)) {
				return false;
			}
		}

		// Ensure that the coupon isn't specific for recurring
		if ($this->isForRecurring() && !$invoice->isRecurring()) {
			return false;
		}

		if (! $discountCode) {
			$discountCode = $this->getCouponCode();
		}

		// Do not allow user to apply same discount code multiple times
		$options = array('type' => $this->getCouponType(), 'reference' => $discountCode);
		$modifiers = $invoice->getModifiers($options);

		if (count($modifiers) > 0) {
			return false;
		}

		// If multiple discount on same invoice is not allowed then check for the perviously applied discount
		$allowClubbing = $this->config->get('multipleDiscount');

		/**
		 * Check whether clubbing of discounts is to be given for the current discount
		 * case 1: If $allowClubbing is yes, and the applying discount allow clubbing then do nothing
		 * case 2: If $allowClubbing is yes, and the applying discount doesn't allow clubbing with others then
		 *         check whether any other discount exist which already disallow clubbing or not, if yes, then show error msg
		 *         otherwise apply current discount
		 * case 3: If $allowClubbing is no, and invoice already have some discount ammount then show error msg
		 *
		 */
		$filters = array('serial' => array(PP_MODIFIER_FIXED_DISCOUNT, PP_MODIFIER_PERCENT_DISCOUNT));
		$discountModifiers = $invoice->getModifiers($filters);

		$model = PP::model('Discount');
		$nonCombinableModifiers = $model->getNonCombinableDiscounts($discountModifiers);

		if (($allowClubbing && !$this->isCombinable() && count($nonCombinableModifiers)) || (!$allowClubbing && $invoice->getDiscount() != 0)) {

			$this->setError('COM_PAYPLANS_PRODISCOUNT_CANT_APPLY_MULTIPLE_DISCOUNT');
			return false;
		}

		// Disallow user to use the same discount code on different subscriptions if discount is not reusable
		$user = $invoice->getBuyer();

		if (!$this->isReusable() && $model->hasUsed($discountCode, $user->getId(), $this->getCouponType())) {
			$this->setError('COM_PAYPLANS_PRODISCOUNT_ERROR_ALREADY_USED');
			return false;
		}

		// If admin configured the use quantity, then we need to check if it is allowed
		$allowedQuantity = $this->getAllowedQuantity();

		if ($allowedQuantity) {
			// Get total usage
			$usage = $this->getCounter();

			if ($usage >= $allowedQuantity) {
				$this->setError('COM_PAYPLANS_PRODISCOUNT_ERROR_CODE_ALLOWED_QUANTITY_CONSUMED');
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves the id of the discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getId()
	{
		return $this->table->prodiscount_id;
	}

	/**
	 * Retrieves discount title
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return JText::_($this->table->title);
	}

	/**
	 * Retrieves a list of plans associated with this discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlans()
	{
		if (!$this->plans) {
			return array();
		}

		$plans = json_decode($this->plans);
		return $plans;
	}

	/**
	 * Retrieves the starting date of the discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStartDate()
	{
		return $this->start_date;
	}

	/**
	 * Retrieves the ending date of the discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEndDate()
	{
		return $this->end_date;
	}

	//set published as true/false
	public function setPublished($published)
	{
		return $this->published = $published;
	}

	/**
	 * Retrieves the parameters for the discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		static $cache = array();

		if (!isset($cache[$this->getId()])) {
			$cache[$this->getId()] = new JRegistry($this->params);
		}

		return $cache[$this->getId()];
	}

	//get params
	public function getParam($key,$default=null)
	{
		$params = $this->getParams();

		return $params->get($key, $default);
	}

	/**
	 * Retrieves the coupon type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponType()
	{
		return $this->table->coupon_type;
	}

	/**
	 * Retrieves the coupon type label
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponTypeLabel()
	{
		$label = 'COM_PP_DISCOUNTS_TYPE_' . strtoupper($this->table->coupon_type);
		$label = JText::_($label);
		return $label;
	}

	/**
	 * Determines the total number of usage allowed for the discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllowedQuantity()
	{
		$params = $this->getParams();
		$total = (int) $params->get('allowed_quantity', '');

		return $total;
	}

	/**
	 * Retrieves the amount of the coupon
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponAmount()
	{
		return $this->table->coupon_amount;
	}

	/**
	 * Retrieves the coupon code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCouponCode()
	{
		return $this->coupon_code;
	}

	/**
	 * set coupon code
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setCouponCode($code)
	{
		$this->coupon_code = $code;
	}


	/**
	 * Used as reference codes in logging
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferenceCode()
	{
		$unique = array(PP_PRODISCOUNT_AUTOONRENEWAL, PP_PRODISCOUNT_AUTOONUPGRADE, PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION);

		// Get normal reference codes with coupon code
		if (!in_array($this->coupon_type, $unique)) {
			return $this->coupon_code;
		}

		if ($this->coupon_type == PP_PRODISCOUNT_AUTOONRENEWAL) {
			$value = PP_PRODISCOUNT_RENEWAL_DISCOUNT;
		}

		if ($this->coupon_type == PP_PRODISCOUNT_AUTOONUPGRADE) {
			$value = PP_PRODISCOUNT_UPGRADE_DISCOUNT;
		}

		if ($this->coupon_type == PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION) {
			$value = PP_PRODISCOUNT_INVOICE_CREATION_DISCOUNT;
		}

		$value = $value . '_' . $this->getId();

		return $value;
	}

	/**
	 * For renewal based coupons, we need to calculate the total amount of discount given
	 * as different discounts can be given before / after expiration
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRenewDiscountAmount(PPSubscription $subscription)
	{
		$params = $this->getParams();

		if ($subscription->isExpired()) {
			return $params->get('amount_post_expiry', 0);
		}

		if ($subscription->isActive()) {
			return $params->get('amount_pre_expiry', 0);
		}

		return 0;
	}

	/**
	 * Retrieves the total usage of the discount. This does not mean it has been used.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCounter()
	{
		return PPHelperModifier::getTotalUsage($this->getReferenceCode(), $this->coupon_type);
	}

	/**
	 * Retrieves the total consumption of the discount.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getConsumption($count = true)
	{
		return PPHelperModifier::getActualConsumption($this->getReferenceCode(), $this->coupon_type, $count);
	}

	/**
	 * Determines if this is a core discount which is applicable to all plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCoreDiscount()
	{
		return $this->core_discount ? true : false;
	}

	/**
	 * Determines if this coupon can be combined with other coupons
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCombinable()
	{
		$params = $this->getParams();

		$clubbing = (bool) $params->get('allow_clubbing', false);

		return $clubbing;
	}

	/**
	 * Determines if the coupon type is fixed amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isFixed()
	{
		$params = $this->getParams();
		$fixed = $params->get('coupon_amount_type', 'fixed') == 'fixed' ? true : false;

		return $fixed;
	}

	/**
	 * Determines if the coupon type is percentage amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPercentage()
	{
		$params = $this->getParams();
		$percentage = $params->get('coupon_amount_type', 'fixed') == 'percentage' ? true : false;

		return $percentage;
	}

	/**
	 * Determines if the coupon type is for recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isForExtension()
	{
		return $this->getCouponType() == PP_PRODISCOUNT_EXTEND_TIME_DISCOUNT;
	}

	/**
	 * Determines if the coupon type is for recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isForRecurring()
	{
		return $this->getCouponType() == PP_PRODISCOUNT_EACHRECURRING;
	}

	/**
	 * Determines if the coupon type is for recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isForRenewals()
	{
		return $this->getCouponType() == PP_PRODISCOUNT_AUTOONRENEWAL;
	}

	/**
	 * Determines if this discount is published or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPublished()
	{
		return $this->published ? true : false;
	}

	/**
	 * Determines if the coupon can be reused
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isReusable()
	{
		$params = $this->getParams();
		$reusable = $params->get('reusable', true) ? true : false;

		return $reusable;
	}

	/**
	 * Handle our own save method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$state = parent::save();

		// Log the event
		if ($state) {
			// PayplansEventLog::_save($prev, $new,'PRODISCOUNT');
		}
	}

	/**
	 * Creates a standard object that can be used as a modifier
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toModifier(PPInvoice $invoice)
	{
		$modifier = new stdClass();
		$modifier->message = $this->table->title;
		$modifier->type = $this->getCouponType();
		$modifier->reference = $this->getCouponCode();

		// PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION
		// this discount type says it will be applied on every new invoice creation
		// so it also applies when create new invoice of recurring subscription, but the amount asked to payment gateway is the full price.
		// so make this discount type to eachtime.
		$modifier->frequency = !$this->isForRecurring() ? PP_MODIFIER_FREQUENCY_ONE_TIME : PP_MODIFIER_FREQUENCY_EACH_TIME;

		if ($this->isForExtension()) {
			$modifier->message = JText::_('COM_PAYPLANS_PRODISCOUNT_EXETEND_TIME_MODIFIER_MESSAGE');

			return $modifier;
		}

		$amount = $this->getTotalDiscount($invoice);

		if (floatval($amount) == PP_ZERO) {
			return false;
		}

		$modifier->amount = -$amount;
		$modifier->percentage = $this->isPercentage() ? true : false;

		// VERY IMPORTANT: When applying discount, we need to specify if this is a percentage or fixed amount for modifier.
		$modifier->serial = $modifier->percentage ? PP_MODIFIER_PERCENT_DISCOUNT : PP_MODIFIER_FIXED_DISCOUNT;

		return $modifier;
	}

	/**
	 * Ensure that by applying the discount, the total amount does not exceed the amount payable
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function checkForUpperLimit(&$newModifier, $invoice)
	{
		$subTotal = $invoice->getSubtotal();
		$discountable = $invoice->getDiscountable();
		$actualTotal = $subTotal + $discountable;

		$maxDiscount = (float) $this->config->get('allowedMaxPercentDiscount');
		$allowedAmount = ($maxDiscount * $actualTotal) / 100;

		$existingDiscounts = $invoice->getDiscount();

		// If existing discount is already greater or equal to max allowed
		if ($existingDiscounts >= $allowedAmount) {
			return false;
		}

		// Create dummy modifier
		$modifier = PP::modifier();
		$modifier->amount = $newModifier->amount;
		$modifier->percentage = $newModifier->percentage;
		$modifier->serial = $newModifier->serial;

		// merge dummy and pervious modifier to calculate the actual modification amount
		// after applying the new discount
		$total = $invoice->getTotal();

		$serialsFilter = array(PP_MODIFIER_FIXED_DISCOUNT, PP_MODIFIER_PERCENT_DISCOUNT);
		$modifiers = array_merge($invoice->getModifiers(array('serial' => $serialsFilter)), array($modifier));

		PPHelperModifier::getTotal($subTotal, $modifiers);
		$resultAmount = -(PPHelperModifier::getModificationAmount($total, $modifiers, $serialsFilter));

		//if crossing the limit apply the possible discount and give a message
		$applicableAmount = ($allowedAmount - $existingDiscounts);
		$msg = false;

		if ($resultAmount > $allowedAmount && $applicableAmount > 0) {
			// $msg =  JText::_('COM_PAYPLANS_PRODISCOUNT_MAX_ALLOWED_DISCOUNT_CROSSED');

			//in case of fixed discount
			$newModifier->amount = -$applicableAmount;

			/**
			 * If the current discount is in percentage
			 * then calculate and apply the allowable percent discount
			 */
			if ($newModifier->percentage) {
				$newModifier->amount = ($newModifier->amount*100)/($actualTotal-$existingDiscount);
			}

			return false;
		}

		return true;
	}

	/**
	 * Deprecated. Use @isPublished instead
	 *
	 * @deprecated	4.0.0
	 */
	public function getPublished()
	{
		return $this->isPublished();
	}

	/**
	 * Deprecated. Use @isCoreDiscount instead
	 *
	 * @deprecated	4.0.0
	 */
	public function getCoreDiscount()
	{
		return $this->isCoreDiscount();
	}
}
