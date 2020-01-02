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

jimport('joomla.filesystem.file');

class PPHelperK2item extends PPHelperStandardApp
{
	const DO_NOTHING = -1;
	const ALLOWED = 1;
	const BLOCKED = 0;
	protected $_location = __FILE__;

	/**
	 * Determines if K2 exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_k2');
			$folder = JPATH_ROOT . '/components/com_k2';
			$folderExists = JFolder::exists($folder);
			$exists = false;

			if ($enabled && $folderExists) {
				$exists = true;
			}
		}

		return $exists;
	}

	/**
	 * Determines if the given category id 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCategoryApplicable($categoryId)
	{
		$categories = $this->getChildCategoriesAndSelf($categoryId);
		$disallowed = $this->getDisallowedCategories();

		if (array_intersect($disallowed, $categories)) {
			return true;
		}
		
		return false;
	}

	/**
	 * Retrieves a list of disallowed categories from K2 because
	 * there are categories associated with the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDisallowedCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$categories = $this->params->get('restricted_category', array());

			if (!is_array($categories) && $categories) {
				$categories = array($categories);
			}
		}

		return $categories;
	}

	/**
	 * Retrieves a list of disallowed k2items from K2 because
	 * there are k2items associated with the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDisallowedItems()
	{
		static $k2items = null;

		if (is_null($k2items)) {
			$k2items = $this->params->get('k2item', array());

			if (!is_array($k2items) && $k2items) {
				$k2items = array($k2items);
			}
		}

		return $k2items;
	}

	/**
	 * Get categories that are accessible by the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccessibleCategories(PPUser $user)
	{
		$allCategories = $this->getAllCategories();
		$categories = array();

		foreach ($allCategories as $categoryId) {
			$allowed = $this->isAllowed($user, $categoryId, 'category');

			// Check if there is atleast one app that is allowing this item
			if (in_array(self::ALLOWED, $allowed)) {
				$categories[$categoryId] = $categoryId;
			}

			// None of the app allow it, we shall proceed.
			if (in_array(self::BLOCKED, $allowed)) {
				continue;
			}

			// If it reach here means the item is not configured in any of the k2 app instance.
			$categories[$categoryId] = $categoryId;
		}

		return $categories;
	}

	/**
	 * Get categories that are accessible by the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccessibleItems(PPUser $user)
	{
		$items = $this->getAllItems();

		foreach ($items as $item) {

			$allowed = $this->isAllowed($user, $item->id, 'article');
			$count = array_count_values($allowed);

			// Check if there is atleast one app that is allowing this item
			if (in_array(self::ALLOWED, $allowed)) {
				continue;
			}

			// None of the app allow it, we shall proceed.
			if (in_array(self::BLOCKED, $allowed)) {
				unset($items[$item->id]);
			}
		}

		return $items;
	}

	/**
	 * Retrieves a list of k2 items
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getAllItems()
	{
		$db = PP::db();
		$query = 'SELECT `id`, `title` FROM ' . $db->qn('#__k2_items') . ' where ' . $db->qn('trash') . '=' . $db->Quote(0);
		$db->setQuery($query);
		
		return $db->loadObjectList('id');
	}

	/**
	 * Retrieves all categories from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$db = PP::db();
			$query = 'SELECT `id` FROM `#__k2_categories` WHERE `published` = 1';
			$db->setQuery($query);
			$categories = $db->loadColumn();
		}

		return $categories;
	}

	/**
	 * Determines if the user allowed to view this item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isAllowed(PPUser $user, $item, $type = 'article')
	{
		$apps = $this->getAvailableApps('k2item');

		if (!$apps) {
			return array(SELF::ALLOWED);
		}
		$result = array();
		$plans = $user->getPlans();

		// We would want to get the plan_id only
		$userPlanId = array();
		
		foreach ($plans as $plan) {
			$userPlanId[] = $plan->plan_id;
		}

		// Check which app allow item, which not allow and which app do nothing
		foreach ($apps as $app) {

			if ($type == 'category') {
				$disallowedItems = $app->helper->getDisallowedCategories();
			} else {
				$disallowedItems = $app->helper->getDisallowedItems();
			}

			// If app is not applicable on that item 
			if (count(array_intersect(array($item), $disallowedItems)) == 0) {
				$result[] = self::DO_NOTHING;
				continue;
			}

			// If user has no plans, we assume that they should be blocked
			if (!$plans) {
				$result[] = self::BLOCKED;
				continue;
			}

			// If user has plans and app is core app 
			if ($plans && $app->getParam('applyAll') != false) {
				$result[]  = self::ALLOWED;
				continue;
			}

			$appPlans = $app->getPlans();

			if (array_intersect($userPlanId, $appPlans) != false) {
				$result[] = self::ALLOWED;
				continue;
			}

			$result[] = self::BLOCKED;
		}

		return $result;
	}
	
	/**
	 * Get parent categories
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getChildCategoriesAndSelf($categoryId)
	{
		$categories = array();

		while ($categoryId) {
			$categories[] = (int) $categoryId;

			$db = PP::db();
			$query = 'SELECT `parent` FROM `#__k2_categories` WHERE `published` = 1 AND `id` = ' . $db->Quote($categoryId);
			$db->setQuery($query);
			
			$categoryId = $db->loadResult();
		}
		
		return $categories;
	}
}