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

class PPThemesHelperFilter extends PPThemesHelperAbstract
{
	/**
	 * Renders the date range filter
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function dateRange($selected = '', $name = 'dateRange', $placeholder = '')
	{
		if (!$placeholder) {
			$placeholder = 'COM_PP_SELECT_DATE_RANGE';
		}

		$placeholder = JText::_($placeholder);

		// Get today
		$start = false;
		$end = false;

		if ($selected && is_array($selected)) {
			$start = $selected['start'];
			$end = $selected['end'];
		}

		$uid = uniqid();

		$theme = PP::themes();
		$theme->set('uid', $uid);
		$theme->set('start', $start);
		$theme->set('end', $end);
		$theme->set('name', $name);
		$theme->set('placeholder', $placeholder);
		$theme->set('selected', $selected);
		$output = $theme->output('admin/helpers/filters/daterange');

		return $output;
	}

	/**
	 * Renders the items per page selection
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function limit($selected = 5, $name = 'limit', $step = 5, $min = 5, $max = 100, $showAll = true)
	{
		$theme = PP::themes();
		$theme->set('selected', $selected);
		$theme->set('name', $name);
		$theme->set('step', $step);
		$theme->set('min', $min);
		$theme->set('max', $max);
		$theme->set('showAll', $showAll);

		$contents = $theme->output('admin/helpers/filters/limit');

		return $contents;
	}

	/**
	 * Renders a dropdown list for filters
	 *
	 * @since	4.0
	 * @access	public
	 */
	public static function lists($items = array(), $name = 'listitem', $selected = 'all', $initial = '', $initialValue = 'all')
	{
		$theme = PP::themes();
		$initial = JText::_($initial);
		$options = array();

		if ($items) {
			foreach ($items as $item) {
				$object = (object) $item;

				$options[] = $object;
			}
		}

		$theme->set('initialValue', $initialValue);
		$theme->set('initial', $initial);
		$theme->set('name', $name);
		$theme->set('options', $options);
		$theme->set('selected', $selected);

		$contents = $theme->output('admin/helpers/filters/list');

		return $contents;
	}

