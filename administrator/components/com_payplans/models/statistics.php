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

class PayplansModelStatistics extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('statistics');
	}

	/**
	 * Insert new statistics data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function insertStatisticsData($value)
	{
		$db = $this->db;
		$today = PP::date('now');

		$str = '('. $db->Quote($value['statistics_type']) . ',' . $value['purpose_id_1'] . ',' . (isset($value['purpose_id_2']) ? $value['purpose_id_2'] : 0);

		for ($count = 1; $count <= 4; $count++) {
			$str .= ',' . (isset($value['count_' . $count]) ? $value['count_' . $count] : 0);
		}

		$str .= ',' . $db->Quote(isset($value['details_1']) ? htmlentities($value['details_1'], ENT_QUOTES) : ' ');
		$str .= ',' . $db->Quote(isset($value['details_2']) ? htmlentities($value['details_2'], ENT_QUOTES) : ' ');
		$str .= ',' . $db->Quote(isset($value['message']) ? htmlentities($value['message'], ENT_QUOTES) : ' ');
		$str .= ',' . $db->Quote($value['statistics_date']->toMySQL(false, '%Y-%m-%d'));
		$str .= ',' . $db->Quote($today->toMySQL());
		$str .= ')';

		$query  = 'INSERT INTO `#__payplans_statistics`';
		$query .= ' (`statistics_type`, `purpose_id_1`, `purpose_id_2`, `count_1`, `count_2`, `count_3`, `count_4`, `details_1`, `details_2`, `message`, `statistics_date`, `modified_date`)';
		$query .= ' VALUES ' . $str;

		$db->setQuery($query);

		if (!$db->query()) {
			return false;
		}
	}

	/**
	 * Update the existings statistics data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updateStatiscticsData($values)
	{
		$db = $this->db;

		$now = PP::date('now');
		$modified_date = $now->toMySQL();
		$statistics_date = $values['statistics_date'];

		$setQuery = '`purpose_id_2` = ' . $db->Quote($values['purpose_id_2']);

		for ($count = 1; $count <= 4; $count++) {
			$setQuery .= ', `count_' . $count . '` = ' . $db->Quote($values['count_' . $count]);
		}

		$setQuery .= ', `details_1` = ' . $db->Quote($values['details_1']);
		$setQuery .= ', `details_2` = ' . $db->Quote($values['details_2']);
		$setQuery .= ', `message` = ' . $db->Quote($values['message']);
		$setQuery .= ', `modified_date` = ' . $db->Quote($modified_date);

		$query = 'UPDATE `#__payplans_statistics`';
		$query .= ' SET ' . $setQuery;
		$query .= ' WHERE `statistics_type` = ' . $db->Quote($values['statistics_type']);
		$query .= ' AND `purpose_id_1` = ' . $db->Quote($values['purpose_id_1']);
		$query .= ' AND date(`statistics_date`) = ' . $db->Quote($statistics_date->toMySQL(false, '%Y-%m-%d'));

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Method to retrieve latest statistics date on given type
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getLatestStatisticsDate($type)
	{
		$db = $this->db;

		$query = 'SELECT max(`statistics_date`) as latest FROM `#__payplans_statistics`';
		$query .= ' WHERE `statistics_type` = ' . $db->Quote($type);

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Get sum of statistic records for either plan or subscriptions statistic
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getSumOfRecords($options = array())
	{
		$db = $this->db;

		// Normalize options
		$statisticType = isset($options['statistics_type']) ? $options['statistic_type'] : 'plan';
		$firstDate = isset($options['firstDate']) ? $options['firstDate'] : false;
		$lastDate = isset($options['lastDate']) ? $options['lastDate'] : false;
		$allTimeRecords = isset($options['allTimeRecords']) ? $options['allTimeRecords'] : false;

		$query = 'SELECT date(`statistics_date`) as statistics_date, purpose_id_1 as plan_id, details_1 as title';

		for ($count = 1; $count < 4; $count++) { 
			$column = 'count_' . $count;
			$query .= ', sum(' . $db->nameQuote($column) . ') as ' . $column;
		}

		$query .= ' FROM `#__payplans_statistics`';

		$query .= ' WHERE `statistic_plan` = ' . $db->Quote($statisticType);

		if (!$allTimeRecords && ($firstDate || $lastDate)) {
			if ($firstDate) {
				$query .= ' AND date(statistic_date) >= date(' . $firstDate . ')';
			}

			if ($lastDate) {
				$query .= ' AND date(statistic_date) <= date(' . $lastDate . ')';
			}
		}

		$query .= ' GROUP BY plan_id, statistic_date';

		$db->setQuery($query);

		$records = $db->loadObjectList();

		return $records;
	}

	/**
	 * Clear all the statistics records
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function truncateStatistics()
	{
		$db = $this->db;

		$query = 'TRUNCATE `#__payplans_statistics`';

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Get statistic for active and expired subscription
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAllActiveExpiredSubscription($firstDate, $lastDate)
	{
		$db = $this->db;

		$status = array(PP_SUBSCRIPTION_EXPIRED, PP_SUBSCRIPTION_ACTIVE);

		$query = 'SELECT count(`subscription_id`) as count, status FROM `#__payplans_subscription`';
		$query .= ' WHERE `status` IN(' . implode(',', $status) . ')';
		$query .= ' AND date(`subscription_date`) >= ' . 'date(' . $db->Quote($firstDate) . ')';
		$query .= ' AND date(`subscription_date`) <= ' . 'date(' . $db->Quote($lastDate) . ')';
		$query .= ' GROUP BY `status`';

		$db->setQuery($query);

		return $db->loadObjectList('status');
	}

	/**
	 * Retrieve plan data within specified date
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPlanDataWithinDates($firstDate, $lastDate, $type = PP_STATISTICS_TYPE_ALL)
	{
		$db = $this->db;

		// purpose_id_1 = plan_id
		// details_1 = title
		$query = 'SELECT date(`statistics_date`) as statistics_date, purpose_id_1 as plan_id, details_1 as title';

		// count_1 = total sales
		// count_2 = total revenue
		// count_3 = total renewals
		// count_4 = total upgrades
		$columnType = array('count_1' => 'sales', 'count_2' => 'revenue', 'count_3' => 'renewals', 'count_4' => 'upgrades');

		if ($type == 'all') {
			for ($count = 1; $count <= 4; $count++) { 
				$column = 'count_' . $count;
				$query .= ', sum(' . $db->nameQuote($column) . ') as ' . $columnType[$column];
			}
		} else {
			foreach ($columnType as $column => $value) {
				if ($type == $value) {
					$query .= ', sum(' . $db->nameQuote($column) .') as ' . $type;
					break;
				}
			}
		}

		$query .= ' FROM `#__payplans_statistics`';

		$query .= ' WHERE `statistics_type` = ' . $db->Quote('plan');
		$query .= ' AND date(statistics_date) >= ' . 'date(' . $db->Quote($firstDate) . ')';
		$query .= ' AND date(statistics_date) <= ' . 'date(' . $db->Quote($lastDate) . ')';

		$query .= ' GROUP BY plan_id, statistics_date';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve subscription stats within specified date
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionDataWithinDates($firstDate, $lastDate)
	{
		$db = $this->db;

		$query = 'SELECT date(`statistics_date`) as statistics_date';

		// count_1 = active subscription
		// count_2 = expired subscription
		$columnType = array('count_1' => 'active', 'count_2' => 'expire');

		for ($count = 1; $count <= 2; $count++) { 
			$column = 'count_' . $count;
			$query .= ', sum(' . $db->nameQuote($column) . ') as ' . $columnType[$column];
		}

		$query .= ' FROM `#__payplans_statistics`';

		$query .= ' WHERE `statistics_type` = ' . $db->Quote('subscription');
		$query .= ' AND date(statistics_date) >= ' . 'date(' . $db->Quote($firstDate) . ')';
		$query .= ' AND date(statistics_date) <= ' . 'date(' . $db->Quote($lastDate) . ')';

		$query .= ' GROUP BY purpose_id_1, statistics_date';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get the oldest date for the statistics to start process
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getOldestDate()
	{
		$db = $this->db;

		$query = 'SELECT min(date(`modified_date`)) as latest FROM `#__payplans_subscription` WHERE  `modified_date` > "0000-00-00"';

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}
}
