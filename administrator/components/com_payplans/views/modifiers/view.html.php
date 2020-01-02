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

class PayPlansViewModifiers extends PayPlansAdminView
{
	public function __construct()
	{
		parent::__construct();
		
		$this->checkAccess('plans');
	}

	public function display($tpl = null)
	{
		$this->heading('Modifiers');

		JToolbarHelper::addNew();
		JToolbarHelper::publish('modifiers.publish');
		JToolbarHelper::unpublish('modifiers.unpublish');
		JToolbarHelper::deleteList('COM_PP_DELETE_SELECTED_ITEMS', 'modifiers.delete');

		$model = PP::model('App');
		$model->initStates();

		$modifiers = $model->getAppInstances(array('type' => 'planmodifier'));
		$pagination = $model->getPagination();

		$this->set('pagination', $pagination);
		$this->set('modifiers', $modifiers);

		parent::display('modifiers/default/default');
	}

	/**
	 * Renders the modifier form
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function form()
	{
		$this->heading('New Plan Modifier');

		JToolbarHelper::apply('modifiers.apply');
		JToolbarHelper::save('modifiers.save');
		JToolbarHelper::save2new('modifiers.saveNew');
		JToolbarHelper::cancel('modifiers.cancel');

		$id = $this->input->get('id', 0, 'int');
		$activeTab = $this->input->get('active', '', 'word');

		// Load the app instance
		$app = PP::app($id);
		$options = array();

		if ($app->getId()) {
			$this->heading('Editing Plan Modifier');

			// Get the app params
			$appParams = $app->getAppParams();
			$timePrice = unserialize($appParams->get('time_price'));

			if ($timePrice) {
				foreach ($timePrice['title'] as $key => $value) {
					$obj = new stdClass;
					$obj->title = $value;
					$obj->price = $timePrice['price'][$key];
					$obj->time = $timePrice['time'][$key];

					$options[] = $obj;
				}
			}
		}

		if (empty($options)) {
			$obj = new stdClass;
			$obj->title = '';
			$obj->price = '';
			$obj->time = '';

			$options[] = $obj;
		}

		$this->set('activeTab', $activeTab);
		$this->set('app', $app);
		$this->set('options', $options);

		parent::display('modifiers/form/default');
	}

}
