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

class EasySocialViewWorkflows extends EasySocialAdminView
{
	/**
	 * Renders the list of workflows on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_ES_MENU_WORKFLOWS');

		JToolbarHelper::deleteList();

		$model = ES::model('Workflows', array('initState' => true));
		$workflows = $model->getItems();

		$pagination = $model->getPagination();
		$type = $model->getState('type');
		$search = $model->getState('search');
		$limit = $model->getState('limit');

		// Get the current ordering.
		$ordering = $this->input->get('ordering', $model->getState('ordering'));
		$direction = $this->input->get('direction', $model->getState('direction'));

		$this->set('limit', $limit);
		$this->set('search', $search);
		$this->set('type', $type);
		$this->set('workflows', $workflows);
		$this->set('pagination', $pagination);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);

		parent::display('admin/workflows/default/default');
	}

	/**
	 * Renders workflow form page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function form()
	{
		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'default');

		$workflow = ES::workflows($id, $type);
		$title = 'COM_ES_MENU_GROUP_WORKFLOWS_FORM';
		$description = '';

		$steps = $workflow->getSteps();
		$installedFields = $workflow->getInstalledFields();

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));

		if ($workflow->id) {
			JToolbarHelper::save2copy('savecopy', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AS_COPY'));
		}

		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		$this->setHeading($workflow->getTitle(), $workflow->getDescription());

		// Set custom action to edit the title and description of the workflow
		$this->setCustomAction('<a href="javascript:void(0);" data-workflow-edit-heading><i class="far fa-edit"></i></a>');

		$this->set('workflow', $workflow);
		$this->set('steps', $steps);
		$this->set('installedFields', $installedFields);

		parent::display('admin/workflows/form/default');
	}

	/**
	 * Default fields applications listing
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fields($tpl = null)
	{
		// Set the page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_APPS_FIELDS');

		// Add Joomla buttons here.
		JToolbarHelper::publishList('publish');
		JToolbarHelper::unpublishList('unpublish');
		JToolbarHelper::deleteList('', 'uninstall', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_UNINSTALL'));

		// Get the applications model.
		$model = ES::model('Apps', array('initState' => true, 'namespace' => 'apps.fields'));

		// Get the current ordering.
		$search = $this->input->get('search', $model->getState('search'));
		$state = $this->input->get('state', $model->getState('state'));
		$group = $this->input->get('group', $model->getState('group'));

		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$search = $model->getState('search');
		$group = $model->getState('group');

		// Load the applications.
		$options = array('filter' => 'fields');
		$apps = $model->getItemsWithState($options);

		// Get the pagination.
		$pagination	= $model->getPagination();

		$this->set('filter', '');
		$this->set('outdatedApps', '');
		$this->set('layout', 'fields');
		$this->set('group', $group);
		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('state', $state);
		$this->set('apps', $apps);
		$this->set('pagination', $pagination);

		parent::display('admin/workflows/fields/default');
	}

	/**
	 * Renders the custom fields form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function fieldsform()
	{
		$id = $this->input->get('id', 0, 'int');
		$app = ES::table('App');
		$app->load($id);

		if (!$id || !$app->id) {
			return $this->exception('COM_EASYSOCIAL_APP_INVALID_ID');
		}

		// Load front end's language
		ES::language()->loadSite();

		// Set the page heading
		$this->setHeading($app->_('title'), 'COM_EASYSOCIAL_DESCRIPTION_APPS_CONFIGURATION');

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::cancel();

		$access = $app->getAccess();
		$selectedAccess = $access->getAllowed();

		$showDefaultSetting = false;

		if ($app->type == SOCIAL_TYPE_APPS && !$app->system && $app->group != SOCIAL_TYPE_GROUP && $app->group != SOCIAL_TYPE_PAGE) {
			$showDefaultSetting = true;
		}

		$meta = $app->getMeta();

		$this->set('meta', $meta);
		$this->set('selectedAccess', $selectedAccess);
		$this->set('app', $app);
		$this->set('showDefaultSetting', $showDefaultSetting);

		parent::display('admin/apps/form/default');
	}

	/**
	 * Post processing after workflow is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save($workflow, $task)
	{
		// If there's an error on the storing, we don't need to perform any redirection.
		if ($this->hasErrors()) {
			return $this->form($workflow);
		}

		if ($task == 'apply') {
			return $this->redirect('index.php?option=com_easysocial&view=workflows&layout=form&id=' . $workflow->id);
		}

		if ($task == 'savenew') {
			return $this->redirect('index.php?option=com_easysocial&view=workflows&layout=form');
		}
		
		return $this->redirect('index.php?option=com_easysocial&view=workflows');
	}

	/**
	 * Post processing after workflows is deleted
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function remove()
	{
		return $this->redirect('index.php?option=com_easysocial&view=workflows');
	}
}