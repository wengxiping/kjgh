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

class PayplansModelGateways extends PayPlansModel
{
	// Let the parent know that we are trying to filter by app table
	protected $_name = 'app';

	public function __construct()
	{
		parent::__construct('gateways');
	}
	
	/**
	 * Initialize default states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initStates($view = null)
	{
		parent::initStates();

		$type = $this->getUserStateFromRequest('type' , 'all');

		$this->setState('type', $type);
	}

	/**
	 * Retrieves a list of available payment gateways
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItems()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('payment');

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND LOWER(' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
		}

		$published = $this->getState('published');

		if ($published != 'all' && $published !== '') {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}

		$type = $this->getState('type');

		if ($type != 'all') {
			$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote($type);	
		}

		$this->setTotal($query, true);

		$query[] = 'ORDER BY `app_id` DESC';
		
		$result	= $this->getData($query);

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Retrieve a list of payment gateways installed and published
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItemsWithoutState($options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('payment');

		$published = PP::normalize($options, 'published', null);

		if (!is_null($published)) {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Retrieve payment apps that is associated with the plan without states and pagination
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentGateways(PPPlan $plan)
	{
		static $cache = array();
		$planId = $plan->getId();

		if (!isset($cache[$planId])) {
			$db = PP::db();

			$query = array();
			$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
			$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('payment');
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);

			$db->setQuery($query);
			$result = $db->loadObjectList();

			$apps = array();

			if ($result) {
				foreach ($result as $row) {
					$app = PP::app($row);
					$coreParams = $app->getCoreParams();

					if (!$coreParams->get('applyAll')) {
						$planApp = PP::model('planapp')->getAppPlans($app->getId());
						
						if (!in_array($planId, $planApp)){
							continue;
						}
					}

					$apps[] = $app;
				}
			}

			$cache[$planId] = $apps;
		}

		return $cache[$planId];
	}

	/**
	 * Retrieves a list of known payment app types
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTypes()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT DISTINCT(' . $db->qn('type') . ') FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('payment');

		$db->setQuery($query);
		$types = $db->loadColumn();

		return $types;
	}

	/**
	 * Retrieves a list of payment gateways 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApps()
	{
		static $gateways = null;

		if (is_null($gateways)) {
			$file = PP_ADMIN . '/defaults/gateways.json';
			$contents = JFile::read($file);
			
			$gateways = json_decode($contents);
		}

		return $gateways;
	}
}