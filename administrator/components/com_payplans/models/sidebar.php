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

PP::import('admin:/includes/model');

class PayPlansModelSidebar extends PayPlansModel
{
	static $path = null;

	public function __construct()
	{
		self::$path = PP_DEFAULTS . '/sidebar.json';

		parent::__construct('sidebar');
	}

	/**
	 * Retrieves the contents of the sidebar json file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getDefaultSidebarItems()
	{
		static $menu = null;

		if (is_null($menu)) {
			$contents = JFile::read(self::$path);
			$menu = json_decode($contents);			
		}

		return $menu;
	}

	/**
	 * Returns a list of menus for the admin sidebar.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItems($currentView, $currentLayout = '')
	{
		$result = array();

		$items = self::getDefaultSidebarItems();
		$my = JFactory::getUser();

		foreach ($items as $item) {

			$uid = uniqid();

			$obj = clone($item);
			$obj->uid = $uid;
			$obj->count	= 0;

			if (isset($item->access) && !empty($item->access)) {
				$authorized = $my->authorise('payplans.' . $item->access, 'com_payplans');

				if (!$authorized) {
					continue;
				}
			}

			// Test if there's a counter key.
			if (isset($obj->counter)) {
				$obj->count = $this->getCount($obj->counter);
			}

			$obj->views = array($item->view);

			if (is_array($item->view)) {
				$obj->views = $item->view;
			}

			$obj->active = in_array($currentView, $obj->views);

			if (!empty($obj->childs)) {

				foreach ($obj->childs as &$child) {

					// Initialize the counter
					$child->count = 0;

					// Check if there's any sql queries to execute.
					if (isset($child->counter)) {
						$child->count = $this->getCount($child->counter);
					}

					// Add a unique id for the side bar for accordion purposes.
					$child->uid = $uid;

					$child->views = array();

					if (isset($child->view)) {

						if (is_array($child->view)) {
							$obj->views = array_merge($obj->views, $child->view);
							$child->views = array_merge($child->views, $child->view);
						} else {
							array_push($obj->views, $child->view);
							$child->views[] = $child->view;
						}
					}

					$child->active = false;

					if (isset($child->view) && in_array($currentView, $child->views) && !isset($child->layouts)) {
						$child->active = true;
						$obj->active = true;
					}

					// if (isset($child->layout) && $child->layout == $currentLayout) {
					// 	$child->active = true;
					// }

					if (isset($child->view) && $child->view == $currentView && isset($child->layouts) && in_array($currentLayout, $child->layouts)) {
						$child->active = true;
					}
				}
			}

			$obj->views = array_unique($obj->views);

			$result[] = $obj;
		}

		return $result;
	}

	/**
	 * Retrieves a specific count item based on the namespace
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCount($namespace)
	{
		list($modelName, $method) = explode('/', $namespace);

		$model = PP::model($modelName);
		$count = $model->$method();

		return $count;
	}
}
