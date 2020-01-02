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

jimport('joomla.application.component.model');

PP::import('admin:/includes/modelform');

class PayPlansModel extends JModelLegacy
{
	protected $total = null;
	protected $db = null;
	protected $pagination = null;
	protected $element = null;
	protected $key = null;

	protected $_query = null;
	protected $_name = null;
	protected $_records = array();

	public $searchables = array();
	public $filterMatchOpeartor = array();
	public $crossTableNetwork = array();
	public $innerJoinCondition = array();

	public function __construct($element = null, $config = array())
	{
		$this->db = PP::db();
		$this->element = $element;

		// Set the key element for this model.
		$index = PP_ID;

		if (isset($config['namespace'])) {
			$index .= '.' . $config['namespace'];
		}

		$index .= '.' . $element;

		$this->key = $index;

		// We don't want to load any of the tables path because we use our own FD::table method.
		$options = array('table_path' => JPATH_ROOT . '/libraries/joomla/database/table');

		parent::__construct($options);
	}

	/**
	 * Initialize default states used by default
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function initStates()
	{
		$app = JFactory::getApplication();
		$jConfig = PP::jConfig();

		// Get the system defined limit
		$systemLimit = $jConfig->get('list_limit');
		$limit = $this->getUserStateFromRequest('limit', $systemLimit , 'int' );

		// Get the limitstart.
		$limitstart = $this->getUserStateFromRequest('limitstart' , 0 );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );

		$dateRange = $this->getUserStateFromRequest('daterange', null, 'array');

		if (!is_null($dateRange)) {
			$start = PP::normalize($dateRange, 'start', '');
			$end = PP::normalize($dateRange, 'end', '');

			if ($start && $end) {
				$dateRange['start'] = PP::date($dateRange['start'])->toSql();
				$dateRange['end'] = PP::date($dateRange['end'])->toSql();
			}

			if (!$start && !$end) {
				$dateRange = null;
			}
		}

		$search = $this->getUserStateFromRequest('search', '');
		$published = $this->getUserStateFromRequest('published', '');
		$parent = $this->getUserStateFromRequest('parent', '');
		$visible = $this->getUserStateFromRequest('visible', '');
		$ordering = $this->getUserStateFromRequest('ordering', 'modified_date');
		$direction = $this->getUserStateFromRequest('direction' , 'desc');
		$message = $this->getUserStateFromRequest('message' , '');
		$level = $this->getUserStateFromRequest('level' , 'all');
		$class = $this->getUserStateFromRequest('class' , '');
		$username = $this->getUserStateFromRequest('username' , '');
		$invoiceId = $this->getUserStateFromRequest('invoice_id' , '');

		$this->setState('dateRange', $dateRange);
		$this->setState('direction', $direction);
		$this->setState('ordering', $ordering);
		$this->setState('search', $search);
		$this->setState('published', $published);
		$this->setState('visible', $visible);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setState('message', $message);
		$this->setState('level', $level);
		$this->setState('class', $class);
		$this->setState('parent', $parent);
		$this->setState('username', $username);
		$this->setState('invoice_id', $invoiceId);
	}

	/**
	 * Fix date range if the start and end is the same
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getEndingDateRange($start, $end)
	{
		$end = PP::date($end . ' + 1day');
		return $end->toSql();
	}

	protected function getData($query, $debug = false)
	{
		// If enforced to use limit, we get the limitstart values from properties.
		$limit = $this->getState('limit', null);
		$limitstart = $this->getState('limitstart', null);

		if (is_null($limit)) {
			$limit = 0;
		}

		if (is_null($limitstart)) {
			$limitstart = 0;
		}

		// Check if there's anything wrong with the limitstart because
		// User might be viewing on page 7 but switches a different view and it does not contain 7 pages.
		$total = $this->getTotal();

		if ($limitstart > $total) {
			$limitstart = 0;
			$this->setState('limitstart' , 0 );
		}

		if ($query instanceof JDatabaseQuery) {
			if ($limit) {
				$query->limit($limitstart, $limit);
			}
			$query = $query->getSql();
			$this->db->setQuery($query);
		} else {
			$this->db->setQuery($query, $limitstart, $limit);
		}

		return $this->db->loadObjectList();
	}


	/**
	 * Get user's state from request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUserStateFromRequest($key, $default = '', $type = 'none')
	{
		$app = JFactory::getApplication();
		$namespace = $this->key . '.' . $key;
		$value = $app->input->get($key, null, $type);

		$value = $app->getUserStateFromRequest($namespace, $key, $default, $type);

		return $value;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function normalize($data, $key, $default = null)
	{
		if (!$data) {
			return $default;
		}

		// $key cannot be an array
		if (is_array($key)) {
			$key = $key[0];
		}

		if (isset($data[$key])) {
			return $data[$key];
		}

		return $default;
	}

	public function setUserState($key, $value)
	{
		$app = JFactory::getApplication();

		return $app->setUserState($this->key . '.' . $key, $value);
	}

	public function getUserState($key, $default = null)
	{
		$app = JFactory::getApplication();

		return $app->getUserState($this->key . '.' . $key, $default);
	}

	/**
	 * Overrides parent's setState
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setState($key, $value = null)
	{
		$namespace 	= $this->key . '.' . $key;

		parent::setState($namespace, $value);
	}

	/**
	 * Retrieve a list of state items
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getState($key = null, $default = null)
	{
		$key = $this->key . '.' .$key;

		$value = parent::getState($key);

		return $value;
	}

	/**
	 * Determines the total number of items based on the query given.
	 *
	 * @since	4.0
	 * @access	public
	 */
	protected function setTotal($query, $wrapTemporary = false)
	{
		if (is_array($query)) {
			$query = implode(' ', $query);
		}

		if ($wrapTemporary) {
			$query 	= 'SELECT COUNT(1) FROM (' . $query . ') AS zcount';
		}

		$this->db->setQuery($query);

		$total = (int) $this->db->loadResult();

		// Set the total items here.
		$this->setState('total', $total);

		$this->total = $total;

		return $total;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function getTotal()
	{
		if (! is_null($this->total)) {
			return $this->total;
		}

		$query = $this->getQuery();

		//Support query cleanup
		$tmpQuery = clone ($query);

		$queryClean = array('select','limit','order');

		foreach ($queryClean as $clean){
			$tmpQuery->clear(JString::strtolower($clean));
		}

		$tmpQuery->select('COUNT(*)');

		$this->total = $this->db->setQuery($tmpQuery)->loadResult();

		return $this->total;
	}

	/**
	 * Sets the limit state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setLimit($limit = null)
	{
		if (is_null($limit)) {
			$jConfig = PP::jconfig();

			$systemLimit = $jConfig->get('list_length');
			$app = JFactory::getApplication();
			$limit = $app->getUserStateFromRequest( 'com_payplans.' . $this->element . '.limit' , 'limit' , $systemLimit, 'int' );
		}

		$this->setState('limit', $limit);

		return $this;
	}


	/**
	 * Returns the Query Object if exist
	 * else It builds the object
	 * @return JDatabaseQuery
	 */
	public function getQuery()
	{
		//query already exist
		if ($this->_query) {
			return $this->_query;
		}

		//create a new query
		$this->_query =  PP::db()->getQuery(true);

		// Query builder will ensure the query building process
		// can be overridden by child class
		if ($this->_buildQuery($this->_query)) {
			return $this->_query;
		}

		//in case of errors return null
		return null;
	}

	/**
	 * Retrieves the PPTable
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTable($tableName=null, $prefix = 'Table', $options = array())
	{
		// support for parameter
		if ($tableName===null) {
			$tableName = $this->getName();
		}

		$table = PP::table($tableName);

		if(!$table) {
			$this->setError(JText::_('NOT_ABLE_TO_GET_INSTANCE_OF_TABLE'.':'.$this->getName()));
		}

		return $table;
	}

	/**
	 * Retrieves the current name of the model
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name)) {
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "PayPlansModel::getName() : Can't get or parse class name.");
			}
			$name = strtolower( $r[1] );
		}

		return $name;
	}


	/*
	 * Collect prefix auto-magically
	 */
	public function getPrefix()
	{
		if (isset($this->_prefix) && empty($this->_prefix)===false) {
			return $this->_prefix;
		}

		$r = null;
		
		preg_match('/(.*)Model/i', get_class($this), $r);

		$this->_prefix = JString::strtolower($r[1]);
		return $this->_prefix;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		$property	= 'id';
		return $this->getState($property) ;
	}


	public function getPagination()
	{
		if ($this->pagination) {
			return $this->pagination;
		}

		$limitstart = (int) $this->getState('limitstart');
		$limit = (int) $this->getState('limit');
		$total = (int) $this->getState('total');

		$this->pagination = PP::pagination($total, $limitstart, $limit);

		return $this->pagination;
	}


	public function clearQuery()
	{
		$this->_query = null;
	}


	public function getEmptyRecord()
	{
		$vars = $this->getTable()->getProperties();
		$retObj = new stdClass();

		foreach($vars as $key => $value)
			$retObj->$key = null;

		return array($retObj);
	}

	/**
	 * Returns Records from Model Tables as per Model STATE
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function loadRecords($queryFilters = array(), $queryClean = array(), $emptyRecord = false, $orderby = null)
	{
		$query = $this->getQuery();

		// There might be no table and no query at all
		if ($query === null) {
			return null;
		}

		//Support Query Filters, and query cleanup
		$tmpQuery = clone ($query);

		foreach ($queryClean as $clean) {
			$tmpQuery->clear(JString::strtolower($clean));
		}

		foreach ($queryFilters as $key => $value) {

			//support id too, replace with actual name of key
			$key = ($key === 'id')? $this->getTable()->getKeyName() : $key;

			// only one condition for this key
			if (is_array($value) == false) {
				$tmpQuery->where("`tbl`.`$key` =".$this->_db->Quote($value));
				continue;
			}

			// multiple keys are there
			foreach ($value as $condition) {

				// not properly formatted
				if (is_array($condition)==false) {
					continue;
				}

				// first value is condition, second one is value
				list($operator, $val)= $condition;
				$tmpQuery->where("`tbl`.`$key` $operator ".$val);
			}

		}

		if ($orderby === null) {
			$orderby = $this->getTable()->getKeyName();
		}

		$tmpQuery->order($orderby . ' ASC');

		// set limits
		// If enforced to use limit, we get the limitstart values from properties.
		$limit = $this->getState('limit', null);
		$limitstart = $this->getState('limitstart', null);
		if ($limit) {
			$tmpQuery->limit($limitstart, $limit);
		}

		// we want returned record indexed by columns
		$this->db->setQuery($tmpQuery);
		$this->_recordlist = $this->db->loadObjectList($orderby);

		//handle if some one required empty records, only if query records were null
		if ($emptyRecord && empty($this->_recordlist)) {
			$this->_recordlist = $this->getEmptyRecord();
		}

		return $this->_recordlist;
	}


	/**
	 * This should vaildate and filter the data
	 * @param unknown_type $data
	 * @param unknown_type $pk
	 * @param array $filter
	 * @param array $ignore
	 */
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}


