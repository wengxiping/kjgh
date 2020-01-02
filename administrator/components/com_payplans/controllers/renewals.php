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

class PayplansControllerRenewals extends PayPlansController
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

		$this->info->set('COM_PP_RENEWAL_DELETED_SUCCESS');
		return $this->redirectToView('renewals');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=renewals');
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
			return $this->redirectToView('renewals', 'form');
		}

		$app = PP::app($id);
		$app->bind($data);

		// Since we know this is for upgrades, we can set the type here
		$app->type = 'renewal';
		$app->type = 'core';

		$coreParams = new JRegistry($data['core_params']);
		$app->setCoreParams($data['core_params']);

		$appParams = new JRegistry($data['app_params']);
		$app->setAppParams($data['app_params']);

		$state = $app->save();

		$message = 'COM_PP_RENEWAL_CREATED_SUCCESS';

		if ($state === false) {
			$this->info->set('COM_PP_RENEWAL_SAVED_FAILED', 'error');

			return $this->redirectToView('renewals', 'form');
		}

		if ($id) {
			$message = 'COM_PP_RENEWAL_SAVED_SUCCESS';
		}

		$this->info->set($message, 'success');

		$task = $this->getTask();

		if ($task == 'apply') {
			return $this->redirectToView('renewals', 'form', 'id=' . $app->getId());
		}

		if ($task == 'savenew') {
			return $this->redirectToView('renewals', 'form');
		}
		return $this->redirectToView('renewals');
	}


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
		return $this->redirectToView('renewals');
	}

}
