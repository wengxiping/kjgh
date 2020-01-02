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

PP::import('admin:/includes/helpers/discount');

class PPEventDiscount extends PayPlans
{
	/**
	 * Triggered when applying for discounts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansDiscountRequest(PPInvoice $invoice, $code)
	{
		if (!$this->config->get('enableDiscount')) {
			return false;
		}

		// Ensure that the user is validated
		if (!$invoice->validateBuyer()) {
			return false;
		}

		$args = array($invoice, $code);
		$trigger = true;

		// if all false is true, means there is no processing on other discount app.
		$allFalse = true;

		$errors = array();

		// Check if invoice is paid/refunded , then don't apply discount
		if ($invoice->isPaid() || $invoice->isRefunded()) {
			$trigger = false;
			$allFalse = false; // set this to false so that it doesn't go into 'direct-discount' checking block
			$errors[] = JText::_('COM_PAYPLANS_PRODISCOUNT_CANT_APPLY_DISCOUNT_ON_THIS_INVOICE');
		}

		// Trigger the discount
		if ($trigger) {
			$results = PP::event()->trigger('onPayplansApplyDiscount', $args, '', $invoice);

			foreach ($results as $result) {

				if (is_bool($result) && $result == false) {
					$errors[] = $result;
				}

				// if the result is no false, mean there is processing on other discount apps such as referral app / gift app
				if ($result !== false){
					$allFalse = false;
				}
			}
		}

		// if there is no other discount apps that has the processing, then we
		// process futher here to check if the discount is a direct discount or not.
		// if ($allFalse && $this->app->isAdmin()) {

		// 	// reset errror container
		// 	// $error = array(JText::_('COM_PAYPLANS_PRODISCOUNT_ERROR_INVALID_CODE'));
		// 	// $error = JText::_('COM_PAYPLANS_PRODISCOUNT_ERROR_INVALID_CODE');

		// 	// check for direct discount by admin
		// 	// if ($this->app->isAdmin()) {
		// 		$error = self::checkForDirectDiscount($invoice, $code);
		// 	// }

		// 	if ($error) {
		// 		$errors[] = $error;
		// 	}
		// }

		return $errors;
	}

	public static function getFormattedAmount($currency, $amount)
	{
		$formattedAmount = PP::themes()->html('html.amount', $amount, $currency);
		return $formattedAmount;
	}

	/**
	 * Check if the given discount is a direct discount
	 */
	protected static function checkForDirectDiscount(PPInvoice $invoice, $discount)
	{
		$params = new stdClass();
		$percentage = false;
		$error = "";

		//Percentage discount
		if (strrpos($discount, '%')) {
			$percentage = true;
			$discount   = substr($discount, 0, strrpos($discount, '%'));
			$params->serial = PP_MODIFIER_PERCENT_DISCOUNT;
		}

		//if discount is not numeric and not greater than 0 then do nothing
		if (!is_numeric( $discount ) && $discount <= 0) {
			return $error = JText::_('COM_PAYPLANS_PRODISCOUNT_ERROR_INVALID_CODE');
		}

		$discountAmount = $discount;

		//if discount is in terms of percentage then calculate the
		//percentage amount.
		if ($percentage) {
			$discountAmount = ($discount * $invoice->getTotal()) / 100;
		}

		// if amount of applied percentage is greater than total or
		// fixed discount is greater then subtotal then do nothing
		if ($discountAmount > $invoice->getTotal()) {
			return JText::_('COM_PAYPLANS_PRODISCOUNT_ERROR_EXCEED_TOTAL_AMOUNT');
		}

		//add modifier if it is a direct discount
		$params->message = JText::_('COM_PAYPLANS_PRODISCOUNT_DIRECT_DISCOUNT_BY_ADMIN');
		$params->amount = -1 * $discount;
		$params->percentage = $percentage;
		$invoice->addModifier($params);
		//also save invoice when modifier applies
		$invoice->save();

		return $error;
	}