	/**
	 * Save given data for the given record
	 * @param array $data : date to be saved
	 * @param int/string $pk : the record ID, if 0 given data will be saved as new record
	 * @param boolean $new : is a new record (then we will not load it from table)
	 */
	public function save($data, $pk = null, $new = false)
	{
		if (isset($data) === false || count($data) <= 0) {
			$this->setError(JText::_('COM_PAYPLANS_NO_DATA_TO_SAVE'));
			return false;
		}

		//try to calculate automatically
		 if ($pk === null) {
			$pk = (int) $this->getId();
		}

		//also validate via model
		if ($this->validate($data, $pk)===false) {
			return false;
		}

		// resolve parameter type variables
		//$this->resolveParameters($data);

		//load the table row
		$table = $this->getTable();

		if (!$table) {
			$this->setError(JText::_('COM_PAYPLANS_TABLE_DOES_NOT_EXIST'));
			return false;
		}

		// Bug #29
		// If table object was loaded by some code previously
		// then it can overwrite the previous record
		// So we must ensure that either PK is set to given value
		// Else it should be set to 0
		$table->reset(true);

		//it is a NOT a new record then we MUST load the record
		//else this record does not exist
		if ($pk && $new===false && $table->load($pk)===false) {
			$this->setError(JText::_('COM_PAYPLANS_NOT_ABLE_TO_LOAD_ITEM'));
			return false;
		}

		//bind, and then save
		//$myData = $data[$this->getName()][$pk===null ? 0 : $pk];
		if ($table->bind($data) && $table->store()) {
			// We should return the record's ID rather then true false
			return $table->{$table->getKeyName()};
		}

		//some error occured
		$this->setError($table->getError());
		return false;
	}

