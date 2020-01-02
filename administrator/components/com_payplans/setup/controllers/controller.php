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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
jimport('joomla.database.driver');
jimport('joomla.installer.helper');

class PayPlansSetupController
{
	private $result = array();

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
	}

	protected function data($key, $value)
	{
		$obj = new stdClass();
		$obj->$key = $value;

		$this->result[] = $obj;
	}

	/**
	 * Capture info message
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setInfo($message, $state = true, $args = array())
	{
		$result = new stdClass();
		$result->state = $state;
		$result->message = JText::_($message);

		if (!empty($args)) {
			foreach ($args as $key => $val) {
				$result->$key = $val;
			}
		}

		$this->result = $result;
	}

	/**
	 * Renders a response with proper headers
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function output($data = array())
	{
		header('Content-Type: application/json; UTF-8');

		if (empty($data)) {
			$data = $this->result;
		}

		echo json_encode($data);
		exit;
	}

	/**
	 * Generates a result object that can be output using @output
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getResultObj($message, $state, $stateMessage = '')
	{
		$obj = new stdClass();
		$obj->state = $state;
		$obj->stateMessage = $stateMessage;
		$obj->message = JText::_($message);

		return $obj;
	}

	/**
	 * Retrieves the current version of the installed launcher
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getVersion()
	{
		static $version = null;

		// Get the version from the manifest file
		if (is_null($version)) {
			$contents = JFile::read(PP_LAUNCHER_MANIFEST);
			$parser = simplexml_load_string($contents);

			$version = $parser->xpath('version');
			$version = (string) $version[0];
		}

		return $version;
	}

	/**
	 * Retrieves the installed Joomla major version
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getJoomlaVersion()
	{
		$version = explode('.', JVERSION);
		$version = $version[0] . '.' . $version[1];

		return $version;
	}

	/**
	 * Retrieves the latest version of PayPlans
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getInfo()
	{
		$resource = curl_init();

		// Determines which version of the current launcher is being used
		$from = $this->getVersion();

		curl_setopt($resource, CURLOPT_URL, PP_MANIFEST);
		curl_setopt($resource, CURLOPT_POST, true);
		curl_setopt($resource, CURLOPT_POSTFIELDS, 'apikey=' . PP_KEY . '&from=' . $from);
		curl_setopt($resource, CURLOPT_TIMEOUT, 120);
		curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($resource);

		curl_close($resource);

		if (!$result) {
			return false;
		}

		$obj = json_decode($result);

		return $obj;
	}

	/**
	 * Determines if we are in development mode
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isDevelopment()
	{
		$session = JFactory::getSession();
		$developer = $session->get('payplans.developer');

		return $developer;
	}

	/**
	 * Saves a configuration item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function updateConfig($key, $value)
	{
		$this->engine();

		$table = PP::table('Config');
		$exists = $table->load(array('key' => $key));

		if (!$exists) {
			$table->key = $key;
		}

		$table->value = $value;
		$table->store();
	}

	/**
	 * Loads up the Payplans library if it exists
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function engine()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';

		if (!JFile::exists($file)) {
			return false;
		}

		// Include payplans framework
		require_once($file);
	}

	/**
	 * Loads the current version that was installed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getInstalledVersion()
	{
		$this->engine();

		$path = JPATH_ADMINISTRATOR . '/components/com_payplans/payplans.xml';
		$contents = JFile::read($path);

		$parser = simplexml_load_string($contents);

		$version = $parser->xpath('version');
		$version = (string) $version[0];

		return $version;
	}

	/**
	 * Loads the previous version that was installed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPreviousVersion($versionType)
	{
		// Render Payplans engine
		$this->engine();

		$table = PP::table('Config');
		$exists = $table->load(array('key' => $versionType));

		if ($exists) {
			return $table->value;
		}

		// there is no value of the version type. return false.
		return false;
	}

	/**
	 * method to extract zip file in installation part
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function ppExtract($destination, $extracted)
	{
		if (JVERSION < 4.0) {
			$state = JArchive::extract($destination, $extracted);

		} else {
			$archive = new Joomla\Archive\Archive();
			$state = $archive->extract($destination, $extracted);
		}

		return $state;
	}

	/**
	 * Determine if database is set to mysql or not.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isMySQL()
	{
		$jConfig = JFactory::getConfig();
		$dbType = $jConfig->get('dbtype');

		return $dbType == 'mysql' || $dbType == 'mysqli';
	}

	/**
	 * Determine if mysql can support utf8mb4 or not.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function hasUTF8mb4Support()
	{
		static $_cache = null;

		if (is_null($_cache)) {

			$db = JFactory::getDBO();

			if (method_exists($db, 'hasUTF8mb4Support')) {
				$_cache = $db->hasUTF8mb4Support();
				return $_cache;
			}

			// we check the server version 1st
			$server_version = $db->getVersion();
			if (version_compare($server_version, '5.5.3', '<')) {
				 $_cache = false;
				 return $_cache;
			}

			$client_version = '5.0.0';

			if (function_exists('mysqli_get_client_info')) {
				$client_version = mysqli_get_client_info();
			} else if (function_exists('mysql_get_client_info')) {
				$client_version = mysql_get_client_info();
			}

			if (strpos($client_version, 'mysqlnd') !== false) {
				$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);
				$_cache = version_compare($client_version, '5.0.9', '>=');
			} else {
				$_cache = version_compare($client_version, '5.5.3', '>=');
			}

		}

		return $_cache;
	}

	/**
	 * Convert utf8mb4 to utf8
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function convertUtf8mb4QueryToUtf8($query)
	{
		if ($this->hasUTF8mb4Support())
		{
			return $query;
		}

		// If it's not an ALTER TABLE or CREATE TABLE command there's nothing to convert
		$beginningOfQuery = substr($query, 0, 12);
		$beginningOfQuery = strtoupper($beginningOfQuery);

		if (!in_array($beginningOfQuery, array('ALTER TABLE ', 'CREATE TABLE')))
		{
			return $query;
		}

		// Replace utf8mb4 with utf8
		return str_replace('utf8mb4', 'utf8', $query);
	}

	/**
	 * method to execute query
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function ppQuery($db)
	{
		if (JVERSION < 4.0) {
			return $db->query();
		} else {
			return $db->execute();
		}
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function splitSql($contents)
	{
		if (JVERSION < 4.0) {
			$queries = JInstallerHelper::splitSql($contents);

		} else {
			$queries = JDatabaseDriver::splitSql($contents);
		}

		return $queries;
	}
}
