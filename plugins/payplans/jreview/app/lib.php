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

class PPJReview
{
	protected $file = JPATH_ROOT . '/components/com_jreviews/jreviews.php';

	/**
	 * Determines if JReviews exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_jreviews');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Loads JReviews library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function load()
	{
		$path = dirname($this->file) . '/jreviews/framework.php';
		require_once($path);

		return true;
	}

	/**
	 * Retrieves a list of parent categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCategories($catId = 0, &$parents = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT `parent_id` FROM ' . $db->qn('#__categories');
		$query[] = 'WHERE `extension` = ' . $db->Quote('com_content');
		$query[] = 'AND `id`=' . $db->Quote((int) $catId);

		$db->setQuery($query);
		$parent = $db->loadResult();

		// Recursively find it's parents
		if ($parent != 0) {
			$parents[] = (int) $parent;

			$this->getParentCategories((int) $parent,$parents);
		}

		return $parents;
	}
	
	/**
	 * Retrieves a list of kunena categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategories()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT Category.id AS `cat_id`, CONCAT(REPEAT("- ", IF(Category.level>0,Category.level - 1,1)), Category.title) AS `cat_title`';
		$query[] = 'FROM ' . $db->qn('#__categories') . ' AS Category';
		$query[] = 'LEFT OUTER JOIN ' . $db->qn('#__categories') . ' AS ParentCategory';
		$query[] = 'ON Category.lft <= ParentCategory.lft AND Category.rgt >= ParentCategory.rgt';
		$query[] = 'INNER JOIN ' . $db->qn('#__jreviews_categories') . ' AS JreviewCategory';
		$query[] = 'ON JreviewCategory.id = Category.id AND JreviewCategory.`option` = ' . $db->Quote('com_content');
		$query[] = 'WHERE Category.extension=' . $db->Quote('com_content');
		$query[] = 'GROUP BY Category.id';
		$query[] = 'ORDER BY Category.lft';

		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		return $result;
	}

	/**
	 * Retrieves the total of post created by user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategoryCount($category = 0, $userId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__content');
		$query[] = 'WHERE ' . $db->qn('state') . '=' . $db->Quote(1);
		$query[] = 'AND ' . $db->qn('created_by') . '=' . $db->Quote((int) $userId);

		if ($category) {
			$query[] = 'AND ' . $db->qn('catid') . '=' . $db->Quote((int) $category);
		}

		$db->setQuery($query);
		$total = (int) $db->loadResult();
		
		return $total;
	}

	/**
	 * Retrieves the total number of media user has posted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalMediaEntries($category, $userId, $all = false, $mediaType = 'photo')
	{
		$childCategories = array($category);
		$childCategories = $this->getChildCategories($category, $childCategories);
			
		$rawlistings = array();

		foreach ($childCategories as $childCategory) {
			$rawlistings = array_merge($rawlistings, $this->getCategoryCount($childCategory, $userId));
		}

		$listing = array();

		if ($rawlistings) {
			foreach ($rawlistings as $list) {
				$listing[] = $list->id;
			}
		}
		
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__jreviews_media') . ' AS `media`';
		$query[] = 'WHERE media.`user_id`=' . $db->Quote((int) $userId);
		$query[] = 'AND media.`published`=' . $db->Quote(1);
		$query[] = 'AND media.`approved`=' . $db->Quote(1);
		$query[] = 'AND media.`media_type`=' . $db->Quote($mediaType);

		if (!$all && $category && $listing) {
			$query[] = 'AND media.`listing_id` IN(' . implode(',', $listing) . ')';
		}

		$db->setQuery($query);
		$total = (int) $db->loadResult();
		
		return $total;
	}

	/**
	 * Given a listing id, retrieve the category id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategoryFromlist($listingId = 0)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT `catid` FROM ' . $db->qn('#__content');
		$query[] = 'WHERE ' . $db->qn('id') . '=' . $db->Quote($listingId);

		$db->setQuery($query);
		$id = (int) $db->loadResult();

		return $id;
	}
	
	/**
	 * Returns child categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getChildCategories($categoryId = 0, &$childs = array())
	{
		if (!$categoryId) {
			return $childs;
		}

		$db = PP::db();
		$query = array();
		$query[] = 'SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__categories');
		$query[] = 'WHERE ' . $db->qn('extension') . '=' . $db->Quote('com_content');
		$query[] = 'AND ' . $db->qn('parent_id') . '=' . $db->Quote((int) $categoryId);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return $childs;
		}

		// Recursively loop th children
		foreach ($result as $row) {
			$childs[] = $row->id;
			$this->getChildCategories($child->id, $childs);
		}
		
		return $childs;
	}

	/**
	 * It will fetch userentries only for a category all it's childeren's entries also
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalPostEntries($category = 0, $userId)
	{
		// If all entries are required
		if (!$category) {
			return $this->getCategoryCount(0, $userId);
		}
		
		$count = 0;
		
		$childCategories = array($category);
		$childCategories = $this->getChildCategories($category, $childCategories);
		
		foreach ($childCategories as $childCategory) {
			$count += $this->getCategoryCount($childCategory, $userId);
		}
		
		return $count;
	}

	/**
	 * Retrieves the resource usage
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResourceUsageOfUser($category, $userId, $mediaRestriction = false, $mediaType = 'photo')
	{
		if($mediaType == 'photo') {
			$mediaType = 'image';
		}
		
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_resource');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote((int) $userId);
		$query[] = 'AND ' . $db->qn('value') . '=' . $db->Quote($category);

		if ($mediaRestriction) {
			$query[] = 'AND ' . $db->qn('title') . '=' . $db->Quote('com_jreview.' . $mediaType);
		}

		if (!$mediaRestriction) {
			$query[] = 'AND ' . $db->qn('title') . '=' . $db->Quote('com_jreview.category');
		}

		$query = implode(' ', $query);

		$db->setQuery($query);

		$total = (int) $db->loadResult();
		return $total;
	}
}