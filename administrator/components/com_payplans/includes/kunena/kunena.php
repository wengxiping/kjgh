<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPKunena
{
	protected $file = JPATH_ROOT . '/components/com_kunena/kunena.php';

	/**
	 * Determines if kunena exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_kunena');
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
			$query = 'SELECT * FROM ' . $db->qn('#__kunena_categories') . ' WHERE ' . $db->qn('published') . '=' . $db->Quote(1);
			$db->setQuery($query);

			$categories = $db->loadObjectList();
		}

		return $categories;
	}

	/**
	 * Retrieves a list of parent categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParentCategories($catId)
	{
		static $items = array();

		if (!isset($items[$catId])) {
			//we have to traverse to parent =0
			//XITODO : improve performance
			$allCat = array();

			while ($catId) {
				$allCat[] = $catId;
				
				$db = PP::db();
				$query = array();
				$query[] = 'SELECT ' . $db->qn('parent_id') . ' FROM ' . $db->qn('#__kunena_categories');
				$query[] = 'WHERE ' . $db->qn('published') . '=' . $db->Quote(1);
				$query[] = 'AND ' . $db->qn('id') . '=' . $db->Quote($catId);

				$query = implode(' ', $query);
				$db->setQuery($query);

				$catId = $db->loadResult();
			}

			$items[$catId] = $allCat;
		}
		
		return $items[$catId];
	}

	/**
	 * Retrieves the user object from Kunena
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUser($id)
	{
		if (!$this->exists()) {
			return false;
		}

		$user = KunenaFactory::getUser($id);

		return $user;
	}
}
