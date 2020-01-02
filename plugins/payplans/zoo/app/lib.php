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

class PPZoo
{
	/**
	 * Determine if zoo is exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_zoo');
		$path = JFile::exists(JPATH_ROOT . '/components/com_zoo/zoo.php');

		if (!$enabled || !$path) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve all zoo categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$db = PP::db();

			$query = 'SELECT `name`, `id` FROM `#__zoo_category`';
			$query .= ' WHERE `published` = ' . $db->Quote(1);

			$db->setQuery($query);
			$categories = $db->loadObjectList('id');
		}

		return $categories;
	}
}