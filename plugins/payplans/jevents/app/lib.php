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

class PPJevents
{
	protected $file = JPATH_ROOT . '/components/com_jevents/jevents.php';

	/**
	 * Determines if Mosets exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_jevents');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a list of kunena categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			// @TODO: This should be placed in the proper model
			$db = PP::db();

			$query = array();
			$query[] = 'SELECT ' . $db->qn('id') . ' AS `category_id`, `title` FROM ' . $db->qn('#__categories');
			$query[] = 'WHERE ' . $db->qn('extension') . '=' . $db->Quote('com_jevents');
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);

			$query = implode(' ', $query);
			$db->setQuery($query);
			$categories = $db->loadObjectList();
		}

		return $categories;
	}

	/**
	 * Retrieve the ACL data for a given user id from JEvents 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserAcl($userId)
	{
		$db = PP::db();

		$query = 'SELECT * FROM `#__jev_users` WHERE `user_id` =' . $db->Quote($userId);
		$db->setQuery($query);

		$acl = (array) $db->loadObject();
		
		return $acl;
	}

	/**
	 * Inserts a record into JEvents table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function insertAcl($userId, $data)
	{
		$db = PP::db();
		$now = PP::date();

		$keys = array_keys($data);

		$query = array();
		$query[] = 'INSERT INTO `#__jev_users` (`user_id`,`created`, ';
		$query[] = implode(',', $keys);
		$query[] = ') VALUES (';
		$query[] = $db->Quote($userId) . ',' . $db->Quote($now->toSql()) . ',' . implode(',', $data);
		$query[] = ')';

		$db->setQuery($query);
		return $db->query();
	}

	/**
	 * Determines if the user record exists on JEvents table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isUserExists($id)
	{
		$db = PP::db();
		
		$query = 'SELECT COUNT(1) FROM `#__jev_users` WHERE `user_id`=' . $db->Quote($id);
		$db->setQuery($query);
		$exists = $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Merge acl for a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function mergeAcl($userId, $appAcl)
	{
		$finalAcl = array();

		$userAcl = $this->getUserAcl($userId);

		foreach ($userAcl as $key => $value) {
			
			// Skip unwanted data
			if (in_array($key, array('id', 'user_id', 'created', 'calendars'))) {
				continue;
			}

			if ($key == 'categories') {
				
				// allow all categories of active plans
				$previousCat = array();

				// Ensure this category id is not empty
				if ($value) {
					$previousCat = explode('|', $value);
				}

				$appAcl[$key] = (is_array($appAcl[$key])) ? $appAcl[$key] : array($appAcl[$key]);
				
				$finalAcl[$key] = array_merge($appAcl[$key], $previousCat);
				$finalAcl[$key] = array_unique($finalAcl[$key]);

				//If there is more than 1 category
				if (is_array($finalAcl[$key])) {
					$finalAcl[$key] = implode('|', $finalAcl[$key]);
				}

				continue;
			}

			//allow total events
			if ($key == 'eventslimit' || $key == 'extraslimit') {
				$finalAcl[$key] = $appAcl[$key] + $value;
				continue;
			}
			
			//choose better value for user
			$finalAcl[$key] = ($appAcl[$key] > $value) ? $appAcl[$key] : $userAcl[$key];
		}

		return $finalAcl;
	}

	/**
	 * Inserts a record into JEvents table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateAcl($userId, $data)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'UPDATE ' . $db->qn('#__jev_users') . ' SET';

		$insert = array();
		foreach ($data as $key => $value) {
			$insert[] = $db->qn($key) . '=' . $db->Quote($value);
		}

		$query[] = implode(',', $insert);

		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($userId);

		$db->setQuery($query);
		return $db->query();
	}
}