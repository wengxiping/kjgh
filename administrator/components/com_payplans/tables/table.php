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

class PayPlansTable extends JTable
{
	public function __construct($table, $key, $db)
	{
		// Set internal variables.
		$this->_tbl = $table;
		$this->_tbl_key = $key;

		// For Joomla 3.2 onwards
		$this->_tbl_keys = array($key);

		$this->_db = $db;

		// Implement JObservableInterface:
		// Create observer updater and attaches all observers interested by $this class:
		$this->_observers = new JObserverUpdater($this);
		JObserverMapper::attachAllObservers($this);
	}

	/**
	 * Retrieves the primary key of the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrimaryColumn()
	{
		return $this->_tbl_key;
	}

	/**
	 * Tired of fixing conflicts with JTable::getInstance . We'll overload their method here.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getInstance($type, $prefix = 'PayPlansTable', $config = array())
	{
		// Sanitize and prepare the table class name.
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass)) {
			// Search for the class file in the JTable include paths.
			$path = dirname(__FILE__) . '/' . strtolower($type) . '.php';

			// Import the class file.
			include_once $path;
		}

		return parent::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  void
	 *
	 * @link    http://docs.joomla.org/JTable/reset
	 * @since   11.1
	 */
	public function reset()
	{
		$properties = get_object_vars($this);
		$columns = array();

		foreach($properties as $key => $value) {
			if ($key != $this->_tbl_key && strpos($key, '_') !== 0) {
				$columns[] = $value;
			}
		}

		return $columns;
	}

	/**
	 * Normalize properties on this table before storing
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function normalize($debug = false)
	{
		$properties = get_object_vars($this);


		foreach ($properties as $property => $value) {

			// Ignore legacy variables which contains $_
			if (preg_match('/^_/', $property)) {
				continue;
			}

			// JRegistry items
			if (is_object($this->$property) && method_exists($this->$property, 'toString') && is_a($this->$property, 'JRegistry')) {
				$this->$property = $this->$property->toString();
				continue;
			}

			if ($value instanceof PPDate) {
				// $this->$property = PPFormats::date($value);
				$this->$property = $value->toMySQL();
				continue;
			}

			if (is_object($this->$property) && method_exists($this->$property, 'toArray')) {
				$this->$property = $this->$property->toArray();
				continue;
			}
		}


	}

	/**
	 * Runs some count query to determine if there's any record.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function exists($wheres)
	{
		$db = PP::db();

		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote($this->_tbl) . ' WHERE 1 ';

		foreach ($wheres as $key => $value) {
			$query .= 'AND ' . $db->nameQuote($key) . '=' . $db->Quote($value) . ' ';
		}

		$db->setQuery($query);

		return $db->loadResult() > 0;
	}

	/**
	 * @since   3.7
	 * @access  public
	 */
	private function changeState($items, $state = PP_STATE_PUBLISHED)
	{
		$db = PP::db();
		$state = (int) $state;

		// Fix the values to avoid anyone from abusing it.
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = (int) $items[$i];
			$items[$i] = $db->Quote($items[$i]);
		}

		$items = $db->nameQuote($this->_tbl_key) . '=' . implode(' OR ' . $db->nameQuote($this->_tbl_key) . '=', $items);

		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' SET ' . $db->nameQuote('state') . '=' . $db->Quote($state) . ' WHERE (' . $items . ')';

		$db->setQuery($query);

		if (!$db->query()) {
			return false;
		}

