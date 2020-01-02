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

class PPHelperMailster extends PPHelperStandardApp
{
	protected $resource = 'com_mailster.group';

	/**
	 * Renders the mailster library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {
			require_once(__DIR__ . '/lib.php');

			$lib = new PPMailster();
		}

		return $lib;
	}

	/**
	 * Determines if Mailster exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAssociatedGroups($type)
	{
		$groups = $this->params->get('addToGroupon' . ucfirst($type));

		if (!is_array($groups)) {
			$groups = array($groups);
		}

		return $groups;
	}

	/**
	 * Given a group id, try to get it's name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getGroupName($id)
	{
		static $names = array();

		if (!isset($names[$id])) {
			$db = PP::db();
			$query = 'SELECT `name` FROM ' . $db->qn('#__mailster_groups') . ' WHERE ' . $db->qn('id') . '=' . $db->Quote($id);
			$db->setQuery($query);
			$names[$id] = $db->loadResult();
		}

		return $names[$id];
	}

	/**
	 * Add and remove users into / from the necessary groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateUserGroups($userId, $subscriptionId, $action)
	{
		$types = array('active', 'expire', 'hold');

		foreach ($types as $type) {
			$groups = $this->getAssociatedGroups($type);

			// Add the user into the necessary group
			if ($type == $action) {
				$this->insert($userId, $groups, $subscriptionId);

				continue;
			}

			// Remove from the rest
			$this->remove($userId, $groups, $subscriptionId);
		}

		return;
	}

	/**
	 * Removes user from a list of groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove($userId, $groups, $subscriptionId)
	{
		if (!$groups) {
			return;
		}

		$db = PP::db();

		foreach ($groups as $id) {
			if (!$id) {
				continue;
			}

			$this->removeResource($subscriptionId, $userId, $this->getGroupName($id), $this->resource);

			$query = 'DELETE FROM `#__mailster_group_users` WHERE `user_id`=' . $db->Quote($userId) . ' AND `group_id`=' . $db->Quote($id);
			$db->setQuery($query);
			$db->Query();
		}

		return true;
	}

	/**
	 * Insert users into a list of groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function insert($userId, $groups, $subscriptionId)
	{
		if (!$groups) {
			return;
		}

		$db = PP::db();

		foreach ($groups as $id) {

			if (!$id) {
				continue;
			}

			$query = 'SELECT COUNT(1) FROM ' . $db->qn('#__mailster_group_users') . ' WHERE ' . $db->qn('group_id') . '=' . $db->Quote($id) . ' AND ' . $db->qn('user_id') . '=' . $db->Quote($userId);
			$db->setQuery($query);
			$users = (int) $db->loadResult();

			// Only insert users when they really do not exist
			if ($users <= 0) {
				$query = 'INSERT INTO ' . $db->qn('#__mailster_group_users') . ' VALUES(' . $db->Quote($id) . ',' . $db->Quote($userId) . ', 1)';

				$db->setQuery($query);
				$db->query();
			}

			$this->addResource($subscriptionId, $userId, $this->getGroupName($id), $this->resource);
		}
	}
}