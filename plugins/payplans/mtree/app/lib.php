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

class PPMosets
{
	protected $file = JPATH_ROOT . '/components/com_mtree/mtree.php';

	/**
	 * Determines if Mosets exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_mtree');
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
			$query[] = 'SELECT ' . $db->qn('cat_id') . ' AS `category_id`, `cat_name` AS `title` FROM ' . $db->qn('#__mt_cats');
			$query[] = 'WHERE ' . $db->qn('cat_published') . '=' . $db->Quote(1);
			$query[] = 'AND ' . $db->qn('cat_parent') . '=' . $db->Quote(0);

			$query = implode(' ', $query);
			$db->setQuery($query);
			$categories = $db->loadObjectList();
		}

		return $categories;
	}
}