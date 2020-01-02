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

class PayplansModelTransaction extends PayPlansModel
{

	public $crossTableNetwork 	= array(
								"users"=>array('users'),
								"payment"=>array('payment')
								);

	//this is to ftech on condition for cross table
	public $innerJoinCondition = array(
								'tbl-users' => ' #__users as cross_users on tbl.user_id = cross_users.id',
								'tbl-payment' => ' #__payplans_payment as cross_payment on cross_payment.payment_id = tbl.payment_id'
	);

	public $filterMatchOpeartor = array(
										'invoice_id' => array('='),
										'user_id' => array('='),
										'amount' => array('>=', '<='),
										'created_date' => array('>=', '<='),
										'cross_users_username' => array('LIKE'),
										'cross_payment_app_id' => array('=')
	);

	public function __construct()
	{
		parent::__construct('transaction');
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

		$ordering = $this->getUserStateFromRequest('ordering', 'created_date');
		$appId = $this->getUserStateFromRequest('app_id', null);
		
		$this->setState('app_id', $appId);
		$this->setState('ordering', $ordering);
	}

	public function getItems()
	{
		$createdDate = $this->getState('created_date');	
		$amount = $this->getState('amount');
		$username = $this->getState('username');
		$invoiceId = $this->getState('invoice_id');
		$appId = $this->getState('app_id');

		$db = $this->db;
		$query = array();

		$query[] = 'SELECT a.*';
		$query[] = 'FROM `#__payplans_transaction` AS a';
		$query[] = 'INNER JOIN `#__users` AS b ON a.`user_id` = b.`id`';
		$query[] = 'INNER JOIN `#__payplans_invoice` AS c ON a.`invoice_id` = c.`invoice_id`';
		$query[] = 'LEFT JOIN `#__payplans_payment` AS d ON a.`payment_id` = d.`payment_id`';
		$query[] = 'LEFT JOIN `#__payplans_app` AS e ON d.`app_id` = e.`app_id`';

		$wheres = array();

		if ($amount) {
			$wheres[] = $db->nameQuote('a.amount') . ' = ' . $db->Quote((int) $amount);
		}

		if ($invoiceId) {
			$wheres[] = $db->nameQuote('a.invoice_id') . ' = ' . $db->Quote($invoiceId);
		}

		if ($appId) {
			$wheres[] = 'e.' . $db->nameQuote('app_id') . ' = ' . $db->Quote((int) $appId);
		}

		// Date range filter
		$dateRange = $this->getState('dateRange');

		if (!is_null($dateRange)) {
			// If the start and end date is the same, we need to add 1 day to the end
			$end = $this->getEndingDateRange($dateRange['start'], $dateRange['end']);

			$wheres[] = 'a.' . $db->qn('created_date') . '>' . $db->Quote($dateRange['start']);
			$wheres[] = 'a.' . $db->qn('created_date') . '<' . $db->Quote($end);
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$search = $this->getState('search');

		if ($search) {
			$searchQuery = array();

			// Search by amount
			$searchQuery[] = 'a.`amount` LIKE ' . $db->Quote('%' . $search . '%');

			// Search by username, email or name
			$searchQuery[] = 'LOWER(b.' . $db->qn('name') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
			$searchQuery[] = 'LOWER(b.' . $db->qn('username') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
			$searchQuery[] = 'LOWER(b.' . $db->qn('email') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');

			// Search text could be invoice key
			$searchQuery[] = 'LOWER(c.' . $db->qn('invoice_id') . '=' . $db->Quote(PP::getIdFromKey($search)) . ')';
			$searchQuery[] = 'LOWER(c.' . $db->qn('invoice_id') . '=' . $db->Quote($search) . ')';

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
		
		$query .= ' ORDER BY a.' . $db->qn($ordering) . ' ' . $direction;

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		return $result;
	}

	/**
	 * Retrieve transactions without using states
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItemsWithoutState($options = array())
	{
		$db = $this->db;
		$query = array();

		$query[] = 'SELECT a.*';
		$query[] = 'FROM `#__payplans_transaction` AS a';
		$query[] = 'INNER JOIN `#__users` AS b ON a.`user_id` = b.`id`';
		$query[] = 'INNER JOIN `#__payplans_invoice` AS c ON a.`invoice_id` = c.`invoice_id`';
		$query[] = 'LEFT JOIN `#__payplans_payment` AS d ON a.`payment_id` = d.`payment_id`';
		$query[] = 'LEFT JOIN `#__payplans_app` AS e ON d.`app_id` = e.`app_id`';

		$wheres = array();

		$invoiceId = PP::normalize($options, 'invoice_id', '');

		if ($invoiceId) {
			$wheres[] = $db->nameQuote('a.invoice_id') . ' = ' . $db->Quote($invoiceId);
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$ordering = PP::normalize($options, 'ordering', 'transaction_id');
		$direction = PP::normalize($options, 'direction', 'DESC');
		
		$query .= ' ORDER BY a.' . $db->qn($ordering) . ' ' . $direction;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the latest transaction for invoice that has amount associated with it
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLatestTransactionWithAmount($invoiceId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_transaction');
		$query[] = 'WHERE ' . $db->qn('invoice_id') . '=' . $db->Quote((int) $invoiceId);
		$query[] = 'AND ' . $db->qn('amount') . '>' . $db->Quote(0);
		$query[] = 'ORDER BY ' . $db->qn('transaction_id') . ' DESC';

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result) {
			return $result;
		}

		$transaction = PP::transaction($result);
		
		return $transaction;
	}

	public function getRecentTransactions($limit = 5, $offset = 0)
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
				->from('#__payplans_transaction')
				->order('`created_date` DESC')
				->limit($limit, $offset);

		return $this->db->setQuery($query)->loadObjectList('transaction_id');
	}

	/**
	 * Returns total revenue of individual plans between given starting date and ending date
	*/
	public function getRevenuesOfPlans(PPDate $firstDate, PPDate $lastDate)
	{
//    	SELECT subscription.`plan_id` as plan_id, sum(transaction.`amount`) as amount
//			FROM `j285_payplans_transaction` as transaction
//			INNER JOIN `j285_payplans_invoice` as invoice
//					ON transaction.`invoice_id` = invoice.`invoice_id`
//						and transaction.`created_date` >= '2012-10-02 00:00:00'
//						and transaction.`created_date` <= '2012-10-02 23:59:59'
//			LEFT JOIN `j285_payplans_subscription` as subscription
//					ON invoice.`object_id` = subscription.`order_id`
//			GROUP BY subscription.`plan_id`,date(subscription_date);

		$query = $this->db->getQuery(true);
		$query->select('subscription.`plan_id` as plan_id, sum(transaction.`amount`) as amount, date(subscription_date) as sub_date')
					->from('`#__payplans_transaction` as transaction')
					->innerJoin('`#__payplans_invoice` as invoice ON transaction.`invoice_id` = invoice.`invoice_id`'
						 .'and transaction.`created_date` >= '."'". $firstDate->toMySQL() . "'"
						 .'and transaction.`created_date` <= '."'". $lastDate->toMySQL() . "'")
					->leftJoin('`#__payplans_subscription` as subscription ON invoice.`object_id` = subscription.`order_id`')
					->group('subscription.`plan_id`')
					->group('date(subscription_date)');

		$record = $this->db->setQuery($query)->loadObjectList();
		$revenue = array();
		if ($record) {
			foreach ($record as $key => $value) {
				$revenue[$value->plan_id][$value->sub_date] = $value->amount;

			}
		}

		return $revenue;

	}
}
