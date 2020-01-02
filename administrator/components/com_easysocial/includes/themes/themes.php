<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

require_once(SOCIAL_LIB . '/template/template.php');

class SocialThemes extends SocialTemplate
{
	// Static has higher precendence of instance
	public static $_inlineScript	 = true;
	public static $_inlineStylesheet = true;

	public $inlineScript     = true;
	public $inlineStylesheet = true;

	public $mode = 'php';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * This is the factory method to ensure that this class is always created all the time.
	 * Usage: FD::get( 'Template' );
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory()
	{
		return new self();
	}

	/**
	 * Determines if there are modules on specific positions
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getModulesFromPosition($position)
	{
		static $modules = array();

		if (!isset($modules[$position])) {
			$modules[$position] = JModuleHelper::getModules($position);
		}

		return $modules[$position];
	}

	/**
	 * Resolve a given POSIX path.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function resolve($namespace)
	{
		static $profiles = array();

		$path = '';
		$parts = explode( '/' , $namespace );
		$config = FD::config();

		// Get and remove the location from parts.
		$location	= array_shift( $parts );

		// Get the current theme.
		$theme = $config->get('theme.' . $location, 'wireframe');

		$app = JFactory::getApplication();

		if (ES::responsive()->isMobile() && $app->getTemplate() == 'esmobile') {
			$theme = 'wireframe';
		}

		// Get the absolute path for fields
		if ($location == 'fields') {

			// Get and remove the group.
			$group		= array_shift($parts);

			// Get and remove the element.
			$element	= array_shift($parts);

			// Get the default path so we can fall back to this
			$default 	= SOCIAL_FIELDS . '/' . $group . '/' . $element . '/themes/default/' . implode( '/' , $parts );

			// Check if the template override exists in the path below:
			// /templates/JOOMLA_TEMPLATE/html/com_easysocial/apps/fields/$group/$element
			$current = FD::assets()->getJoomlaTemplate();
			$override = JPATH_ROOT . '/templates/' . $current . '/html/com_easysocial/apps/fields/' . $group . '/' . $element . '/' . implode( '/' , $parts );

			if (JFile::exists($override)) {
				return $override;
			}


			return $default;
		}

		// Get the absolute path for apps
		if ($location == 'apps') {
			// Get and remove the group.
			$group		= array_shift( $parts );

			// Get and remove the element.
			$element	= array_shift( $parts );

			// Get the default path
			$default 	= SOCIAL_APPS . '/' . $group . '/' . $element . '/themes/default/' . implode( '/' , $parts );

			// Check if the template override exists in the path below:
			// /templates/JOOMLA_TEMPLATE/html/com_easysocial/apps/fields/$group/$element
			$current 	= FD::assets()->getJoomlaTemplate();
			$override 	= JPATH_ROOT . '/templates/' . $current . '/html/com_easysocial/apps/' . $group . '/' . $element . '/' . implode( '/' , $parts );

			if (JFile::exists($override)) {
				return $override;
			}

			return $default;
		}

		// Default theme
		$default = 'default';

		// Get the absolute path of the initial location
		if ($location == 'admin') {
			$path 		= SOCIAL_ADMIN;
			$default	= $config->get('theme.admin_base');
		}

		if ($location == 'site' || $location == 'emails') {
			$path 		= SOCIAL_SITE;
			$default	= $config->get('theme.site_base');
		}

		// Determine if there's a joomla template override.
		$client = 'site';
		$base = JPATH_ROOT;

		// If the location is admin, we should respect that.
		if ($location == 'admin') {
			$base = JPATH_ADMINISTRATOR;
			$client = 'admin';
		}

		$currentTemplate = ES::assets()->getJoomlaTemplate($client);
		$override = $base . '/templates/' . $currentTemplate . '/html/com_easysocial/' . implode('/', $parts);
		$overrideExists = JFile::exists($override);

		if ($overrideExists) {
			return $override;
		}

		// Test if the file really exists
		$file = $path . '/themes/' . $theme . '/' . implode( '/' , $parts);

		// If the file doesn't exist, always revert to the original base theme
		if (!JFile::exists($file)) {
			$file = $path . '/themes/' . $default . '/' . implode('/', $parts);
		}

		return $file;
	}

	/**
	 * Outputs the data from a template file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function output($tpl = null, $args = null)
	{
		$template = $this->getTemplate($tpl);

		$this->file	= $template->file;
		$output = $this->parse($args);

		// Script
		if (JFile::exists($template->script)) {
			$script = FD::get('Script');
			$script->file = $template->script;
			$script->vars = $this->vars;

			if (!self::$_inlineScript || !$this->inlineScript) {
				$script->attach();
			} else {
				$script->scriptTag	= true;
				$output .= $script->parse($args);
			}
		}

		$debug = $this->input->get('debug', false, 'bool');

		if (!$this->my->isSiteAdmin()) {
			$debug = false;
		}

		$wrapper = '';

		if ($debug) {
			$path = str_ireplace(array(JPATH_ROOT, JPATH_ADMINISTRATOR), '', $this->file);
			$path = str_ireplace('/components/com_easysocial/themes', '', $path);

			$wrapper .= '<div class="es-debug-code">';
		}

		$wrapper .= $output;

		if ($debug) {
			$wrapper .= '<span data-es-debug="'. $path.'" class="es-debug-code__text" style=""><i class="fa fa-cog"></i> Hover </span>';
			$wrapper .= '</div>';
		}

		return $wrapper;
	}

	public function json_encode($value)
	{
		return FD::json()->encode($value);
	}

	public function json_decode($value)
	{
		return FD::json()->decode($value);
	}

	/*
	 * Returns a JSON encoded string for the current theme request.
	 *
	 * @param	null
	 * @return	string	JSON encoded string.
	 */
	public function toJSON()
	{
		return $this->json_encode($this->vars);
	}