		return true;
	}

	/**
	 * @since   3.7
	 * @access  public
	 */
	public function unpublish($items = array())
	{
		if (empty($items)) {
			$items = array($this->{$this->_tbl_key});
		}

		// Single item.
		if (!is_array($items)) {
			$items = array($items);
		}

		return self::changeState($items, PP_STATE_UNPUBLISHED);
	}

	/**
	 * Publishes a specific item.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		if (empty($items)) {
			$items = array($this->{$this->_tbl_key});
		}

		// Ensure that the items is an array.
		$items = PP::makeArray($items);

		return self::changeState($items, PP_STATE_PUBLISHED);
	}

	public function renderParams($params, $file)
	{
		return PP::get('Parameter', $params, $file)->render();
	}

	/**
	 * Converts a table layer into a JSON encoded string.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function toJSON()
	{
		$properties = get_class_vars(get_class($this));
		$result = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$result[$key] = is_null($this->get($key)) ? '' : $this->get($key);
			}
		}

		return json_encode($result);
	}


	/**
	 * Converts a table layer into an array
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function toArray()
	{
		$properties = get_class_vars(get_class($this));
		$result = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$result[$key] = is_null($this->get($key)) ? '' : $this->get($key);

				if (is_object($result[$key]) && ($result[$key] instanceof PPDate)) {
					$result[$key] = $result[$key]->toSql();
				}
			}
		}
		return $result;
	}

	/**
	 * Override behavior of store
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function store($updateNulls = false, $new = false)
	{
		// #21 Ordering Bug fix
		$k = $this->_tbl_key;
		$columns = array_keys($this->getProperties());

		$now = PP::date();
		
		// It must be required when migration is running from any subscription system to payplans system 
		// and we need to insert manually created and modified date. 
		if (!(defined('PAYPLANS_MIGRATION_START') && !defined('PAYPLANS_MIGRATION_END'))) {
			// if a new record, handle created date
			if(($new || !($this->$k)) && in_array('created_date', $columns)){
				$this->created_date = $now->toSql();
			}
	
			//handle modified date
			if (in_array('modified_date', $columns)) {
				$this->modified_date = $now->toSql();
			}
		}

		// //Special Case :  we have pk and want to add new record
		// if ($new && $this->$k) {
		// 	if (!$this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key)) {
		// 		$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
		// 		return false;
		// 	}
		// 	return true;
		// }
		
		return parent::store($updateNulls);
	}

	/**
	 * Allows caller to bind and store params to the column `params`
	 * Client must have the column `params` in order for this to work.
	 *
	 * @since   3.7
	 * @access  public
	 */
	public function storeParams()
	{
		if (property_exists($this, 'params')) {
			$raw = JRequest::getVar('params', '');

			$param = PP::get('Parameter', '');
			$param->bind($raw);

			$this->params = $param->toJSON();

			$this->store();
		}
	}

	/**
	 * @since   3.7
	 * @access  public
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Returns a translated text from the table column.
	 *
	 * @since   3.7
	 * @access  public
	 */
	public function _($key, $default = null)
	{
		if (empty($this->$key)) {
			return $default;
		}

		return JText::_($this->$key);
	}

	/**
	 * Responsible to output all properties of the table object.
	 *
	 * @since   3.7
	 * @access  public
	 **/
	public function export()
	{
		$obj = new stdClass();
		$properties = get_class_vars(get_class($this));

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$obj->$key = $this->$key;
			}
		}

		return (array) $obj;
	}

	/**
	 * If child table has a `params` column, this method serves as a helper to convert it into a JRegistry object
	 *
	 * @since   3.7
	 * @access  public
	 */
	public function getParams()
	{
		if (!isset($this->params) || !is_string($this->params)) {
			return;
		}

		$params = PP::registry($this->params);

		return $params;
	}

	/**
	 * Overwrites JTable's getNextOrder function by expecting an array of columns and values or SocialSql object
	 *
	 * @since   4.0.0
	 * @access  public
	 **/
	public function getNextOrder($where = '')
	{
		$string = '';

		if (is_string($where)) {
			$string = $where;
		}

		if (is_array($where)) {
			$db = PP::db();

			$string = array();

			foreach ($where as $k => $v) {
				$string[] = $db->nameQuote($k) . ' = ' . $db->quote($v);
			}

			$string = implode(' AND ', $string);
		}

		if (is_object($where) && get_class($where) === 'SocialSql') {
			$string = $where->getConditionSql();
		}

		return parent::getNextOrder($string);
	}
}