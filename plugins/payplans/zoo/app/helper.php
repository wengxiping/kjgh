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

class PPHelperZoo extends PPHelperStandardApp
{
	/**
	 * Retrieve the zoo library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLib()
	{
		$lib = new PPZoo();
		return $lib;
	}

	/**
	 * Method to publish or unpublished the query
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function publishOrUnpublishEntry($userid, $publishCount, $action, $zooCategory)
	{
		// publish only those entries which are not currently published
		// and vice versa
		$complement = 1 - $action;

		$db = PP::db();

		$query = 'UPDATE `#__zoo_item`';
		$query .= ' SET `state` = ' . $db->Quote($action);
		$query .= ' WHERE `created_by` = ' . $db->Quote($userid) . ' AND `state` = ' . $db->Quote($complement);

		if ($zooCategory == '0') {
			$query .= ' AND `id` NOT IN (SELECT `item_id` FROM `#__zoo_category_item`)';
		} else {
			$query .= ' AND `id` IN (SELECT `item_id` FROM `#__zoo_category_item` WHERE `category_id` = ' . $db->Quote($zooCategory) . ')';
		}

		$query .= ' LIMIT ' . $publishCount;

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Method to retrieve the category of the item
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategory($itemId)
	{
		$db = PP::db();

		$query = 'SELECT `category_id` FROM `#__zoo_category_item`';
		$query .= ' WHERE `category_id` != ' . $db->Quote(0);
		$query .= ' AND `item_id` = ' . $db->Quote($itemId);

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method to retrieve the top parent categories for the specified category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCategories($catId)
	{
		$allCat = array();

		while ($catId) {
			$allCat[] = $catId;

			$db = PP::db();

			$query = 'SELECT `parent` FROM `#__zoo_category`';
			$query .= ' WHERE `published` = ' . $db->Quote(1) . ' AND `id` = ' . $db->Quote($catId);

			$db->setQuery($query);
			$catId = $db->loadResult();
		}

		return $allCat;
	}

	/**
	 * Method to retrieve the parent category from the given item id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentFromItemid($itemId)
	{
		$db = PP::db();
		$query =  ' SELECT `category_id` FROM `#__zoo_category_item`';
		$query .= ' WHERE `item_id`= ' . $db->Quote($itemId);

		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * Decide the redirection method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectDecision($zooCategoryApps, $controlOn, $category, $flag, $user, $edited = false)
	{
		$userEntries = $this->getUserEntries($user->getId(), $category[0]);

		if ($edited) {
			$userEntries--;
		}

		$record = $this->getZooResource($user->getId(), $category[1]);
		$count = ($record != false) ? $record->count : 0 ;
		// if user not record of category in which he/she want to post
		// then check available app for that category
		// if app is created then not allowed to post otherwise ok
		if ($count == 0) {
			// check which plan is required for this category/app
			foreach ($zooCategoryApps as $app) {
				if (in_array($app->getAppParam('controlOn'), $controlOn)) {
					$postInCategory = $app->getAppParam('zoo_category', array());

					if ($postInCategory == $category) {
						$flag = 1;
						break;
					}
				}
			}
		}
		if ($count == 0 && $flag == 1) {
			return true;
		}
		
		if ($count != 0 && ($userEntries >= $count || $flag == 1)) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve all of the specified user entries
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserEntries($userId, $category)
	{
		$db = PP::db();

		if ($category == 0) {
			$query =  ' SELECT COUNT(*) FROM `#__zoo_item`';
			$query .= ' WHERE `created_by`= ' . $db->Quote($userId) . ' AND `id` NOT IN (SELECT `item_id` FROM `#__zoo_category_item`)';

			$db->setQuery($query);
			return $db->loadResult();
		} elseif ($category == -1) {
			$query = ' SELECT COUNT(*) FROM `#__zoo_item`';
			$query .= ' WHERE `created_by`= ' . $db->Quote($userId);

			$db->setQuery($query);
			return $db->loadResult();
		} else {
			$query = ' SELECT COUNT(*) FROM `#__zoo_item`';
			$query .= ' WHERE `created_by`= ' . $db->Quote($userId);
			$query .= ' AND `id` IN (';
			$query .= '		SELECT `item_id` FROM `#__zoo_category_item` WHERE `category_id` = ' . $db->Quote($category);
			$query .= ' )';

			$db->setQuery($query);
			return $db->loadResult();
		}
	}

	/**
	 * Method to retrieve the resources related to the Zoo
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getZooResource($userId, $category)
	{
		$model = PP::model('Resource');
		$record = $model->loadRecords(array('user_id'  => $userId, 'title' => 'com_zoo.submission' . $category));
		$record = array_shift($record);

		if (empty($record) || !$record) {
			return false;
		}

		return $record;
	}
}