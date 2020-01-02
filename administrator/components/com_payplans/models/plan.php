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

class PayplansModelPlan extends PayPlansModel
{
	public $filterMatchOpeartor = array(
									'title' 	=> array('LIKE'),
									'published' => array('='),
									'visible' 	=> array('=')
								);

	public function __construct()
	{
		parent::__construct('plan');
	}

	/**
	 * Initialize default states used by default
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$groupId = $this->getUserStateFromRequest('group_id', '', 'int');

		$this->setState('group_id', $groupId);
	}

	/**
	 * Determines if the current subscribed plan can be upgraded to a new plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canUpgradeTo(PPSubscription $subscription, $newPlanId)
	{
		if (!$subscription->canUpgrade($newPlanId)) {
			return false;
		}

		// Get a list of plans that the user can upgrade to
		$plans = $this->getAvailableUpgrades($subscription->getPlan()->getId());

		if (!$plans) {
			return false;
		}

		if (!in_array($newPlanId, $plans)) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a list of plans available for the current subscription plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvailableUpgrades($planId)
	{
		static $upgradeAppInstances = null;
		static $result = array();

		$planId = (int) $planId;

		// We need to get available upgrade app instances on the site
		if (is_null($upgradeAppInstances)) {
			$options = array(
				'published' => 1,
				'type' => 'upgrade'
			);

			$model = PP::model('App');
			$upgradeAppInstances = $model->loadRecords($options);
		}

		if (!isset($result[$planId])) {
			$plans = array();

			$appModel = PP::model('App');

			foreach ($upgradeAppInstances as $app) {
				$app = PP::app($app);
				$coreParams = $app->getCoreParams();
				$appParams = $app->getAppParams();

				$upgradableTo = $appParams->get('upgrade_to', array());

				if (!is_array($upgradableTo)) {
					$upgradableTo = array($upgradableTo);
				}

				if ($coreParams->get('applyAll')) {
					$plans = array_merge($plans, $upgradableTo);
					continue;
				}

				// If the upgrade rule is not applied to all, ensure that this plan is in their allowed list.
				$related = $appModel->isPlanRelated($app->getId(), $planId);

				if ($related) {
					$plans = array_merge($plans, $upgradableTo);
				}
			}

			// Ensure that there are no duplicates
			$plans = array_unique($plans);

			$result[$planId] = $plans;
		}

		return $result[$planId];
	}

	/**
	 * Retrieve plan's subscription count and its status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllSubscriptionStats()
	{
		$db = $this->db;

		$query = "select count(1) as `count`, `plan_id`, `status`";
		$query .= " from `#__payplans_subscription`";
		$query .= " group by plan_id, status";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	public function getItems()
	{
		$search = $this->getState('search');
		$state = $this->getState('published');
		$visible = $this->getState('visible');
		$ordering = $this->getState('ordering');
		$direction	= $this->getState('direction');

		$db = $this->db;

		$query = array();

		$query[] = 'SELECT a.*';
		$query[] = 'FROM ' . $db->qn('#__payplans_plan') . ' AS a';

		$wheres = array();

		$groupId = (int) $this->getState('group_id');

		if ($groupId) {
			$query[] = 'INNER JOIN ' . $db->qn('#__payplans_plangroup') . ' AS b';
			$query[] = 'ON a.' . $db->qn('plan_id') . ' = b.' . $db->qn('plan_id');

			$wheres[] = 'b.' . $db->qn('group_id') . '=' . $db->Quote($groupId);
		}

		if ($search) {
			$wheres[] = 'a.' . $db->nameQuote('title') . " like " . $db->Quote('%' . $search . '%');
		}

		if ($state != 'all' && $state != '') {
			$wheres[] = 'a.' . $db->nameQuote('published') . " = " . $db->Quote((int) $state);
		}

		if ($visible != 'all' && $visible != '' ) {
			$wheres[] = $db->nameQuote('a.visible') . " = " . $db->Quote((int) $visible);
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		if ($ordering) {
			$query .= " ORDER BY " . $ordering . " " . $direction;
		}

		$result	= $this->getData($query);

		return $result;
	}

	// XITODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}


	/**
	 * Publish / Unpublish plans from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function publish($ids, $state)
	{
		$db = PP::db();

		if (!is_array($ids)) {
			$ids = PP::makeArray($ids);
		}

		$query = "update `#__payplans_plan` set `published` = " . $db->Quote($state);
		$query .= " where `plan_id` IN (" . implode(',', $ids)  . ")";

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Deletes a plan from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete($pk=null)
	{
		// can not delete plan if app exists corresponding to that plan
		$plans = PP::model('planapp')->loadRecords(array('plan_id'=>$pk));

		if (!empty($plans)) {
			$this->setError(JText::_('COM_PAYPLANS_PLAN_CAN_NOT_DELETE_APP_EXISTS'));
			return false;
		}

		if (!parent::delete($pk)) {
			$db = JFactory::getDBO();
			XiError::raiseError(500, $db->getErrorMsg());
		}

		// delete plan from plangroup table
		return PP::model('plangroup')->deleteMany(array('plan_id' => $pk));
	}

	/**
	 * Deletes a relation between a plan and other apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deleteAppRelations($planId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__payplans_planapp');
		$query[] = 'WHERE ' . $db->qn('plan_id') . '=' . $db->Quote($planId);

		$db->setQuery($query);

		return $db->Query();
	}

	public function getUngrouppedPlans($queryFilters = array())
	{
		$db = PP::db();

		$query = 'SELECT * FROM `#__payplans_plan`';
		$query .= ' WHERE `plan_id` NOT IN (';
		$query .= '		SELECT DISTINCT pg.`plan_id` FROM `#__payplans_plangroup` AS pg';
		$query .= '		LEFT JOIN `#__payplans_group` AS g ON g.`group_id` = pg.`group_id`';
		$query .= '		WHERE g.`published` = ' . $db->Quote('1');
		$query .= ' )';
		$query .= ' AND `published` = ' . $db->Quote('1') . ' AND `visible` = ' . $db->Quote('1');
		$query .= ' ORDER BY `ordering` ASC';

		$db->setQuery($query);

		return $db->loadObjectList('plan_id');
	}

	public function getGrouppedPlans($queryFilters, $groupId)
	{
		$db = PP::db();

		$sql = " SELECT plans.* "
				." FROM ".$db->quoteName('#__payplans_plan')." as plans "
				." WHERE plans.`plan_id` IN ( "
					." SELECT DISTINCT ".$db->quoteName('plan_id')." "
					." FROM ".$db->quoteName('#__payplans_plangroup')." "
					." WHERE ".$db->quoteName('group_id')." = ". $groupId ." )";

		foreach($queryFilters as $key=>$value){
			$sql .= " AND ".$db->quoteName($key) ." = '".$value."' ";
		}

		$sql .= " ORDER BY plans.`ordering` ASC";

		$db->setQuery($sql);

		return $db->loadObjectList('plan_id');
	}

	/**
	 * Get total renewal per plan from given date range
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTotalRenewalPerPlan(PPDate $firstDate, PPDate $lastDate)
	{
		$db = $this->db;

		$query = 'SELECT group_concat(invoice.`invoice_id`) AS invoice_group, `object_id` AS order_id';
		$query .= ' FROM `#__payplans_payment` AS payment';
		$query .= ' LEFT JOIN `#__payplans_invoice` AS invoice ON invoice.`invoice_id` = payment.`invoice_id` and invoice.status = ' . $db->Quote(PP_INVOICE_PAID);
		$query .= ' LEFT JOIN `#__payplans_order`AS orders ON invoice.`object_id` = orders.`order_id`';
		$query .= ' WHERE orders.`params` not like CONCAT("%", "first_invoice_id\":", invoice.invoice_id, "%")';
		$query .= ' AND invoice.`paid_date` >= ' . $db->Quote($firstDate->toMySQL());
		$query .= ' AND invoice.`paid_date` <= ' . $db->Quote($lastDate->toMySQL());
		$query .= ' GROUP BY orders.`order_id`';

		$db->setQuery($query);
		$invoiceRecords = $db->loadObjectList('order_id');

		if (empty($invoiceRecords)) {
			return 0;
		}

		$keys = array_keys($invoiceRecords);
		$query = 'SELECT `order_id`, `plan_id` FROM `#__payplans_subscription` AS subs';
		$query .= ' WHERE subs.`order_id` in (' . implode(',', $keys) . ')';

		$db->setQuery($query);
		$subRecord = $db->loadObjectList('order_id');

		$count = array();

		foreach ($invoiceRecords as $key => $record) {
			$planId = $subRecord[$key]->plan_id;
			$invoiceGroup = $record->invoice_group;

			if (!is_array($invoiceGroup)) {
				$invoiceGroup = explode(',', $invoiceGroup);
			}

			$total = count($invoiceGroup);

			$count[$planId] = isset($count[$planId]) ? $count[$planId] + $total : $total;
		}

		return $count;
	}

	/**
	 * Retrieve plans based on plan ids
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlans($planIds = array())
	{
		if (!$planIds) {
			return false;
		}

		// Ensure that it is an array of plan ids
		if (!is_array($planIds)) {
			$planIds = array($planIds);
		}

		$db = PP::db();

		$query = 'SELECT * FROM `#__payplans_plan`';
		$query .= ' WHERE `plan_id` IN (' . implode(',', $planIds) . ')';
		$query .= ' AND `published` = ' . $db->Quote(1) . ' AND `visible` = ' . $db->Quote(1);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve a list of user existing plan ids
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserPlans($userid)
	{
		$db = PP::db();

		$query = 'SELECT DISTINCT `plan_id` AS `id` FROM `#__payplans_subscription`';
		$query .= ' WHERE `user_id` = ' . $db->Quote($userid);
		$query .= ' AND `status` = ' . $db->Quote(PP_SUBSCRIPTION_ACTIVE);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}	

	/**
	 * Saves the ordering of plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateOrdering($id, $order)
	{
		$db = PP::db();

		$query = "update `#__payplans_plan` set ordering = " . $db->Quote($order);
		$query .= " where plan_id = " . $db->Quote($id);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}
}

class PayplansModelformPlan extends PayPlansModelform {}
