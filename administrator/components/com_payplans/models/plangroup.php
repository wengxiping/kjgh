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

class PayplansModelPlangroup extends PayPlansModel
{
	static $_plangroups = null;
	static $_groupplans = null;

	public function __construct()
	{
		parent::__construct('plangroup');
	}

	// XITODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	protected function _loadCache($query)
	{
		$query->clear('select')->clear('where');

		$query->select('group_id,plan_id');
		$this->db->setQuery($query);
		$group = $this->db->loadObjectList();

		self::$_plangroups = array();
		self::$_groupplans = array();

		foreach($group as $obj){
			if(isset(self::$_plangroups[$obj->plan_id]) ==false){
				self::$_plangroups[$obj->plan_id] = array();
			}
			array_push(self::$_plangroups[$obj->plan_id], $obj->group_id);

			if(isset(self::$_groupplans[$obj->group_id]) ==false){
				self::$_groupplans[$obj->group_id] = array();
			}
			array_push(self::$_groupplans[$obj->group_id], $obj->plan_id);
		}
	}

	public function getPlanGroups($planId)
	{
		if (self::$_plangroups === null) {
			$this->_loadCache(clone($this->getQuery()));
		}

		if (isset(self::$_plangroups[$planId]) ===false) {
			return array();
		}

		return self::$_plangroups[$planId];
	}


	public function getGroupPlans($groupId)
	{
		if (self::$_groupplans === null) {
			$this->_loadCache(clone($this->getQuery()));
		}

		if (isset(self::$_groupplans[$groupId]) ===false) {
			return array();
		}

		return self::$_groupplans[$groupId];
	}
}

