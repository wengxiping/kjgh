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

PP::import('admin:/tables/table');

class PayplansTableParentchild extends PayplansTable
{
	public $dependent_plan = null;
	public $base_plan = null;
	public $relation = null;
	public $display_dependent_plan = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__payplans_parentchild', 'dependent_plan', $db);
	}

	public function store($updateNulls = false, $new = false)
	{
		// Update values.
		$db = PP::db();
		$obj = new stdClass();

		$properties = get_object_vars($this);

		foreach ($properties as $key => $value) {
			if (stripos($key, '_') !== 0) {
				$obj->$key = $value;
			}
		}

		// Ensure that there's a record.
		$exists = $this->exists($this->dependent_plan);

		// Update existing records
		if ($exists) {
			$state = $db->updateObject($this->_tbl, $obj, 'dependent_plan');
			return $state;
		}

		// If the record does not exist yet, create it.
		$state = $db->insertObject($this->_tbl, $obj);

		return $state;
	}

	public function exists($id)
	{
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote($this->_tbl);
		$query[] = 'WHERE ' . $db->nameQuote('dependent_plan' ) . '=' . $db->Quote($id);

		$query = implode(' ', $query);

		$db->setQuery($query);
		$exists = $db->loadResult() ? true : false;

		return $exists;
	}
}