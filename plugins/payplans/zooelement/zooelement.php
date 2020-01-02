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

class plgPayplansZooelement extends PPPlugins
{
	const NONE  = -1;

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if (!$this->exists()) {
			return;
		}

		require_once dirname(__FILE__) . '/zooelement/model.php';
		require_once dirname(__FILE__) . '/zooelement/table.php';
	}

	/**
	 * Retrieve the model
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getModel()
	{
		$model =  new PayPlansModelZooitemelement('Zooitemelement');
		return $model;
	}

	/**
	 * Retrieve the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getTable()
	{
		$table = new PayplansTableZooitemelement();
		return $table;
	}

	/**
	 * Determine if zoo is exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_zoo');
		$path = JFile::exists(JPATH_ROOT . '/components/com_zoo/zoo.php');

		if (!$enabled || !$path) {
			return false;
		}

		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		// Make sure App class from Zoo exists
		$appClass = class_exists('App');

		if (!$appClass) {
			return false;
		}

		return true;
	}

	/**
	 * Prerequisite processing after payplan is starting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSystemStart()
	{
		// make sure ZOO exists
		if (!$this->exists()) {
			return;
		}

		// Load zoo config
		require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

		// Here are a number of events for demonstration purposes.
		// Have a look at administrator/components/com_zoo/config.php
		// and also at administrator/components/com_zoo/events/

		// Get the ZOO App instance
		$zoo = App::getInstance('zoo');

		$zoo->event->register('ElementEvent');

		$zoo->event->dispatcher->connect('element:beforedisplay', array('plgPayplansZooelement', 'onZooElementBeforeDisplay'));
		$zoo->event->dispatcher->connect('element:afteredit', array('plgPayplansZooelement', 'onZooElementAfterEdit'));
		$zoo->event->dispatcher->connect('element:configform', array('plgPayplansZooelement', 'onZooElementConfigForm'));
		$zoo->event->dispatcher->connect('element:download', array('plgPayplansZooelement', 'onZooElementDownload'));

		// item saved trigger
		$zoo->event->dispatcher->connect('item:saved', array('plgPayplansZooelement', 'onZooItemSaved'));

		return true;
	}

	/**
	 * Process trigger after zoo item is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onZooItemSaved($event)
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$use = $input->get('use_payplans_with_zoo_item', false, 'int');

		if (!$use) {
			return true;
		}

		$item = $event->getSubject();
		$new = $event['new'];

		$ppplans = $input->get('ppplans', array(), 'array');

		$data = array();
		$data['itemid'] = $item->id;
		$data['params'] = serialize($ppplans);

		$table = self::getTable();

		$table->delete($data['itemid']);
		$table->load(array('itemid' => $item->id));

		if (!$table->itemid) {
			$table->itemid = $item->id;
		}

		$table->params = serialize($ppplans);
		$table->store();
	}

	/**
	 * Process trigger after zoo element is edited
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onZooElementAfterEdit($event)
	{
		$element = $event->getSubject();
		$config  = json_decode($element->config);
		$itemid = $element->getItem()->id;
		$selected = array();

		$selected = array();
		if ($itemid) {
			$selected = self::_getItemParams($itemid);
		}

		if (!isset($selected[$element->identifier])) {
			$selected[$element->identifier] = array('plans' => '', 'action' => 'show');
		}

		if (!isset($selected[$element->identifier]['plans'])) {
			$selected[$element->identifier]['plans'] = array();
		}

		if (!isset($selected[$element->identifier]['action'])) {
			$selected[$element->identifier]['action'] = 'show';
		}

		// if ppplans is not set or does not contain any value
		// it means it is available to all users, then do nothing 
		if(!isset($config->enableppplans) || !$config->enableppplans){
			return true;
		}

		// html of actions
		$actionOptions = array(
			array('value' => 'show', 'text' => JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_SHOW')),
			array('value' => 'hide', 'text' => JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_HIDE'))
		);

		$plans = PP::plan()->getPlans(true, true);
		$formattedPlans = array();

		$defaultPlan = new stdClass();
		$defaultPlan->plan_id = -1;
		$defaultPlan->title = JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_PLANS_NONE');

		$formattedPlans[] = $defaultPlan;

		foreach ($plans as $plan) {
			$obj = new stdClass();
			$obj->plan_id = $plan->getId();
			$obj->title = $plan->getTitle();

			$formattedPlans[] = $obj;
		}

		$html = $event['html'];
		$pop = array_pop($html);
		$html[] = '<strong>&nbsp;</strong>
					<div class="more-options">
						<div class="trigger">
							<div>
								<div class="advanced button hide">' . JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_HIDE_PLANS') . '</div>
								<div class="advanced button">' . JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_SHOW_PLANS') . '</div>
							</div>
						</div>
						<div class="advanced options">' .
							JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_PLAN') . '
							<div class="row short pp-plans">' .
								$element->app->html->_('control.genericList', $formattedPlans, 'ppplans[' . $element->identifier . '][plans][]', 'multiple="true"', 'plan_id', 'title', $selected[$element->identifier]['plans']) . '
							</div>
							<div class="row short pp-plans">' .
								JText::_('COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION') .
								$element->app->html->_('control.genericList', $actionOptions, 'ppplans['.$element->identifier.'][action]', '', 'value', 'text', $selected[$element->identifier]['action']) . '
							</div>
						</div>
					</div>';
		$html[] = $pop;

		static $hiddenfield = false;

		if ($hiddenfield === false) {
			$html[] = '<input type="hidden" name="use_payplans_with_zoo_item" value="1" />';
			$hiddenfield = true;
		}

		$event['html'] = $html;
	}

	/**
	 * Trigger before the zoo element display
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onZooElementBeforeDisplay($event)
	{
		// dump('asdsa');
		$item = $event->getSubject();
		$element = $event['element'];

		$event['render'] = self::_isAllowed($item, $element);
		return true;
	}

	/**
	 * Process trigger during zoo element download
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function onZooElementDownload($event)
	{
		$element = $event->getSubject();
		$item = $element->getItem();

		$event['canDownload'] = self::_isAllowed($item,$element);

		if (!$event['canDownload']) {
			$msg = JText::_('COM_PAYPLANS_APP_ZOO_ELEMENT_NO_DIRECT_DOWNLOAD');
			$this->app->redirect(PPR::_('index.php?option=com_payplans&view=plan&task=subscribe'), $msg);

			return true;
		}

		return true;
	}

	public static function onZooElementConfigForm($event)
	{		
		$xmlString	= '<element type="element" group="Payplans" hidden="true">
							<params>
								<param 	name="action" 
										type="list" 
										label="COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_LABEL"
										description="COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_DESC">
										<option value="show">COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_OPTION_SHOW</option>
										<option value="hide">COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_ACTION_OPTION_HIDE</option>
								</param>
								
								<param 	name="ppplans" 
										type="plans" 
										label="COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_PLAN"
										description="COM_PAYPLANS_PLG_ZOO_ELEMENT_SELECT_PLAN_DESC"/>
										
								<param 	name="enableppplans" 
										type="radio" 
										default="0"
										label="COM_PAYPLANS_PLG_ZOO_ELEMENT_ENABLE_PLANS_SELECTION"
										description="COM_PAYPLANS_PLG_ZOO_ELEMENT_ENABLE_PLANS_SELECTION_DESC">
										<option value="0">COM_PAYPLANS_NO</option>
										<option value="1">COM_PAYPLANS_YES</option>
								</param>
							</params>
						</element>
				  	';
		
		$element = $event->getSubject();
		$element->app->path->register(dirname(__FILE__).'/zooelement/fields', 'fields');
		$form = $event['form'];
		$form->addXml($xmlString);
	}

	protected static function _getItemParams($itemid)
	{
		static $params = array();

		if (!isset($param[$itemid])) {
			$model = self::getModel();
			$records = $model->loadRecords(array('itemid' => $itemid));

			if (count($records)) {
				$record = array_shift($records);
				$params[$itemid] = unserialize($record->params);
			} else {
				$params[$itemid] = array();
			}
		}

		return $params[$itemid];
	}

	protected static function _getUserPlans(PPUser $user)
	{
		// get the users plan
		static $userplans = null;

		if (!isset($userplans[$user->getId()])) {
			$plans = $user->getPlans();
			$ids = array();

			foreach ($plans as $plan) {
				$ids[] = $plan->getId();
			}

			$userplans[$user->getId()] = $ids;
		}

		return $userplans[$user->getId()];
	}

	public static function _isAllowed($item, $element)
	{
		$user = PP::user();

		// Admin can view everything
		if ($user->isAdmin()) {
			return true;
		}

		// check for individual item
		$itemid = $item->id;
		$plansConfig = self::_getItemParams($itemid);

		// Ensure that the element is valid for the plans configuration
		$identifier = $element->identifier;
		if (!isset($plansConfig[$identifier])) {
			return true;
		}

		// Retrieve user's plans
		$userplans = self::_getUserPlans($user);

		// if action is not set or its set to "show" then element should be shown to only subscriber
		// so action should be false
		$action = false;
		if (isset($plansConfig[$identifier]['action']) && $plansConfig[$identifier]['action'] == 'hide') {
			$action = true;
		}

		// if user do not have any plans then element should not be renderred
		if (!count($userplans)) {
			return $action;
		}

		$plans = isset($plansConfig[$identifier]['plans']) ? $plansConfig[$identifier]['plans'] : array();
		$plans = is_array($plans) ? $plans : array($plans);

		if (in_array(self::NONE, $plans) || count(array_intersect($userplans, $plans)) == 0) {
			return $action;
		}

		// if above conditions are not true then return inverse action
		return !$action;
	}
}
