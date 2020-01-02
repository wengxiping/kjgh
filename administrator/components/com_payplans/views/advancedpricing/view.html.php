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

class PayPlansViewAdvancedpricing extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}
	
	public function display($tpl = null)
	{
		$this->heading('Advanced Pricing');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('advancedpricing.publish');
		JToolbarHelper::unpublish('advancedpricing.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'advancedpricing.delete');

		$model = PP::model('Advancedpricing');

		$results = $model->getItems();
		$items = array();

		foreach ($results as $result) {
			$itemPlans = array();
			if ($result->assignedPlans) {
				foreach ($result->assignedPlans as $planId) {
					if (!$planId) {
						continue;
					}

					$plan = PP::plan($planId);
					$itemPlans[] = $plan;

				}
			}
			$result->plans = $itemPlans;
			$items[] = $result;
		}

		$this->set('items', $items);

		parent::display('advancedpricing/default/default');
	}

	/**
	 * Renders the modifier form
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function form()
	{
		$this->heading('New Advanced Pricing');

		JToolbarHelper::apply('advancedpricing.apply');
		JToolbarHelper::save('advancedpricing.save');
		JToolbarHelper::save2new('advancedpricing.saveNew');
		JToolbarHelper::cancel('advancedpricing.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		// Load the instance
		$item = PP::table('Advancedpricing');
		$item->load($id);

		$plans = array();
		$priceSet = array();

		if ($item->advancedpricing_id) {
			$this->heading('Editing Advanced Pricing');

			$tmp = json_decode($item->plans);

			if (is_null($tmp)) {
				// Legacy data
				$itemPlans = explode(',', $item->plans);
			} else {
				$itemPlans = $tmp;
			}
			
			if ($itemPlans) {
				foreach ($itemPlans as $planId) {
					if (!$planId) {
						continue;
					}

					$plans[] = $planId;
				}
			}

			// Get the params
			$params = new JRegistry($item->params);

			$prices = $params->get('price');
			$durations = $params->get('expiration_time');

			for ($i=0; $i < count($durations); $i++) { 
				$priceSet[] = array('duration' => $durations[$i], 'price' => $prices[$i]);
			}
		}

		if (empty($priceSet)) {
			$priceSet[] = array('duration' => '', 'price' => '');
		}

		$this->set('activeTab', $activeTab);
		$this->set('item', $item);
		$this->set('plans', $plans);
		$this->set('priceSet', $priceSet);

		parent::display('advancedpricing/form/default');
	}
}
