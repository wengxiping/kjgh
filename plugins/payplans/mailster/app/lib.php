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

class PPMailster
{
	protected $file = JPATH_ROOT . '/components/com_mailster/mailster.php';

	/**
	 * Determines if Mailster exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_mailster');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Get a list of mailing list groups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getGroups()
	{
		$db = PP::db();

		$query = 'SELECT `id`, `name` FROM ' . $db->qn('#__mailster_groups');

		$db->setQuery($query);
		$groups = $db->loadObjectList();

		return $groups;
	}
}