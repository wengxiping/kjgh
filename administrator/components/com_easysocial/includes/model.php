<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

class EasySocialModel extends JModelLegacy
{
	/**
	 * Total number of records.
	 * @var	int
	 */
	protected $total = null;

	/**
	 * The database layer from Joomla.
	 * @var	JDatabase
	 */
	protected $db = null;

	/**
	 * The pagination object.
	 * @var SocialPagination
	 */
	protected $pagination = null;

	/**
	 * The element name.
	 * @var string
	 */
	protected $element = null;
	protected $key = null;

	// Implemented by child
	public $searchables = array();

	public function __construct($element = null, $config = array())
	{
		$this->db = ES::db();

		$this->element = $element;

		// Set the key element for this model.
		$index = 'com_easysocial';

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
	 * Initializes all the generic states from the form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function initStates()
	{
		$app = JFactory::getApplication();
		$config = ES::config();
		$jConfig = ES::jConfig();

		// Get the system defined limit
		$systemLimit = $jConfig->getValue( 'list_limit' );
		$systemLimit = $config->get($this->element . '.limit' , $systemLimit );

		// Get the limit.
		$limit = $this->getUserStateFromRequest( 'limit' , $systemLimit , 'int' );

		// Get the limitstart.
		$limitstart = $this->getUserStateFromRequest( 'limitstart' , 0 );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );

		// Get the search
		$search = $this->getUserStateFromRequest('search', '');

		// Get the ordering
		$ordering = $this->getUserStateFromRequest('ordering', 'id');

		// Get the direction
		$direction = $this->getUserStateFromRequest('direction' , 'DESC');

		$this->setState('direction', $direction);
		$this->setState('ordering', $ordering);
		$this->setState('search', $search);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Get searchable columns
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getSearchableItems($query)
	{
		$query = explode(':', $query);

		if (count($query) == 1 || !$this->searchables) {
			return false;
		}

		$column = $query[0];

		if (!in_array(strtoupper($column), $this->searchables) && !in_array(strtolower($column), $this->searchables)) {
			return false;
		}

		$data = new stdClass();
		$data->column = $column;
		$data->query = $query[1];

		return $data;
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

		// // Try to get the key first. If the key is not in the request, then the userstate won't go back to empty
		// if ($value == null) {
		// 	$app->setUserState($namespace, null);
		// }

		$value = $app->getUserStateFromRequest($namespace, $key, $default, $type);

		return $value;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	1.4
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

		parent::setState( $namespace , $value );
	}

	/**
	 * Retrieve a list of state items
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getState( $keyItem = null , $default = null )
	{
		$key 	= $this->key . '.' .$keyItem;

		$value 	= parent::getState( $key );

		return $value;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function getTotal()
	{
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
			$jConfig = ES::jconfig();
			$config = ES::config();

			$systemLimit = $jConfig->getValue('list_length');
			$app = JFactory::getApplication();
			$limit = $app->getUserStateFromRequest( 'com_easysocial.' . $this->element . '.limit' , 'limit' , $config->get( $this->element . '.limit' , $systemLimit ) , 'int' );
		}

		$this->setState('limit', $limit);

		return $this;
	}


	/**
	 * Returns the pagination object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPagination()
	{
		if ($this->pagination) {
			return $this->pagination;
		}

		$limitstart = (int) $this->getState('limitstart');
		$limit = (int) $this->getState('limit');
		$total = (int) $this->getState('total');

		$this->pagination = ES::pagination($total, $limitstart, $limit);

		return $this->pagination;
	}

	/**
	 * Determines the total number of items based on the query given.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function setTotal($query, $wrapTemporary = false)
	{
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
	 * Determines the total number of items based on the query given.
	 *
	 * @since	1.0
	 * @access	public
	 */
	protected function setTotalCount($total)
	{
		// Set the total items here.
		$this->setState('total', $total);
		$this->total = $total;
		return true;
	}

	/**
	 * If using the pagination query, child needs to use this method.
	 *
	 * @since	1.0
	 * @access	public
	 */
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

		if ($query instanceof SocialSql) {

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

	protected function getDataColumn( $query, $useLimit = true)
	{
		// If enforced to use limit, we get the limitstart values from properties.
		$limitstart = $useLimit ? $this->getState( 'limitstart' ) : 0;
		$limit 		= $useLimit ? $this->getState( 'limit' ) : 0;

		if ($query instanceof SocialSql) {

			if ($limit) {
				$query->limit($limitstart, $limit);
			}

			$query = $query->getSql();

			$this->db->setQuery($query);
		} else {
			$this->db->setQuery($query, $limitstart, $limit);
		}


		// $this->db->setQuery( $query , $limitstart , $limit );

		return $this->db->loadColumn();
	}

	protected function bindTable($tableName, $result)
	{
		$binded = array();

		foreach ($result as $row) {
			$table = FD::table($tableName);
			$table->bind($row);

			$binded[] = $table;
		}

		return $binded;
	}
}
