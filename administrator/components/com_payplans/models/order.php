<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

PP::import('admin:/includes/model');

class PayPlansModelOrder extends PayPlansModel
{
	//protected $_hasone  = array('orderfield' => array('foreignKey'=>'order_id'));
	protected $_hasmany = array('orderitem' => array('foreignKey'=>'order_id'));

	public $filterMatchOpeartor = array(
										'status' => array('='),
										'total' => array('>=', '<=')
										);

	public function __construct()
	{
		parent::__construct('order');
	}

	// XITODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	public function getItems()
	{
		$total = $this->getState('total');
		$status = $this->getState('status');
		$ordering = $this->getState('ordering');
		$direction	= $this->getState('direction');

		$db = $this->db;

		$query = array();

		$query[] = "select a.*";
		$query[] = " from `#__payplans_order` as a";

		$wheres = array();

		if ($status != 'all' && $status != '') {
			$wheres[] = $db->nameQuote('a.status') . " = " . $db->Quote((int) $status);
		}

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$orders = array();

		if ($result) {
			foreach ($result as $record) {
				$order = PP::order($record);
				$orders[] = $order;
			}
		}

		return $orders;
	}

	/*
	 * Count number of total records as per current query
	 * clean the query element
	 */
	public function getTotal($queryClean = array('select','limit','order'))
	{
		//for pagination in frontend
		//order total is calculated for the user logged in
		if (JFactory::getApplication()->isSite()) {
			$userId =  JFactory::getUser()->id;
			$query = $this->getQuery();

			//Support query cleanup
			$tmpQuery = clone($query);

			foreach ($queryClean as $clean) {
				$tmpQuery->clear(JString::strtolower($clean));
			}

			$tmpQuery->select('COUNT(*)')
					 ->where('buyer_id = '.$userId);


			$this->_total = $this->db->setQuery($tmpQuery)->loadResult();

			return $this->_total;
		} else {
			return parent::getTotal();
		}
	}

	/**
	 * Find all orders that are older from the given time
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDummyOrders($time = null, $status = PP_NONE, $subStatus = PP_NONE)
	{
		if ($time === null) {
			$time = PP::date();
		}

		$valueOfStatus = implode(',', $status);

		$db = PP::db();
		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_order') . ' AS tbl';
		$query[] = 'INNER JOIN ' . $db->qn('#__payplans_subscription') . ' AS s';
		$query[] = 'ON s.' . $db->qn('order_id') . ' = tbl.' . $db->qn('order_id');
		$query[] = 'WHERE s.' . $db->qn('status') . '=' . $db->Quote($subStatus);
		$query[] = 'AND tbl.' . $db->qn('modified_date') . '<' . $db->Quote($time->toSql());
		$query[] = 'AND tbl.' . $db->qn('status') . ' IN(' . $valueOfStatus . ')';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		return $result;
	}
}

class PayplansModelformOrder extends PayPlansModelform {}
