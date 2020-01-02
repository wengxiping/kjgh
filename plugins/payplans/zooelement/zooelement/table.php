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

class PayplansTableZooitemelement extends PayPlansTable
{
	public $itemid = null;
	public $params = null;

	public function __construct($tbl = null, $pk = 'itemid', $db = null)
	{
		return parent::__construct('#__payplans_zooitemelement', $pk, PP::db());
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
		$exists = $this->exists(array('itemid' => $this->itemid));

		// Update existing records
		if ($exists) {
			$state = $db->updateObject($this->_tbl, $obj, 'itemid');
			return $state;
		}

		// If the record does not exist yet, create it.
		$state = $db->insertObject($this->_tbl, $obj);
		
		return $state;
	}
 }