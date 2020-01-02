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

class PayPlansViewRenewals extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Renewals');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('renewals.publish');
		JToolbarHelper::unpublish('renewals.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'renewals.delete');

		$model = PP::model('App');
		$options = array('type' => 'renewal');

		$renewals = $model->loadRecords($options);
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('renewals', $renewals);

		parent::display('renewals/default/default');
	}

	/**
	 * Renders the renewal form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('Renewals Form');

		JToolbarHelper::apply('renewals.apply');
		JToolbarHelper::save('renewals.save');
		JToolbarHelper::save2new('renewals.saveNew');
		JToolbarHelper::cancel('renewals.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		$app = PP::app($id);

		$this->set('activeTab', $activeTab);
		$this->set('app', $app);

		parent::display('renewals/form/default');
	}
}
