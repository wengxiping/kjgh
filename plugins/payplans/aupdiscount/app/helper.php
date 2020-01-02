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

class PPHelperAupDiscount extends PPHelperStandardApp
{
	/**
	 * Applies discount to an invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function apply(PPInvoice $invoice, $points)
	{
		if (!is_numeric($points)) {
			return false;
		}

		$lib = PP::aup();

		if (!$lib->exists()) {
			return false;
		}

		if (!$this->isAllowedToUsePoints($invoice, $points)) {
			return false;
		}

		// Here we assume that the user is allowed to use their points
		$balance = $lib->getPoints() - $points;


		if ($balance <= 0) {
			$this->setError('Not enough points to perform for this transaction');
			return false;
		}


		$state = $this->applyDiscountToInvoice($invoice, $points);

		// Deduct user's AUP points
		if (!$state) {
			return false;
		}

		PPLog::log(PPLogger::LEVEL_INFO, JText::_('_AupDiscountPoints'), $this->app, 'Remove AUP Points: -' . $points, 'PayplansAppAupDiscountFormatter');

		// Ensure that the rule exists on AUP
		if (!$lib->isRuleExists('plgaup_payplans_aupdiscount')) {
			$lib->createRule('PayPlans Rule', 'AUP Discounts', 'PayPlans', 'plgaup_payplans_aupdiscount');
		}

		// We cannot use the invoice user to prevent others from executing this request
		$user = PP::user();

		$lib->assignPoints($user, 'plgaup_payplans_aupdiscount', $invoice->getId(), 'Discount Plan with Points', -$points);

		return true;
	}

	/**
	 * Applies discount on the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function applyDiscountToInvoice(PPInvoice $invoice, $points)
	{
		$pointsUsed = 0;

		$price = $invoice->getSubTotal();
		$discount = $invoice->getDiscount();

		// Check if plan is allowed to use such discount
		$applicable = $this->app->doCheckApplicable($invoice, $price, $discount);

		if (!$applicable) {
			return $pointsUsed;
		}


		$amount = $this->calculateDiscount($invoice, $price, $discount, $points);
		$frequency = $this->isApplicableToFirstInvoice() ? PP_MODIFIER_FREQUENCY_ONE_TIME : PP_MODIFIER_FREQUENCY_EACH_TIME;

		$modifier = PP::createModifier($invoice, -$amount, false, $this->app->getType(), JText::_('COM_PP_AUPDISCOUNT_DISCOUNT_APPLIED'), $frequency, PP_MODIFIER_FIXED_DISCOUNT);

		// Ensure that we only apply on the current user's invoice
		$modifier->user_id = PP::user()->getId();
		$modifier->reference = 'aup_' . $this->app->getId();
		$modifier->save();

		// Refresh the object after applying discount
		$invoice->refresh();

		// if ($invoice->isRecurring() && $this->getAppParam('onlyFirstRecurringDiscount', false)) {
		// 	$params = $object->getParams()->toArray();
		// 	$object->setParam('expirationtype', 'recurring_trial_1');
		// 	$object->setParam('recurrence_count', ($params['recurrence_count']> 0 ) ? $params['recurrence_count']-1 : 0);
		// 	$object->setParam('trial_price_1', $params['price']);
		// 	$object->setParam('trial_time_1', $params['expiration']);
		// }

		$invoice->save();
		return true;
	}

	/**
	 * Calculate the discount used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function calculateDiscount(PPInvoice $invoice, $price, $discount, $points)
	{
		if ($price <= 0) {
			return 0;
		}

		$ratio = $this->getRatio();
		$round = $this->shouldRoundPoints();

		$computed = $points / $ratio;
		$amount = floor($computed);

		if ($round) {
			$amount = ceil($computed);
		}

		return $amount;
	}

	/**
	 * Retrieves allowed quantity amount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllowedQuantity()
	{
		return $this->params->get('allowed_quantity', '');
	}

	/**
	 * Retrieves campaign end date
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEndDate()
	{
		return $this->params->get('publish_end', '');
	}

	/**
	 * Retrieves the ratio for points to $1
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRatio()
	{
		return $this->params->get('ratio', 1);
	}

	/**
	 * Retrieves the minimum points required
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMinimumPoints()
	{
		return $this->params->get('min_aup', 0);
	}

	/**
	 * Retrieves the maximum points allowed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMaximumPoints()
	{
		return $this->params->get('max_aup', 0);
	}

	/**
	 * Determines if app is really enabled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isEnabled()
	{
		if (!$this->app->getId()) {
			return false;
		}

		if (!$this->app->published) {
			return false;
		}
		
		return true;
	}

	/**
	 * Determines if discount can be applied multiple times
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isReusable()
	{
		return (bool) $this->params->get('reusable', true);
	}

	/**
	 * Determines if discount is only applicable to first recurring
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicableToFirstInvoice()
	{
		return $this->params->get('onlyFirstRecurringDiscount', false);
	}

	/**
	 * Check if user is allowed to use their points on the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isAllowedToUsePoints(PPInvoice $invoice, $points)
	{
		$lib = PP::aup();

		$availablePoints = $lib->getPoints();

		// if no points then they should not be able to apply any
		if ($availablePoints <= 0) {
			$this->setError('Not enough points available to use for this transaction');
			return false;
		}

		// If discount amount less than the max allowed
		if ($points > $this->getMaximumPoints()) {
			$this->setError('Points used exceeded maximum points allowed');
			return false;
		}

		// If discount amount more than the minimum required
		if ($points < $this->getMinimumPoints()) {
			$this->setError('Points used does not meet the minimum requirements');
			return false;
		}

		// If discount was already applied previously, do not allow using it the second time
		if ($this->isUsed($invoice)) {
			return false;
		}

		// If multiple discount on same invoice is not allowed then check for the perviously applied discount
		$config = PP::config();
		if (!$config->get('multipleDiscount') && $invoice->getDiscount() != 0) {
			$this->setError(JText::_('COM_PAYPLANS_APP_DISCOUNT_CANT_APPLY_MULTIPLE_DISCOUNT'));
			return false;
		}


		$user = PP::user();
		$model = PP::model('Modifier');
		$modifiers = $model->loadRecords(array(
			'user_id' => $user->getId(),
			'reference' => 'aup_' . $this->app->getId(),
			'type' => $this->app->getType()
		));

		// Restrict user to use the same discount code on different subscriptions if reusable parameter is set to no
		if (!$this->isReusable() && count($modifiers) > 0) {
			//user already used the mentioned discount code, not allowed to use it again
			$this->setError(JText::_('COM_PAYPLANS_APP_DISCOUNT_ERROR_ALREADY_USED'));
			return false;
		}

		$modifiers = $model->loadRecords(array(
			'reference' => 'aup_' . $this->app->getId(),
			'type' => $this->app->getType()
		));

		// if coupon have been used completely unlimited usage if allowed quantity is ''
		$allowedQuantity = $this->getAllowedQuantity();

		if ($allowedQuantity && $allowedQuantity <= count($modifiers)) {
			$this->setError(JText::_('COM_PAYPLANS_APP_DISCOUNT_ERROR_CODE_ALLOWED_QUANTITY_CONSUMED'));
			return false;
		}

		return true;
	}

	/**
	 * Determines if aupdiscount has been applied before on the invoice
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUsed(PPInvoice $invoice)
	{
		// Do not allow user to apply the same points again and again on the same invoice
		$modifiers = $invoice->getModifiers(array('type' => $this->app->getType()));

		if (!empty($modifiers)) {
			$this->setError(JText::_('COM_PAYPLANS_APP_DISCOUNT_ERROR_ALREADY_USED'));
			return true;
		}

		return false;
	}

	/**
	 * Determines if we should round up the points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function shouldRoundPoints()
	{
		return (bool) $this->params->get('round', true);
	}
}