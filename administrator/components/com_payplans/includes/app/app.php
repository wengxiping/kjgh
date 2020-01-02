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

require_once(PP_LIB . '/abstract.php');

// Formatters
require_once(PP_LIB . '/formatter.php');
require_once(__DIR__ . '/formatter.php');

// App Helpers
require_once(__DIR__ . '/helpers/abstract.php');
require_once(__DIR__ . '/helpers/standard.php');

class PPApp extends PPAbstract
{
	public $name = 'app';
	public $_trigger = true;

	protected $helper = null;
	private $appplans = array();
	private $templateVars = array();

	protected $input = null;

	public static function factory($id = null)
	{
		return new self($id);
	}

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->helper = $this->getHelper();
		$this->input = JFactory::getApplication()->input;
		// //load language
		// $this->loadLanguage($this->getName(), dirname($this->_location));

		// //return $this to chain the functions
		// return parent::__construct($config);
	}

	
	/**
	 * Resets the data from the table
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset($config=array())
	{
		$this->table->app_id = 0 ;
		$this->table->title = '';
		$this->table->type = $this->getName();
		$this->table->ordering = 0;
		$this->table->published = 1;
		$this->table->core_params = new JRegistry();
		$this->table->app_params = new JRegistry();
		$this->_appplans = array();

		return $this;
	}

	/**
	 * Determines if the app can be deleted
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canDelete()
	{
		// Check if there are any payments associated with the app
		$model = PP::model('Payment');
		$payments = $model->loadRecords(array('app_id' => $this->getId()));

		if ($payments) {
			return false;
		}

		// //can not delete adminpay application
		// if($this->_hasType($pk, 'adminpay') == true){
		// 	$this->setError(JText::_('COM_PAYPLANS_APP_CAN_NOT_DELETE_ADMINPAY'));
		// 	return false;
		// }
		
		return true;
	}

	/**
	 * Apps needs to be created as new instance as the app could be a payment app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppInstance($id = null)
	{
		if (!$id) {
			return false;
		}

		$model = PP::model('App');
		$item = $model->loadRecords(array('id' => $id));
		$item = array_shift($item);

		$type = $item->type;

		$path = PPHelperApp::getAppPath($item);

		require_once($path);

		$className = 'PPApp' . ucfirst($type);
		$instance = new $className($item);

		return $instance;
	}

	/**
	 * Retrieves the form for the app that is used at the back end
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getForm()
	{
		$form = PP::form('apps');
		$path = $this->getFormManifest();

		if (!JFile::exists($path)) {
			throw new Exception('admin.json config file not available in ' . $path);
		}

		$form->load($path, $this->app_params);

		return $form;
	}

	/**
	 * Retrieves the path to the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFolder()
	{
		static $folders = array();

		$key = $this->table->app_id;

		if (!isset($folders[$key])) {
			$path = JPATH_ROOT . '/plugins/payplans/' . $this->type . '/app';

			$folders[$key] = $path;
		}

		return $folders[$key];
	}

	/**
	 * Retrieves helper for the app if it exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getHelper()
	{
		static $helpers = array();

		$key = $this->type . $this->table->app_id;

		if (!isset($helpers[$key])) {
			$helperFile = $this->getFolder() . '/helper.php';

			$exists = JFile::exists($helperFile);

			if (!$exists) {
				$helpers[$key] = false;

				return $helpers[$key];
			}

			require_once($helperFile);

			$className = 'PPHelper' . ucfirst($this->type);

			if (!class_exists($className)) {
				$helpers[$key] = false;

				return $helpers[$key];
			}

			$helpers[$key] = new $className($this->getAppParams(), $this);

		}

		return $helpers[$key];
	}

	/**
	 * Retrieves the form for the app that is used at the back end
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFormManifest()
	{
		$model = PP::model('App');
		$path = $model->getAppManifestPath($this->type);

		return $path;
	}

	/**
	 * Retrieves the table associated with the current lib
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTable()
	{
		$table = PP::table('App');
		return $table;
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

		$name = 'App';

		if (!isset($models[$name])) {
			$models[$name] = PP::model($name);
		}

		return $models[$name];
	}

	/**
	 * Retrieves the prefix of the payplans apps.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPrefix()
	{
		return 'pp';
	}

	/**
	 * Get app id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getId()
	{
		return (int) $this->table->app_id;
	}

	/**
	 * Get app core params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getParam($key, $default=null)
	{
		return $this->table->core_params->get($key,$default);
	}

	/**
	 * Set app core params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setParam($key, $value)
	{
		$param = $this->table->core_params;

		if (!$param instanceof JRegistry) {
			$param = new JRegistry($param);
		}

		$param->set($key, $value);

		$this->table->core_params = $param;

		return $this;
	}

	/**
	 * Get app's app params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppParam($key, $default=null)
	{
		$appParams = $this->table->app_params;

		if (is_string($appParams)) {
			$appParams = new JRegistry($appParams);
		}
		return $appParams->get($key,$default);
	}

	/**
	 * Set application's core params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setCoreParams($registry)
	{
		if (is_array($registry) || is_string($registry)) {
			$registry = new JRegistry($registry);
		}

		$this->table->core_params = $registry->toString();
	}

	/**
	 * Set application's core params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setAppParams($registry)
	{
		if (is_array($registry)) {
			$registry = new JRegistry($registry);
		}

		if ($registry instanceof JRegistry) {
			$registry = $registry->toString();
		}

		$this->table->app_params = $registry;
	}

	/**
	 * Set app's app params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setAppParam($key, $value)
	{
		$param = $this->table->app_params;

		if (!$param instanceof JRegistry) {
			$param = new JRegistry($param);
		}

		$param->set($key,$value);

		$this->table->app_params = $param;

		return $this;
	}

	/**
	 * Set App Id
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setId($id)
	{
		$this->table->app_id = $id;
		return $this;
	}

	// IMP : app require to overload load function, as getName != 'app'
	/**
	 * Load an app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function load($id = 0)
	{
		if (!$id) {
			return $this;
		}

		$model = PP::model('App');
		$apps = $model->loadRecords(array('id' => $id));

		$this->bind(array_shift($apps));

		return $this;
	}

	// load given id
	public function afterBind($id = 0)
	{
		if(!$id) {
			return $this;
		}

		$this->_appplans = PP::model('planapp')->getAppPlans($id);
		return $this;
	}


	/**
	 * App data binding
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function bind($data = array(), $ignore = array())
	{
		if (is_object($data)) {
			$data = (array) ($data);
		}

		parent::bind($data, $ignore);

		$paramKeys = array('core_params', 'app_params');

		foreach ($paramKeys as $key) {
			$paramsData = array();

			if (is_array($data) && isset($data[$key])) {
				$paramsData = $data[$key];

				if ($data[$key] instanceof JRegistry) {
					$paramsData = $data[$pkey]->toArray();
				}
			}

			if (is_object($data) && isset($data->$key)) {
				$paramsData = $data->$key;
			}

			$params = new JRegistry($paramsData);

			$this->table->$key = $params;
		}

		if (isset($data['appplans'])) {
			$this->_appplans = $data['appplans'];
		}

		return $this;
	}

	/**
	 * Saving app details and app's plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		$state = parent::save();

		if (!$state) {
			return $state;
		}

		$this->_saveAppPlans();

		return $this;
	}


	/**
	 * Retrieve plans associated with this app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlans()
	{
		return $this->_appplans;
	}

	/**
	 * Actual saving process to store app and app's plan
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function _saveAppPlans()
	{
		// delete all existing values of current app id
		$model = PP::model('planapp');
		$model->deleteMany(array('app_id' => $this->getId()));

		// insert new values into planapp for current plan id
		$data['app_id'] = $this->getId();

		if (empty($this->_appplans) || !is_array($this->_appplans)) {
			return $this;
		}

		foreach ($this->_appplans as $plan) {
			$data['plan_id'] = $plan;
			$model->save($data);
		}

		return $this;
	}

	/**
	 * Determine if we need to implement if plugin is applicable or not
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isApplicable($object = null, $eventName = '')
	{
		// if not with reference to payment then return
		if ($object === null || !($object instanceof PPAppTriggerableInterface)) {
			return false;
		}

		// App is applied on selected plans
		if ($this->getParam('applyAll', false) == false) {
			$objectPlans = $object->getPlans();

			// Retrieve plan id from current object
			if (isset($objectPlans->table) && $objectPlans->table instanceof PayplansTablePlan) {
				$planId = $objectPlans->table->plan_id;

				// Check if current object plan id is existed on the selected plans from the apps
				if (!in_array($planId, $this->getPlans())) {
					return false;
				}
			} else {
				// Legacy checking where $objectPlans is not a table instance
				$ret = array_intersect($this->getPlans(), $objectPlans);

				if (count($ret) <= 0) {
					return false;
				}
			}
		}

		// finally check if plugin want trigger for this or not
		return (boolean) $this->_isApplicable($object, $eventName);
	}

	/**
	 * Check the plugin purpose
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hasPurpose($purpose = '')
	{
		// I am always as app
		if ($purpose === '') {
			return true;
		}

		$type = JString::ucfirst(JString::strtolower($purpose));

		// @TODO:: change the PayplansApp to PPApp from all the apps plugins.

		//simply check if I am instance of app type
		return is_a($this, 'PPApp' . $type);
	}

	/**
	 * Get App title
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->table->title;
	}

	/**
	 * Set App title
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setTitle($title)
	{
		$this->table->title = $title;
		return $this;
	}

	/**
	 * Get App description
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDescription()
	{
		return $this->table->description;
	}

	/**
	 * Get App's type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->table->type;
	}

	/**
	 * Get App's publishing date
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPublished()
	{
		return $this->table->published;
	}

	/**
	 * Retrieves the plugin object of the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPlugin()
	{
		$table = JTable::getInstance('Extension');
		$options = array(
			'element' => $this->type,
			'folder' => 'payplans',
			'type' => 'plugin'
		);

		$table->load($options);

		return $table;
	}

	/**
	 * Get App's ordering
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOrdering()
	{
		return $this->table->ordering;
	}

	/**
	 * Get App's core params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCoreParams()
	{
		return $this->table->core_params;
	}

	/**
	 * Determine if this app is applied to all plans
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getApplyAll()
	{
		return $this->table->core_params->get('applyAll');
	}

	/**
	 * Get App's app params
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppParams()
	{
		return $this->table->app_params;
	}

	/**
	 * If child app doesn't have this method, always return true
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventName='')
	{
		return true;
	}

	// Do nothing
	// TODO:: check what is this for
	public static function _install()
	{
		return true;
	}

	/**
	 * Retrieves the resource model
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getResourceModel()
	{
		$model = PP::model('Resource');

		return $model;
	}

	/**
	 * Retrieves the resource record
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function _getResource($userId, $groupId, $title)
	{
		$resource = PP::table('Resource');
		$resource->load(array(
			'user_id' => $userId,
			'title' => $title,
			'value' => $groupId
		));

		// always trim the string by comma (,)
		$resource->subscription_ids = JString::trim($resource->subscription_ids, ',');

		return $resource;
	}

	protected function _addToResource($subId, $userid, $groupid, $title, $count = 0)
	{
		$resource = PP::resource();
		return $resource->add($subId, $userid, $groupid, $title, $count);
	}

	protected function _removeFromResource($subId, $userid, $groupid, $title, $count = 0)
	{
		$resource = PP::resource();
		return $resource->remove($subId, $userid, $groupid, $title, $count);
	}

	/**
	 * Retrieve variable used in templates
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getVars($key, $default=null)
	{
		if (isset($this->_tplVars[$key])) {
			return $this->_tplVars[$key];
		}
		return $default;
	}

	/**
	 * Retrieve variable used in templates
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function assign($key, $value)
	{
		$this->_tplVars[$key] = $value;
	}

	/**
	 * Get the ??
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getLocation()
	{
		return dirname($this->_location);
	}

	/**
	 * Publishes the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function publish()
	{
		$this->table->published = true;
		return $this->save();
	}

	/**
	 * Unpublishes the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function unpublish()
	{
		$this->table->published = false;
		return $this->save();
	}

	/**
	 * This is used since 3.x.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	final public function collectCoreParams(array $data)
	{
		$registry = new JRegistry($data['core_params']);
		
		return $registry->toString();
	}

	/**
	 * This is used since 3.x. Apps are overloading this method to override it's behavior
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function collectAppParams($data)
	{
		$registry = new JRegistry($data['app_params']);

		return $registry->toString();
	}

	public function loadDefaultManifest($element, $group = 'payplans')
	{
		$path = JPATH_ROOT . '/plugins/' . $group . '/' . $element . '/config/default.json';

		if (JFile::exists($path)) {
			$contents = JFile::read($path);
			$defaults = json_decode($contents);

			if (isset($defaults[0]) && $defaults[0]) {
				$this->bind($defaults[0]);
			}
		}
	}

	/**
	 * Provides assistance to the payment app to set variables which can be extracted with the @display method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function set($key, $value)
	{
		$this->templateVars[$key] = $value;
	}

	/**
	 * Provides assistance to the payment app to output contents.
	 * This is similar to the display method in views
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($namespace)
	{
		$namespace = 'apps:/' . $this->type . '/' . $namespace;
		
		$theme = PP::themes();
		$theme->setVars($this->templateVars);

		return $theme->output($namespace);
	}

}

// App abstracts
require_once(__DIR__ . '/abstract/payment.php');
require_once(__DIR__ . '/abstract/discounts.php');