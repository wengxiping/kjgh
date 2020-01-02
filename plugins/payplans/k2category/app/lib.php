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

class PPK2category
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

	/**
	 * Retrieves a list of k2 categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$db = PP::db();
			$query = 'SELECT ' . $db->qn('id') . ' AS `category_id`, ' . $db->qn('name') . ' FROM ' . $db->qn('#__k2_categories');
			
			$db->setQuery($query);
			$categories = $db->loadObjectList('category_id');;
		}

		return $categories;
	}
}