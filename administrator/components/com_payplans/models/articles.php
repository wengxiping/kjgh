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

PP::import('admin:/includes/model');

class PayplansModelArticles extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('articles');
	}

	/**
	 * Retrieve a suggestion lists for joomla articles
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function searchArticles($search)
	{
		$db = PP::db();

		$query = 'SELECT `id`, `title` FROM `#__content`';
		$query .= ' WHERE `title` LIKE ' . $db->Quote('%' . $search . '%');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Retrieve a suggestion lists of Joomla Article Categories
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function searchCategories($search)
	{
		$db = PP::db();

		$query = 'SELECT `id`, `title` FROM `#__categories`';
		$query .= ' WHERE `extension` = ' . $db->Quote('com_content');

		$query .= ' AND `title` LIKE ' . $db->Quote('%' . $search . '%');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
