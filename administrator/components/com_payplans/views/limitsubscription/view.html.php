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

class PayPlansViewLimitsubscription extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Limit Subscription');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('limitsubscription.publish');
		JToolbarHelper::unpublish('limitsubscription.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'limitsubscription.delete');

		$model = PP::model('App');
		$options = array('type' => 'limitsubscription');

		$limitsubscriptions = $model->loadRecords($options);

		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('limitsubscriptions', $limitsubscriptions);

		parent::display('limitsubscription/default/default');
	}

	/**
	 * Renders the upgrade form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('Limit Subscription Form');

		JToolbarHelper::apply('limitsubscription.apply');
		JToolbarHelper::save('limitsubscription.save');
		JToolbarHelper::save2new('limitsubscription.saveNew');
		JToolbarHelper::cancel('limitsubscription.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		$app = PP::app($id);
		$appParams = $app->app_params;

		$this->set('activeTab', $activeTab);
		$this->set('app', $app);
		$this->set('appParams', $appParams);

		parent::display('limitsubscription/form/default');
	}
}
