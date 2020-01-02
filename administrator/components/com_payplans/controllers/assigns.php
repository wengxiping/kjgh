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

class PayplansControllerAssigns extends PayPlansController
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
	 * Delete a list of plan assigns instance from the site
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

		$this->info->set('COM_PP_MODIFIER_DELETED_SUCCESS');
		return $this->redirectToView('assigns');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=assigns');
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
		return $this->redirectToView('assigns');
	}

	/**
	 * Saves a modifier
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
			return $this->redirectToView('assigns', 'form');
		}

		$app = PP::app($id);
		$app->bind($data);
		$app->type = 'profilebasedplan';

		// We need to force the app group to be core based
		$app->group = 'core';

		// Standardizing params
		$data['app_params']['profile_type'] = array($data['app_params']['profile_type']);
		$appParams = $app->collectAppParams($data);

		$app->setAppParams($appParams);

		// Save the app
		$state = $app->save();

		$message = 'COM_PP_ASSIGNS_CREATED_SUCCESS';

		if ($state === false) {
			$this->info->set('COM_PP_ASSIGNS_SAVED_FAILED', 'error');

			return $this->redirectToView('assigns', 'form');
		}

		$this->info->set($message, 'success');

		$task = $this->getTask();

		if ($task == 'apply') {
			return $this->redirectToView('assigns', 'form', 'id=' . $app->getId());
		}

		if ($task == 'savenew') {
			return $this->redirectToView('assigns', 'form');
		}

		return $this->redirectToView('assigns');
	}

}