	/**
	 * For extend subscription on applying discount
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionBeforeSave($prevSubs, $newSubs)
	{
		if (!isset($prevSubs)) {
			return true;
		}

		if ($prevSubs->getStatus() != PP_SUBSCRIPTION_ACTIVE && $newSubs->getStatus() == PP_SUBSCRIPTION_ACTIVE) {

			$orderObj = $newSubs->getOrder(PP_INSTANCE_REQUIRE);
			$invoice = $orderObj->getLastMasterInvoice(PP_INSTANCE_REQUIRE);

			//if invoice is not there then do nothing
			if (!($invoice instanceOf PPInvoice)) {
				return true;
			}

			$modifiers = $invoice->getModifiers(array('type'=> PP_PRODISCOUNT_EXTEND_TIME_DISCOUNT));

			foreach ($modifiers as $modifier) {
				$code = $modifier->getReference();
				$type = $modifier->getType();

				$prodiscountModel = PP::model('Discount');

				$filter = array('coupon_type' => $type,'coupon_code' => $code);

				$prodiscountRecords = $prodiscountModel->loadRecords($filter);

				foreach ($prodiscountRecords as $prodiscountRecord) {

					$discount = PP::discount($prodiscountRecord->prodiscount_id);

					$exptime = $discount->getParam('extend_time_discount', 000000000000);
					$newSubs->setExpiration($newSubs->getExpirationDate()->addExpiration($exptime));

				}
			}
		}
	}


	/**
	 * For autodiscount on upgrades
	 * catch the event of upgrade and work on it
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onPayplansUpgradeBeforeDisplay($newPlanId, PPSubscription $oldSub, PPInvoice $newInvoice)
	{
		$this->applyAutoDiscount($newInvoice, PP_PRODISCOUNT_AUTOONUPGRADE, PP_PRODISCOUNT_UPGRADE_DISCOUNT);
	}

	/**
	 * For autoDiscount on renewals
	 * catch the event of renewal app and work on it
	 * @since	4.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterRenewalInvoiceCreation(PPSubscription $oldSubscription, PPSubscription $newSubscription, PPOrder $order, PPInvoice $newInvoice)
	{
		$this->applyAutoDiscount($newInvoice, PP_PRODISCOUNT_AUTOONRENEWAL, PP_PRODISCOUNT_RENEWAL_DISCOUNT);
	}

	/**
	 * For autoDiscount on invoice Creation and Url discount
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onPayplansInvoiceAfterSave($prev, $newInvoice)
	{
		$status = array(PP_INVOICE_CONFIRMED, PP_NONE);

		if (floatval(0) != floatval($newInvoice->getTotal() && !isset($prev) && in_array($newInvoice->getStatus(),$status))) {
			 $this->applyAutoDiscount($newInvoice, PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION, PP_PRODISCOUNT_INVOICE_CREATION_DISCOUNT);
		}

		return true;
	}

	/**
	 *
	 * Apply automatic discounts...
	 * @param PayplansInvoice $newInvoice: Invoice on which discount is to be applied
	 * @param string $discountType 		 : Type of discount to be applied
	 * @param string $discountCodePrefix : prefix to be added before prodiscount_id
	 * 									 ( auto discounts don't ask for discount code but its required to
	 *                                     store some reference in modifiers for uniquely identify the applied discount)
	 * @since	4.0
	 * @access	public
	 */
	public function applyAutoDiscount($newInvoice, $discountType, $discountCodePrefix)
	{
		//get the applicable discounts and then process each one
		$applicableDiscounts =  PPHelperDiscount::getApplicableDiscounts($newInvoice, array('coupon_type' => $discountType,'published' => 1));

		if ($applicableDiscounts) {
			foreach ($applicableDiscounts as $discount) {

				$discountId = $discount->getId();

				//set coupon_code so that unique reference can be identified from modifier for this instance
				$discountCode = $discountCodePrefix . '_' . $discountId;

				$discount->setCouponCode($discountCode);

				if ($discount->isInvoiceApplicable($newInvoice)) {
					$this->doApplyDiscount($newInvoice, $discount);
				}
			}
		}
	}



	/**
	 * Iteratively apply discount
	 * Tips : Avoid overriding this function
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function doApplyDiscount(PPInvoice $invoice, PPDiscount $discount)
	{
		$modifier = $discount->toModifier($invoice);

		// Check for maximum allowed discount
		$allowed = $discount->checkForUpperLimit($modifier, $invoice);

		if (!$allowed) {
			return false;
		}

		//add modifier
		$invoice->addModifier($modifier);

		// For non recurring discounts
		if (!$discount->isForRecurring() && $invoice->getRecurringType() == PP_PRICE_RECURRING) {
			$invoiceParams = $invoice->getParams();

			$recurrenceCount = (int) $invoiceParams->get('recurrence_count');

			$invoiceParams->set('expirationtype', 'recurring_trial_1');
			$invoiceParams->set('recurrence_count', $recurrenceCount > 0 ? $recurrenceCount - 1 : 0);
			$invoiceParams->set('trial_price_1', $invoiceParams->get('price'));
			$invoiceParams->set('trial_time_1', $invoiceParams->get('expiration'));

			$invoice->params = $invoiceParams;
		}

		$invoice->refresh();
		$invoice->save();

		// Create a new log
		$data = array();
		$data['Coupon Code'] = $discount->getCouponCode();
		$data['Invoice ID'] = $invoice->getId();
		$data['Prodiscount ID'] = $discount->getId();
		$data['User ID'] = $invoice->getBuyer()->getId();

		if ($discount->isForExtension()) {
			$data['Extended Time'] = $discount->getParams('extend_time_discount');
		}

		if (!$discount->isForExtension()) {
			$data['Discount Amount'] = $modifier->amount;
			$data['Percentage'] = $discount->isPercentage() ? JText::_('Yes') : JText::_('No');
		}

		PP::log()->log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_PRODISCOUNT_LOG_DISCOUNT_APPLIED'), $discount, $data);

		return true;
	}


	/**
	 * Remove plan mapping from discount on plan deletion
	 *
	 * @since	4.0.0
	 * @access	public
	*/
	public function onPayplansPlanAfterDelete($itemId)
	{

		$model = PP::model('Discount');
		$items = $model->getDiscountPlans();

		if ($items) {

			foreach ($result as $dis) {

				$plans = json_decode($dis->plans);

				// Do nothing if plan is not mapped with discount
				if (!in_array($itemId, $plans)){
					continue;
				}

				$updatedPlans = array();
				$found = false;

				foreach ($plans as $pid) {
					if ($data == $itemId) {
						$found = true;
					} else {
						$updatedPlans[] = $pid;
					}
				}

				if ($found) {
					$updatedPlans = json_encode($updatedPlans);
					$model->updateDiscountPlans($dis->prodiscount_id, $updatedPlans);
				}
			}
		}
	}


}
