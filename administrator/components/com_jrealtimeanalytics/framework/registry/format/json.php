<?php
// namespace components\com_jrealtimeanalytics\libraries\framework\registry\format;
/**
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @subpackage format
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Format Json handler for backward compatibility
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @subpackage format
 * @since 2.0
 */
class JRealtimeRegistryFormatJson extends JRealtimeRegistryFormat {

	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object  $object   Data source object.
	 * @return  string  JSON formatted string.
	 */
	public function objectToString($object) {
		return json_encode($object);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * If the string is not in JSON format, this method will attempt to parse it as INI format.
	 *
	 * @param   string  $data     JSON formatted string to convert.
	 * @return  object   Data object.
	 */
	public function stringToObject($data) {
		$data = trim($data);
		$obj = json_decode($data);
		return $obj;
	}
}