	/**
	 * Renders the published dropdown on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function published($name = 'state', $selected = 'all', $extras = array())
	{
		$options = array();

		if ($extras) {
			foreach ($extras as $extra) {
				$obj = (object) $extra;

				$options[] = $obj;
			}
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/published');

		return $contents;
	}

	/**
	 * Renders the search form on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function search($value = '', $name = 'search')
	{
		$theme = PP::themes();

		$theme->set('value', $value);
		$theme->set('name', $name);

		$contents = $theme->output('admin/helpers/filters/search');

		return $contents;
	}

	/**
	 * Renders the username form on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function username($value = '', $name = 'username')
	{
		$theme = PP::themes();

		$theme->set('value', $value);
		$theme->set('name', $name);

		$contents = $theme->output('admin/helpers/filters/username');

		return $contents;
	}

	/**
	 * Renders the invoice id on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function invoice($value = '', $name = 'invoice_id')
	{
		$theme = PP::themes();

		$theme->set('value', $value);
		$theme->set('name', $name);

		$contents = $theme->output('admin/helpers/filters/invoice');

		return $contents;
	}

	/**
	 * Renders the group list on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function group($name = 'group', $selected = '', $extras = array(), $attr = array())
	{
		$options = array();

		$model = PP::model('Group');
		$groups = $model->getGroups();

		if (isset($attr['none'])) {
			$option = new stdClass();
			$option->title = JText::_($attr['none']);
			$option->value = '';

			$options[] = $option;
		}

		foreach ($groups as $group) {
			$option = new stdClass();
			$option->title = JText::_($group->title);
			$option->value = $group->group_id;

			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/group');

		return $contents;
	}

	/**
	 * Renders the plan list on table listings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function plans($name = 'plan', $selected = '', $extras = array(), $attr = array())
	{
		$options = array();
		$model = PP::model('Plan');
		$plans = $model->loadRecords(array(), array('where', 'limit'));

		// Default option
		$option = new stdClass();
		$option->title = JText::_('COM_PP_SELECT_PLAN');
		$option->value = '';

		$options[] = $option;

		foreach ($plans as $plan) {
			$option = new stdClass();
			$option->title = JText::_($plan->title);
			$option->value = $plan->plan_id;

			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/plan');

		return $contents;
	}

	/**
	 * Renders a status listing on grid layouts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function status($name = 'status', $selected = '', $entity, $exclude = '', $attr = array())
	{ 
		$options = array();
		$statuses = PP::getStatuses($entity);

		if ($exclude) {

			// @TODO: Refactor the exclusions
			//dump($exclude);
			$exclude = explode(",", $exclude);

			// It will remove any particular status+ any entity related fields.
			// underscore(_) was there in order to handle entity related data like PAYMENT_ and not remove any other string containg payment word
			// but as we have added ^ symbol, means that remove from starting, so, so no need to add underscore.
			foreach ($statuses as $key => $val) {
				foreach ($exclude as $exc) {
					if (preg_match("/^{$exc}/i", $key))
						unset($statuses[$key]);
				}
			}
		}

		$option = new stdClass();
		$option->title = JText::_('COM_PP_SELECT_STATUS');
		$option->value = -1;
		$option->selected = $selected === null || $selected == -1 ? true : false;
		
		$options[] = $option;

		foreach ($statuses as $key => $value) {
			$option = new stdClass();
			$option->title = JText::_('COM_PP_' . strtoupper($key));
			$option->value = $value;
			$option->selected = $selected == $value && !is_null($selected);
			
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/status');

		return $contents;
	}

	/**
	 * Renders the payplans app's on table listings
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function apps($name = 'app_id', $selected = '',  $group = '', $attr = array(), $ignoreType = array())
	{
		$options = array();

		$model = PP::model('app');
		$apps = $model->getItemsWithoutState(array('published' => '', 'group' => $group));

		$defaultOption = new stdClass();
		$defaultOption->title = JText::_('COM_PAYPLANS_SELECT_APPS');
		$defaultOption->value = 0;

		if (isset($attr['none']) && !empty($attr['none'])) {
			$defaultOption->title = $attr['none'];
		}

		$options[] = $defaultOption;
		
		foreach ($apps as $app) {
			$option = new stdClass();
			$option->title = PPFormats::app($app);
			$option->value = $app->type;
			
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/apps');

		return $contents;
	}

	/**
	 * Renders the filter for payment methods
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function gateways($name = 'app_id', $selected = '',  $types = '', $attr = array(), $ignoreType = array())
	{
		$options = array();

		$model = PP::model('Gateways');
		$apps = $model->getItemsWithoutState(array('published' => 1));

		$defaultOption = new stdClass();
		$defaultOption->title = JText::_('COM_PAYPLANS_SELECT_APPS');
		$defaultOption->value = 0;

		if (isset($attr['none']) && !empty($attr['none'])) {
			$defaultOption->title = $attr['none'];
		}

		$options[] = $defaultOption;
		
		foreach ($apps as $app) {
			$option = new stdClass();
			$option->title = PPFormats::app($app);
			$option->value = $app->app_id;
			
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/apps');

		return $contents;
	}

	/**
	 * Renders the usertype's on table listings
	 * @since	4.0
	 * @access	public
	 */
	public function usertype($name = 'usertype', $selected = '', $attr=null, $ignore=array())
	{
		$options = array();
		
		$groups = XiHelperJoomla::getUsertype();

		if (isset($attr['none'])) {
			$option = new stdClass();
			$option->title = JText::_('COM_PAYPLANS_SELECT_USERTYPE');
			$option->value = 0;
			
			$options[] = $option;		
		}
		
		foreach ($groups as $value) {
			$option = new stdClass();
			$option->title = $value;
			$option->value = $value;
			
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/usertype');

		return $contents;
	}


	/**
	 * Renders the payplans log levels on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function loglevel($name = 'loglevel', $view = 'log', $selected = '', $prefix = 'filter_payplans', $attr = "")
	{ 
		$options = array();

		$levels = PP::logger()->getLevels();

		if (isset($attr['none'])) {
			$option = new stdClass();
			$option->title = JText::_('COM_PAYPLANS_SELECT_LOGLEVEL');
			$option->value = 'all';
			
			$options[] = $option;
		}

		foreach ($levels as $key => $value) {
			$option = new stdClass();
			$option->title = $value;
			$option->value = $key;
			
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/loglevel');

		return $contents;
			
	}

	/**
	 * Renders the payplans log class on table listings
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function logclass($name = 'logclass', $view = 'log', $selected = '', $prefix = 'filter_payplans', $attr = "")
	{ 
		$options = array();

		// Retrieve a list of class log
		$classes = PP::log()->getClassLog();

		if (isset($attr['none'])) {
			$option = new stdClass();
			$option->title = JText::_('COM_PAYPLANS_SELECT_LOGCLASS');
			$option->value = 0;
			
			$options[] = $option;
		}

		foreach ($classes as $value) {
			$option = new stdClass();
			$option->title = $value;
			$option->value = $value;
			$options[] = $option;
		}

		$theme = PP::themes();
		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/helpers/filters/logclass');

		return $contents;
			
	}

}
