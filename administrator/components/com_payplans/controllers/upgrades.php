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

PP::import('admin:/includes/upgrade/upgrade');

class PayplansControllerUpgrades extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('plans');

		$this->registerTask('save', 'save');
		$this->registerTask('savenew', 'save');
		$this->registerTask('apply', 'save');

		$this->registerTask('close', 'cancel');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	/**
	 * Delete a list of subscriptions from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', 0, 'int');

		foreach ($ids as $id) {
			$app = PP::app((int) $id);
			$app->delete();
		}

		$this->info->set('Selected upgrade rules is deleted successfully');
		return $this->redirectToView('upgrades');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=upgrades');
	}

	/**
	 * Saves a subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$id = $this->input->get('app_id', 0, 'int');
		$data = $this->input->post->getArray();

		if (empty($data['title'])) {
			$this->info->set('COM_PP_TITLE_REQUIRED', 'error');
			return $this->redirectToView('modifiers', 'form');
		}

		$app = PP::app($id);
		$app->bind($data);

		// Since we know this is for upgrades, we can set the type here
		$app->type = 'upgrade';
		$app->group = 'core';

		$coreParams = new JRegistry($data['core_params']);
		$app->setCoreParams($data['core_params']);

		$appParams = new JRegistry($data['app_params']);
		$app->setAppParams($data['app_params']);

		$state = $app->save();

		$message = 'COM_PP_UPGRADE_CREATED_SUCCESS';

		if ($state === false) {
			$this->info->set('COM_PP_UPGRADE_SAVED_FAILED', 'error');

			return $this->redirectToView('upgrades', 'form');
		}

		if ($id) {
			$message = 'COM_PP_UPGRADE_SAVED_SUCCESS';
		}

		$this->info->set($message, 'success');

		$task = $this->getTask();

		if ($task == 'apply') {
			return $this->redirectToView('upgrades', 'form', 'id=' . $app->getId());
		}

		if ($task == 'savenew') {
			return $this->redirectToView('upgrades', 'form');
		}
		return $this->redirectToView('upgrades');
	}

	// /**
	//  * Triggered when user submits for an upgrade (Backend)
	//  *
	//  * @since	4.0.0
	//  * @access	public
	//  */
	// public function upgrade()
	// {
	// 	$user = PP::user($this->my->id);

	// 	if (!$user->isSiteAdmin()) {
	// 		die('You are not allowed here');
	// 	}

	// 	$newPlanId = $this->input->get('upgrade_to', 0, 'int');
	// 	$subscriptionId = $this->input->get('id', 0, 'int');
	// 	$upgradeType = $this->input->get('type', '', 'word');

	// 	if (!$subscriptionId || !$upgradeType || !$newPlanId) {
	// 		die('Invalid data provided in POST. Please verify the form has the proper inputs.');
	// 	}

	// 	$subscription = PP::subscription($subscriptionId);
	// 	$newPlan = PP::plan($newPlanId);

	// 	// Get the new invoice
	// 	$invoice = PPUpgrade::upgradeSubscription($subscription, $newPlan, $upgradeType);

	// 	$defaultUrl = JRoute::_('index.php?option=com_payplans&view=subscription&layout=form&id=' . $subscription->getId(), false);

	// 	// Since this is on the back end form, we can display proper error message and redirect to the proper view
	// 	if (!$invoice) {
	// 		$this->info->set('There was an error when trying to create the invoice for the upgraded plan. Please try upgrading the subscription again.', 'error');

	// 		return $this->app->redirect($defaultUrl);
	// 	}

	// 	$message = 'COM_PAYPLANS_UPGRADE_SUBSCRIPTION_SUCCESSFULLY_UPGRADED_FROM_PARTIAL_TYPE';

	// 	if ($upgradeType == 'free') {
	// 		$message = 'COM_PAYPLANS_UPGRADE_SUBSCRIPTION_SUCCESSFULLY_UPGRADED_FROM_FREE_TYPE';
	// 	}

	// 	if ($upgradeType == 'offline') {
	// 		$message = 'COM_PAYPLANS_UPGRADE_SUBSCRIPTION_SUCCESSFULLY_UPGRADED_FROM_OFFLINE_TYPE';
	// 	}

	// 	$this->info->set(JText::_($message), 'success');
	// 	return $this->app->redirect($defaultUrl);
	// }

	/**
	 * Allow caller to toggle published state
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function togglePublish()
	{
		$ids = $this->input->get('cid', 0, 'int');

		$task = $this->getTask();

		foreach ($ids as $id) {
			$table = PP::table('App');
			$table->load($id);

			$table->$task();
		}

		$message = $task == 'publish' ? 'COM_PP_ITEM_PUBLISHED_SUCCESSFULLY' : 'COM_PP_ITEM_UNPUBLISHED_SUCCESSFULLY';

		$this->info->set($message);
		return $this->redirectToView('upgrades');
	}

}
