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

PP::import('site:/views/views');

class PayPlansViewDiscounts extends PayPlansSiteView
{
	/**
	 * Checks if a given coupon code is valid
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function check()
	{
		// Ensure that discounts is really enabled
		if (!$this->config->get('enableDiscount')) {
			throw new Exception('Discounts has been disabled');
		}

		// If social media is used, we need to get the code
		$socialMedia = $this->input->get('socialMedia', '', 'default');
		$code = '';
		if ($socialMedia) {
			$socialDiscount = PP::socialDiscount();
			$code = $socialDiscount->getCode($socialMedia);
		}

		// Get the plan the user is trying to purchase
		$invoiceKey = $this->input->get('invoice_key', '', 'default');
		$invoiceId = (int) PP::encryptor()->decrypt($invoiceKey);

		$invoice = PP::invoice($invoiceId);
		$code = $this->input->get('code', $code, 'default');
		$code = trim($code);

		if (!$code) {
			return $this->reject(JText::_('COM_PP_DISCOUNT_COUPON_CANNOT_EMPTY'));
		}

		// Ensure that the discount code is applicable for the plan
		$table = PP::table('Discount');
		$exists = $table->load(array('coupon_code' => $code, 'published' => 1));

		if (!$invoice->getId() || !$exists || !$table->prodiscount_id) {
			return $this->reject(JText::_('COM_PP_DISCOUNT_COUPON_INVALID'));
		}

		// Check if the coupon code can really be applied on the selected plan
		$discount = PP::discount($table);

		$allowed = $discount->isInvoiceApplicable($invoice);

		if (!$allowed) {
			$msg = JText::_('COM_PP_DISCOUNT_COUPON_CANNOT_USE');

			$msgObj = $discount->getErrors();
			if ($msgObj) {
				$msg = $msgObj->text;
			}

			return $this->reject($msg);
		}

		// Create temporary standard object to apply as modifier
		$modifier = $discount->toModifier($invoice);

		// Check for maximum allowed discount
		$allowed = $discount->checkForUpperLimit($modifier, $invoice);

		if (!$allowed) {
			$msg = JText::_('COM_PAYPLANS_PRODISCOUNT_MAX_ALLOWED_DISCOUNT_CROSSED');
			return $this->reject($msg);
		}

		$args = array(&$invoice, $code);
		$results = PP::event()->trigger('onPayplansDiscountRequest', $args, '', $invoice);

		// if something return, means there are errors
		if ($results && isset($results[0]) && $results[0]) {

			$msg = JText::_('COM_PP_DISCOUNT_COUPON_CANNOT_USE');

			$msgObj = $invoice->getErrors();
			if ($msgObj) {
				$msgObj = is_array($msgObj) ? $msgObj[0] : $msgObj;
				$msg = $msgObj->text;
			}

			return $this->reject($msg);
		}

		// Apply the modifier on the invoice
		// Exchange the standard object with a proper PPModifier object
		$modifier = $invoice->addModifier($modifier);

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

		// Trigger event to allow apps to manipulate data
		$args = array(&$invoice, $discount->getCouponCode());

		PP::event()->trigger('onPayplansDiscountAfterApply', $args, '', $invoice);

		// If discount is extending subscription, we need to let the user know
		if ($discount->isForExtension()) {
			$msg = "<span class='text-success'>".JText::_('COM_PAYPLANS_PRODISCOUNT_EXETEND_SUBSCRIPTION_MESSAGE')."</span>";
		}

		$modifiers = $invoice->getModifiers();
		$total = PPHelperModifier::getTotal($invoice->getSubtotal(), $modifiers);

		// Get all modifiers
		// Generate the output for the modifier row
		$theme = PP::themes();
		$theme->set('modifiers', $modifiers);
		$theme->set('invoice', $invoice);
		$html = $theme->output('site/checkout/default/modifier');

		// Get new total for the invoice
		$total = $theme->html('html.amount', $invoice->getTotal(), $invoice->getCurrency());

		return $this->resolve($html, $total, $invoice->getTotal(), $invoice->isRecurring());
	}

}
