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

class PayPlansViewGroup extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Groups');

		// new
		JToolbarHelper::addNew();
		JToolbarHelper::publish('group.publish');
		JToolbarHelper::unpublish('group.unpublish');
		JToolbarHelper::deleteList(JText::_('COM_PP_CONFIRM_DELETE_GROUP'), 'group.delete');
		JToolbarHelper::custom('group.copy', '', '', 'COM_PAYPLANS_TOOLBAR_COPY', true);

		$model = PP::model('Group');
		$model->initStates();

		// IMP : this is required for the pagination issue
		// we should load records after pagination is set, so that it can work well
		$model->getPagination();

		$rows = $model->getItems();

		$states = $this->getStates(array('search', 'published', 'visible', 'parent', 'ordering', 'direction', 'limit'));

		$this->set('rows', $rows);
		$this->set('pagination', $model->getPagination());
		$this->set('states', $states);

		return parent::display('group/default/default');
	}

	public function form($tpl = null)
	{
		$model = PP::model('Group'); 
		$renderEditor = $this->config->get('layout_plan_description_use_editor');

		$groupId = $this->input->get('id', null, 'int');
		$groupId = ($groupId === null) ? $model->getState('id') : $groupId;

		$activeTab = $this->input->get('active', '', 'word');

		// editing
		JToolbarHelper::apply('group.apply');
		JToolbarHelper::save('group.save');
		JToolbarHelper::save2new('group.savenew');
		JToolbarHelper::cancel('group.cancel');

		$group = PP::group($groupId);

		// setup heading.
		if ($groupId) {
			$this->heading('Edit Group');
		} else {
			$this->heading('New Group');
		}

		// Get all groups for parent selection
		$groups = PP::model('group')->loadRecords(array('group_id' => array(array('!=', $group->getId()))));

		$parentSelection = array();
		$defaultValue = new stdClass;
		$defaultValue->title = JText::_('COM_PP_SELECT_PARENT');
		$defaultValue->value = 0;

		$parentSelection[] = $defaultValue;

		foreach ($groups as $parent) {
			$parent->value = $parent->group_id;
			$parentSelection[] = $parent;
		}

		// Retrieve a list of log for this subscription
		$logModel = PP::model('Log');
		$options = array('object_id' => $groupId, 'class' => 'group', 'level' => 'all');

		$badgePositions = $this->getBadgePositions();

		$logs = $logModel->getItems($options);
		$pagination = $logModel->getPagination();

		$renderFilterBar = false;
		$ordering = $logModel->getState('ordering');
		$filter_order_Dir = $logModel->getState('filter_order_Dir');

		$params = $group->getParams();

		$this->set('activeTab', $activeTab);
		$this->set('group', $group);
		$this->set('logs', $logs);
		$this->set('badgePositions', $badgePositions);
		$this->set('parentSelection', $parentSelection);
		$this->set('params', $params);
		$this->set('pagination', $pagination);
		$this->set('renderFilterBar', $renderFilterBar);
		$this->set('filter_order', $ordering);
		$this->set('filter_order_Dir', $filter_order_Dir);
		$this->set('renderEditor', $renderEditor);

		parent::display('group/form/default');
		return true;
	}

	/**
	 * Get available badge positions
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getBadgePositions()
	{
		$data = array(
					'right' => 'COM_PP_PLAN_EDIT_PLAN_BADGE_POSITION_TOP_RIGHT',
					'left' => 'COM_PP_PLAN_EDIT_PLAN_BADGE_POSITION_TOP_LEFT',
					'center' => 'COM_PP_PLAN_EDIT_PLAN_BADGE_POSITION_TOP_CENTER'
				);

		$positions = array();

		foreach ($data as $key => $title) {
			$obj = new stdClass();

			$obj->title = JText::_($title);
			$obj->value = $key;

			$positions[] = $obj;
		}

		return $positions;
	}
}
