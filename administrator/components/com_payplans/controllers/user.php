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

 class PayplansControllerUser extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('users');
		
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');
	}

	/**
	 * Saves a user record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$user = PP::user($id);

		if (!$id || !$user->getId()) {
			$this->info->set('Unable to save user as the id provided is invalid', 'error');
			return $this->redirectToView('user');
		}

		$data = $this->input->post->getArray();
		$preference = isset($data['preference']) ? $data['preference'] : '';
		$params = isset($data['params']) ? $data['params'] : '';

		$user->bind($data);

		if ($preference) {
			$user->setPreferences($preference);
		}

		if ($params) {
			$user->setParams($params);
		}

		$state = $user->save();

		$this->info->set('COM_PP_USER_SAVED_SUCCESS');
		
		$task = $this->getTask();

		if ($task == 'apply') {
			$active = $this->input->get('activeTab', '');
			
			return $this->redirectToView('user', 'form', 'id=' . $user->getId() . '&activeTab=' . $active);
		}

		if ($task == 'save') {
			return $this->redirectToView('user');
		}
		
		return $this->redirectToView('user', 'form');
	}

	/**
	 * Applies a plan for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function applyPlan()
	{
		$ids = $this->input->get('cid', '', 'default');
		$planId = $this->input->get('apply_plan_id', 0, 'int');

		if (!$planId) {
			$this->info->set('Invalid plan id provided. Please try again by selecting a plan', 'error');
			return $this->redirectToView('user');
		}

		$plan = PP::plan($planId);

		foreach ($ids as $id) {
			$id = (int) $id;

			$order = $plan->subscribe($id);

			// Create an invoice for the order
			$invoice = $order->createInvoice($order->getSubscription());

			// Apply 100% discount
			$modifier = PP::modifier();

			$modifierData = array(
				'message' => 'COM_PAYPLANS_APPLY_PLAN_ON_USER_MESSAGE',
				'invoice_id' => $invoice->getId(),
				'user_id' => 'apply_plan',
				'amount' => -100,
				'percentage' => true,
				'frequency' => PP_MODIFIER_FREQUENCY_ONE_TIME,
				'serial' => PP_MODIFIER_FIXED_DISCOUNT
			);
			
			$modifier->save();

			$invoice->refresh();
			$invoice->save();

			// Create a transaction with 0 amount since the plan is applied by the admin
			$transaction = PP::transaction();
			$transaction->user_id = $invoice->getBuyer()->getId();
			$transaction->invoice_id = $invoice->getId();
			$transaction->amount = $invoice->getTotal();
			$transaction->message = 'COM_PAYPLANS_TRANSACTION_CREATED_FOR_APPLY_PLAN_TO_USER';
			$transaction->save();
		}

		$message = 'COM_PP_SELECTED_PLAN_APPLIED_SUCCESS';

		$this->info->set($message);
		return $this->redirectToView('user');
	}

	/**
	 * Deletes a download request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteDownload()
	{
		$ids = $this->input->get('cid', array(), 'array');

		if ($ids) {
			foreach ($ids as $id) {
				$table = PP::table('Download');
				$table->load($id);
				$table->delete();
			}
		}

		$this->info->set('Selected download requests has been deleted successfully');
		
		$this->redirectToView('user', 'downloads');
	}
}

