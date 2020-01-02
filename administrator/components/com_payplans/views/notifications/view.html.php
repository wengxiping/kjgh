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

class PayplansViewNotifications extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('notifications');
	}

	public function display($tpl = null)
	{
		$this->heading('Notifications');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('app.publish');
		JToolbarHelper::unpublish('app.unpublish');
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_NOTIFICATION_RULE'), 'notifications.delete');
		$model = PP::model('Notifications');
		$model->initStates();

		// Get only apps related to payments
		$apps = $model->getItems();
		$pagination = $model->getPagination();

		$states = $this->getStates(array('search', 'published', 'type', 'limit', 'ordering', 'direction'), $model);
		
		$this->set('pagination', $pagination);
		$this->set('apps', $apps);
		$this->set('states', $states);

		return parent::display('notifications/default/default');
	}

	/**
	 * Renders a list of e-mail templates available on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function templates($tpl = null)
	{
		$this->heading('Notification Templates');

		JToolbarHelper::addNew();

		$model = PP::model('Notifications');

		// Get only apps related to payments
		$files = $model->getFiles();

		$this->set('files', $files);

		return parent::display('notifications/templates/default');
	}

	/**
	 * Renders the template file editing form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function editFile($tpl = null)
	{
		$this->heading('Notification Templates');

		JToolbarHelper::apply('notifications.applyFile');
		JToolbarHelper::save('notifications.saveFile');
		JToolbarHelper::cancel('notifications.cancel');

		$file = $this->input->get('file', '', 'default');
		
		$data = new stdClass();
		$data->name = '';
		$data->contents = ' ';
		$data->relative = '';
		
		if ($file) {
			$file = urldecode($file);

			$model = PP::model('Notifications');
			$absolutePath = $model->getFolder() . '/' . $file;

			$data = $model->getFileObject($absolutePath, true);
		}

		// Always use codemirror
		$editor = JFactory::getEditor('codemirror');

		$this->set('editor', $editor);
		$this->set('data', $data);

		return parent::display('notifications/editfile/default');
	}

	/**
	 * Unique form to create new notification rules
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function create()
	{
		$this->heading('Create Notification Rule');

		$element = $this->input->get('element', '', 'default');

		if (!$element) {
			// Get a list of available payment gateways
			$model = PP::model('Notifications');
			$apps = $model->getApps();

			$this->set('view', 'notifications');
			$this->set('layout', 'create');
			$this->set('apps', $apps);

			return parent::display('app/create/default');
		}

		$this->form();
	}

	/**
	 * Renders the form to edit the notification rules
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');

		$this->heading('Edit Notification Rule');

		JToolbarHelper::apply('notifications.apply');
		JToolbarHelper::save('notifications.save');
		JToolbarHelper::cancel('notifications.cancel');

		$activeTab = $this->input->get('activeTab', '', 'word');
		$app = PP::app($id);

		$params = $app->getAppParams();
		$when = $this->input->get('element', $params->get('when_to_email', 'on_status'));

		$this->set('app', $app);
		$this->set('params', $params);
		$this->set('when', $when);
		$this->set('controller', 'notifications');
		$this->set('activeTab', $activeTab);

		return parent::display('notifications/form/default');
	}
}