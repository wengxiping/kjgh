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

class PayplansModelPlanapp extends PayPlansModel
{
	static $_planapps = null;
	static $_appplans = null;

	public function __construct()
	{
		parent::__construct('planapp');
	}

	// TODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	protected function _loadCache($query)
	{
		$query->clear('select')->clear('where');

		$query->select('app_id,plan_id');
		$this->db->setQuery($query);
		$app = $this->db->loadObjectList();

		self::$_planapps = array();
		self::$_appplans = array();

		foreach ($app as $obj) {

			if (isset(self::$_planapps[$obj->plan_id]) ==false) {
				self::$_planapps[$obj->plan_id] = array();
			}

			array_push(self::$_planapps[$obj->plan_id], $obj->app_id);

			if (isset(self::$_appplans[$obj->app_id]) ==false) {
				self::$_appplans[$obj->app_id] = array();
			}
			array_push(self::$_appplans[$obj->app_id], $obj->plan_id);
		}
	}

	public function getPlanApps($planId)
	{
		if (self::$_planapps === null) {
			$this->_loadCache(clone($this->getQuery()));
		}

		if (isset(self::$_planapps[$planId]) ===false) {
			return array();
		}

		return self::$_planapps[$planId];
	}


	public function getAppPlans($appId)
	{
		// Perfomance Fix :  Only check required is cache loaded or not, else it will generate false cache loading
		// as It is not neccessary that every app have some attached plans
		// /* !isset(self::$_appplans[$appId]) ||*/
		if (self::$_appplans === null) {
			$this->_loadCache(clone($this->getQuery()));
		}

		if (isset(self::$_appplans[$appId])===false) {
			return array();
		}

		return self::$_appplans[$appId];
	}
}

