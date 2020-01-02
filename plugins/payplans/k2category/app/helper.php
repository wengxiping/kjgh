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

class PPHelperK2category extends PPHelperStandardApp
{
	protected $_location = __FILE__;

	const DO_NOTHING = -1;
	const ALLOWED = 1;
	const BLOCKED = 0;

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
	 * Determines if the given category id applicable in this app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isCategoryApplicable($categoryId)
	{
		// Get all the ancestor categories for that category
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
			$categories = $this->params->get('category', array());

			if (!is_array($categories) && $categories) {
				$categories = array($categories);
			}
		}

		return $categories;
	}

	/**
	 * Get categories that are accessible by the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccessibleCategories(PPUser $user)
	{
		$categories = $this->getAllCategories();

		foreach ($categories as $category) {
			
			$allowed = $this->isAllowed($user, $category);
			$count = array_count_values($allowed);

			// If some one allows then its allowed
			if (in_array(self::ALLOWED, $allowed)) {
				continue;
			}

			// if no one allows but some one blocks then its blocked
			if (in_array(self::BLOCKED, $allowed)) {
				unset($categories[$category->id]);
			}
		}

		return $categories;
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
	public function isAllowed(PPUser $user, $category)
	{
		$apps = $this->getAvailableApps('k2category');

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
		
		// Check which app allow category, which not allow and which app do nothing
		foreach ($apps as $app) {

			$disallowedCategories = $app->helper->getDisallowedCategories();

			// If app is not applicable on that category 
			if (count(array_intersect(array($category), $disallowedCategories)) == 0) {
				$result[] = self::DO_NOTHING;
				continue;
			}

			// If user has no plans, we assume that they should be blocked
			if (!$plans) {
				$result[] = self::BLOCKED;
				continue;
			}

			if (!$plans) {
				$appResult[] = self::BLOCKED;
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
	 * Determine if category is allowed for user
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function isCategoryAllowed($categoryId)
	{
		require_once(JPATH_ROOT . '/com_k2/helpers/permissions.php');

		$allowed = false;
		
		$userid = PP::user()->id;
		$k2user = K2HelperPermissions::getK2User($userid);
		
		if ($k2user) {
			$k2Group = K2HelperPermissions::getK2UserGroup($k2user->group);  
			$permissions= new JRegistry($k2Group->permissions);
			$k2Category = $permissions->get('categories');

			$k2Category = is_array($k2Category) ? $k2Category : array($k2Category);

			if (in_array($categoryId, $k2Category)) {
				 $allowed = true;
			}

			if ($k2Category == "all") {
				$allowed = true;
			}
		}
		
		return $allowed;
	}

	/**
	 * Retrieve user's posts
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getUserPosts($userid, $catId)
	{
		$db = PP::db();
		$query = 'SELECT *'
				. ' FROM #__k2_items'
				. ' WHERE `created_by` = ' . $db->Quote($userid) . ' AND `catid` = ' . $db->Quote($catId);
		$db->setQuery($query);
		return $db->loadObjectList('id');
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

	public function changeItemState($userid, $publishCount, $action, $category)
	{
		$complement = 1 - $action;
		$db = PP::db();
		
		$query = ' UPDATE `#__k2_items`'
			 . ' SET `published`=' . $db->Quote($action) 
			 . ' WHERE `created_by`=' . $db->Quote($userid)
			 . ' AND `published`=' . $db->Quote($complement) 
			 . ' AND `catid` =' . $db->Quote($category)
			 . ' LIMIT ' . $publishCount;
			
		$db->setQuery($query);
		$db->query();
	}
}