<?php
// namespace components\com_jrealtimeanalytics\libraries\framework\registry;
/**
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Registry for backward compatibility
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @since 2.0
 */
abstract class JRealtimeRegistryFormat {
	/**
	 * Instances container
	 * 
	 * @var array
	 */
	public static $registryFormats = array();
	
	/**
	 * Singleton abstract factory
	 * 
	 * @access public
	 * @param $format
	 */
	public static function getInstance($format) {
		if (!isset(self::$registryFormats[$format])) {
			$className = 'JRealtimeRegistryFormat' . ucfirst($format);
			self::$registryFormats[$format] = new $className();
		}
		return self::$registryFormats[$format];
	}

	/**
	 * @access public
	 * @param $object
	 */
	public abstract function objectToString($object);

	/**
	 * @access public
	 * @param $data
	 */
	public abstract function stringToObject($data);
}