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

class EasySocialControllerPrivacy extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'save');
		$this->registerTask('apply', 'save');

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$task = $this->getTask();

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PRIVACY_INVALID_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$privacy = ES::table('Privacy');
			$privacy->load((int) $id);

			// We don't want the user to toggle publishing for core.view.
			if ($privacy->type == 'core' && $privacy->rule == 'view') {
				continue;
			}

			$privacy->state = $task == 'publish' ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;
			$privacy->store();
		}

		$message = 'COM_EASYSOCIAL_PRIVACY_PUBLISHED_SUCCESS';

		if ($task == 'unpublish') {
			$message = 'COM_EASYSOCIAL_PRIVACY_UNPUBLISHED_SUCCESS';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Deletes a privacy
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_PRIVACY_INVALID_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$privacy = ES::table('Privacy');
			$privacy->load((int) $id);

			if ($privacy->core) {
				continue;
			}

			$privacy->delete();
		}

		$this->view->setMessage('COM_EASYSOCIAL_PRIVACY_DELETED_SUCCESS');
		return $this->view->call( __FUNCTION__ );
	}

	/**
	 * Saves a privacy
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$privacy = ES::table('Privacy');
		$privacy->load($id);

		if (!$id || !$privacy->id) {
			return $this->view->exception('COM_EASYSOCIAL_PRIVACY_INVALID_ID_PROVIDED');
		}

		$post = JRequest::get('POST');
		$value = $post['value'];

		$privacy->value = $value;
		$state = $privacy->store();

		if ($state === false) {
			$this->view->setMessage('COM_EASYSOCIAL_PRIVACY_UPDATED_FAILED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage('COM_EASYSOCIAL_PRIVACY_UPDATED_SUCCESS');
		
		return $this->view->call(__FUNCTION__, $this->getTask(), $privacy);
	}

	/**
	 * Processes the uploaded rule file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		$file = JRequest::getVar('package' , '' , 'FILES');
		$allowed = array('zip', 'privacy');

		$rules = ES::rules();
		$state = $rules->upload($file, 'privacy', $allowed);

		if ($state === false) {
			$this->view->setMessage($rules->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->view->setMessage($state);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Discover .points files from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discoverFiles()
	{
		ES::checkToken();

		$model = ES::model('Privacy');

		$paths[] = '/administrator/components';
		$paths[] = '/components';
		$paths[] = '/media/com_easysocial/apps/user';
		$paths[] = '/media/com_easysocial/apps/fields/user';
		$paths[] = '/plugins';

		$files = array();

		foreach ($paths as $path) {
			$data = $model->scan($path);

			foreach ($data as $file) {
				$files[] = $file;
			}
		}

		return $this->view->call(__FUNCTION__, $files);
	}

	/**
	 * Scans for rules throughout the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan()
	{
		ES::checkToken();

		$file = JRequest::getVar('file', '');
		$model = ES::model('Privacy');

		$obj = new stdClass();
		$obj->file = str_ireplace(JPATH_ROOT, '', $file);
		$obj->rules = $model->install($file);

		return $this->view->call(__FUNCTION__, $obj);
	}
}