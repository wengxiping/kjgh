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

class PayPlansViewDiscounts extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('discounts');
	}

	public function display($tpl = null)
	{
		$this->heading('Discounts');

		JToolbarHelper::addNew();
		JToolbarHelper::deleteList(JText::_('COM_PP_DELETE_SELECTED_ITEMS'), 'discounts.delete');

		$model = PP::model('Discount');
		$model->initStates();

		$discounts = $model->getItems();

		$pagination = $model->getPagination();

		if ($discounts) {
			foreach ($discounts as &$discount) {
				$discount = PP::discount($discount);
			}
		}

		// Get states used in this list
		$states = $this->getStates(array('search', 'published', 'ordering', 'direction', 'limit'), $model);

		$this->set('states', $states);
		$this->set('pagination', $pagination);
		$this->set('discounts', $discounts);

		parent::display('discounts/default/default');
	}

	/**
	 * Renders the upgrade form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function form($generator = false)
	{
		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		$this->heading('Discounts Form');

		$discount = PP::discount($id);
		$params = $discount->getParams();

		$model = PP::model('Discount');
		$types = $model->getCouponTypes();

		// Retrieve a list of user who consumed this discount code
		$consumersData = $discount->getConsumption(false);

		// here we need to trigger other plugins incase there is any plugin related to discount type. e.g. invitex
		$arg = array(&$types);
		PP::event()->trigger('onPayplansGetDiscountType', $arg);

		if ($generator) {
			JToolbarHelper::apply('discounts.generate', JText::_('Generate Coupons'));

			unset($types['autodiscount_onrenewal'], $types['autodiscount_onupgrade'], $types['autodiscount_oninvoicecreation'], $types['discount_for_time_extend']);
		}

		if (!$generator) {
			JToolbarHelper::apply('discounts.apply');
			JToolbarHelper::save('discounts.save');
			JToolbarHelper::save2new('discounts.saveNew');
		}

		JToolbarHelper::cancel('discounts.cancel');

		$this->set('generator', $generator);
		$this->set('params', $params);
		$this->set('types', $types);
		$this->set('activeTab', $activeTab);
		$this->set('discount', $discount);
		$this->set('isEdit', $id);
		$this->set('consumersData', $consumersData);

		parent::display('discounts/form/default');
	}

	/**
	 * Renders the upgrade form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generator()
	{
		return $this->form(true);
	}
}
