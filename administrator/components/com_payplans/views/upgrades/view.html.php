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

class PayPlansViewUpgrades extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Upgrades');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('upgrades.publish');
		JToolbarHelper::unpublish('upgrades.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'upgrades.delete');

		$model = PP::model('App');
		$options = array('type' => 'upgrade');

		$upgrades = $model->loadRecords($options);

		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('upgrades', $upgrades);

		parent::display('upgrades/default/default');
	}

	/**
	 * Renders the upgrade form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('Upgrades Form');

		JToolbarHelper::apply('upgrades.apply');
		JToolbarHelper::save('upgrades.save');
		JToolbarHelper::save2new('upgrades.saveNew');
		JToolbarHelper::cancel('upgrades.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		$app = PP::app($id);
		$appParams = $app->app_params;

		$this->set('activeTab', $activeTab);
		$this->set('app', $app);
		$this->set('appParams', $appParams);

		parent::display('upgrades/form/default');
	}
}