	/**
	 * Method to delete rows.
	 */
	public function delete($pk=null)
	{
		//load the table row
		$table = $this->getTable();

		if (!$table)
			return false;

		//try to calculate automatically
		 if ($pk === null) {
			$pk = (int) $this->getId();
		 }

		//if we have itemid then we MUST load the record
		// else this is a new record
		if (!$pk) {
			$this->setError(JText::_('COM_PAYPLANS_NO_ITEM_ID_AVAILABLE_TO_DELETE'));
			return false;
		}

		//try to delete
		if ($table->delete($pk)) {
			return true;
		}

		//some error occured
		$this->setError($table->getError());
		return false;
	}

	/**
	 * Method to delete more than one rows
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function deleteMany($condition, $glue='AND', $operator='=')
	{
		$query = $this->db->getQuery(true);
		$query->delete()
				->from($this->getTable()->getTableName());

		foreach ($condition as $key => $value) {
			$query->where(" $key $operator $value ", $glue);
		}

		return $this->db->setQuery($query)->query();
	}

	/**
	 * XITODO Method to order rows.
	 */
	public function order($pk, $change)
	{
		//load the table row
		$table = $this->getTable();

		if (!$table)
			return false;

		//try to calculate automatically
		 if ($pk == null)
			$pk = (int) $this->getId();

		//if we have itemid then we MUST load the record
		// else this is a new record
		if (!$pk) {
			$this->setError(JText::_('COM_PAYPLANS_ERROR_NO_ITEM_ID_AVAILABLE_TO_CHANGE_ORDER'));
			return false;
		}

		//try to move
		if ($table->load($pk) && $table->move($change))
			return true;

		//some error occured
		$this->setError($table->getError());
		return false;
	}

