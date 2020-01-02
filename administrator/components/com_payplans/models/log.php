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

class PayplansModelLog extends PayPlansModel
{
	//this is to fetch the cross field from which table
	public $crossFilterTable = array("cross_users_username" => "users");

	public $crossTableNetwork = array("users"=>array('users'));

	//this is to ftech on condition for cross table
	public $innerJoinCondition = array('tbl-users' => ' #__users as cross_users on tbl.owner_id = cross_users.id');

	//XITODO : move it to variable rather then a function call
	public $filterMatchOpeartor = array(
										'message' => array('LIKE'),
										'level' => array('='),
										'class' => array('='),
										'user_ip' => array('LIKE'),
										'object_id' => array('='),
										'created_date' => array('>=', '<='),
										'cross_users_username' => array('LIKE')
										);

	public function __construct()
	{
		parent::__construct('log');
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

		$class = $this->getUserStateFromRequest('class', '', 'string');
		$appId = $this->getUserStateFromRequest('app_id', '', 'string');

		$this->setState('class', $class);
		$this->setState('app_id', $appId);
	}

	public function getItems($options = array())
	{
		$message = $this->getState('search');
		$level = $this->getState('level');
		$class = $this->getState('class');
		$userIp = $this->getState('user_ip');
		$objectId = $this->getState('object_id');
		$username = $this->getState('username');
		$ordering = $this->getState('ordering');
		$createdDate = $this->getState('created_date');
		$direction = $this->getState('direction');

		// certain place only load specific log
		$objectId = isset($options['object_id']) ? $options['object_id'] : $objectId;
		$class = isset($options['class']) ? $options['class'] : $class;
		$level = isset($options['level']) ? $options['level'] : $level;
		$direction = isset($direction) ? $direction : 'desc';

		// Retrieve a list of class log
		$classMappings = PP::log()->getClassLog(PP_RETRIEVE_MAPPING_CLASS);

		// Mapping those old and new class
		if (isset($classMappings[$class])) {
			$class = $classMappings[$class];
		}

		$db = $this->db;
		$query = array();
		$wheres = array();

		$query[] = "SELECT a.*";
		$query[] = "FROM `#__payplans_log` AS a";

		if ($message) {
			$wheres[] = $db->nameQuote('a.message') . "LIKE" . $db->Quote('%' . $message . '%');
		}

		if ($level != 'all') {
			$wheres[] = $db->nameQuote('a.level') . "=" . $db->Quote((int) $level);
		}

		// Normalize the class and ensure that it is an array
		if ($class) {
			if (!is_array($class)) {
				$class = array($class);
			}

			$wheres[] = $db->nameQuote('a.class') . " IN (" . implode(',', $db->Quote($class)) . ")";
		}

		if ($userIp) {
			$wheres[] = $db->nameQuote('a.user_ip') . "=" . $db->Quote($userIp);
		}

		if ($objectId) {
			$wheres[] = $db->nameQuote('a.object_id') . "=" . $db->Quote((int) $objectId);
		}

		// Date range filter
		$dateRange = $this->getState('dateRange');

		if (!is_null($dateRange)) {

			// If the start and end date is the same, we need to add 1 day to the end
			$end = $this->getEndingDateRange($dateRange['start'], $dateRange['end']);

			$wheres[] = $db->qn('created_date') . '>' . $db->Quote($dateRange['start']);
			$wheres[] = $db->qn('created_date') . '<' . $db->Quote($end);
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' WHERE ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' AND ', $wheres);
		}

		$query = implode(' ', $query);
		$query .= $where;

		$this->setTotal($query, true);

		$query .= " ORDER BY a.`log_id` " . $direction;

		$result	= $this->getData($query);

		return $result;
	}

