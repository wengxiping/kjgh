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

class PPK2usergroup
{
	protected $folder = JPATH_ROOT . '/components/com_k2';

	/**
	 * Determines if k2 exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_k2');
		$exists = JFolder::exists($this->folder);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	public static function getK2UserGroups()
	{
		$db = PP::db();
		$query = 'SHOW COLUMNS FROM ' . $db->qn('#__k2_user_groups') . ' LIKE ' . $db->Quote('groups_id');

		$db->setQuery($query);
		$result = $db->loadResult();

		$column = $result ? 'groups_id' : 'id';

		$query = 'SELECT ' . $db->qn($column) . ' as groups_id, ' . $db->qn('name') . ' FROM ' . $db->qn('#__k2_user_groups');

		$db->setQuery($query);

		$groups = $db->loadObjectList('groups_id');

		// For none selection
		$none = new stdClass();
		$none->groups_id = 0;
		$none->name = JText::_('NONE');
		$groups[] = $none;

		return $groups;
	}
}