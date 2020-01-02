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
jimport('joomla.filesystem.file');

/**
 * Registry object responsibilities
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @since 2.0
 */
interface IJRealtimeRegistry {
	/**
	 * Load DB configuration file
	 *
	 * @access public
	 * @override
	 * @param $path
	 * @param $format
	 * @param $options
	 */
	public function loadFile($path, $format = 'JSON', $options = array());
	
	/**
	 * Write updated DB configuration file
	 * 
	 * @access public
	 * 
	 * @param $data
	 * @param $path
	 * @param $format
	 * 
	 * @return boolean
	 */
	public function writeFile($data, $path, $format);
	
	/**
	 * Load bind string data
	 *
	 * @access public
	 * @override
	 * @param $data
	 * @param $format
	 * @param $options
	 */
	public function loadString($data, $format = 'JSON', $options = array());
	
	/**
	 * Flat input array bot with keys and values
	 *
	 * @access public
	 * @param $array
	 * @param $array
	 *
	 * return array
	 */
	public function toFlatArray($array, $flat = false);
	
	/**
	 * Prepare a flat array to be used in database query
	 *
	 * @access public
	 * @param $data array
	 * @param $array
	 *
	 * return array
	 */
	public function toDatabaseString($data);
}

/**
 * Registry concrete implementation
 *
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage registry
 * @since 2.0
 */
class JRealtimeRegistry extends JRegistry implements IJRealtimeRegistry, IteratorAggregate {
	/**
	 * IteratorAggregate interface implementation
	 * 
	 * @access public
	 * @return ArrayIterator
	 */
	public function getiterator() {
		return new ArrayIterator($this->data);
	}
	
	/**
	 * Load DB configuration file
	 * 
	 * @access public
	 * @override
	 * @param $path
	 * @param $format
	 * @param $options
	 * 
	 * @return boolean
	 */
	public function loadFile($path, $format = 'JSON', $options = array()) {
		$data = JFile::read($path);

		return $this->loadString($data, $format);
	}

	/**
	 * Write updated DB configuration file
	 * 
	 * @access public
	 * 
	 * @param $data
	 * @param $path
	 * @param $format
	 * 
	 * @return boolean
	 */
	public function writeFile($data, $path, $format) {
		// Load a string into the given namespace [or default namespace if not given]
		$handler = JRealtimeRegistryFormat::getInstance($format);
		$string = $handler->objectToString($data);
		
		if(!JFile::write($path, $string)) {
			throw new JRealtimeException(JText::_('COM_JREALTIME_SAVEFILE_ERROR'), 'error');
		}
		
		return true;
	}

	/**
	 * Load bind string data
	 * 
	 * @access public
	 * @override
	 * @param $data
	 * @param $format
	 * @param $options
	 * 
	 * return boolean
	 */
	public function loadString($data, $format = 'JSON', $options = array()) {
		// Load a string into the given namespace [or default namespace if not given]
		$handler = JRealtimeRegistryFormat::getInstance($format);

		$obj = $handler->stringToObject($data);
		$this->loadObject($obj);

		return true;
	}
	
	/**
	 * Flat input array both with keys and values
	 *
	 * @access public
	 * @param $array
	 * @param $flat
	 *
	 * return array
	 */
	public function toFlatArray($array, $flat = false) {
		if (!is_array($array) || empty($array)) return $array;
		if (empty($flat)) $flat = array();
		 
		foreach ($array as $key => $val) {
			if(!in_array($key, $flat) && !is_numeric($key)) {
				$flat[] = $key;
			}
			if (is_array($val)){
				$flat = $this->toFlatArray($val, $flat);
			} else {
				if($val) {
					$flat[] = $val;
				} else {
					continue;
				}
			}
		}
		 
		return $flat;
	}
	
	/**
	 * Prepare a flat array to be used in database query
	 *
	 * @access public
	 * @param $data array
	 *
	 * return array
	 */
	public function toDatabaseString($data) {
		$db = JFactory::getDbo();
		if (!is_array($data) || empty($data)) return $data;
		foreach ($data as $index=>&$value) {
			$value = $db->quote($value);
		}
		
		return implode(',', $data);
	}
}