	public function getItemsWithoutState($options = array())
	{
		// certain place only load specific log
		$objectId = isset($options['object_id']) ? $options['object_id'] : $objectId;
		$class = isset($options['class']) ? $options['class'] : $class;
		$level = isset($options['level']) ? $options['level'] : '';
		$direction = isset($direction) ? $direction : 'desc';

		// Retrieve a list of class log
		$classMappings = PP::log()->getClassLog(PP_RETRIEVE_MAPPING_CLASS);

		// Mapping those old and new class
		if (isset($classMappings[$class])) {
			$class = $classMappings[$class];
		}

		$db = $this->db;
		$query = array();
		$wheres = array();

		$query[] = "SELECT a.*";
		$query[] = "FROM `#__payplans_log` AS a";

		// Normalize the class and ensure that it is an array
		if ($class) {
			if (!is_array($class)) {
				$class = array($class);
			}

			$wheres[] = 'a.' . $db->qn('class') . " IN (" . implode(',', $db->Quote($class)) . ")";
		}

		if ($objectId) {
			$wheres[] = 'a.' . $db->qn('object_id') . "=" . $db->Quote((int) $objectId);
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' WHERE ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' AND ', $wheres);
		}

		$limit = PP::normalize($options, 'limit', 20);

		$query = implode(' ', $query);
		$query .= $where;
		$query .= " ORDER BY a.`log_id` " . $direction;

		if ($limit) {
			$limit = (int) $limit;
			$query .= ' LIMIT 0,' . $limit;
		}

		$db->setQuery($query);
		$result	= $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves a list of payment notifications stored in the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPaymentNotifications($options = array())
	{
		$createdDate = $this->getState('created_date');
		$direction = $this->getState('direction');

		$db = $this->db;

		$query = array();
		$wheres = array();

		$query[] = 'SELECT a.* FROM ' . $db->qn('#__payplans_ipn') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->qn('#__payplans_payment') . ' AS b';
		$query[] = 'ON a.' . $db->qn('payment_id') . '=b.' . $db->qn('payment_id');
		
		$appId = $this->getState('app_id');

		if ($appId) {
			$wheres[] = 'b.`app_id`=' . $db->Quote((int) $appId);
		}

		// Date range filter
		$dateRange = $this->getState('dateRange');

		if (!is_null($dateRange)) {

			// If the start and end date is the same, we need to add 1 day to the end
			$end = $this->getEndingDateRange($dateRange['start'], $dateRange['end']);

			$wheres[] = $db->qn('created') . '>' . $db->Quote($dateRange['start']);
			$wheres[] = $db->qn('created') . '<' . $db->Quote($end);
		}

		$search = $this->getState('search');

		if ($search) {
			$searchQuery = array();
			$searchQuery[] = '(';
			$searchQuery[] = 'a.' . $db->nameQuote('id') . ' = ' . $db->Quote($search);
			$searchQuery[] = 'OR';
			$searchQuery[] = 'a.' . $db->nameQuote('ip') . "LIKE" . $db->Quote('%' . $search . '%');
			$searchQuery[] = 'OR';
			$searchQuery[] = 'b.' . $db->nameQuote('payment_id') . ' = ' . $db->Quote(PP::getIdFromKey($search));
			$searchQuery[] = ')';

			$wheres[] = implode(' ', $searchQuery);
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = ' WHERE ';
			$where .= (count($wheres) == 1) ? $wheres[0] : implode(' AND ', $wheres);
		}

		$query[] = $where;
		$query[] = 'ORDER BY a.`id` ' . $direction;

		$query = implode(' ', $query);

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		if (!$result) {
			return $result;
		}

		$items = array();

		foreach ($result as $row) {
			$table = PP::table('IPN');
			$table->bind($row);

			$items[] = $table;
		}

		return $items;
	}

	public function markRead($logId)
	{
		$query = $this->db->getQuery(true);

		$query->update('`#__payplans_log`')
			  ->set('`read` = 1')
			  ->where('`log_id` ='.$logId);

		$this->db->setQuery($query);

		if($this->db->query()){
			return false;
		}
		return true;
	}

	/**
	 * Get logs that can be exported
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLogsToExport()
	{
		$db = PP::db();

		$sql = " SELECT * "
				." FROM ".$db->quoteName('#__payplans_log')
				." WHERE " . $db->quoteName('content') . " != " . $db->Quote('')
				." ORDER BY " . $db->quoteName('log_id');

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	/**
	 * Get previous position
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPreviousLogPosition($previousToken, $class = '')
	{
		$db = PP::db();

		$sql = "SELECT `position` FROM " . $db->quoteName('#__payplans_log')
				. " WHERE " . $db->quoteName('current_token') . " = " . $db->Quote($previousToken)
				. " ORDER BY `log_id`";

		$db->setQuery($sql);

		return $db->loadResult();
	}

	/**
	 * Get previous log token
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPreviousLogToken($objId, $class, $content)
	{
		$db = PP::db();

		$sql = "SELECT `current_token` FROM " . $db->quoteName('#__payplans_log')
				. " WHERE " . $db->quoteName('object_id') . " = " . $db->Quote($objId)
				. " AND " . $db->quoteName('class') . " = " . $db->Quote($class)
				. " AND " . $db->quoteName('class') . " NOT IN ('SYSTEM', 'payplans_Cron')"
				. " ORDER BY `log_id`";

		$db->setQuery($sql);

		return $db->loadResult();
	}

	/**
	 * Read an encoded base64 log
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function readBaseEncodeLog($log)
	{
		$content = '';

		$logData = base64_decode($log->content);
		$logData = unserialize($logData);
		$type = array_shift($logData);

		if (!empty($logData)) {
			$logData = base64_decode(array_shift($logData));
			$content = unserialize($logData);
		}

		return array($type, $content);
	}

	/**
	 * Retrieve owner Id from the log content
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getOwnerId($content)
	{
		$id = array('user_id' => '', 'buyer_id' => '');
		$compare = isset($content['current']) ? $content['current'] : $content;

		$ownerId = array_intersect_key($id, $compare);

		if (!empty($ownerId)) {
			$ownerId = key($ownerId);
			$ownerId = $compare[$ownerId];

			return $ownerId;
		}

		return '';
	}

	/**
	 * Dumping content into file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function dumpDataIntoFile($log_id, $content)
	{
		$file = $this->generateFileName($log_id);

		$fh = fopen($file, 'a+');
		// a+ - Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.

		fseek($fh, 0, SEEK_END);
		$pos = ftell($fh);
		fwrite($fh, $content);
		fclose($fh);

		$position = json_encode(array('location' => $pos, 'filePath' => urlencode($file)));

		return $position;
	}

	/**
	 * Return a specific folder and file name
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function generateFileName($log_id)
	{
		$config = PP::config();

		// We limit the folder size
		$maxFolderSize = 32768;

		$folderId = $config->get('logBucket', 1);
		$folderName = 'log_bucket_' . $folderId;
		$path = JPATH_ROOT . '/media/payplans/log/';

		$folderPath = $path . $folderName;

		if (!JFolder::exists($folderPath)) {
			JFolder::create($folderPath);
		}

		if (filesize($folderPath) > $maxFolderSize) {
			$folderId++;
			$folderName = 'log_bucket_' . $folderId;
			$config->save(array('logBucket' => $folderId));

			$folderPath = $path . $folderName;
			JFolder::create($folderPath);
		}

		$fileNameId = $log_id % 16;
		$fileName = 'log_' . $fileNameId . '.txt';
		$filePath = $folderPath . '/' . $fileName;

		return $filePath;
	}

	/**
	 * Retrieves a list of payment notifications stored in the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purgePaymentNotifications()
	{
		$db = $this->db;

		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__payplans_ipn');

		$query = implode(' ', $query);

		$db->setQuery($query);
		return $db->Query();
	}

	/**
	 * Retrieves a list of payment notifications stored in the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purgeAll()
	{
		$db = PP::db();

		$query = array();

		$query[] = 'DELETE FROM ' . $db->qn('#__payplans_log');

		$db->setQuery($query);
		
		return $db->Query();
	}

	/**
	 * Retrieves a list of payment notifications stored in the system
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function purgeLogs($logClass, $daysThreshold = 3)
	{
		$db = PP::db();

		$query = array();

		$query[] = 'DELETE FROM ' . $db->qn('#__payplans_log');
		$query[] = 'WHERE ' . $db->qn('class') . '=' . $db->Quote($logClass);
		$query[] = 'AND DATEDIFF(NOW(), ' . $db->qn('created_date') . ') >= ' . $db->Quote($daysThreshold);

		$query = implode(' ', $query);

		$db->setQuery($query);
		
		return $db->Query();
	}
}

class PayplansModelformLog extends PayPlansModelform {}
