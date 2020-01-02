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

class PayplansModelPayment extends PayPlansModel
{
	public $filterMatchOpeartor = array(
										'app_id'		=> array('='),
										'created_date'	=> array('>=', '<='),
										'modified_date'	=> array('>=', '<=')
										);

	public function __construct()
	{
		parent::__construct('payment');
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
		$appId = $this->getState('app_id');
		$createdDate = $this->getState('created_date');
		$modifiedDate = $this->getState('modified_date');
		$ordering = $this->getState('ordering');
		$direction	= $this->getState('direction');

		$db = $this->db;

		$query = array();

		$query[] = "select a.*";
		$query[] = "from `#__payplans_payment` as a";
		$query[] = 'INNER JOIN `#__users` AS b ON a.`user_id` = b.`id`';
		$query[] = 'INNER JOIN `#__payplans_invoice` AS c ON a.`invoice_id` = c.`invoice_id`';

		$wheres = array();

		if ($appId) {
			$wheres[] = $db->nameQuote('a.app_id') . " = " . $db->Quote((int) $appId );
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
			$searchQuery[] = 'a.`gateway_params` LIKE ' . $db->Quote('%' . $search . '%');

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


		$this->setTotal($query, true);

		$result	= $this->getData($query);

		return $result;
	}
}