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

class PayplansViewSubscription extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('orders');
	}

	public function display($tpl = null)
	{
		$this->heading('Subscriptions');

		JToolbarHelper::addNew();
		JToolbarHelper::custom('updateStatus', '', '', 'COM_PP_UPDATE_STATUS', true);
		JToolbarHelper::custom('extend', '', '', 'COM_PAYPLANS_SUBSCRIPTION_TOOLBAR_EXTEND', true);
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_SUBSCRIPTION'), 'subscription.delete');

		$model = PP::model('Subscription');
		$model->initStates();

		$result = $model->getItems();
		$subscriptions = array();

		if ($result) {
			foreach ($result as &$row) {
				$row->buyer = $row->getBuyer();
				$row->order = $row->getOrder();

				$subscriptions[] = $row;
			}
		}


		$pagination = $model->getPagination();

		// Get states used in this list
		$states = $this->getStates(array('search', 'plan_id', 'status', 'subscription_date', 'expiration_date', 'dateRange', 'limit', 'ordering', 'direction'));

		$this->set('subscriptions', $subscriptions);
		$this->set('states', $states);
		$this->set('pagination', $pagination);

		return parent::display('subscription/default/default');
	}

	/**
	 * Renders a new or edit a subscription form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('New Subscription');

		$id = $this->input->get('id', null, 'int');

		if ($id) {
			$this->heading('Edit Subscription');
		}

		JToolbarHelper::apply('subscription.apply');
		JToolbarHelper::save('subscription.save');
		JToolbarHelper::save2new('subscription.saveNew');
		JToolbarHelper::cancel('subscription.cancel');

		$subscription = PP::subscription();
		$subscription->setAfterBindLoad(false);
		$subscription->toggleUseCache();
		$subscription->load($id);

		$order = $subscription->getOrder();

		if ($subscription->isOnHold()) {
			PP::info()->set('This subscription is <b>inactive</b> and you will not be able to add additional invoice to it. It is advisable that you create a new subscription should you need to reactivate it.', 'warning');
		}

		// User probably trying to edit a deleted subscription
		if ($id && !$subscription->getId()) {
			$this->info->set('Invalid subscription id. The subscription may have already been deleted.', 'error');
			return $this->redirectToView('subscription');
		}

		if ($subscription->getId() && !$subscription->isOnHold()) {

			if ($subscription->isRecurring()) {
				JToolbarHelper::custom('newrecurringinvoice', '', '', JText::_('Add New Invoice'), false);
			} else {
				JToolbarHelper::custom('newinvoice', '', '', JText::_('Add New Invoice'), false);
			}
		}

		if ($subscription->canCancel()) {
			JToolbarHelper::custom('subscription.terminate', '', '', JText::_('Cancel Subscription'), false);
		}

		if ($subscription->canUpgrade()) {
			JToolbarHelper::custom('upgrade', 'upgrade', '', JText::_('COM_PP_UPGRADE_SUBSCRIPTION'), false);
		}

		// Get invoices and transactions for the subscription
		$invoices = $order->getInvoices();

		// Transactions
		$transactions = array();
		if ($id) {
			$invoiceModel = PP::model('Invoice');
			$transactions = $invoiceModel->getTransactions($invoices);
		}
		
		// Upgrades
		$upgradedFrom = $order->getParam('upgrading_from', 0);
		$upgradedFromSubscription = false;
		$upgradedTo = $order->getParam('upgraded_to', 0);

		if ($upgradedFrom) {
			$upgradedFromSubscription = PP::Subscription($upgradedFrom);
		}

		$activeTab = $this->input->get('active', '', 'word');

		$params = $subscription->getParams();

		// Retrieve a list of log for this subscription
		$logModel = PP::model('Log');
		$options = array('object_id' => $subscription->subscription_id, 'class' => 'subscription', 'level' => 'all', 'limit' => 20);

		$logs = $logModel->getItemsWithoutState($options);

		// Get any custom details for subscription
		$customDetails = $subscription->getCustomDetails();

		// Check this subscription plan set for fixed date expiration
		$plan = $subscription->getplan();

		if ($plan->isFixedExpirationDate()) {

			$expirationDate = $plan->getExpirationOnDate();
			if ($expirationDate) {

				$message =  JText::sprintf('COM_PP_SUBSCRIPTION_SET_FOR_FIXED_EXPIRATION_INFO', PP::date($expirationDate));
				PP::info()->set($message, 'error');
			}
		}

		$this->set('customDetails', $customDetails);
		$this->set('activeTab', $activeTab);
		$this->set('upgradedFromSubscription', $upgradedFromSubscription);
		$this->set('upgradedFrom', $upgradedFrom);
		$this->set('upgradedTo', $upgradedTo);
		$this->set('params', $params);
		$this->set('order', $order);
		$this->set('subscription', $subscription);
		$this->set('invoices', $invoices);
		$this->set('transactions', $transactions);
		$this->set('logs', $logs);

		return parent::display('subscription/form/default');
	}

	/**
	 * Renders custom details listing for subscription
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function customdetails($tpl = null)
	{
		$this->heading('Subscription Custom Details');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_SELECTED_ITEMS'), 'customdetails.delete');

		$model = PP::model('customdetails');
		$items = $model->getCustomDetails('subscription');
		$pagination = $model->getPagination();

		$states = $this->getStates(array('username', 'plan_id', 'subscription_status', 'usertype', 'limit', 'limitstart', 'ordering', 'direction'));

		$view = $this->input->get('view', '', 'cmd');

		$this->set('view', $view);
		$this->set('states', $states);
		$this->set('items', $items);
		$this->set('pagination', $pagination);

		return parent::display('customdetails/default/default');
	}

	/**
	 * Renders edit custom details form
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function customdetailsform($tpl = null)
	{
		$id = $this->input->get('id', 0, 'int');

		$this->heading('Create Custom Details');

		$table = PP::table('customdetails');

		if ($id) {
			$this->heading('Edit Custom Details');
			$table->load($id);
		}

		JToolbarHelper::apply('customdetails.apply');
		JToolbarHelper::save('customdetails.save');
		JToolbarHelper::save2new('customdetails.saveNew');
		JToolbarHelper::back(JText::_('JTOOLBAR_CANCEL'));

		$editor = JFactory::getEditor('codemirror');

		$activeTab = $this->input->get('activeTab', '', 'word');
		$params = $table->getParams();

		if (!$params) {
			$params = new JRegistry();
		}

		$view = $this->input->get('view', '', 'cmd');

		$this->set('view', $view);
		$this->set('params', $params);
		$this->set('id', $id);
		$this->set('table', $table);
		$this->set('editor', $editor);
		$this->set('activeTab', $activeTab);
		$this->set('type', 'subscription');

		return parent::display('customdetails/form/default');
	}
}
