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

class PayplansControllerApp extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('apps');
		
		// Map the alias methods here.
		$this->registerTask('save', 'store');
		$this->registerTask('savenew', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
		$this->registerTask('remove', 'delete');
	}

	/**
	 * Allows remote caller to delete an app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		$ids = $this->input->get('cid', array(), 'int');

		foreach ($ids as $id) {
			$id = (int) $id;

			$app = PP::app($id);

			if (!$app->canDelete()) {
				$this->info->set('COM_PP_UNABLE_TO_DELETE_APP', 'error');
				return $this->redirectToView('app');
			}

			$app->delete();
		}

		$this->info->set('COM_PP_APP_DELETED_SUCCESS', 'success');

		return $this->redirectToView('app');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->app->redirect('index.php?option=com_payplans&view=app');
	}

	/**
	 * Detect if the plugin is installed on the site. If it isn't installed, install it.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function createInstance()
	{
		$element = $this->input->get('element', '', 'default');

		$model = PP::model('App');
		$installed = $model->isPluginInstalled($element);

		// If the plugin is not installed, 
		if (!$installed) {
			$state = $model->installPlugin($element);
		}

		$view = $this->input->get('view', 'app', 'word');
		$layout = $this->input->get('layout', 'create', 'word');

		// Redirect to the respective form
		$this->redirectToView($view, $layout, 'element=' . $element);
	}

	/**
	 * Toggles publishing state
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function togglePublish()
	{
		$ids = $this->input->get('cid', array(), 'array');
		$task = $this->getTask();

		foreach ($ids as $id) {
			$id = (int) $id;

			$app = PP::app($id);
			$app->$task();
		}

		$message = 'COM_PP_APP_PUBLISHED_SUCCESSFULLY';

		if ($task == 'unpublish') {
			$message = 'COM_PP_APP_UNPUBLISHED_SUCCESSFULLY';
		}

		$this->info->set($message);

		$return = $this->input->get('return', '', 'default');

		if (!$return) {
			return $this->redirectToView('app');
		}

		$return = base64_decode($return);

		return $this->app->redirect($return);
	}

	/**
	 * Store the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();
		$coreParams = $this->input->get('core_params', array(), 'array');
		$appParams = $this->input->get('app_params', array(), 'raw');

		$data['core_params'] = $coreParams;
		$data['app_params'] = $appParams;

		$message = 'COM_PP_APP_UPDATED_SUCCESSFULLY';

		$app = PP::app();

		if ($id) {
			$app = PP::app()->getAppInstance($id);	
		}

		$data['core_params'] = $app->collectCoreParams($data);
		$data['app_params']  = $app->collectAppParams($data);

		$app->setCoreParams($data['core_params']);
		$app->setAppParams($data['app_params']);

		// All apps should have an instance of "app"
		if (!$id) {
			$data['group'] = 'app';
			$message = 'COM_PP_APP_ADD_SUCCESSFULLY';
		}

		$app->bind($data);

		try {
			$app->save();

			// Check if the app plugin is published or not
			$plugin = $app->getPlugin();

			if (!$plugin->enabled) {
				$plugin->enabled = true;
				$plugin->store();
			}

		} catch (Exception $e) {
			$this->info->set($e->getMessage(), 'error');

			return $this->redirectToView('app', 'form', 'id=' . $app->getId());
		}

		$this->info->set($message);

		if ($this->getTask() == 'apply') {
			return $this->redirectToView('app', 'form', 'id=' . $app->getId());
		}

		return $this->redirectToView('app');
	}
}

