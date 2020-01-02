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

class PayplansModelDownload extends PayPlansModel
{
	public function __construct()
	{
		parent::__construct('download');
	}

	/**
	 * Determines if the download has been requested before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDownloadStateByUser($userId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT ' . $db->qn('state') . ' FROM ' . $db->qn('#__payplans_download');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote((int) $userId);

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
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
	}

	/**
	 * Determines if the download has been requested before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getItems()
	{
		$db = PP::db();

		$query = array();
		$wheres = array();

		$query[] = 'SELECT a.* FROM ' . $db->qn('#__payplans_download') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->qn('#__users') . ' AS b';
		$query[] = 'ON a.' . $db->qn('user_id') . ' = b.' . $db->qn('id');

		$where = '';
		if (count($wheres) > 0) {
			$where = ' where ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' and ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		return $result;
	}

	/**
	 * Retrieves a list of download requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getRequests()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_download');
		$query[] = 'WHERE ' . $db->qn('state') . ' IN(0, 1)';

		$db->setQuery($query);
		$requests = $db->loadObjectList();

		return $requests;
	}

	/**
	 * Retrieve list of expired requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function deleteExpiredRequests($days)
	{
		$date = PP::date();
		$now = $date->toSql();
		$days = (int) $days;

		$db = PP::db();

		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__payplans_download');
		$query[] = 'WHERE `state` = 3';
		$query[] = 'AND `created` <= DATE_SUB(' . $db->Quote($now) . ', INTERVAL ' . $days . ' DAY)';
		$query[] = 'ORDER BY `download_id`';

		$db->setQuery($query);
		return $db->query();
	}

	/**
	 * Determines if the download has been requested before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isDownloadRequestedByUser($userId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_download');
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote((int) $userId);

		$db->setQuery($query);
		$result = $db->loadResult() > 0;

		return $result;
	}
}