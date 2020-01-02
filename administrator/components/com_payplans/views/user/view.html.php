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

class PayplansViewUser extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('users');
	}

	public function display($tpl = null)
	{
		$this->heading('Users');

		JToolbarHelper::custom('user.browsePlan', '', '', 'COM_PP_ASSIGN_PLAN', true);

		$model = PP::model('User');
		$model->initStates();

		$users = $model->getItems();
		$pagination = $model->getPagination();

		$states = $this->getStates(array('search', 'plan_id', 'limit', 'ordering', 'direction'));

		$this->set('states', $states);
		$this->set('users', $users);
		$this->set('pagination', $pagination);

		return parent::display('user/default/default');
	}

	/**
	 * Renders a list of download requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function downloads($tpl = null)
	{
		$this->heading('Download Requests');

		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_SELECTED_ITEMS'), 'user.deleteDownload');

		$model = PP::model('Download');
		$model->initStates();

		$requests = $model->getItems();
		$pagination = $model->getPagination();

		if ($requests) {
			foreach ($requests as $request) {
				$request->user = PP::user($request->user_id);
				$request->params = new JRegistry($request->params);
			}
		}

		$states = $this->getStates(array('user_id', 'limit', 'ordering', 'direction'), $model);

		$this->set('states', $states);
		$this->set('requests', $requests);
		$this->set('pagination', $pagination);

		return parent::display('user/downloads/default');
	}

	/**
	 * Renders a list of download requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function download()
	{
		$userId = $this->input->get('id', 0, 'int');

		if (!$userId) {
			throw new Exception('Invalid user id');
		}

		$gdpr = PP::gdpr();
		$gdpr->download($userId);
		exit;	
	}

	/**
	 * Renders the edit user form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		$id = $this->input->get('id', 0, 'int');

		$user = PP::user($id);

		if (!$id || !$user->getId()) {
			return $this->redirect('index.php?option=com_payplans&view=user');
		}
		
		$this->heading('Editing User');

		JToolbarHelper::apply('user.apply');
		JToolbarHelper::save('user.save');
		JToolbarHelper::cancel();
		
		$orderModel = PP::model('Order');
		$userOrders = $orderModel->loadRecords(array('buyer_id' => $user->getId()));
		$subscriptions = array();

		$orders = array();

		if ($userOrders) {
			foreach ($userOrders as $item) {

				$order = PP::order();
				$order->setAfterBindLoad(false);
				$order->toggleUseCache();
				$order->bind($item);

				$orders[] = $order;

				$subscription = $order->getSubscription();

				if (!$subscription) {
					continue;
				}

				if ($subscription) {
					$subscriptions[] = $subscription;
				}
			}
		}

		$invoiceModel = PP::model('Invoice');
		$userInvoices = $invoiceModel->loadRecords(array('user_id' => $user->getId(), 'status' => array(array('!=', 0))));

		$invoices = array();

		if ($userInvoices) {
			foreach ($userInvoices as $item) {

				$invoice = PP::invoice();
				$invoice->setAfterBindLoad(false);
				$invoice->toggleUseCache();
				$invoice->bind($item);

				$invoices[] = $invoice;
			}
		}

		// Retrieve a list of log for this user
		$logModel = PP::model('Log');
		$options = array('object_id' => $user->getId(), 'class' => 'user', 'level' => 'all');

		$logs = $logModel->getItems($options);
		$pagination = $logModel->getPagination();

		$activeTab = $this->input->get('activeTab', '', 'default');

		$preferences = $user->getPreferences();
		$params = $user->getParams();

		// Get any custom details for user
		$customDetails = $user->getCustomDetails();

		$referralDetails = $user->getReferralDetails();

		$this->set('customDetails', $customDetails);
		$this->set('referralDetails', $referralDetails);
		$this->set('invoices', $invoices);
		$this->set('subscriptions', $subscriptions);
		$this->set('params', $params);
		$this->set('preferences', $preferences);
		$this->set('activeTab', $activeTab);
		$this->set('user', $user);
		$this->set('orders', $orders);
		$this->set('logs', $logs);
		$this->set('pagination', $pagination);

		return parent::display('user/form/default');
	}

	/**
	 * Renders the user custom details list.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function customdetails($tpl = null)
	{
		$this->heading('User Custom Details');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_SELECTED_ITEMS'), 'customdetails.delete');

		$model = PP::model('customdetails');
		$items = $model->getCustomDetails('user');
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
	 * Renders the edit user form
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
		JToolbarHelper::cancel('customdetails.cancel');
		
		// Always use codemirror
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
		$this->set('type', 'user');

		return parent::display('customdetails/form/default');
	}
}

