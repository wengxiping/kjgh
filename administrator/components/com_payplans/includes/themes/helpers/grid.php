<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPThemesHelperGrid extends PPThemesHelperAbstract
{
	/**
	 * Renders a check all checkbox
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkall()
	{
		$theme = PP::themes();
		$output = $theme->output('admin/helpers/grid/checkall');

		return $output;
	}

	/**
	 * Renders an empty block for table layout
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function emptyBlock($text, $columns, $center = false)
	{
		$theme = PP::themes();
		$theme->set('columns', $columns);
		$theme->set('text', $text);
		$theme->set('center', $center);

		$contents = $theme->output('admin/helpers/grid/empty.block');

		return $contents;
	}

	/**
	 * Renders a checkbox for each row in a table
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function id($number, $id, $allowed = true, $checkedOut = false, $name = 'cid')
	{
		$theme = PP::themes();
		$theme->set('allowed', $allowed);
		$theme->set('number', $number);
		$theme->set('name', $name);
		$theme->set('checkedOut', $checkedOut);
		$theme->set('id', $id);

		$contents = $theme->output('admin/helpers/grid/id');

		return $contents;
	}

	/**
	 * Renders the ordering column for table output
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ordering($total, $current, $showOrdering = '', $ordering = 0, $controllerName = '')
	{
		$theme = PP::themes();

		$theme->set('current', $current);
		$theme->set('total', $total);
		$theme->set('ordering', $ordering);
		$theme->set('showOrdering', $showOrdering);
		$theme->set('controller', $controllerName);

		$contents = $theme->output('admin/helpers/grid/ordering');

		return $contents;
	}

	/**
	 * Renders the order save button in a grid
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function order($rows, $controllerName = '')
	{
		$count = count($rows);

		if (!$rows || !$count) {
			return '';
		}

		$task = $controllerName.'.saveorder';

		$theme = PP::themes();
		$theme->set('total', $count);
		$theme->set('task', $task);

		$contents = $theme->output('admin/helpers/grid/order');
		return $contents;
	}

	/**
	 * Renders the pagination for tables
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function pagination(PPPagination $pagination, $columns)
	{
		$theme = PP::themes();
		$theme->set('columns', $columns);
		$theme->set('pagination', $pagination);

		$contents = $theme->output('admin/helpers/grid/pagination');

		return $contents;
	}

	/**
	 * Renders icon for publishing state
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function published($obj, $controllerName = '', $key = '', $tasks = array(), $tooltips = array(), $classes = array(), $allowed = true)
	{
		// If primary key is not provided, then we assume that we should use 'state' as the key property.
		$key = !empty($key) ? $key : 'state';

		// array_replace is only supported php>5.3
		// While array_replace goes by base, replacement
		// Using + changes the order where base always goes last

		$classes += array(
							-1 => 'trash',
							0 => 'unpublish',
							1 => 'publish'
					);

		$tasks += array(
							-1 => 'publish',
							0 => 'publish',
							1 => 'unpublish'
						);

		$tooltips = array(
								-1 => 'COM_PP_GRID_TOOLTIP_TRASHED_ITEM',
								0 => 'COM_PP_GRID_TOOLTIP_PUBLISH',
								1 => 'COM_PP_GRID_TOOLTIP_UNPUBLISH'
							);

		$class = isset($classes[$obj->$key]) ? $classes[$obj->$key] : '';
		$task = isset($tasks[$obj->$key]) ? $controllerName . '.' . $tasks[$obj->$key] : '';
		$tooltip = isset($tooltips[$obj->$key]) ? JText::_($tooltips[$obj->$key]) : '';

		$theme = PP::themes();
		$theme->set('allowed', $allowed);
		$theme->set('tooltip', $tooltip);
		$theme->set('task', $task);
		$theme->set('class', $class);

		return $theme->output('admin/helpers/grid/published');
	}

	/**
	 * Renders a sortable column for table heading
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function sort($column, $text, $currentOrdering, $direction = '')
	{
		$theme = PP::themes();
		$text = JText::_($text);
		$ordering = $currentOrdering;

		if (is_object($currentOrdering) && isset($currentOrdering->direction)) {
			$direction = $currentOrdering->direction;
		}

		if (is_object($currentOrdering) && isset($currentOrdering->ordering)) {
			$ordering = $currentOrdering->ordering;
		}

		// Ensure that the direction is always in lowercase because we will check for it in the theme file.
		$direction = JString::strtolower($direction);
		$ordering = JString::strtolower($ordering);
		$column = JString::strtolower($column);

		$theme->set('column', $column);
		$theme->set('text', $text);
		$theme->set('currentOrdering', $ordering);
		$theme->set('direction', $direction);

		$contents = $theme->output('admin/helpers/grid/sort');

		return $contents;
	}
}
