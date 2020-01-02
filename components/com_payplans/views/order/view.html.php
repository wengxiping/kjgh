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
PP::import('admin:/includes/renewal/renewal');

class PayPlansViewOrder extends PayPlansSiteView
{
	/**
	 * Allows user to renew subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processRenew()
	{ 
		PP::requireLogin();

		$subscriptionKey = $this->input->get('subscription_key');

		if (!$subscriptionKey) {
			// if subscription key not exist
			$message = JText::_('Invalid Subscription for Renewal');

			$this->info->set($message, 'error');
			return $this->redirectToView('dashboard');
		}

		$subscriptionId = PP::getIdFromKey($subscriptionKey);

		$subscription = PP::subscription($subscriptionId);

		// Get previous plan price and type
		$previousPrice = $subscription->getPrice();
		$previousPlanType = $subscription->getExpirationType();

		$newPlan = $subscription->getPlans();

		$newPrice = $newPlan->getPrice();
		$newPlanType = $newPlan->getExpirationType();

		// Check plan is valid 
		$isValidPlan = PPHelperPlan::isValidPlan($newPlan->getId());
		if (!$isValidPlan) {
			$message = JText::_('COM_PAYPLANS_RENEWAL_PLAN_NOT_AVAILABLE_TO_RENEW');

			$this->info->set($message, 'error');
			return $this->redirectToView('dashboard');
		}

		$newInvoice = PPRenewal::renew($subscription, $newPlan);
		$newSubscription = $subscription;
		$order = $subscription->getOrder();

		$expirationType = $newInvoice->getExpirationType();
		if ( in_array($expirationType, array('PP_RECURRING_TRIAL_1', 'PP_RECURRING_TRIAL_2')) ) {
			$newInvoice->setParam('expirationtype', 'recurring');
			$newInvoice->save();
		}
		
		// Check whether plan price is changed or not?
		if ( ($previousPrice != $newPrice) || ($previousPlanType != $newPlanType) ) {
			PP::info()->set('COM_PAYPLANS_RENEWAL_CHANGE_PLAN_PRICE');
		}

		// Trigger an event after invoice creation
		$args = array($subscription, $newSubscription, $order, $newInvoice);
		$results = PPEvent::trigger('onPayplansSubscriptionAfterRenewalInvoiceCreation', $args, '', $newSubscription);

		// redirect to checkout page
		$url = PPR::_('index.php?option=com_payplans&view=checkout&invoice_key='.$newInvoice->getKey() . '&tmpl=component', false);
		return PP::redirect($url);
	}

}