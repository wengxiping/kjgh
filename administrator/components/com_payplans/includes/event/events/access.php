<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPEventAccess extends PayPlans
{
	public function onPayplansAccessCheck(PPUser $user)
	{
		if (!$user->getId()) {
			return true;
		}

		$option = $this->input->get('option', '', 'default');
		$task = $this->input->get('task', '', 'cmd');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$planId = $this->input->get('plan_id', 0, 'int');

		if ($planId && $option == 'com_payplans' && $task == 'plan.subscribe') {
			return $this->allowPurchasing($planId);
		}

		// Determine if the system require redirection to another page.
		$this->processRedirection();

		// Block if user don't have active subscription
		if (!$this->config->get('accessLoginBlock')) {
			return true;
		}

		// hack for jomsocail facebook connect
		// do not restric ajax
		if ($option == 'community' && $task == 'azrul_ajax') {
			return true;
		}

		// Fix issue with quicklogout extension
		if ($option == 'com_quicklogout' && $view == 'quicklogout') {
			return true;
		}

		if ($option == 'com_payplans') {
			return true;
		}

		// Do not block login and logout attempt, we will capture on next page
		if (($option == 'com_users') || ($task == 'logout') || ($view == 'logout')) {
			return true;
		}

		// EasySocial oauth registration page
		if ($option == 'com_easysocial' && $view == 'registration') {
			return true;
		}

		$subs = $user->getSubscriptions(PP_SUBSCRIPTION_ACTIVE);

		// Block user if no active subscription
		if (count($subs) <= 0) {
			$redirect = JRoute::_('index.php?option=com_payplans&view=denied', false);
			return PP::redirect($redirect);
		}

		return true;
	}

	/**
	 * Internal even for manipulating view contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansViewBeforeRender($view, $task)
	{
		// do nothing when application is administrator
		if ($this->app->isAdmin()) {
			return;
		}

		$user = PP::user();
		$userPlans = $user->getPlans();

		$displaySubscribedPlans = PP::config()->get('displayExistingSubscribedPlans');

		if ($displaySubscribedPlans == 1 || !$user->id) {
			return;
		}

		$userPlanIds = array();
		if (count($userPlans)) {
			foreach ($userPlans as $plan) {
				$userPlanIds[] = $plan->getId();
			}
		}

		// don't display the plans on subscribe page that user have already subscribed
		if ($view instanceof PayplansViewPlan) {

			if (count($userPlans) == 0) {
				return ;
			}

			$plans = $view->get('plans');

			//unset all plans subscribed by the user
			foreach ($userPlanIds as $planId) {
				foreach ($plans as $key => $value) {
					if (in_array($value->getId(), $userPlanIds)) {
						unset($plans[$key]);
					}
				}
			}

			// assign plans to lib
			$view->set('plans',$plans);
			return true;
		}

		// don't redirect user to order confirm page that user have already subscribed
		if ($view instanceof PayPlansViewCheckout) {

			$plan = $view->get('plan');

			$id = $view->getKey('invoice_key');
			$invoice = PP::invoice($id);

			$order = $invoice->getOrder();

			// when display subscribed plan set to no and
			// user renew their plan by renewal link then
			//check the status of order if complete then order is for renewal
			if ($order->getStatus() == PP_ORDER_COMPLETE) {
				return ;
			}

			if (in_array($plan->getId(),$userPlanIds)) {
				PP::info()->set('COM_PAYPLANS_DASHBOARD_NOT_ALLOWED_TO_SUBSCRIBED_THIS_PLAN_AS_ALREADY_SUBSCRIBED', 'error');
				$redirect = JRoute::_('index.php?option=com_payplans&view=dashboard', false);

				return PP::redirect($redirect);
			}

			return true;
		}

	}

	/**
	 * Check if the user can really purchase the selected plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function allowPurchasing($planId)
	{
		// do not work in admin
		if ($this->app->isAdmin()) {
			return true;
		}

		$user = PP::user();
		$plans = $user->getPlans();

		if (PP::config()->get('displayExistingSubscribedPlans') || !$user->id) {
			return true;
		}

		// Here we assume the admin configured Payplans to not render plans they own
		if (in_array($planId, $plans)) {
			PP::info()->set('COM_PAYPLANS_DASHBOARD_NOT_ALLOWED_TO_SUBSCRIBED_THIS_PLAN_AS_ALREADY_SUBSCRIBED', 'error');

			$redirect = JRoute::_('index.php?option=com_payplans&view=dashboard', false);

			return PP::redirect($redirect);
		}

		return true;
	}

	/**
	 * Determine if we require to redirect to checkout page
	 *
	 * @since	4.1.8
	 * @access	public
	 */
	public function processRedirection()
	{
		$option = $this->input->get('option', '', 'default');
		$task = $this->input->get('task', '', 'cmd');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$planId = $this->input->get('plan_id', 0, 'int');

		// Check for jfbconnect integration with easysocial
		if ($option == 'com_easysocial' && $view == 'dashboard') {

			// Check for jfbconnect
			$session = PP::session();
			$jfbcRegistration = $session->get('PP_EASYSOCIAL_REGISTRATION_SUCCESS_JFBC', 0);

			// Redirect to checkout page
			if ($jfbcRegistration) {
				$session->set('PP_EASYSOCIAL_REGISTRATION_JFBC_REDIRECT_SUCCESS', 1);
				$key = $this->input->get('invoice_key', 0, 'default');

				if (!$key) {
					$key = $session->get('PP_INVOICE_KEY', 0);
				}

				if (!$key) {
					return;
				}

				$id = PP::getIdFromKey($key);
				$invoice = PP::invoice($id);
				$invoiceKey = $invoice->getKey();

				// Directly go to thanks page for free invoice
				if ($this->config->get('skip_free_invoices') && $invoice->isFree()) {

					$redirect = PPR::_('index.php?option=com_payplans&task=checkout.confirm&invoice_key=' . $invoiceKey . '&app_id=0', false);
					return $this->app->redirect($redirect);
				}

				$redirect = PPR::_('index.php?option=com_payplans&view=checkout&invoice_key=' . $invoiceKey . '&tmpl=component', false);

				$this->app->redirect($redirect);
				return $this->app->close();
			}
		}

		return;
	}
}
