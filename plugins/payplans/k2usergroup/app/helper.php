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

class PPHelperK2usergroup extends PPHelperStandardApp
{
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

	public function addToGroup($userId, $groupId)
	{
		if (!$groupId) {
			return true;
		}

		$db = PP::db();
		$query = 'SELECT id FROM ' . $db->qn('#__k2_users') . ' WHERE ' . $db->qn('userID') . ' = ' . $db->Quote($userId);
			
		$db->setQuery($query);
		$id = $db->loadResult();

		if (empty($id)) {
			$query = 'INSERT INTO ' . $db->qn('#__k2_users')
				. ' (`userID`, `userName`, `group`)'
				. ' VALUES (' . $db->Quote($userId) . ', ' . $db->Quote($user->username) . ', ' . $db->Quote($groupId) . ')';
		} else {
			$query = 'UPDATE ' . $db->qn('#__k2_users') . ' SET `group` = ' . $db->Quote($groupId)
				. ' WHERE `userID` = ' . $db->Quote($userId);
		}
		
		$db->setQuery($query);
		$db->query();
		
		// Log when user is added to k2 group
		$message = JText::sprintf('COM_PAYPLANS_APP_K2_LOG_ADDED_TO_GROUP', $userId, $groupId);
		$content = array('previous' => array('user_id'=>$userid, 'k2_user_group' => $groupId));

		PPLog::log(PPLogger::LEVEL_INFO, $message, null, $content, 'PayplansAppK2Formatter');
		return true;
	}
}