	/**
	 * XITODO Method to switch boolean column values.
	 */
	public function boolean($pk, $column, $value, $switch)
	{
		//load the table row
		$table = $this->getTable();

		if (!$table)
			return false;

		//try to calculate automatically
		 if ($pk === null)
			$pk = (int) $this->getId();

		//if we have itemid then we MUST load the record
		if (!$pk) {
			$this->setError(JText::_('COM_PAYPLANS_NO_ITEM_ID_AVAILABLE_TO_CHANGE_ORDER'));
			return false;
		}

		//try to switch
		if ($table->load($pk) && $table->boolean($column, $value, $switch))
			return true;

		//some error occured
		$this->setError($table->getError());
		return false;
	}

	/* Child classes should not overload it */
	final public function _buildQuery(&$query=null)
	{
		static $functions = array('Fields','From','Joins','Where','Group','Order','Having');

		// $table	= $this->getTable();

		// if (!$table) {
		// 	$this->_query = null;
		// 	return false;
		// }

		if ($query === null) {
			$query = $this->getQuery();
		}

		foreach ($functions as $func) {
			$functionName = "_buildQuery$func";
			$this->$functionName($query);
		}

		// if working for individual record then no need to add limit
		if (!$this->getId()) {
			$this->_buildQueryLimit($query);
		}

		return true;
	}


	protected function _buildQueryFields(&$query)
	{
		$query->select('tbl.*');
	}

	/**
	 * Builds FROM tables list for the query
	 */
	protected function _buildQueryFrom(&$query)
	{
		$name = $this->getTable()->getTableName();
		$query->from($name.' AS tbl');
	}

	/*
	 * Every entity should define this function, as they need to
	 * join with fields table
	 */
	protected function _buildQueryJoins(&$query)
	{

	}

