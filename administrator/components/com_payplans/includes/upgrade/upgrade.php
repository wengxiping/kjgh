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

class PPUpgrade extends PPAbstract
{
	const APPLY_TRIAL_ALWAYS =  1;
	const APPLY_TRIAL_NEVER =  0;


	/**
	 * Retrieves a list of plans available for the current subscription plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function findAvailableUpgrades(PPSubscription $subscription)
	{
		$plans = array();

		// Subscription must be active in order to be able to upgrade
		if (!$subscription->isActive()) {
			return $plans;
		}

		$plan = $subscription->getPlan();

		// only published and visible plans
		$plans = PPHelperPlan::getPlans(array('published' => 1));

		$upgradePlans = array();

		$upgrades = self::loadUpgrades();

		if ($upgrades) {
			foreach ($upgrades as $upgrade) {

				// we need to check if this upgrade can be used by this subscription or not
				if (!$upgrade->getApplyAll()) {
					$appPlans = $upgrade->getPlans();

					// this upgrade is not meant for this subscription
					if (! in_array($plan->getId(), $appPlans)) {
						continue;
					}
				}

				$param = $upgrade->app_params;
				$tmpPlans = $param->get('upgrade_to');

				if ($tmpPlans) {
					foreach ($tmpPlans as $pid) {
						// make sure the plan is valid
						if (isset($plans[$pid])) {
							// at the same time, we distinct the plans
							$upgradePlans[$pid] = $plans[$pid];
						}
					}
				}
			}
		}

		return $upgradePlans;
	}


	/**
	 * Get all upgrades
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function loadUpgrades()
	{
		static $_cache = null;

		if (is_null($_cache)) {

			$model = PP::model('App');
			$options = array('type' => 'upgrade', 'published' => 1);
			$results = $model->loadRecords($options);

			if ($results) {
				$_cache = array();
				foreach ($results as $item) {
					$upgrade = PP::app($item);
					$_cache[] = $upgrade;
				}
			}
		}

		return $_cache;
	}


	/**
	 * Calculates unutilized values from the old subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function calculateUnutilizedValue(PPSubscription $subscription)
	{
		$default = array('paid' => 0, 'unutilized'=> 0);

		// If subscription is no more active, value as 0
		if (!$subscription->isActive()) {
			return $default;
		}

		$subStart = $subscription->getSubscriptionDate();
		$expDate = $subscription->getExpirationDate();

		// Find value utilized by old subscription
		$start = ($subStart !== false) ? intval($subscription->getSubscriptionDate()->toUnix()) : 0;
		$expires = ($expDate !== false) ? intval($subscription->getExpirationDate()->toUnix()) : 0;
		$now = intval(PP::date()->toUnix());

		$totalTime = $expires - $start;
		$order = $subscription->getOrder();


		$totalValue = self::calculatePaymentsDuringPreviousUpgradations($order);

		$usedTax = 0;
		$usedValue = 0;

		// Pro rate values if it is a paid plan previously
		if ($totalValue['planPrice'] != 0 && $expires != 0) {
			$used = $now - $start;

			// if total time is not in hours, then calculate as per days
			$oneday = 24 * 60 * 60;

			if ($totalTime > (3 * $oneday)) {
				$used = intval($used / $oneday);
				$totalTime = intval($totalTime/$oneday);
			}

			$usedValue = $totalValue['planPrice'] * $used / $totalTime;
			$usedTax = $totalValue['taxIncluded'] * $used / $totalTime;
		}

		// the value which is not utilized, and will be added into discount
		$unutilizedValue = $totalValue['planPrice'] - $usedValue;
		$unutilizedTax = $totalValue['taxIncluded'] - $usedTax;

		$result = array(
			'paid' => $totalValue['planPrice'],
			'unutilized' => $unutilizedValue,
			'unutilizedTax' => $unutilizedTax
		);

		return $result;
	}

	/**
	 * Creates a new order from the old subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function createUpgradeOrder(PPSubscription $subscription, PPPlan $newPlan)
	{
		// Get the previous order
		$oldOrder = $subscription->getOrder();
		$newOrder = $newPlan->subscribe($subscription->getBuyer()->id);

		// @TODO: Currently we do not support trial prices in new plan, We will do it in future

		// Update the params so we can refer to the newly upgraded order
		$newOrder->setParam('upgrading_from', $subscription->getId())->save();
		$oldOrder->setParam('upgraded_to', $newOrder->getSubscription()->getId())->save();

		return $newOrder;
	}

	/**
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected static function calculatePaymentsDuringPreviousUpgradations($order)
	{
		// get payments
		$invoices = $order->getInvoices(PP_INVOICE_PAID);

		if (count($invoices) == 0) {
			// none of payment were completed
			$totalValue  = 0;
		} else {
			// pick last paid invoice
			$invoice = array_pop($invoices);
			$modifiers = $invoice->getModifiers();
			$modifiers = PPHelperModifier::rearrange($modifiers);

			$discountableAddon = array(0);
			$addonTaxableTotal = array(0);
			$taxValue = array(0);
			$adjustment = 0;

			if ($modifiers) {
				// if there are something to process, lets do it.
				foreach ($modifiers as $modifier) {
					if (in_array($modifier->getSerial(), array(PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE,PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE))) {
						$addonTaxableTotal[] = str_replace('-', '', PPFormats::displayAmount($modifier->_modificationOf));
					}

					if (in_array($modifier->getSerial(), array(PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE))) {
						$discountableAddon[] = str_replace('-', '', PPFormats::displayAmount($modifier->_modificationOf));
					}

					if (in_array($modifier->getSerial(), array(PP_MODIFIER_FIXED_TAX,PP_MODIFIER_PERCENT_TAX))) {
						$taxValue[] = $modifier->_modificationOf;
					}

					if (in_array($modifier->getSerial(),array(PP_MODIFIER_FIXED_NON_TAXABLE))) {
						$adjustment = PPFormats::displayAmount($modifier->_modificationOf);
					}
				}
			}

			$plandiscountableamount = $invoice->getSubtotal() + $adjustment;

			if ($plandiscountableamount > 0 && array_sum($discountableAddon) > 0) {
				$discountApplicableonAddon = (array_sum($discountableAddon) * $invoice->getDiscount()) / ($plandiscountableamount + array_sum($discountableAddon));
			} else {
				$discountApplicableonAddon = 0;
			}

			$finalAddonTotal= array_sum($addonTaxableTotal) - $discountApplicableonAddon;

			if ($plandiscountableamount > 0 && array_sum($discountableAddon) > 0) {
				$discountAmountofplan = ($plandiscountableamount * $invoice->getDiscount()) / ($plandiscountableamount + array_sum($discountableAddon));
			} else {
				$discountAmountofplan = 0;
			}

			$totalAmountofPlan= $plandiscountableamount - $discountAmountofplan;

			if ($totalAmountofPlan > 0 && $finalAddonTotal > 0) {
				$taxAmount = ($totalAmountofPlan * array_sum($taxValue)) / ($totalAmountofPlan + $finalAddonTotal);
			} else {
				$taxAmount = 0;
			}
		}

		return array('planPrice' => $totalAmountofPlan, 'taxIncluded' => $taxAmount);
	}

	/**
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function willTrialApply($oldPlan, $newPlan)
	{
		$upgradeApps = self::loadUpgrades();

		if ($upgradeApps) {
			foreach ($upgradeApps as $app) {

				$upgradeTo = $app->app_params->get('upgrade_to',array());
				$upgradeTo = is_array($upgradeTo) ? $upgradeTo : array($upgradeTo);

				$willTrialApply = $app->app_params->get('willTrialApply','always');

				if ($app->getApplyAll() && in_array($newPlan->getId(), $upgradeTo)) {
					if ($willTrialApply == 'always') {
						return self::APPLY_TRIAL_ALWAYS;
					}
				} else if (in_array($oldPlan->getId(), $app->getPlans()) && in_array($newPlan->getId(),$upgradeTo)) {
					if ($willTrialApply == 'always') {
						return self::APPLY_TRIAL_ALWAYS;
					}
				}
			}
		}

		return self::APPLY_TRIAL_NEVER;
	}

	/**
	 * Sends out an e-mail when a subscription is being updated
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sendCancelUpgradeEmail($oldOrder, $oldInvoice, $oldSub, $oldPayment)
	{
		$params = array(
			'order_key' => $oldOrder->getKey(),
			'invoice_key' => $oldInvoice->getKey(),
			'subscription_key' => $oldSub->getKey(),
			'payment_key' => $oldPayment->getKey()
		);

		$recipient = $oldOrder->getBuyer()->getEmail();

		$mailer = PP::mailer();
		$mailer->send($recipient, 'COM_PAYPLANS_UPGRADES_ORDER_CANCEL_SUBJECT', 'plugins:/payplans/upgrade/emails/cancel', $params);
	}

	//update new invoice as per trial if applicable
	public static function updateInvoiceParams($newInvoice, $willTrialApply)
	{
		$isRecurring = $newInvoice->getRecurringType();

		if ($willTrialApply == self::APPLY_TRIAL_NEVER &&
			($isRecurring == PP_PRICE_RECURRING_TRIAL_1 || $isRecurring == PP_PRICE_RECURRING_TRIAL_2)) {

			$oldParams = $newInvoice->getParams()->toArray();

			$newParams['expirationtype'] = 'recurring';
			$newParams['trial_price_1'] = '0.00';
			$newParams['trial_time_1'] = '000000000000';
			$newParams['trial_price_2'] = '0.00';
			$newParams['trial_time_2'] = '000000000000';

			$params = array_merge($oldParams, $newParams);

			$invoiceParams = new JRegistry($params);
			$newInvoice->params = $invoiceParams->toString();
			$newInvoice->refresh()->save();
		}

		// change new invoice to trial so that to apply discounted price only once
		if ($isRecurring == PP_PRICE_RECURRING) {

			$oldParams = $newInvoice->getParams()->toArray();

			$recurrenceCount = 0;
			if ($oldParams['recurrence_count']) {
				$recurrenceCount = $oldParams['recurrence_count'] -1;
			}

			$newParams['expirationtype'] = 'recurring_trial_1';
			$newParams['recurrence_count'] = $recurrenceCount;
			$newParams['trial_price_1'] = $oldParams['price'];
			$newParams['trial_time_1'] = $oldParams['expiration'];

			$params = array_merge($oldParams, $newParams);

			$invoiceParams = new JRegistry($params);
			$newInvoice->params = $invoiceParams->toString();
		

			//subtotal does not modified by params value automatically
			$newInvoice->subtotal = $oldParams['price'];
			$newInvoice->refresh()->save();
		}

		return $newInvoice;
	}

	/**
	 * Upgrades a subscription to a new plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function upgradeSubscription(PPSubscription $subscription, PPPlan $newPlan, $upgradeType)
	{
		// Determines if the subscription can be upgraded to the new plan
		if (! $subscription->canUpgrade($newPlan->getId())) {
			return false;
		}

		// Get the previous plan
		$old = new stdClass();
		$old->plan = $subscription->getPlan();
		$old->order = $subscription->getOrder();
		$old->invoices = $old->order->getInvoices(PP_INVOICE_PAID);

		$oldInvoice = null;
		if ($old->invoices) {
			$oldInvoice = array_pop($old->invoices);
		} else {
			$oldInvoice = $old->order->createInvoice();
		}

		$result = self::calculateUnutilizedValue($subscription);
		$paidAmount = $result['paid'];
		$unutilized = $result['unutilized'];
		$unutilizedTax = $result['unutilizedTax'];

		// Create a new upgrade order for the subscription
		$newOrder = self::createUpgradeOrder($subscription, $newPlan);
		$newInvoice = $newOrder->createInvoice();

		// Check whether trial is applicable or not and then update invoice params accordingly
		$applyTrial = self::willTrialApply($old->plan, $newPlan);
		$newInvoice = self::updateInvoiceParams($newInvoice, $applyTrial);

		$params = new stdClass();
		$params->type = 'upgrade';
		$params->reference = $oldInvoice->getKey();
		$params->percentage = false;
		$params->amount = -$unutilized;
		// $params->amount = $unutilized;
		$params->serial = PP_MODIFIER_FIXED_NON_TAXABLE;
		$params->message = JText::_('COM_PAYPLANS_UPGRADE_MESSAGE');

		$modifier = $newInvoice->addModifier($params);

		// Save the new invoice with the modifier
		$newInvoice->save();

		// Update the tax modifiers
		$params = new stdClass();
		$params->type = 'upgradeTax';
		$params->reference = $oldInvoice->getKey();
		$params->percentage = false;
		$params->amount = -$unutilizedTax;
		$params->serial = PP_MODIFIER_FIXED_NON_TAXABLE_TAX_ADJUSTABLE;
		$params->message = JText::_('COM_PAYPLANS_UPGRADE_TAX_MESSAGE');

		$modifier = $newInvoice->addModifier($params);
		$newInvoice->save();

		// Post process after the invoice is saved
		if ($upgradeType == 'free') {
			self::upgradeFree($newInvoice);
		}

		if ($upgradeType == 'offline') {
			self::upgradeOffline($newInvoice);
		}

		if ($upgradeType == 'user') {
			self::upgradePartial($newInvoice);
		}

		return $newInvoice;
	}

	/**
	 * Process free upgrades
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function upgradeFree($invoice)
	{
		$newOrder = $invoice->getReferenceObject();

		if (is_a($newOrder, 'PPOrder')) {
			$reference = $newOrder->getParam('upgrading_from');
		}

		//set the modifier reference to the old subscription key
		$reference = isset($reference)? $reference : 'order_upgrade';

		$modifierParams = new stdClass();
		$modifierParams->type ='free_upgrade';
		$modifierParams->percentage	= true;
		$modifierParams->serial = PP_MODIFIER_PERCENT_DISCOUNTABLE;
		$modifierParams->amount = -100;
		$modifierParams->message = 'COM_PAYPLANS_FREE_UPGRADE_MESSAGE';
		$modifierParams->reference = $reference;

		$invoice->addModifier($modifierParams);
		$invoice->save();

		// Transaction added for free upgrade
		$transaction = PP::transaction();
		$transaction->user_id = $invoice->getBuyer()->id;
		$transaction->invoice_id = $invoice->getId();
		$transaction->amount = 0;
		$transaction->payment_id = 0;
		$transaction->message = 'COM_PAYPLANS_TRANSACTION_CREATED_FOR_FREE_UPGRADE';

		$transaction->save();

		return true;
	}

	/**
	 * Process offline upgrades. We need to create a new transaction to offset the amount.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function upgradeOffline($invoice)
	{
		$params = new JRegistry();
		$params->set('transaction_amount', $invoice->getTotal());
		$params->set('transaction_message', JText::_('COM_PAYPLANS_TRANSACTION_CREATED_FOR_OFFLINE_UPGRADE'));

		$transaction = $invoice->addTransaction($params);
		return true;
	}

	/**
	 * Process partial upgrades
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function upgradePartial($invoice)
	{
		// Send user an e-mail confirmation so that they can pay for the new invoice

		$subject = JText::_('COM_PAYPLANS_INVOICE_EMAIL_LINK_SUBJECT');
		$namespace = 'emails/upgrade/order.payment';

		$text = JText::_('COM_PAYPLANS_INVOICE_EMAIL_LINK_BODY');
		$content = PP::rewriteContent($text, $invoice);

		// Send notification to the buyer
		$user = $invoice->getBuyer();
		$data = array(
			'content' => $content
		);

		$mailer = PP::mailer();
		$state = $mailer->send($user->getEmail(), $subject, $namespace, $data);

		if (!$state) {
			PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_EMAIL_SENDING_FAILED'), $invoice, $content);
			return false;
		}

		PPLog::log(PPLogger::LEVEL_INFO, JText::_('COM_PAYPLANS_EMAIL_SEND_SUCCESSFULLY'), $invoice, $content);
		return true;
	}
}
