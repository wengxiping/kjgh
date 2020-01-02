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

class PPResolver extends PayPlans
{
	/**
	 * Resolve ajax request namespaces
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ajax($parts, $extension = 'php')
	{
		static $paths = array();

		$key = md5(implode('.', $parts) . $extension);

		// Get and remove the location from parts.
		$location = array_shift($parts);

		// Determins the base path
		$base = $location == 'admin' ? PP_ADMIN : PP_SITE;

		// Determine if this is a view or controller.
		if ($location == 'site' || $location == 'admin') {

			$glued = implode('/', $parts);
			$path = $base . '/' . $glued . '.' . $extension;

			if ($parts[0] != 'controllers') {
				$path = $base . '/' . $glued . '/view.ajax.' . $extension;
			}

			// Import the base view if this is made to the view
			PP::import($location . ':/views/views');
		}

		// Determine if this is a plugin
		if ($location == 'plugins') {
			$element = implode('/', $parts);
			$path = JPATH_ROOT . '/plugins/payplans/' . $element . '/ajax.php';
		}

		return $path;
	}

	/**
	 * Resolves the namespace for apps
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function apps($parts, $extension = 'php')
	{
		if (!$parts || count($parts) == 1) {
			return $parts;
		}

		$app = $parts[0];

		// Get the group and element of the plugin
		$path = JPATH_ROOT . '/plugins/payplans/' . $app . '/app/tmpl';

		// Remove the first portion of the array since we already used it
		array_shift($parts);

		$path .= '/' . implode('/', $parts) . '.' . $extension;

		// Allow template overrides for app
		// Let check if the override path exists or not.
		// JOOMLA_ROOT/templates/JOOMLA_TEMPLATE/html/com_payplans/apps/offlinepay/form.php

		$currentTemplate = $this->getCurrentSiteTemplate('site');
		$override = JPATH_ROOT . '/templates/' . $currentTemplate . '/html/com_payplans/apps/' . $app . '/' . implode('/' , $parts) . '.' . $extension;

		if (JFile::exists($override)) {
			return $override;
		}

		// there is no override, lets continue to get the actual theme file.
		return $path;
	}

	/**
	 * Gets the current template
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrentSiteTemplate($location)
	{
		static $template = array();

		if (!isset($template[$location])) {
			$template[$location] = PP::getJoomlaTemplate($location);
		}

		return $template[$location];
	}

	/**
	 * Retrieves the override folder given the namespace
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getOverrideFolder($namespace)
	{
		// Get the type of namespace
		$parts = explode(':/', $namespace);
		$type = array_shift($parts);

		$parts = explode('/', implode('', $parts));
		$base = $parts[0];

		if ($base == 'site') {
			array_shift($parts);

			$path = JPATH_ROOT . '/templates/' . $this->getCurrentSiteTemplate($base) . '/html/com_payplans/' . implode('/', $parts);
			return $path;
		}
	}

	/**
	 * Resolves the namespace of plugins (e.g: plugins:/system/payplans)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function plugins($parts, $extension = 'php')
	{
		if (!$parts || count($parts) == 1) {
			return $parts;
		}

		// Get the group and element of the plugin
		$path = JPATH_ROOT . '/plugins/' . $parts[0] . '/' . $parts[1] . '/tmpl';

		// Remove the first 2 portions of the array since we already used it
		array_shift($parts);
		array_shift($parts);

		$path .= '/' . implode('/', $parts) . '.' . $extension;
		
		return $path;
	}

	/**
	 * Resolves a given namespace
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function resolve($namespace, $extension = 'php', $debug = false)
	{
		// Get the type of namespace
		$parts = explode(':/', $namespace);
		$type = array_shift($parts);

		// We can't resolve unknown types
		if (!method_exists($this, $type)) {
			return $namespace;
		}

		$parts = array_shift($parts);
		$parts = explode('/', $parts);

		$path = $this->$type($parts, $extension);

		return $path;
	}

	/**
	 * Translates media:/path/to/something to an absolute path
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function media($parts, $extension = 'php')
	{
		$path = JPATH_ROOT . '/media/com_payplans/' . implode('/', $parts);
		
		return $path;
	}

	/**
	 * Translates a posix path into an absolute path to the filesystem
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function themes($parts, $extension = 'php')
	{
		static $paths = array();

		$key = md5(implode('.', $parts) . $extension);

		if (!isset($paths[$key])) {
			// Get and remove the location from parts.
			$location = array_shift($parts);

			// For plugins, we resolve using it's own method
			if ($location == 'plugins') {
				$paths[$key] = $this->plugins($parts);

				return $paths[$key];
			}

			$base = JPATH_ROOT;

			// Default theme name
			$defaultThemeName = 'wireframe';

			// If the location is admin, we should respect that.
			if ($location == 'admin') {
				$base = JPATH_ADMINISTRATOR;
				$defaultThemeName = 'default';
			}

			$currentTemplate = $this->getCurrentSiteTemplate($location);

			$fileName = implode('/', $parts);

			if ($extension) {
				$fileName .= '.' . $extension;
			}

			// Test if override exists
			$override = $base . '/templates/' . $currentTemplate . '/html/com_payplans/' . $fileName;

			jimport('joomla.filesystem.file');

			if (JFile::exists($override)) {
				$paths[$key] = $override;
				
				return $paths[$key];
			}

			$paths[$key] = $base . '/components/com_payplans/themes/' . $defaultThemeName . '/' . $fileName;
		}

		return $paths[$key];
	}
}