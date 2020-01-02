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

jimport('joomla.filesystem.file');

class PPThemes
{
	// Variables that exists throughout this theme scope
	public $app = null;
	public $my = null;
	public $jConfig = null;
	public $config = null;

	// This holds all properties accessible to the theme file
	private $vars = array();

	// Static has higher precendence of instance
	public static $_inlineScript	 = true;
	public static $_inlineStylesheet = true;

	public $inlineScript     = true;
	public $inlineStylesheet = true;

	public $extension = 'php';

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->jConfig = PP::jconfig();
		$this->config = PP::config();
		$this->my = JFactory::getUser();

		$input = $this->app->input;
		$this->tmpl = $input->get('tmpl', '', 'word');
		$theme = 'wireframe';

		$this->theme = $theme;
	}


	/**
	 * To address isseus with document being retrieved before the onAfterRoute event.
	 * Any calls made to retrieve the document in Joomla will result into the document mode being set to html.
	 * Which will be problematic for rss feeds or other formats.
	 *
	 * @since	4.0.15
	 * @access	public
	 */
	public function __get($property)
	{
		if ($property == 'doc') {
			static $doc = null;

			if (is_null($doc)) {
				$doc = JFactory::getDocument();
				return $doc;
			}
		}
	}

	/**
	 * Returns the metadata of a template file.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getTemplate($namespace = null)
	{
		// Explode the namespace
		$parts = explode(':', $namespace);

		// For apps, we cannot use themes:/
		if (count($parts) <= 1) {
			$namespace = 'themes:/' . $namespace;
		}

		$resolver = PP::resolver();

		$template = new stdClass();
		$template->file = $resolver->resolve($namespace);
		$template->script = $resolver->resolve($namespace, 'js');

		return $template;
	}

	/**
	 * Template helpers to generate generic html codes
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function html($namespace)
	{
		static $objects = array();

		// Include the base abstract
		require_once(__DIR__ . '/helpers/abstract.php');

		// Get the correct namespaces
		list($helperName, $methodName) = explode('.', $namespace);

		$className = "PPThemesHelper" . ucfirst($helperName);
		$uid = md5($className);

		// If it doesn't exists yet, create it
		if (!isset($objects[$uid])) {

			$file = __DIR__ . '/helpers/' . strtolower($helperName) . '.php';

			include_once($file);

			$objects[$uid] = new $className;
		}

		// Remove the first 2 arguments from the args.
		$args = func_get_args();
		$args = array_splice($args, 1);


		$obj = $objects[$uid];

		$response = call_user_func_array(array($obj, $methodName), $args);

		return $response;
	}

	/**
	 * Outputs the data from a template file.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function output($tpl = null, $args = null)
	{
		// Try to get the template data.
		$template = $this->getTemplate($tpl);

		$output = $this->parse($template->file, $args);

		// Script
		if (JFile::exists($template->script)) {

			$script = PP::script();
			$script->file = $template->script;
			$script->vars = $this->vars;

			if (!self::$_inlineScript || !$this->inlineScript) {
				$script->attach();
			} else {
				$script->scriptTag	= true;
				$output .= $script->parse($args);
			}
		}

		return $output;
	}

	/**
	 * Cleaner extract method. All variables that are set in $this->vars would be extracted within this scope only.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function parse($file, $vars = null)
	{
		ob_start();

		// If argument is passed in, we only want to load that into the scope.
		if (is_array($vars)) {
			extract($vars);
		} else {
			// Extract variables that are available in the namespace
			if(!empty($this->vars)) {
				extract($this->vars);
			}
		}

		// Magic happens here when we include the template file.
		include($file);

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Allows caller to assign value to the theme
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function set($key, $value)
	{
		$this->vars[$key] = $value;

		return $this;
	}

	/**
	 * Allows caller to assign value to the theme
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setVars($vars)
	{
		$this->vars = array_merge($this->vars, $vars);

		return $this;
	}

	/**
	 * Allows caller to fetch the assigned value from the theme
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function get($key, $default)
	{
		return isset($this->vars[$key]) ? $this->vars[$key] : $default;
	}

	/**
	 * Determines if this is a mobile layout
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function isMobile()
	{
		static $responsive = null;

		if (is_null($responsive)) {
			$responsive = PP::responsive()->isMobile();
		}

		return $responsive;
	}

	/**
	 * Renders the widget items
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function render()
	{
		$args = func_get_args();

		// Get the type of the widget from the first parameter.
		$type = array_shift($args);
		$method = 'render' . ucfirst($type);

		if (!method_exists($this, $method)) {
			return;
		}

		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * Render modules
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderModule($position, $wrapper = null, $attributes = array(), $content = null, $force = false)
	{
		$doc = JFactory::getDocument();
		$tmp = $doc->getType();

		// If this is loaded from an ajax call, we cannot display modules because if
		// the module used some sort of document to attach scripts, it would mess up the layout
		if ($doc->getType() != 'html') {
			return;
		}

		// For Joomla 2.5, we need to include the module
		jimport('joomla.application.module.helper');

		$contents = '';
		$modules = JModuleHelper::getModules($position);

		// If there's nothing to load, just skip this
		if (!$modules) {

			// We cannot return false here otherwise the theme will echo as 0.
			return;
		}

		$output = array();

		// Use a standard module style if no style is provided
		if (!isset($attributes['style'])) {
			$attributes['style']	= 'xhtml';
		}

		foreach ($modules as $module) {

			// We need to clone the module to avoid the $module->content from being cached.
			$module = clone($module);
			$renderer = $doc->loadRenderer('module');
			
			$theme = PP::themes();
			$showTitle = false;

			// If we are using our own wrapper, we need to tell the renderer to not show the title in the module since we are using our own wrapper
			if (!is_null($wrapper)) {
				$showTitle	= $module->showtitle;

				// Always set the title to false
				$module->showtitle 	= false;
			}

			$moduleOutput = $renderer->render($module, $attributes, $content);
			$moduleOutput = JString::trim($moduleOutput);

			if (!$moduleOutput) {
				continue;
			}

			$theme->set('position', $position );
			$theme->set('output', $moduleOutput);

			$contents = $theme->output('site/structure/modules');

			// Determines if we need to add an additional wrapper to surround it
			if (!is_null($wrapper)) {
				// Reset the module title back
				$module->showtitle 	= $showTitle;

				$theme = PP::themes();
				$registry = PP::registry($module->params);

				$theme->set('module', $module);
				$theme->set('params', $registry);
				$theme->set('contents', $contents);
				$contents = $theme->output($wrapper);
			}

			$output[] = $contents;
		}

		$output = implode('', $output);

		return $output;
	}

	/**
	 * Renders the output for plugins based on specific positions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderPlugins($plugins, $position, $wrapper = true)
	{
		$options = array(
			'result' => $plugins,
			'position' => $position,
			'wrapper' => $wrapper
		);

		$output = $this->output('site/plugins/result', $options);

		return $output;
	}
}
