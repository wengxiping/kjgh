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

class PPHelperEasyBlog extends PPHelperStandardApp
{
	const DO_NOTHING = -1;
	const ALLOWED = 1;
	const BLOCKED = 0;

	public $supportedViews = array('categories', 'entry');

	/**
	 * Retrieves a list of disallowed categories from EasyBlog because
	 * there are categories associated with the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDisallowedCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$categories = $this->params->get('addToCategoryOnActive', array());

			if (!is_array($categories) && $categories) {
				$categories = array($categories);
			}
		}

		return $categories;
	}

	/**
	 * Determines if the provided view is part of our checks
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isSupportedView($view)
	{
		static $supported = array();

		if (!isset($supported[$view])) {
			$supported[$view] = in_array($view, $this->supportedViews);
		}

		return $supported[$view];
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

	public function isAllowed(PPUser $user, $category)
	{
		$apps = $this->getAvailableApps('easyblog');
		
		if (!$apps) {
			return array(SELF::ALLOWED);
		}

		$result = array();

		foreach ($apps as $app) {
			$disallowedCategories = $app->helper->getDisallowedCategories();

			// If app is not applicable on that category 
			if (count(array_intersect(array($category), $disallowedCategories)) == 0) {
				$result[] = self::DO_NOTHING;
				continue;
			}
		
			$plans = $user->getPlans();
			$plans = PP::getIds($plans);

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

			if (array_intersect($plans, $appPlans) != false) {
				$result[] = self::ALLOWED;
				continue;
			}

			$result[] = self::BLOCKED;
		}
		
		return $result;
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
				unset($categories[$category]);
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
			$query = 'SELECT `id` FROM `#__easyblog_category` WHERE `published`=1';
			$db->setQuery($query);

			$result = $db->loadColumn();
			$categories = array();

			if ($result) {
				foreach ($result as $categoryId) {
					$categories[$categoryId] = $categoryId;
				}
			}
		}

		return $categories;
	}

	/**
	 * Try to get the category and it's subcategories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getChildCategoriesAndSelf($categoryId)
	{
		$categories = array();

		while ($categoryId) {
			$categories[] = (int) $categoryId;

			$db = PP::db();
			$query = 'SELECT `parent_id` FROM `#__easyblog_category` WHERE `published` = 1 AND `id` = ' . $db->Quote($categoryId);
			$db->setQuery($query);
			
			$categoryId = $db->loadResult();
		}
		
		return $categories;	
	}

	/**
	 * Redirect non logged in users
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function requireLogin()
	{
		$user = PP::user();

		if (!$user->id) {
			PP::info()->set('COM_PAYPLANS_APP_EASYBLOG_LOGIN_TO_ACCESS_DESC', 'error');
			$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);
			return PP::redirect($redirect);
		}		
	}
}