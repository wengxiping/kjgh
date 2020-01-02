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

jimport('joomla.plugin.plugin');

/**
 * VERY IMP :
 * While adding functions into plugin, we should keep in mind
 * that all function not starting with _ (under-score), will be
 * added into plugins event functions. So while adding supportive
 * function, always start them with underscore
 */
class PPPlugins extends JPlugin
{
	protected $_tplVars = array();

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->config = PP::config();
		$this->my = JFactory::getUser();
		$this->info = PP::info();
		$this->theme = PP::themes();
		$this->initalize();

		$path = $this->getPluginPath() . '/' . $this->getName();
		$this->loadLanguage('', $path);
	}

	/**
	 * Attach a script for the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function attachScripts($files = array())
	{
		if (!$files) {
			return;
		}

		$path = $this->getAssetsPath();

		$lib = PP::scripts();

		foreach ($files as $file) {
			$file = $path . '/' . $file . '.js';

			$lib->addScript($file);
		}
	}

	/**
	 * Attach a script for the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function attachScriptContents($contents)
	{
		PP::scripts()->addInlineScripts($contents);
	}

	/**
	 * Retrieves an app's helper
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAppHelper()
	{
		static $helpers = null;

		$key = $this->getName();

		if (!isset($helpers[$key])) {
			$path = $this->getPluginPath();
			$path .= '/app/helper.php';

			if (!JFile::exists($path)) {
				$helpers[$key] = false;
				return $helpers[$key];
			}

			require_once($path);
			$className = 'PPHelper' . $this->getName();

			// Get the first available app
			$apps = $this->getAvailableApps();
			$app = PP::app();

			if ($apps) {
				$app = array_pop($apps);
			}

			$helpers[$key] = new $className($app->getAppParams(), $app);
		}

		return $helpers[$key];
	}

	/**
	 * Retrieves an app's helper
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAvailableApps()
	{
		$apps = PPHelperApp::getAvailableApps($this->getName());

		return $apps;
	}

	/**
	 * Retrieves the name of the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getName()
	{
		return $this->get('_name');
	}

	/**
	 * Retrieves the name of the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->get('_type');
	}

	/**
	 * Retrieves the path to the plugin's app.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function getAppPath()
	{
		// Since all of the apps are stored in the app folder, we can centralize this
		$path = $this->getPluginPath() . '/app';

		return $path;
	}

	/**
	 * All plugins should store their script files under /plugins/element/assets/
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function getAssetsPath($uri = true)
	{
		$path = $this->getPluginPath();

		if ($uri) {
			$path = str_ireplace(JPATH_ROOT, rtrim(JURI::root(), '/'), $path);
		}

		$path .= '/assets';

		return $path;
	}

	/**
	 * Retrieves the path to the plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function getPluginPath()
	{
		$name = $this->getName();
		$type = $this->getType();

		$path = JPATH_PLUGINS . '/' . $type . '/' . $name;

		return $path;
	}

	/**
	 * Resolves an xhr request by sending the contents back
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function resolve()
	{
		if (!isset($this->ajax) || !$this->ajax) {
			return false;
		}

		return call_user_func_array(array($this->ajax, __FUNCTION__), func_get_args());
	}

	/**
	 * Allows plugin to assign template variables
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function set($property, $value = null)
	{
		$this->theme->set($property, $value);
	}

	/**
	 * Allows plugin to output plugin template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function output($namespace)
	{
		$type = $this->getType();
		$name = $this->getName();

		$namespace = 'plugins:/' . $type . '/' . $name . '/' . $namespace;

		$output = $this->theme->output($namespace);

		return $output;
	}

	/**
	 * Legacy support. Use @initialize instead
	 *
	 * @deprecated	4.0.0
	 */
	protected function _initialize(Array $options = array()) { }

	/**
	 * Initializes the plugins. Method implementation could be implemented by child classes.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function initalize($options= array())
	{
		// For backward compatibility with 3.6.5 and earlier
		$this->_initialize();
	}

	/**
	 * Plugin is available :
	 * If current plugin can be used ir-respective
	 * of conditions
	 */
	public function _isAvailable(Array $options= array())
	{}

	/**
	 * Plugin is available but check if
	 * It should be used for given conditions
	 */
	public function _isApplicable(Array $conditions= array())
	{}
}
