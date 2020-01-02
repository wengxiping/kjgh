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

class PayPlansModelReferrals extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('referrals');
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
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('referral');

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND LOWER(' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
		}

		$published = $this->getState('published');

		if (!is_null($published) && $published != 'all' && $published !== '') {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}
		
		$query = implode(' ', $query);

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$app->params = $app->getAppParams();

				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Determines if there are any referral apps that should be rendered for a given plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApplicableApp(PPPlan $plan)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('referral');
		$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);
		
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		foreach ($result as $row) {
			$app = PP::app($row);
			$params = $app->getCoreParams();

			if ($params->get('applyAll')) {
				return $app;
			}

			// Try to match to see if plan id matches any one of the plan
			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_planapp') . ' WHERE `plan_id`=' . $db->Quote($plan->getId()) . ' AND `app_id`=' . $db->Quote($app->getId());
			$db->setQuery($query);
			$exists = $db->loadResult() > 0;

			if ($exists) {
				return $app;
			}
		}

		// When it reaches here, we know there are no association with the current plan
		return false;
	}

	/**
	 * Determines if there are any referral apps that should be rendered for a given plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApplicableApps(PPPlan $plan)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('referral');
		$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);
		
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		$apps = array();

		foreach ($result as $row) {
			$app = PP::app($row);
			$params = $app->getCoreParams();

			if ($params->get('applyAll')) {
				$apps[] = $app;
				continue;
			}

			// Try to match to see if plan id matches any one of the plan
			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_planapp') . ' WHERE `plan_id`=' . $db->Quote($plan->getId()) . ' AND `app_id`=' . $db->Quote($app->getId());
			$db->setQuery($query);
			$exists = $db->loadResult() > 0;

			if ($exists) {
				$apps[] = $app;
				continue;
			}
		}

		return $apps;
	}

	/**
	 * Determines if there are any referral apps that should be rendered for a given plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasApplicableApp(PPPlan $plan)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('referral');
		$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote(1);
		
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		foreach ($result as $row) {
			$app = PP::app($row);
			$params = $app->getCoreParams();

			if ($params->get('applyAll')) {
				return true;
			}

			// Try to match to see if plan id matches any one of the plan
			$query = array();
			$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_planapp') . ' WHERE `plan_id`=' . $db->Quote($plan->getId()) . ' AND `app_id`=' . $db->Quote($app->getId());
			$db->setQuery($query);
			$exists = $db->loadResult() > 0;

			if ($exists) {
				return $exists;
			}
		}

		// When it reaches here, we know there are no association with the current plan
		return false;
	}

	/**
	 * Retrieves the Referral User's
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getReferralDetails(PPUser $user)
	{
		// Get how many referral code utilized for Referrar
		$db = PP::db();
		$query = array();
		$query[] = 'SELECT * FROM `#__payplans_referral` WHERE `referrar_id`=' . $db->Quote($user->getId());
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
}