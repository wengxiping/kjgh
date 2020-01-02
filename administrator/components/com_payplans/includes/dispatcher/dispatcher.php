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

class PPDispatcher
{
	/**
	 * Trigger joomla plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trigger($eventName, &$data = array(), $prefix = '')
	{
		// Load payplans plugins
		self::loadPlugins();

		$dispatcher = JDispatcher::getInstance();

		return $dispatcher->trigger($eventName, $data);
	}

	/**
	 * Trigger joomla plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function triggerPlugin($eventName, &$data = array(), $prefix = '')
	{
		$dispatcher = JDispatcher::getInstance();

		// Load payplans plugins
		$this->loadPlugins();

		return $dispatcher->trigger($eventName, $data);
	}

	/**
	 * Load plugins from the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadPlugins($type = 'payplans')
	{
		static $loaded = array();

		//is already loaded
		if (isset($loaded[$type])) {
			return true;
		}

		JPluginHelper::importPlugin($type);

		// Set that plugins are already loaded
		$loaded[$type]= true;
		return true;
	}
}
