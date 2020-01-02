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

class PayplansModelSubscription extends PayPlansModel
{

	public $crossTableNetwork = array(
									"users"=>array('users'),
									"usergroups"=>array('user_usergroup_map','usergroups')
								);

	//this is to ftech on condition for cross table
	public $innerJoinCondition = array(
									'tbl-users' => ' #__users as cross_users on tbl.user_id = cross_users.id',
									'tbl-user_usergroup_map' => ' #__user_usergroup_map as cross_user_usergroup_map on tbl.user_id = cross_user_usergroup_map.user_id',
									'user_usergroup_map-usergroups' => ' #__usergroups as cross_usergroups on cross_user_usergroup_map.group_id = cross_usergroups.id'
								);

	//need a opetor for newly addded cross field
	public $filterMatchOpeartor = array(
									'plan_id' => array('='),
									'status' => array('='),
									'subscription_date' => array('>=', '<='),
									'expiration_date' => array('>=', '<='),
									'cross_users_username' => array('LIKE'),
									'cross_usergroups_title' => array('LIKE')
								);

	public function __construct()
	{
		parent::__construct('subscription');
	}

	/**
	 * Initialize default states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$status = $this->getUserStateFromRequest('status', -1, 'int');
		$ordering = $this->getUserStateFromRequest('ordering', 'subscription_id', 'string');
		$direction = $this->getUserStateFromRequest('direction', 'DESC', 'string');
		$planId = $this->getUserStateFromRequest('plan_id', -1, 'int');

		$this->setState('status', $status);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);		
		$this->setState('plan_id', $planId);
	}

	// XITODO : HIGH : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	public function getItems()
	{
		$planId = $this->getState('plan_id');
		$status = $this->getState('status');
		$username = $this->getState('username');
		$subDate = $this->getState('subscription_date');
		$expDate = $this->getState('expiration_date');

		$db = $this->db;

		$query = array();

		$query[] = "select a.*";
		$query[] = " from `#__payplans_subscription` as a";
		$query[] = 'INNER JOIN `#__users` AS b ON a.`user_id` = b.`id`';
		$query[] = 'INNER JOIN `#__payplans_plan` AS c ON a.`plan_id` = c.`plan_id`';
		$query[] = 'INNER JOIN `#__payplans_order` AS d ON a.`order_id` = d.`order_id`';

		$wheres = array();

		if ($planId && $planId != '-1') {
			$wheres[] = $db->nameQuote('a.plan_id') . " = " . $db->Quote((int) $planId );
		}

		if ($status !== -1 && $status !== '') {
			$wheres[] = $db->nameQuote('a.status') . " = " . $db->Quote((int) $status);
		}

		// We do not want to display unconfirmed orders
		$wheres[] = 'd.' . $db->qn('status') . '!=' . $db->Quote(0);

		// Date range filter
		$dateRange = $this->getState('dateRange');

		if (!is_null($dateRange)) {
			// If the start and end date is the same, we need to add 1 day to the end
			$end = $this->getEndingDateRange($dateRange['start'], $dateRange['end']);

			$wheres[] = $db->qn('subscription_date') . '>' . $db->Quote($dateRange['start']);
			$wheres[] = $db->qn('subscription_date') . '<' . $db->Quote($end);
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$search = $this->getState('search');

		if ($search) {
			$searchQuery = array();

			// Search by plan title
			$searchQuery[] = 'LOWER(c.' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');

			// Search by username, email or name
			$searchQuery[] = 'LOWER(b.' . $db->qn('name') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
			$searchQuery[] = 'LOWER(b.' . $db->qn('username') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
			$searchQuery[] = 'LOWER(b.' . $db->qn('email') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');

			// Search text could be subscription key
			$searchQuery[] = 'LOWER(a.' . $db->qn('subscription_id') . '=' . $db->Quote(PP::getIdFromKey($search)) . ')';
			$searchQuery[] = 'LOWER(a.' . $db->qn('subscription_id') . '=' . $db->Quote($search) . ')';

			$searchQuery = implode(' OR ', $searchQuery);

			if ($where) {
				$where .= 'AND (' . $searchQuery . ')';
			}

			if (!$where) {
				$where = 'WHERE (' . $searchQuery . ')';
			}
		}

		$query = implode(' ', $query);
		$query .= $where;

		$ordering = $this->getState('ordering');
		$direction = $this->getState('direction');

		$orderQuery = 'ORDER BY ' . $db->qn('subscription_id') . ' DESC';

		if ($ordering && $direction) {
			$orderQuery = 'ORDER BY ' . $db->qn($ordering) . ' ' . strtoupper($direction);	
		}
		
		$query .= $orderQuery;

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$subscriptions = array();

		if ($result) {
			foreach ($result as $record) {
				$subscription = PP::subscription($record);
				$subscriptions[] = $subscription;
			}
		}

		return $subscriptions;
	}

	/**
	 * Retrieve subscriptions without utilizing the states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItemsWithoutState($options = array())
	{
		$db = PP::db();

		$query = array();
		$wheres = array();

		$query[] = 'SELECT a.* FROM ' . $db->qn('#__payplans_subscription')  . ' AS a';
		$query[] = 'INNER JOIN ' . $db->qn('#__users') . ' AS b';
		$query[] = 'ON a.' . $db->qn('user_id') . ' = b.' . $db->qn('id');
		$query[] = 'INNER JOIN ' . $db->qn('#__payplans_plan') . ' AS c';
		$query[] = 'ON a.' . $db->qn('plan_id') . ' = c.' . $db->qn('plan_id');

		$userId = PP::normalize($options, 'userId', null);

		if ($userId) {
			$wheres[] = 'a.' . $db->qn('user_id') . '=' . $db->Quote((int) $userId);
		}

		$hidePendingOrder = PP::normalize($options, 'hidePendingOrder', null);

		if ($hidePendingOrder) {
			$wheres[] = 'a.' . $db->qn('status') . '!=' . $db->Quote(0);
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		// echo str_ireplace('#__', 'jos_', $query);exit;

		$ordering = PP::normalize($options, 'ordering', 'subscription_id');
		$direction = PP::normalize($options, 'direction', 'DESC');

		$query .= ' ORDER BY a.' . $db->qn($ordering) . ' ' . strtoupper($direction);	
		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$subscriptions = array();

		if ($result) {
			foreach ($result as $record) {
				$subscription = PP::subscription($record);
				$subscriptions[] = $subscription;
			}
		}

		return $subscriptions;
	}

	/**
	 * Find all active subscriptions which are expected to expire soon
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExpiredSubscriptions($time = null)
	{
		if ($time === null) {
			$time = JFactory::getDate();
		}

		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_subscription') . ' AS a';
		$query[] = 'WHERE a.' . $db->qn('expiration_date') . '<' . $db->Quote($time->toSql());
		$query[] = 'AND a.' . $db->qn('expiration_date') . '<>' . $db->Quote('0000-00-00 00:00:00');
		$query[] = 'AND a.' . $db->qn('status') . '=' . $db->Quote(PP_SUBSCRIPTION_ACTIVE);
		$query[] = 'AND a.' . $db->qn('lock') . '=' . $db->Quote(0);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($result) {
			$ids = array();

			foreach ($result as $item) {
				$ids[] = $item->subscription_id;
			}

			// lets lock the items here.
			$this->lock($ids);
		}

		return $result;
	}

	/**
	 * Lock the subscription items for cron process so that other cron process
	 * will not able to access these items.
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function lock($ids)
	{
		$db = PP::db();

		$ids = PP::makeArray($ids);

		$query = "update `#__payplans_subscription`";
		$query .= " SET `lock` = 1";
		$query .= " where subscription_id IN (" . implode(',', $ids) . ")";

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * UnLock the subscription items after the cron process.
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function unlock($ids)
	{
		$db = PP::db();

		$ids = PP::makeArray($ids);

		$query = "update `#__payplans_subscription`";
		$query .= " SET `lock` = 0";
		$query .= " where subscription_id IN (" . implode(',', $ids) . ")";

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Get subscriptions that are about to expire from the given pre expiry time
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function getPreExpirySubscriptions(Array $plans, $preExpiryTime, $onAllPlans = false)
	{
		$e1 = PP::date(PP::config()->get('cronAcessTime'));
		$e2	= PP::date('now');

		$e2->addExpiration($preExpiryTime);

		$db = PP::db();

		$query = 'SELECT * FROM ' . $db->qn('#__payplans_subscription');
		$query .= ' WHERE ' . $db->qn('expiration_date') . ' > ' . $db->Quote($e1->toSql());
		$query .= ' AND ' . $db->qn('expiration_date') . ' < ' . $db->Quote($e2->toSql());
		$query .= ' AND ' . $db->qn('status') . ' = ' . $db->Quote(PP_SUBSCRIPTION_ACTIVE);

		if (!$onAllPlans) {
			$query .= ' AND `plan_id` IN (' . implode(',', $plans) . ')';
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get subscriptions that just expire from the given post expiry time
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function getPostExpirySubscriptions(Array $plans, $postExpiryTime, $onAllPlans = false)
	{
		$e1 = PP::date(PP::config()->get('cronAcessTime'));
		$e2	= PP::date('now');

		$e2->addExpiration($postExpiryTime);

		$db = PP::db();

		$query = 'SELECT * FROM ' . $db->qn('#__payplans_subscription');
		$query .= ' WHERE ' . $db->qn('expiration_date') . ' > ' . $db->Quote($e1->toSql());
		$query .= ' AND ' . $db->qn('expiration_date') . ' < ' . $db->Quote($e2->toSql());
		$query .= ' AND ' . $db->qn('status') . ' = ' . $db->Quote(PP_SUBSCRIPTION_EXPIRED);

		if (!$onAllPlans) {
			$query .= ' AND `plan_id` IN (' . implode(',', $plans) . ')';
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve the actively new subscriptions from the given post activation time
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function getPostActivationSubscriptions(Array $plans, $postActivationTime, $onAllPlans = false)
	{
		$e1 = PP::date(PP::config()->get('cronAcessTime'));
		$e2	= PP::date('now');

		$e2->addExpiration($postActivationTime);

		$db = PP::db();

		$query = 'SELECT * FROM ' . $db->qn('#__payplans_subscription');
		$query .= ' WHERE ' . $db->qn('expiration_date') . ' > ' . $db->Quote($e1->toSql());
		$query .= ' AND ' . $db->qn('expiration_date') . ' < ' . $db->Quote($e2->toSql());
		$query .= ' AND ' . $db->qn('status') . ' = ' . $db->Quote(PP_SUBSCRIPTION_ACTIVE);

		if (!$onAllPlans) {
			$query .= ' AND `plan_id` IN (' . implode(',', $plans) . ')';
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns total sales according to their respective plans, between given starting date and ending date
	*/
  public function getSalesOfPlans(PPDate $firstDate, PPDate $endDate)
	{
		$query = $this->db->getQuery(true);

		$query->select('plan_id,count(subscription_id) as sales, date(subscription_date) as sub_date')
				->from('`#__payplans_subscription`')
				->where('subscription_date >= '."'".$firstDate->toMySQL()."'")
				->where('subscription_date <= '."'".$endDate->toMySQL()."'")
				->group('date(subscription_date)')
				->group('plan_id');

		$record = $this->db->setQuery($query)->loadObjectList();
		$sales  = array();

		if ($record) {
			foreach ($record as $key => $value) {
				$sales[$value->plan_id][$value->sub_date] = $value->sales;
			}
		}

		return $sales;

	}

