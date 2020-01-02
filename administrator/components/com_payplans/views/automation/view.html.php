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

class PayplansViewAutomation extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('automation');
	}

	public function display($tpl = null)
	{
		$this->heading('Automation Scripts');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('app.publish');
		JToolbarHelper::unpublish('app.unpublish');
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_APPS'), 'automation.delete');

		$model = PP::model('Automation');
		$model->initStates();
		
		$apps = $model->getItems();
		$pagination = $model->getPagination();

		// Get states used in this list
		$states = $this->getStates(array('search', 'published', 'type', 'limit', 'ordering', 'direction'), $model);

		$this->set('pagination', $pagination);
		$this->set('apps', $apps);
		$this->set('states', $states);

		return parent::display('automation/default/default');
	}

	/**
	 * Unique form to create new automation scripts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function create()
	{
		$this->heading('Create Automation Scripts');

		// Get a list of available payment gateways
		$model = PP::model('Automation');
		$apps = $model->getApps();

		$this->set('view', 'automation');
		$this->set('layout', 'form');
		$this->set('apps', $apps);

		return parent::display('app/create/default');
	}

	/**
	 * Unique form to create new automation scripts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('Create Automation Scripts');

		$element = $this->input->get('element', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		// Simulate editing app, since this is a new app
		JToolbarHelper::apply('automation.apply');
		JToolbarHelper::save('automation.save');
		JToolbarHelper::cancel('automation.cancel');

		$model = PP::model('App');
		$params = new JRegistry();
		$app = PP::app($id);

		if ($element) {
			$path = $model->getAppManifestPath($element);
		}

		if ($id) {
			$this->heading('Manage Automation Scripts');
			
			$path = $model->getAppManifestPath($app->type);
			$params = $app->getAppParams();
		}

		$form = PP::form('apps');
		$form->load($path, $params);

		$activeTab = $this->input->get('activeTab', '', 'word');

		$this->set('controller', 'automation');
		$this->set('element', $element);
		$this->set('form', $form);
		$this->set('activeTab', $activeTab);
		$this->set('app', $app);
		
		parent::display('app/form/default');
	}
}