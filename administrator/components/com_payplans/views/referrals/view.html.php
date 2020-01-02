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

class PayPlansViewReferrals extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('referrals');
	}

	public function display($tpl = null)
	{
		$this->heading('Referral');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_SELECTED_ITEMS'), 'referrals.delete');

		$model = PP::model('Referrals');
		$apps = $model->getItems();
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('apps', $apps);

		parent::display('referrals/default/default');
	}

	/**
	 * Renders the upgrade form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form()
	{
		$this->heading('Referrals Form');

		JToolbarHelper::apply('referrals.apply');
		JToolbarHelper::save('referrals.save');
		JToolbarHelper::cancel('referrals.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('activeTab', '', 'default');

		$app = PP::app($id);
		$params = $app->getAppParams();

		$this->set('params', $params);
		$this->set('app', $app);
		$this->set('activeTab', $activeTab);

		parent::display('referrals/form/default');
	}
}
