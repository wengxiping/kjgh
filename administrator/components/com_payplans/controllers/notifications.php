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

class PayplansControllerNotifications extends PayPlansController
{
	public function __construct()
	{
		parent::__construct();

		$this->checkAccess('notifications');
		
		$this->registerTask('saveFile', 'storeFile');
		$this->registerTask('applyFile', 'storeFile');
		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		
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
				$this->info->set('Unable to delete notification rule', 'error');
				return $this->redirectToView('notifications');
			}

			$app->delete();
		}

		$this->info->set('COM_PP_PAYMENT_METHODS_DELETED_SUCCESS', 'success');

		return $this->redirectToView('notifications');
	}

	/**
	 * Cancel process
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function cancel()
	{
		return $this->redirectToView('notifications');
	}

	/**
	 * Saves a payment gateway
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store()
	{
		$id = $this->input->get('id', 0, 'int');
		$data = $this->input->post->getArray();
		$coreParams = $this->input->get('core_params', array(), 'array');
		$appParams = $this->input->get('app_params', array(), 'array');

		if ($id) {
			$app = PP::app()->getAppInstance($id);

			$data['core_params'] = $app->collectCoreParams($data);
			$data['app_params']  = $app->collectAppParams($data);
		} else {
			$app = PP::app();
		}

		// All notifications should have the group and type of "email"
		$data['group'] = 'email';
		$data['type'] = 'email';

		$app->bind($data);

		// Encode html contents
		$content = PP::normalize($appParams, 'content', '');

		if ($content) {
			$appParams['content'] = base64_encode($content);
		}

		$app->setCoreParams($coreParams);
		$app->setAppParams($appParams);

		try {
			$app->save();
		} catch (Exception $e) {
			$this->info->set($e->getMessage(), 'error');

			return $this->redirectToView('notifications', 'form', 'id=' . $app->getId());
		}

		$this->info->set('COM_PP_NOTIFICATION_RULE_UPDATED_SUCCESSFULLY');

		if ($this->getTask() == 'apply') {
			return $this->redirectToView('notifications', 'form', 'id=' . $app->getId());
		}
		return $this->redirectToView('notifications');
	}

	/**
	 * Saves a new file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function storeFile()
	{
		$fileName = $this->input->get('filename', '');
		$file = $this->input->get('file', '', 'default');
		$file = base64_decode($file);
		$source = $this->input->get('source', '', 'raw');

		$model = PP::model('Notifications');

		$path = $model->getOverrideFolder($file);

		$state = JFile::write($path, $source);

		$this->info->set('COM_PP_NOTIFICATION_TEMPLATE_FILE_SAVED_SUCCESSFULLY');

		$task = $this->getTask();

		if ($task == 'applyFile') {
			return $this->redirectToView('notifications', 'editFile', 'file=' . urlencode($fileName));
		}

		return $this->redirectToView('notifications', 'templates');
	}
}
