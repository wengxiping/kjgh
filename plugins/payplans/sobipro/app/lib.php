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

class PPSobipro
{
	protected $folder = JPATH_ROOT . '/components/com_sobipro';

	/**
	 * Determines if sobipro exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_sobipro');
		$exists = JFolder::exists($this->folder);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	public function getCategories()
	{
		static $categories = null;

		if (is_null($categories)) {
			$db = PP::db();
			$query = array();

			$query[] = 'SELECT a.' . $db->qn('id') . ' AS `cat_id`, b.' . $db->qn('sValue') . ' as `name`, a.' . $db->qn('parent');
			$query[] = 'FROM ' . $db->qn('#__sobipro_object') . ' as a';
			$query[] = 'INNER JOIN ' . $db->qn('#__sobipro_language') . ' as b';
			$query[] = 'ON a.`id`=b.`id`';
			$query[] = 'WHERE b.`skey`=' . $db->Quote('name');
			$query[] = 'AND a.' . $db->qn('oType') . ' = ' . $db->Quote('category');
			$query[] = 'AND a.' . $db->qn('state') . ' = ' . $db->Quote(1);
			
			$query = implode(' ', $query);
			$db->setQuery($query);

			$categories = $db->loadObjectList('cat_id');
		}

		foreach ($categories as $category){
			
			$res = $this->getSection($category->parent);
			$category->name .= ' ('.$res->name.') ';
		}

		return $categories;
	}
	
	public function getSection($catId)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT a.' . $db->qn('id') . ', b.' . $db->qn('sValue') . ' as `name`, a.' . $db->qn('parent');
		$query[] = 'FROM ' . $db->qn('#__sobipro_object') . ' as a';
		$query[] = 'INNER JOIN ' . $db->qn('#__sobipro_language') . ' as b';
		$query[] = 'ON a.`id`=b.`id`';
		$query[] = 'WHERE b.`skey`=' . $db->Quote('name');
		$query[] = 'AND a.' . $db->qn('id') . ' = ' . $db->Quote($catId);
		$query[] = 'AND a.' . $db->qn('state') . ' = ' . $db->Quote(1);
		
		$query = implode(' ', $query);

		$db->setQuery($query);
		$section = $db->loadObject();

		if ($section->parent != 0){
			$section = $this->getSection($section->parent);
		}

		return ($section);
	}


	public function getSections()
	{
		static $sections = null;

		if (is_null($sections)) {
			$db = PP::db();
			$query = array();
			$query[] = 'SELECT a.' . $db->qn('id') . ' AS `sec_id`, b.' . $db->qn('sValue') . ' AS `name`';
			$query[] = 'FROM ' . $db->qn('#__sobipro_object') . ' as a';
			$query[] = 'INNER JOIN ' . $db->qn('#__sobipro_language') . ' as b';
			$query[] = 'ON a.`id`=b.`id`';
			$query[] = 'WHERE b.`skey`=' . $db->Quote('name');
			$query[] = 'AND a.' . $db->qn('oType') . ' = ' . $db->Quote('section');
			$query[] = 'AND a.' . $db->qn('state') . ' = ' . $db->Quote(1);
			
			$db->setQuery($query);
			$sections = $db->loadObjectList('sec_id');
		}

		return $sections;
	}
}