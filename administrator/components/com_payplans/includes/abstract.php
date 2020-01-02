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

class PPAbstract
{
	// Library name that can be implemented by child classes (Optional)
	public $name = null;

	public $table = null;

	// Determines if the abstract should trigger
	public $trigger = true;

	// to keep error messsages
	private $errors = array();

	// flag to determine if afterBind should load related data or not.
	protected $afterBindLoad = true;

	public function __construct($id)
	{
		$this->config = PP::config();

		// Library initialization
		$this->table = $this->getTable();

		// Always trigger the reset of child objects
		$this->reset();

		if (is_object($id) && $id instanceof PayPlansTable) {
			$this->table = $id;

			$this->afterBind($this->getId());
		}

		if (is_object($id) && !($id instanceof PayPlansTable)) {
			$this->bind($id);
		}

		if (is_int($id) || is_string($id)) {
			$this->load((int) $id);
		}
	}

	/**
	 * Method to toggle the flag on afterBind data load.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setAfterBindLoad($flag = true)
	{
		$this->afterBindLoad = $flag;
	}

	/**
	 * Accessing properties that doesn't exist would be routed to the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function __get($key)
	{
		if (!isset($this->$key)) {
			// return isset($this->table->$key) ? $this->table->$key : '';
			return $this->table->$key;
		}

		return $this->$key;
	}

	/**
	 * Setting properties that doesn't exist would be routed to the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function __set($key, $value)
	{
		// Property exists needs to be used here instead of isset as isset would return false if property is null
		if (!isset($this->$key) && is_object($this->table) && property_exists($this->table, $key)) {
			$this->table->$key = $value;
		}

		$this->$key = $value;
	}

	/**
	 * Retrieve the first error message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getError($clear = true)
	{
		$errs = $this->errors;

		if ($clear) {
			$this->errors = array();
		}

		return $errs[0];
	}

	/**
	 * Retrieve error messages if any.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getErrors($clear = true)
	{
		$errs = $this->errors;

		if ($clear) {
			$this->errors = array();
		}

		if (! $errs) {
			return false;
		}

		if (count($errs) == 1) {
			return $errs[0];
		}

		return $errs;
	}

	/**
	 * Set an error message
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setError($message, $class = PP_MSG_ERROR)
	{
		if (!$message) {
			return;
		}

		$obj = $message;

		if (!is_object($message)) {
			$obj = new StdClass();
			$obj->text = JText::_($message);
			$obj->type = $class;
		}

		$this->errors[] = $obj;
	}

	/**
	 * Determines if this is a new object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isNew()
	{
		$id = $this->getId();

		return !$id;
	}

	/**
	 * Retrieves the name of the library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getName()
	{
		if (!$this->name) {
			$pattern = '/PP(.*)/i';
			$name = preg_replace($pattern, '$1', get_class($this));

			$this->name = strtolower($name);
		}

		return $this->name;
	}

	/**
	 * Retrieves the table's primary key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTableKey()
	{
		return $this->table->getPrimaryColumn();
	}

	/**
	 * Retrieves the table associated with the current lib
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTable()
	{
		$table = PP::table($this->getName());

		return $table;
	}

	/**
	 * Retrieves logs associated with the current object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLogs()
	{
		static $logs = array();

		$key = md5($this->getId() . $this->getName());

		if (!isset($logs[$key])) {

			// @TODO: Need to add checking for the class because PP 4.0 uses PP* as prefix but in 3.x, it uses PayPlans* as prefix
			$model = PP::model('Log');
			$options = array(
					'object_id' => $this->getId(),
					'class' => 'PP' . $this->getName()
				);

			$items = $model->loadRecords($options);

			// Bad implementation! Need to use sql
			ksort($items);

			$logs[$key] = $items;
		}

		return $logs[$key];
	}

	/**
	 * Retrieves the model associated with the current lib
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModel()
	{
		static $models = array();

		$name = $this->getName();

		if (!isset($models[$name])) {
			$models[$name] = PP::model($name);
		}

		return $models[$name];
	}

	/**
	 * Retrieves the prefix of the extension
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrefix()
	{
		return 'payplans';
	}

	public function toDatabase($strict=false, $forReadOnly=false)
	{
		XiError::assert($this);

		$arr = get_object_vars($this);
		$ret = array();
		foreach($arr as $key => $value)
		{

			// ignore extra variables
			if(preg_match('/^_/',$key)){
				continue;
			}

			if($strict === false && is_object($this->$key) && method_exists($this->$key, 'toString') && is_a($this->$key, 'XiParameter')){
				$ret[$key] = $this->$key->toString('JSON');
				continue;
			}

			if ($value instanceof PPDate && $forReadOnly == true){
				$ret[$key] = PPFormats::date($value);
				continue;
			}


			if(is_object($this->$key) && method_exists($this->$key, 'toArray')){
				$ret[$key] = $this->$key->toArray();
				continue;
			}

			$ret[$key] = $arr[$key];
		}

		return $ret;
	}

	/**
	 * Converts the table into an object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toArray($strict = false, $readOnly = false)
	{
		$data = $this->table->toArray();

		return $data;
	}

	/**
	 * Retrieves the class name
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getClassname()
	{
		return get_class($this);
	}

	/**
	 * Bind the table params to joomla's registry
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParams()
	{
		$params = new JRegistry;

		if (isset($this->table->params) && is_string($this->table->params)) {
			$params = new JRegistry($this->table->params);
		}

		return $params;
	}

	public function getParamsHtml($name = 'params', $key= null)
	{
		$name = JString::strtolower($name);

		XiError::assert(is_object($this->$name), JText::_('COM_PAYPLANS_ERROR_PARAMETER_MUST_BE_AN_OBJECT'));
		XiError::assert(method_exists($this->$name,'render'), JText::_('COM_PAYPLANS_ERROR_INVALID_PARAMETER_NAME_TO_RENDER'));

		$key = ($key === null) ? $name : $key;
		return $this->$name->render($key);
	}

	/**
	 * Proxy for the bind method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function bind($data = array(), $ignore = array())
	{
		if (!is_array($ignore)) {
			$ignore = explode(',', $ignore);
		}

		$this->table->bind($data, $ignore);

		// Bind params if needed
		if (isset($this->table->params) && array_key_exists('params', $data)) {

			$paramsData = array();

			if (is_array($data) && isset($data['params'])) {
				$paramsData = $data['params'];

				if ($data['params'] instanceof JRegistry) {
					$paramsData = $data['params']->toArray();
				}
			}

			if (is_object($data) && isset($data->params)) {
				$paramsData = $data->params;
			}

			$params = new JRegistry($paramsData);
			$this->table->params = $params->toString();
		}

		// if id is set in data than set id
		if (array_key_exists('id', $data)) {
			$this->setId($data['id']);
		}

		return $this->afterBind($this->getId());
	}

	/**
	 * Standard implementation after binding data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function afterBind($id = null)
	{
		return $this;
	}

	/**
	 * Since all the primary keys from tables are prefixed with the table name,
	 * this would be the fastest way to get the id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getId()
	{
		$property = $this->getName() . '_id';

		return (int) $this->$property;
	}

	/**
	 * Generic helper to generate a unique key given the object's id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getKey()
	{
		$encryptor = PP::encryptor();

		return $encryptor->encrypt($this->getId());
	}

	/**
	 * Sets an ID on the current object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setId($id)
	{
		$property = strtolower($this->getName()) . '_id';

		$this->$property = $id;

		return $this;
	}

	/**
	 * Given a unique id for the record, load the record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function load($id)
	{
		$this->table->load($id);
		$this->afterBind($this->getId());
	}

	/**
	 * Generic method to save a library data using the table associated with it
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$name = $this->getName();

		// All subclasses needs to have $trigger property set to true if needs to trigger plugins
		if ($this->trigger) {

			$previousObject = null;

			// During save, we need to also store the old data for versioning
			if ($this->getId()) {
				$previousObject = call_user_func_array(array('PP', $this->getName()), array($this->getId()));
			}

			// Trigger on before save
			$args = array($previousObject, $this);

			$event = 'onPayplans' . ucfirst($this->getName()) . 'BeforeSave';

			if ($this instanceof PPApp) {
				$event = 'onPayplansAppBeforeSave';
			}

			$result = PPEvent::trigger($event, $args, '', $this);
		}

		// Normalize values in table properties
		$this->table->normalize();

		// Save the table
		$this->table->store();

		// If above save was not complete, then id will be null then return false and do not trigger after save
		$column = $this->getTableKey();
		$id = $this->table->$column;

		if (!$id) {
			return false;
		}

		// Update with the new id
		$this->setId($id);

		// Trigger after saving
		if ($this->trigger) {

			$args = array($previousObject, $this);
			$event = 'onPayplans' . ucfirst($this->getName()) . 'AfterSave';

			if ($this instanceof PPApp) {
				$event = 'onPayplansAppAfterSave';
			}

			PPEvent::trigger($event, $args, '', $this);
		}

		return $this;
	}

	/**
	 * Deletes a record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function delete()
	{
		if ($this->trigger) {
			$args = array($this);
			$event = 'onPayplans' . ucfirst($this->getName()) . 'BeforeDelete';

			if ($this instanceof PPApp) {
				$event = 'onPayplansAppBeforeDelete';
			}

			$result = PP::event()->trigger($event, $args, '', $this);
		}

		// Delete data from table
		$id = $this->getId();
		$result = $this->table->delete();

		// $this->reset();

		// If above delete was not complete, then result will be null then return false and do not trigger after delete
		if (!$result) {
			return false;
		}

		// Triggers after an item is deleted
		if ($this->trigger === true) {
			$args = array($id, $this);

			$event = 'onPayplans' . ucfirst($this->getName()) . 'AfterDelete';

			if ($this instanceof PPApp) {
				$event = 'onPayplansAppAfterDelete';
			}

			PP::event()->trigger($event, $args, '', $this);
		}

		return $this;
	}

	/**
	 * Create a clone object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getClone($debug = false)
	{
		// Since the table is storing mysql data, we need to
		$tmpTable = $this->table;
		unset($this->table);

		$clone = unserialize(base64_decode(base64_encode(serialize($this))));

		$this->table = $tmpTable;

		return $clone;
	}

	/**
	 * Method to set the params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParam($key, $value)
	{
		$params = $this->getParams();
		$params->set($key, $value);

		$this->table->params = $params->toString();
		return $this;
	}

	/**
	 * Allows caller to reset data of the library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset($data = array())
	{
		return $this;
	}

	/**
	 * Retrieves Joomla's model form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModelForm()
	{
		if (isset($this->_modelform)) {
			return $this->_modelform;
		}

		// setup modelform
		$this->_modelform = PP::modelform($this->getName());

		// set model form to pick data from this object
		$this->_modelform->setLibData($this);

		return $this->_modelform;
	}

	static $instance = array();
}
