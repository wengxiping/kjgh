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

class PPEvent
{
	static protected $events = null;
	static protected $paths = array();

	/**
	 * Add a path from where we can load event classes
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addEventsPath($path = null)
	{
		if ($path != null) {
			self::$paths[] = $path;
		}

		return self::$paths;
	}

	/**
	 * Retrieves event paths
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getPaths()
	{
		$default = __DIR__ . '/events';

		if (!in_array($default, self::$paths)) {
			self::$paths[] = $default;
		}

		return self::$paths;
	}

	/**
	 * Load Event from various folders
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getEvents()
	{
		// Already loaded
		if (self::$events) {
			return self::$events;
		}

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// Load apps from file systems
		$events = array();
		$paths = self::getPaths();

		foreach ($paths as $path) {
			$files = JFolder::files($path, '.php$');

			// Also mark them autoload
			foreach ($files as $file) {
				$absolutePath = $path . '/' . $file;

				include_once($absolutePath);

				$className = JFile::stripExt($file);
				$class = 'PPEvent' . ucfirst($className);

				$obj = new $class();
				$events[$class] = $obj;
			}
		}

		// also sort for consistent behaviour
		sort($events);

		self::$events = $events;

		return $events;
	}

	/**
	 * Trigger all observers of this events instances
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function trigger($eventName, $args = array(), $purpose = '', $refObject = null)
	{
		if (defined('PAYPLANS_MIGRATION_START') && !defined('PAYPLANS_MIGRATION_END')) {
			return true;
		}

		// IMPORTANT: Plugins should be triggered before Apps
		$pluginResults = array();

		if (stristr($eventName, 'onPayplans')) {
			$dispatcher = PP::dispatcher();
			$pluginResults = $dispatcher->trigger($eventName, $args);
		}

		// Internal triggers
		$coreResults = self::triggerInternal($eventName, $args);

		// Trigger apps
		$appResults = PPHelperApp::trigger($eventName, $args, $purpose, $refObject);

		return array_merge($pluginResults, $coreResults, $appResults);
	}

	/**
	 * Trigger internal events stored in event/events
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function triggerInternal($eventName, $args = array())
	{
		// Get plugins
		$observers = self::getEvents();
		$results = array();

		// Trigger all apps if they serve the purpose
		foreach ($observers as $observer) {
			if (method_exists($observer, $eventName)) {
				$results[] = call_user_func_array(array($observer,$eventName), $args);
			}
		}

		return $results;
	}
}
