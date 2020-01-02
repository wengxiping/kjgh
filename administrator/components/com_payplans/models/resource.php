<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/model');

class PayplansModelResource extends PayPlansModel
{
	public $filterMatchOpeartor = array('title'	=> array('LIKE'));

	public function __construct()
	{
		parent::__construct('resource');
	}

	public function getItems()
	{
		$title = $this->getState('title');
		$ordering = $this->getState('ordering');
		$direction	= $this->getState('direction');

		$db = $this->db;

		$query = array();

		$query[] = "select a.*";
		$query[] = " from `#__payplans_resource` as a";

		$wheres = array();

		if ($title) {
			$wheres[] = $db->nameQuote('a.title') . " like " . $db->Quote('%' . $title . '%');
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		return $result;
	}

	/**
	 * Retrieve the resources records
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRecords($options = array())
	{
		$subId = isset($options['subscription_ids']) ? $options['subscription_ids'] : false;
		$title = isset($options['title']) ? $options['title'] : false;

		$db = PP::db();

		$query = 'SELECT * FROM `#__payplans_resource`';
		$queryWhere = array();

		if ($subId) {
			$queryWhere[] = ' `subscription_ids` = ' . $db->Quote($subId);
		}

		if ($title) {
			$queryWhere[] = ' `title` = ' . $db->Quote($title);
		}

		if ($queryWhere) {
			$query .= ' WHERE ';
			$query .= implode(' AND ', $queryWhere);
		}

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}
}

