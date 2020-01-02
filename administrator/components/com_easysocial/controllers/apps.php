<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerApps extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('unpublish', 'unpublish');
		$this->registerTask('save', 'save');
		$this->registerTask('apply', 'save');
		$this->registerTask('cancel', 'cancel');
	}

	/**
	 * Purges discovered items from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purgeDiscovered()
	{
		ES::checkToken();

		$model = ES::model('Apps');
		$model->deleteDiscovered();

		$this->view->setMessage('COM_EASYSOCIAL_APPS_DISCOVERED_APPS_PURGED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Application Discovery
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover()
	{
		ES::checkToken();

		$model = ES::model('Apps');
		$total = $model->discover();

		if (!$total) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_NO_APPS_DISCOVERED');
			return $this->view->call(__FUNCTION__, $total);
		}
		
		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_APPS_DISCOVERED_APPS', $total));
		return $this->view->call(__FUNCTION__, $total);
	}

	/**
	 * Allows caller to save the app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();

		$task = $this->getTask();

		$id = $this->input->get('id', 0, 'int');
		$app = ES::table('App');
		$app->load($id);

		if (!$id || !$app->id) {
			return $this->view->exception('COM_EASYSOCIAL_APPS_UNABLE_TO_FIND_APP');
		}

		// Determines if the "default" value changed
		$default = $this->input->get('default', '', 'int');

		$model = ES::model('Apps');

		// Determine if the default is changed from 0 -> 1
		// This is because when it's changed from 0 -> 1, we need to delete existing user params.
		if ($app->default != $default && $default) {
			$state = $model->removeUserApp($app->id);
		}

		// Get the posted data.
		$post = JRequest::get('post');

		// Retrieve params values
		$rawParams = isset($post['params']) ? $post['params'] : '';
		$post['params']	= json_encode($rawParams);

		// Bind the posted data to the app
		$app->bind($post);
		$state = $app->store();

		if (!$state) {
			return $this->view->exception($app->getError());
		}

		// Bind the acl for the apps
		$access = $this->input->get('access', array(), 'array');
		$model->updateAccess($app, $access);
		
		$this->view->setMessage('COM_EASYSOCIAL_APPS_SAVED_SUCCESS');
		return $this->view->call(__FUNCTION__, $app, $task);
	}
	
	/**
	 * Unpublishes an app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function publish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$ids = ES::makeArray($ids);

		$type = '';

		foreach ($ids as $id) {
			$app = ES::table('App');
			$app->load((int) $id);
			$app->publish();

			$type = $app->type;
		}

		$this->view->setMessage('COM_EASYSOCIAL_APPS_PUBLISHED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $type);
	}

	/**
	 * Unpublishes an app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unpublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$type = '';

		foreach ($ids as $id) {
			$app = ES::table('App');
			$app->load($id);

			// System apps cannot be unpublished
			if ($app->system) {
				$this->view->setMessage('COM_EASYSOCIAL_APPS_CANNOT_UNPUBLISH_CORE_APPS', ES_ERROR);
				return $this->view->call(__FUNCTION__, $type);
			}

			$app->unpublish();

			$type = $app->type;
		}

		$this->view->setMessage('COM_EASYSOCIAL_APPS_UNPUBLISHED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $type);
	}

	/**
	 * Uninstalls an app from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function uninstall()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', 0, 'int');
		$ids = ES::makeArray($ids);

		if (empty($ids)) {
			$this->view->setMessage(JText::_('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED'), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		foreach ($ids as $id) {
			$app = ES::table('App');
			$app->load($id);

			// If app is a core or system app, do not allow the admin to delete this.
			if ($app->core || $app->system) {
				$this->view->setMessage('COM_EASYSOCIAL_APPS_UNABLE_TO_DELETE_CORE_APP', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			// Perform the uninstallation of the app.
			$state = $app->uninstall();
		}

		$this->view->setMessage('COM_EASYSOCIAL_APPS_UNINSTALLED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Processes installation of discovered apps
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installDiscovered()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$ids = ES::makeArray($ids);
		$apps = array();

		foreach ($ids as $id) {
			$app = ES::table('App');
			$app->load((int) $id);

			$path = SOCIAL_APPS;

			if ($app->type == 'apps') {
				$path = $path . '/' . $app->group . '/' . $app->element;
			}

			if ($app->type == 'fields') {
				$path = $path . '/fields/' . $app->group . '/' . $app->element;
			}

			$installer = ES::installer();
			$installer->load($path);

			$app = $installer->install();
			$apps[]	= $app;
		}

		$total = count($apps);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_APPS_DISCOVERED_INSTALLED', $total));
		return $this->view->call(__FUNCTION__, $apps);
	}

	/**
	 * Allows admin to toggle featured groups
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function toggleDefault()
	{
		ES::checkToken();

		// Get the current task since there are a couple of tasks being proxied here.
		$task = $this->getTask();

		// Default message
		$message = 'COM_EASYSOCIAL_APPS_SET_DEFAULT_SUCCESSFULLY';

		// Get the group object
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;
			
			$table = ES::table('App');
			$table->load($id);

			if ($task == 'toggleDefault') {

				if ($table->default) {
					$table->default = false;
					$message = 'COM_EASYSOCIAL_APPS_REMOVE_DEFAULT_SUCCESSFULLY';
				} else {
					$table->default = true;
				}
			}

			$table->store();
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Processes the installation package from directory method.
	 *
	 * @since	1.0
	 * @access	public
	 **/
	public function installFromDirectory($path = '')
	{
		ES::checkToken();

		if (!$path) {
			$path = $this->input->get('package-directory', '');
		}

		// Try to detect if the temporary path is the same as the default path.
		if ($path == $this->jconfig->getValue('tmp_path')) {
			$this->view->setMessage('COM_EASYSOCIAL_INSTALLER_PLEASE_SPECIFY_DIRECTORY', ES_ERROR);
			return $this->view->call('install');
		}

		$installer = ES::installer();
		$state = $installer->load($path);

		// If there's an error, we need to log it down.
		if (!$state) {
			$this->view->setMessage($installer->getError(), ES_ERROR);

			return $this->view->call('install');
		}

		// Install the app now
		$app = $installer->install();

		// If there's an error installing, log this down.
		if ($app === false) {
			$this->view->setMessage($installer->getError(), ES_ERROR);
			return $this->view->call('install');
		}

		return $this->view->call('installCompleted', $app);
	}

	/**
	 * Processes the install by uploading
	 *
	 * @since	1.0
	 * @access	public
	 **/
	public function installFromUpload()
	{
		ES::checkToken();

		$package = JRequest::getVar('package', '', 'files');

		// Test for empty packages.
		if (!isset($package['tmp_name']) || !$package['tmp_name']) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_PLEASE_UPLOAD_INSTALLER', ES_ERROR);
			return $this->view->call('install');
		}

		$source = $package['tmp_name'];

		// Construct the destination path
		$destination = $this->jconfig->getValue('tmp_path') . '/' . $package['name'];


		$installer = ES::installer();
		$state = $installer->upload($source, $destination);

		if (!$state) {
			$this->view->setMessage('COM_EASYSOCIAL_APPS_UNABLE_TO_COPY_UPLOADED_FILE', ES_ERROR);
			return $this->view->call('install');
		}

		// Unpack the archive.
		$path = $installer->extract($destination);

		// When something went wrong with the installation, just display the error
		if ($path === false) {
			$error = ES::get('Errors')->getErrors('installer.extract');

			$this->view->setMessage($error, ES_ERROR);
			$this->app->redirect('index.php?option=com_easysocial&view=applications&layout=error');
			return $this->app->close();
		}

		return $this->installFromDirectory($path);
	}
}
