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

class PayPlansViewDashboard extends PayPlansSiteView
{
	// Display toolbar on this view
	protected $toolbar = true;

	public function display($tpl = null)
	{
		// Ensure that the user is logged in
		PP::requireLogin();

		$user = PP::user();
		$items = array();
		$model = PP::model('Subscription');

		// Hide pending orders if set in congig settings
		$hidePendingOrders = false;
		if ($this->config->get('layout_pending_orders')) {
			$hidePendingOrders = true;
		}

		$options = array('userId' => $user->getId(), 'hidePendingOrder' => $hidePendingOrders);
		$items = $model->getItemsWithoutState($options);

		$subscriptions = array();

		if ($items) {
			foreach ($items as $item) {
				$subscription = PP::subscription($item->subscription_id);
				$order = $subscription->getOrder();

				// Determine if the order has been upgraded
				$upgradedTo = $order->getParam('upgraded_to', 0);

				if ($upgradedTo) {
					$upgradedSubscription = PP::subscription($upgradedTo);

					// @TODO: Not sure what is this for
					// if ($upgradedSub instanceof PayplansSubscription && PP_NONE != $upgradedSub->getStatus() ) {
					// 	//unset the already upgraded plan
					// 	unset($subscriptionRecords[$record->subscription_id]);
					// }
				}

				// So that the template can access these variables easily
				$subscription->expirationDate = $subscription->getExpirationDate();
				$subscription->order = $subscription->getOrder();
				$subscription->currency = $subscription->order->getCurrency();
				$subscription->expirationType = $subscription->getExpirationType();

				// Get any pending invoice for the subscription
				$subscription->pendingInvoice = false;

				if ($subscription->isNotActive()) {
					$inactiveInvoices = $subscription->order->getInvoices(PP_NONE);
					$confirmedInvoices = $subscription->order->getInvoices(PP_INVOICE_CONFIRMED);

					$pendingInvoices = array_merge($inactiveInvoices, $confirmedInvoices);

					$subscription->pendingInvoice = array_pop($pendingInvoices);
				}

				$subscription->canCancel();

				$subscription->actions = $subscription->getActions();

				$subscriptions[] = $subscription;
			}
		}

		// $display_myaccount	= PayPlansFactory::getConfig()->display_my_account;
		// $this->assign('display_myaccount', $display_myaccount);

		$this->set('subscriptions', $subscriptions);

		parent::display('site/dashboard/default/default');
	}

	/**
	 * Displays the list of invoices for a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function subscription()
	{
		PP::requireLogin();

		$subscriptionId = $this->getKey('subscription_key');
		$subscription = PP::subscription($subscriptionId);

		// Get order
		$order = $subscription->getOrder();

		// Ensure that the viewer is really the owner of the order
		if ($order->getBuyer()->getId() != $this->my->id && !PP::isSiteAdmin()) {
			$this->info->set('COM_PAYPLANS_SUBSCRIPTION_CAN_NOT_VIEW_SUBSCRIPTION_OF_OTHERS_USER', 'error');
			$redirect = PPR::_('index.php?option=com_payplans&view=subscription', false);

			return PP::redirect($redirect);
		}

		$invoices = $order->getInvoices();

		// So that the template can access these variables easily
		$subscription->expirationDate = $subscription->getExpirationDate();
		$subscription->order = $subscription->getOrder();
		$subscription->currency = $subscription->order->getCurrency();
		$subscription->expirationType = $subscription->getExpirationType();

		// Get the custom details for this subscription
		$plan = $subscription->getPlan();
		$model = PP::model('Customdetails');
		$customDetails = $model->getPlanCustomDetails($plan, 'subscription');

		$subscriptionParams = $subscription->getParams();

		$this->set('customDetails', $customDetails);
		$this->set('subscription', $subscription);
		$this->set('subscriptionParams', $subscriptionParams);
		$this->set('invoices', $invoices);
		$this->set('order', $order);

		parent::display('site/dashboard/subscription/default');
	}

	/**
	 * Download of user's data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function download()
	{
		PP::requireLogin();

		if (!$this->config->get('users_download')) {
			$this->info->set('COM_PP_FEATURE_NOT_AVAILABLE', 'error');

			return $this->redirectToView('dashboard');
		}

		$user = PP::user();
		$requested = $user->isDownloadRequested();
		$downloadState = false;

		if ($requested) {
			$downloadState = $user->getDownloadState();
		}

		$this->set('downloadState', $downloadState);
		$this->set('user', $user);
		$this->set('requested', $requested);

		parent::display('site/dashboard/download/default');
	}

	/**
	 * Editing of user preferences
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function preferences()
	{
		PP::requireLogin();

		if (!$this->config->get('user_edit_preferences') && !$this->config->get('user_edit_customdetails')) {
			$this->info->set('COM_PP_FEATURE_NOT_AVAILABLE', 'error');

			return $this->redirectToView('dashboard');
		}

		$user = PP::user();
		$customDetails = $user->getCustomDetails();
		$params = $user->getParams();
		$preferences = $user->getPreferences();
		
		$this->set('user', $user);
		$this->set('preferences', $preferences);
		$this->set('params', $params);
		$this->set('customDetails', $customDetails);

		parent::display('site/dashboard/preferences/default');
	}
}