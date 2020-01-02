<?php
// namespace components\com_jrealtimeanalytics\libraries\framework\dic;
/**
 *
 * @package JREALTIMEANALYTICS::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage dic
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Base controller class
 * 
 * @package JREALTIMEANALYTICS::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage dic
 * @since 2.0
 */
class JRealtimeDicInstance {
	public $name;
	public function __construct($instance) {
		$this->name = $instance;
	}
}
