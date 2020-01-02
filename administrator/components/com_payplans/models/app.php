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

class PayplansModelApp extends PayPlansModel
{
	public $filterMatchOpeartor = array(
										'title'		=> array('LIKE'),
										'type' 		=> array('='),
										'published' => array('=')
										);

	public function __construct()
	{
		parent::__construct('app');
	}

	/**
	 * Initialize default states
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function initStates($view = null)
	{
		parent::initStates();

		$username = $this->getUserStateFromRequest('username', -1, 'int');
		$planId = $this->getUserStateFromRequest('plan_id', -1, 'int');
		$type = $this->getUserStateFromRequest('type', '', 'string');

		$this->setState('type', $type);
		$this->setState('username', $username);
		$this->setState('plan_id', $planId);
	}

	/**
	 * Retrieves a list of available apps
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getItems()
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('group') . '=' . $db->Quote('app');

		$search = $this->getState('search');

		if ($search) {
			$query[] = 'AND LOWER(' . $db->qn('title') . ') LIKE ' . $db->Quote('%' . JString::strtolower($search) . '%');
		}

		$published = $this->getState('published');

		if ($published != 'all' && $published !== '') {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}

		$type = $this->getState('type');

		if ($type) {
			$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote($type);
		}

		$this->setTotal($query, true);

		$result	= $this->getData($query);

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Retrieves a list of available apps
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getItemsWithoutState($options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE 1';

		$group = PP::normalize($options, 'group', '');

		if ($group) {
			$query[] = 'AND ' . $db->qn('group') . '=' . $db->Quote($group);
		}

		$published = PP::normalize($options, 'published', 1);

		if ($published) {
			$query[] = 'AND ' . $db->qn('published') . '=' . $db->Quote($published);
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$apps[] = $app;
			}
		}

		return $apps;
	}

	/**
	 * Retrieves a list of available apps
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPaymentApp($type = 'adminpay')
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE ' . $db->qn('type') . '=' . $db->Quote($type);

		$result	= $this->getData($query);

		$apps = array();

		if ($result) {
			foreach ($result as $row) {
				$app = PP::app($row);
				$apps[] = $app;
			}
		}

		return $apps;
	}

	//TODO : Apply validation when it is applied all over
	public function validate(&$data, $pk=null,array $filter = array(),array $ignore = array())
	{
		return true;
	}

	protected function _hasType($pk, $type)
	{
		//load the table row
		$table = $this->getTable();
		if (!$table) {
			$this->setError(JText::_('COM_PAYPLANS_TABLE_DOES_NOT_EXIST'));
			return false;
		}

		//if we have itemid then we MUST load the record
		// else this is a new record
		if ($pk && $table->load($pk)===false) {
			return false;
		}

		return JString::strtolower($table->type) == JString::strtolower($type);
	}

	public function save($data, $pk=null, $new=false)
	{
		return parent::save($data, $pk);
	}

	public function boolean($pk, $column, $value, $switch)
	{
		return parent::boolean($pk, $column, $value, $switch);
	}

	public function delete($pk=null)
	{
		//can not delete payment app if payment exists corresponding to that app
		$payment = PP::model('Payment')->loadRecords(array('app_id'=>$pk));

		if (!empty($payment)) {
			$this->setError(JText::_('COM_PAYPLANS_APP_CAN_NOT_DELETE_PAYMENT_EXISTS'));
			return false;
		}

		if (!parent::delete($pk)) {
			$db = JFactory::getDBO();
			XiError::raiseError(500, $db->getErrorMsg());
		}
		// delete plans from planapp table
		return PP::model('Planapp')->deleteMany(array('app_id' => $pk));
	}

	/**
	 * Retrieves a list of apps
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getApps()
	{
		static $gateways = null;

		if (is_null($gateways)) {
			$file = PP_ADMIN . '/defaults/app.json';
			$contents = JFile::read($file);

			$gateways = json_decode($contents);
		}

		return $gateways;
	}

	/**
	 * Generates the path to the manifest file of an app
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAppManifestPath($app, $group = 'payplans')
	{
		$path = JPATH_ROOT . '/plugins/' . $group . '/' . $app . '/config/admin.json';

		$customPath = PPHelperApp::getCustomPath($app);
			
		if ($customPath) {
			$path = $customPath . '/config/admin.json';
		}

		return $path;
	}

	/**
	 * Get plugin id
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPluginId($plugin)
	{
		$query = $this->db->getQuery(true);
		$query->select('extension_id AS id,element')
			->from('#__extensions')
			->where('enabled >= 1')
			->where('name IN ("' . implode('","', $plugin) . '")');
		$result  = $this->db->setQuery($query)->loadObjectList('element');
		return $result;
	}

	/**
	 * Installs the plugin
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function installPlugin($element, $group = 'payplans')
	{
		$repository = PP_APPS_REPO;

		// For development mode, the repository path would be the joomla root path
		$config = PP::config();

		if ($config->get('environment') == 'development') {
			$repository = JPATH_PLUGINS;
		}

		$repository .= '/' . $group . '/' . $element;

		$installer = JInstaller::getInstance();
		$installer->setOverwrite(true);

		ob_start();
		$state = $installer->install($repository);
		ob_end_clean();

		// Ensure that the plugins are published
		if ($state) {
			$group = strtolower($group);
			$element = strtolower($element);

			$options = array('folder' => $group, 'element' => $element);

			$plugin = JTable::getInstance('Extension');
			$plugin->load($options);

			// set the state to 0 means 'installed'.
			$plugin->state = 0;
			$plugin->enabled = true;
			$state = $plugin->store();
		}

		return $state;
	}

	/**
	 * Determines if a plugin is installed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isPluginInstalled($element, $group = 'payplans')
	{
		$db = PP::db();

		$query = array();

		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__extensions');
		$query[] = 'WHERE ' . $db->qn('enabled') . '>=' . $db->Quote(1);
		$query[] = 'AND ' . $db->qn('element') . '=' . $db->Quote($element);
		$query[] = 'AND ' . $db->qn('folder') . '=' . $db->Quote($group);


		$db->setQuery($query);
		$installed = $db->loadResult() > 0 ? true : false;

		return $installed;
	}

	/**
	 * Determines if a plan is related to an app
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isPlanRelated($appId, $planId)
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->qn('#__payplans_planapp');
		$query[] = 'WHERE ' . $db->qn('app_id') . '=' . $db->Quote($appId);
		$query[] = 'AND ' . $db->qn('plan_id') . '=' . $db->Quote($planId);

		$db->setQuery($query);

		$related = $db->loadResult() > 0 ? true : false;

		return $related;
	}

	public function getAppInstances($options = array())
	{
		$db = PP::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__payplans_app');
		$query[] = 'WHERE 1';

		foreach ($options as $key => $value) {
			$query[] = 'AND ' . $db->qn($key) . ' = ' . $db->Quote($value);
		}

		$query = implode(' ', $query);

		$this->setTotal($query, true);
		$result	= $this->getData($query);

		return $result;
	}
}


class PayplansModelformApp extends PayPlansModelform
{
	public function preprocessForm(JForm $form, $data, $group = 'content')
	{
		if (isset($data['type'])){
			$appObj = PayplansApp::getInstance($data['app_id'],$data['type']);
			 $xml = $appObj->getLocation() . DS . $appObj->getName() . '.xml';
			$form->loadFile($xml, false, '//config');

		}

		return parent::preprocessForm($form, $data);
	}
}