	/**
	 * Get's the current URI for callback
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCallback()
	{
		return FRoute::current(true);
	}

	/**
	 * Renders the widget items
	 *
	 * @since	1.0
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
	 * Alternative to renderXXX as trigger would allow us to trigger multiple fields
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function trigger($type)
	{
		$args = func_get_args();

		// Remove the first argument
		array_shift($args);

		$method = 'trigger' . ucfirst($type);

		if (!method_exists($this, $method)) {
			return;
		}

		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * Renders module output on a theme file.
	 *
	 * @since	1.0
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

		$modules = $this->getModulesFromPosition($position);

		// If there's nothing to load, just skip this
		if (!$modules) {

			// We cannot return false here otherwise the theme will echo as 0.
			return;
		}

		$output 	= array();

		// Use a standard module style if no style is provided
		if (!isset($attributes['style'])) {
			$attributes['style']	= 'xhtml';
		}

		foreach ($modules as $module) {

			// We need to clone the module to avoid the $module->content from being cached.
			$module = clone($module);
			$renderer = $doc->loadRenderer('module');
			$theme = ES::themes();
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

				$theme		= FD::themes();
				$registry	= FD::registry( $module->params );

				$theme->set('module', $module);
				$theme->set('params', $registry);
				$theme->set('contents', $contents);
				$contents	= $theme->output($wrapper);
			}

			$output[]	= $contents;
		}

		$output 	= implode('', $output);

		return $output;
	}

	/**
	 * Triggers a set of fields
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function triggerFields($group, $view, $position, $object)
	{
		$fields = ES::fields();
		return $fields->triggerPosition($group, $view, $position, $object);
	}

	/**
	 * Renders custom field output on a theme file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderFields( $group , $view , $position )
	{
		$fields = ES::fields();
		$args = func_get_args();
		$args = isset($args[3]) ? $args[3] : array();

		return $fields->renderWidgets($group, $view, $position, $args);
	}

	/**
	 * Renders widget output on a theme file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderWidgets($group, $view, $position)
	{
		$apps = ES::apps();
		$args = func_get_args();

		// Argument 3 will always be sent to the apps library
		$appArgs = isset($args[3]) ? $args[3] : array();
		$output = $apps->renderWidgets($group, $view, $position, $appArgs);

		// Argument 4 is used as a template wrapper
		if (isset($args[4]) && $output !== false) {
			$namespace = $args[4];
			$theme = ES::themes();
			$theme->set('output', $output);

			$output = $theme->output($namespace);
		}

		return $output;
	}

	/**
	 * Determine what theme you used.
	 *
	 * @since	1.4.11
	 * @access	public
	 */
	public function getCurrentTheme()
	{
		$config = ES::config();

		$theme = $config->get('theme.site', 'wireframe');
		return $theme;
	}
}
