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

class PPVirtuemart
{

	/**
	 * Determines if Virtuemart exists on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_virtuemart');

			if ($enabled) {
				$exists = true;
			}
		}

		return $exists;
	}

	/**
	 * Retrieves a list of Virtuemart shoper Group on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getGroups()
	{
		static $groups = null;

		if (is_null($groups)) {
			// @TODO: This should be placed in the proper model
			$db = PP::db();
			$query = 'SELECT * FROM `#__virtuemart_shoppergroups`  WHERE `published` = 1';
			$db->setQuery($query);

			$groups = $db->loadObjectList();
		}

	 	return $groups;
	}
}