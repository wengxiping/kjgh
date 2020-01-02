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

require_once(__DIR__ . '/lib.php');

class PPHelperPhoca extends PPHelperStandardApp
{
	protected $resources = array(
								'access' => 'com_phocadownlaod.category.access', 
								'upload' => 'com_phocadownlaod.category.upload'
						);

	/**
	 * Retrieves accessible categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccessibleCategories($key)
	{
		$key = ucfirst($key);

		$categories = $this->params->get('accessToCatOn' . $key, array());

		if ($categories && !is_array($categories)) {
			$categories = array($categories);
		}

		return $categories;
	}

	/**
	 * Retrieves accessible categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAccessibleUploads($key)
	{
		$key = ucfirst($key);

		$categories = $this->params->get('uploadToCatOn' . $key, array());

		if ($categories && !is_array($categories)) {
			$categories = array($categories);
		}

		return $categories;
	}

	/**
	 * Retrieves the phoca library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLib()
	{
		static $lib = null;

		if (is_null($lib)) {
			$lib = new PPPhoca();
		}

		return $lib;
	}

	/**
	 * Retrieves a list of parent categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCategories($categoryId, $parent = array())
	{
		$parent[] = $categoryId;
		
		$lib = $this->getLib();
		$categories = $lib->getCategories();
	
		if ($categories[$categoryId]->parent_id == 0) {
			return $parent;
		}
		
		// Recursively find it's parents
		return $this->getParentCategories($categories[$categoryId]->parent_id, $parent);
	}

	/**
	 * Adds access for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addAccess($userId, $categories, $subscriptionId, $for = 'access')
	{
		if (!$categories) {
			return false;
		}

		foreach ($categories as $categoryId) {
			if (!$categoryId) {
				continue;
			}

			$users = $this->getCategoryMembers($categoryId, $for);

			// If user is already added, no point adding it again.
			if (in_array($userId, $users)) {
				$this->addResource($subscriptionId, $userId, $categoryId, $this->resources[$for]);
				continue;
			} 
			
			// Make sure we have no empty value
			$search = 0;

			while ($search !== false) {
				$search = array_search('', $users);

				if ($search !== false) {
					unset($users[$search]);
				}
			}
	
			$users[] = $userId;
			
			$state = $this->updateCategoryMembers($categoryId, $users, $for);

			if ($state) {
				$this->addResource($subscriptionId, $userId, $categoryId, $this->resources[$for]);
			}
		}
		
		return true;
	}

	/**
	 * Removes access for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeAccess($userId, $categories, $subscriptionId, $for = 'access')
	{
		if (!$categories) {
			return;
		}

		foreach ($categories as $categoryId) {

			if (!$categoryId) {
				continue;
			}

			$users = $this->getCategoryMembers($categoryId, $for);
	
			if (!in_array($userId, $users)) {
				continue;
			}
			
			$key = array_search($userId, $users);
			unset($users[$key]);
	
			// Make sure we have no empty value
			$search = 0;
			while ($search !== false) {
				$search = array_search('', $users);
				
				if ($search !== false) {
					unset($users[$search]);
				}
			}
			
			$this->removeResource($subscriptionId, $userId, $categoryId, $this->resources[$for]);
			$this->updateCategoryMembers($categoryId, $users, $for);
		}
		return true;
	}

	/**
	 * Retrieves a list of category members
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function getCategoryMembers($categoryId, $for)
	{
		$db = PP::db();

		$query = 'SELECT `accessuserid` FROM ' . $db->qn('#__phocadownload_categories') . ' WHERE `id` = ' . $db->Quote($categoryId);

		if ($for == 'upload') {
			$query = 'SELECT `uploaduserid` FROM ' . $db->qn('#__phocadownload_categories') . ' WHERE `id` = ' . $db->Quote($categoryId);			
		}

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Update category members
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateCategoryMembers($categoryId, $users, $for)
	{
		$db = PP::db();

		$value = $db->Quote(implode(',', $users));
		$query = array();
		$query[] = 'UPDATE ' . $db->qn('#__phocadownload_categories') . ' SET ' . $db->qn('accessuserid') . ' = ' . $value;

		if ($for == 'upload') {
			$query = array();
			$query[] = 'UPDATE ' . $db->qn('#__phocadownload_categories') . ' SET ' . $db->qn('uploaduserid') . '=' . $value;
		}

		$query[] = 'WHERE ' . $db->qn('id') . '=' . $db->Quote($categoryId);

		$query = implode(' ', $query);

		$db->setQuery($query);

		return $db->query();
	}

	/**
	 * Retrieves a list of categories accessible by a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserAccessibleCategories($userId)
	{
		static $cache = array();

		if (isset($cache[$userId])) {
			return $cache[$userId];
		}

		$categories = array();
		$db = PP::db();

		$query = 'SELECT `id`, `title`, `accessuserid` FROM ' . $db->qn('#__phocadownload_categories');
		$db->setQuery($query);

		$result = $db->loadObjectList();

		if ($result) {

			foreach ($result as $row) {
				$list = explode(',', $row->accessuserid);

				if (in_array($userId, $list)) {
					$categories[$row->id] = $row->title;
				}
			}
		}

		$cache[$userId] = $categories;

		return $cache[$userId];
	}
}