	// XITODO : Remove this final keword, and break up filter
	final protected function _buildQueryWhere(&$query)
	{
		//get generic filter and fix it
		$filters = $this->getState(PP::getObjectContext($this));

		if (is_array($filters)===false)
			return;

		$temp = array();

		foreach ($filters as $key=>$value) {
			if($value === null)
				continue;

			$this->_buildQueryFilter($query, $key, $value,$temp);
		}

		if (!empty($temp)) {
			foreach ($temp as $key => $value) {
				$condition = $this->innerJoinCondition[$key].$value;
				$query->innerJoin($condition);
			}

		}
		return;
	}

	protected function _buildQueryFilter(&$query, $key, $value, &$temp)
	{
		// Only add filter if we are working on bulk reocrds
		if ($this->getId()) {
			return $this;
		}

		XiError::assert(isset($this->filterMatchOpeartor[$key]), "OPERATOR FOR $key IS NOT AVAILABLE FOR FILTER");
		XiError::assert(is_array($value), JText::_('COM_PAYPLANS_VALUE_FOR_FILTERS_MUST_BE_AN_ARRAY'));

		$cloneOP = $this->filterMatchOpeartor[$key];
		$cloneValue= $value;

		while (!empty($cloneValue) && !empty($cloneOP)){
			$op = array_shift($cloneOP);
			$val= array_shift($cloneValue);

			// discard empty values
			if (!isset($val) || '' == JString::trim($val))
				continue;

			$table = "tbl";

			// CROSS FILTERING STARTS HERE
			if(stristr($key,"cross_")) {
				//seprate the variables
				$crossKey = str_replace("cross_", "",$key); 			// key = cross_filtertable_fieldname
				$crosstable = strtok($crossKey,'_');				  			// crosstable = filtertable
				$crossKey = str_replace("{$crosstable}_", "",$crossKey); 	// key = fieldname

				if (isset($this->crossTableNetwork[$crosstable])) {
					$travesingTables = $this->crossTableNetwork[$crosstable];
					$prevTable = "tbl";

					foreach ($travesingTables as $traversed) {
						if (!isset($temp["{$prevTable}-{$traversed}"])) {
							$temp["{$prevTable}-{$traversed}"] = "";
						}

						if ($crosstable == $traversed) {
							$crossValue = "'$val'";
							if (JString::strtoupper($op) == 'LIKE') {
								$crossValue = "'%{$val}%'";
							}

						if (stristr($crossKey,'date')) {
							$temp["{$prevTable}-{$traversed}"] .= " AND date(cross_{$crosstable}.$crossKey) $op $crossValue ";
						} else {
							$temp["{$prevTable}-{$traversed}"] .= " AND cross_{$crosstable}.`$crossKey` $op $crossValue ";
						}

						$prevTable = $traversed;
						continue;
						}

						$temp["{$prevTable}-{$traversed}"] .= "";
						$prevTable = $traversed;

					}
				}
				//CROSS FILTERING ENDS HERE
			} else {

				if (JString::strtoupper($op) == 'LIKE') {
					$query->where("`{$table}`.`$key` $op '%{$val}%'");
					continue;
				}

				if (stristr($key,'date')) {
					$query->where("date({$table}.$key) $op '$val'");
				} else {
					$query->where("`{$table}`.`$key` $op '$val'");
				}
			}
		}
	}

	protected function _buildQueryGroup(&$query)
	{}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 */
	protected function _buildQueryOrder(&$query)
	{
		$order = $this->getState('filter_order');
		$direction = strtoupper($this->getState('filter_order_Dir'));

		if ($order) {
			$query->order("$order $direction");
		}

		// if (array_key_exists('ordering', $this->getTable()->getFields())) {
		// 	$query->order('ordering ASC');
		// }
	}

	protected function _buildQueryHaving(&$query)
	{}

	protected function _buildQueryLimit(&$query)
	{
		$limit = $this->getState('limit');
		$limitstart = $this->getState('limitstart');

		if ($limit) {
			$query->limit($limit, $limitstart);
		}
		return;
	}
}
