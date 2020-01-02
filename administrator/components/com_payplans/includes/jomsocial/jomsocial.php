<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPJomsocial
{
	protected $folder = JPATH_ROOT . '/components/com_community';

	/**
	 * Determines if jomsocial exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_community');
		$exists = JFolder::exists($this->folder);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a list of jomsocial profiles
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getProfiles()
	{
		static $profiles = null;

		if (is_null($profiles)) {
			$db = PP::db();
			$query = 'SELECT ' . $db->qn('id') . ', ' . $db->qn('name') . ' FROM ' . $db->qn('#__community_profiles');
			
			$db->setQuery($query);
			$profiles = $db->loadObjectList('id');
		}

		return $profiles;
	}
}
