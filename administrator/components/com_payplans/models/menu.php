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

class PayPlansModelMenu extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get all frontend menu items associated with PayPlans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getMenuItems()
	{
		$db = PP::db();

		$query = "SELECT * FROM " . $db->qn('#__menu');
		$query .= " WHERE " . $db->qn('published') . " = " . $db->Quote(1);
		$query .= " AND " . $db->qn('link') . " LIKE " . $db->Quote('index.php?option=com_payplans%');
		$query .= " AND " . $db->qn('client_id') . " = " . $db->Quote('0');
		$query .= " ORDER BY `id`";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}
}
