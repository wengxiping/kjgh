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

class PayPlansViewAddons extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Addons');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('addons.publish');
		JToolbarHelper::unpublish('addons.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'addons.delete');

		$model = PP::model('Addons');
		$model->initStates();

		$addons = $model->getItems();
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('addons', $addons);

		parent::display('addons/default/default');
	}

	/**
	 * Renders the addons usage stats
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function stats()
	{
		JToolbarHelper::back('Back', 'index.php?option=com_payplans&view=addons');

		$id = $this->input->get('id', 0, 'int');

		$model = PP::model('Addons');
		$model->initStates();

		$stats = $model->getStats($id);
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('stats', $stats);

		parent::display('addons/stats/default');
	}

	/**
	 * Renders the addons edit form
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function form()
	{
		JToolbarHelper::apply('addons.apply');
		JToolbarHelper::save('addons.save');
		JToolbarHelper::save2new('addons.saveNew');
		JToolbarHelper::cancel('addons.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		if ($id) {
			$this->heading('Addons Edit');
		} else {
			$this->heading('Addons Form');
		}

		$addon = PP::addon($id);
		$params = $addon->getParams();

		$conditions = $addon->getConditionRules();
		$priceTypes = array(0 => JText::_('COM_PP_ADDONS_PRICE_FIXED'), 1 => JText::_('COM_PP_ADDONS_PRICE_PERCENTAGE'));

		$taxesTypes = array('PERCENT_OF_SUBTOTAL_NON_TAXABLE' => JText::_('COM_PP_ADDONS_NON_TAXABLE'),
						'PERCENT_OF_SUBTOTAL_DISCOUNTABLE' => JText::_('COM_PP_ADDONS_DISCOUNTABLE'),
						'PERCENT_OF_SUBTOTAL_TAXABLE' => JText::_('COM_PP_ADDONS_TAXABLE')
						);

		$availabilityTypes = array(0 => JText::_('COM_PP_ADDONS_AVAILABILITY_UNLIMITED'), 1 => JText::_('COM_PP_ADDONS_AVAILABILITY_LIMITED'));

		$this->set('params', $params);
		$this->set('activeTab', $activeTab);
		$this->set('addon', $addon);
		$this->set('conditions', $conditions);
		$this->set('priceTypes', $priceTypes);
		$this->set('taxesTypes', $taxesTypes);
		$this->set('availabilityTypes', $availabilityTypes);

		parent::display('addons/form/default');
	}
}
