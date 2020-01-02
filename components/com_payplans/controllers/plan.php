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

class PayPlansControllerPlan extends PayPlansController
{
	/**
	 * Processes after a user clicks on a subscribe link or button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function subscribe()
	{
		$planId = $this->input->get('plan_id', 0, 'int');
		// Ensure that the plan is a valid plan
		$plan = PP::plan($planId);

		if (!$planId || !$plan->getId()) {
			throw new Exception('COM_PAYPLANS_PLAN_PLEASE_SELECT_A_VALID_PLAN');
		}

		// Determine if user can really subscribe to the plan
		if (!$plan->canSubscribe()) {
			$this->info->set($plan->getError()->text, 'error');
			return $this->redirectToView('plan');
		}

		// Trigger event after a plan has been selected
		$args = array(&$planId, $this);

		PP::event()->trigger('onPayplansPlanAfterSelection', $args, '', $plan);

		// If the user is not logged in, we'll link to a dummy user
		$userId = $this->my->id;

		// Reset the user's new id from session at this point when they are placing a new order
		$session = PP::session();
		$session->set('REGISTRATION_NEW_USER_ID', 0);

		if (!$userId && $this->config->get('registrationType') == 'auto') {
			$userId = PP::getDummyUserId();

			// Reset to the dummy id
			$session->set('REGISTRATION_NEW_USER_ID', $userId);
		}

		// Check if there is any modifier to this plan
		$modifier = $this->input->get('modifier', false, 'cmd');

		if ($modifier && $modifier != 'default') {
			$planModifier = PP::planModifier($modifier);
			$modifierData = $planModifier->parse($modifier);
				
			if (!$planModifier->hasPrice($modifierData->price)) {
				die('Invalid price provided');
			}

			$plan->setModifier($modifier);
		}

		// Check if there is any advanced pricing assigned to this plan
		$advPricing = $this->input->get('advpricing', false, 'cmd');

		if ($advPricing) {
			$plan->setAdvPricing($advPricing);
		}

		// Subscribe to the plan
		$order = $plan->subscribe($userId);
		$invoice = $order->createInvoice();

		$invoiceKey = $invoice->getKey();

		// Construct url variable
		$var = 'invoice_key=' . $invoiceKey . '&tmpl=component';

		// Directly go to thanks page for free invoice
		if ($this->config->get('skip_free_invoices') && $invoice->isFree()) {

			if ($this->my->id) {
				$redirect = PPR::_('index.php?option=com_payplans&task=checkout.confirm&invoice_key=' . $invoiceKey . '&app_id=0', false);
				return $this->app->redirect($redirect);
			} else {
				$var .= '&skipInvoice=1';
			}
		}

		// Redirect to confirm action
		return $this->redirectToView('checkout', '', $var);
	}
}