	public function getRecentSubscriptions($limit = 5, $offset = 0)
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
				->from('#__payplans_subscription')
				->where('`subscription_date` <>'."'".'0000-00-00 00:00:00'."'")
				->order('`subscription_date` DESC')
				->limit($limit, $offset);

		return $this->db->setQuery($query)->loadObjectList('subscription_id');
	}

	/**
	 * Generates count for subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTotalSubscriptions($status)
	{
		$db = PP::db();
		$query = array();

		$query[] = 'SELECT count(1) FROM ' . $db->qn('subscription_id');
		$query[] = 'FROM ' . $db->qn('#__payplans_subscription');
		$query[] = 'WHERE ' . $db->qn('status') . '=' . $db->Quote($status);

		$db->setQuery($query);
		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Returns total upgrades according to their respective plans, between given starting date and ending date
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUpgradesOfPlans(PPDate $firstDate, PPDate $endDate)
	{
		//SELECT count(distinct orders.order_id) as upgCount, sub.plan_id as plan_id
		//FROM `j542_payplans_order` as orders left join `j542_payplans_invoice` as invoice
		//on orders.order_id=invoice.object_id and invoice.status=402 left JOIN `j542_payplans_subscription`
		// as sub ON sub.order_id = orders.order_id where orders.params like concat('%"upgrading%') group by sub.plan_id,sub_subscripton_date;
		$query = $this->db->getQuery(true);

		$query->select('count(distinct orders.order_id) as upgCount, sub.plan_id as plan_id , date(sub.subscription_date) as sub_date')
				->from('`#__payplans_order` AS orders')
				->leftJoin('`#__payplans_invoice` AS invoice ON invoice.`object_id` = orders.`order_id` and invoice.status='.PP_INVOICE_PAID)
				->leftJoin('`#__payplans_subscription` AS sub ON sub.`order_id` = orders.`order_id`')
				->where('orders.`params` like CONCAT("%","upgrading", "%")')
				->where('sub.`subscription_date` >= '."'".$firstDate->toMySQL()."'")
				->where('sub.`subscription_date` <= '."'".$endDate->toMySQL()."'")
				->group('date(sub.`subscription_date`)')
				->group('sub.`plan_id`');

		$results = $this->db->setQuery($query)->loadObjectList();

		$sales = array();
		if ($results) {
			foreach ($results as $key => $value) {
				$sales[$value->plan_id][$value->sub_date] = $value->upgCount;
			}
		}

		return $sales;

	}

	/**
	 * Retrieve record to export to CSV
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getDataToExport($options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = "SELECT " . $db->nameQuote('a.subscription_id') . ", ". $db->nameQuote('b.username') . ", " . $db->nameQuote('a.user_id') . ", " . $db->nameQuote('a.status') . ", " . $db->nameQuote('a.total') . ", " . $db->nameQuote('a.subscription_date') . ", " . $db->nameQuote('a.expiration_date');
		$query[] = "from `#__payplans_subscription` as a";
		$query[] = "inner join `#__users` as b on b.id = a.user_id";

		if (isset($options['plans']) && $options['plans']) {
			$query[] = "AND a.plan_id in (".implode(',', $options['plans']).")";
		}

		if (isset($options['status']) && $options['status']) {
			$query[] = "AND a.status in (".implode(',',$options['status']).")";
		}

		if (isset($options['dateFrom']) && $options['dateFrom']) {
			$query[] = "AND a.subscription_date >= " . $db->Quote($options['dateFrom']);
		}

		if (isset($options['dateTo']) && $options['dateTo']) {
			$query[] = "AND a.subscription_date <= " . $db->Quote($options['dateTo']);
		}

		$query[] = "group by a.subscription_id LIMIT " . $options['limit'];

		$query = implode(' ', $query);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	public function getStatusString($statusCode)
	{
		$text = 'COM_PP_SUBSCRIPTION_NONE';

		if ($statusCode == 1601) {
			$text = 'COM_PP_SUBSCRIPTION_ACTIVE';
		}

		if ($statusCode == 1602) {
			$text = 'COM_PP_SUBSCRIPTION_HOLD';
		}

		if ($statusCode == 1603) {
			$text = 'COM_PP_SUBSCRIPTION_EXPIRED';
		}

		return JText::_($text);
	}

	/**
	 * Get active subscription from given date range
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getActiveSubscription(PPdate $firstDate, PPDate $lastDate)
	{
		$db = $this->db;

		$query = 'SELECT count(*) as count, date(subscription_date) FROM `#__payplans_subscription`';
		$query .= ' WHERE `subscription_date` >= ' . $db->Quote($firstDate->toMySQL());
		$query .= ' AND `subscription_date` <= ' . $db->Quote($lastDate->toMySQL());
		$query .= ' GROUP BY date(`subscription_date`)';

		$db->setQuery($query);

		return $db->loadObjectList('date(subscription_date)');
	}

	/**
	 * Get expire subscription from given date range
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getExpireSubscription(PPDate $firstDate, PPDate $lastDate)
	{
		$db = $this->db;

		$query = 'SELECT count(*) as count, date(expiration_date) FROM `#__payplans_subscription`';
		$query .= ' WHERE `expiration_date` >= ' . $db->Quote($firstDate->toMySQL());
		$query .= ' AND `expiration_date` <= ' . $db->Quote($lastDate->toMySQL());
		$query .= ' GROUP BY date(`expiration_date`)';

		$db->setQuery($query);

		return $db->loadObjectList('date(expiration_date)');
	}

	/**
	 * Get user subscriptions from the status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUserSubscription($options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = "SELECT " . $db->nameQuote('a.subscription_id') . ", " . $db->nameQuote('b.title'). ", " . $db->nameQuote('a.plan_id') . ", " . $db->nameQuote('a.order_id') . ", " . $db->nameQuote('a.status') . ", " . $db->nameQuote('a.subscription_date') . ", " . $db->nameQuote('a.expiration_date') . ", " . $db->nameQuote('a.params');
		$query[] = "from `#__payplans_subscription` as a";
		$query[] = "inner join `#__payplans_plan` as b on a.plan_id = b.plan_id";
		$query[] = "where `user_id` = " . $db->Quote($options['userId']);
		$query[] = "and `status` in (".implode(',', $options['status']).")";
		$query[] = "limit " . $options['limit'];

		$query = implode(' ', $query);

		$db->setQuery($query);
		$subscriptions = $db->loadObjectList();

		return $subscriptions;
	}
}


class PayplansModelformSubscription extends PayPlansModelform {}
