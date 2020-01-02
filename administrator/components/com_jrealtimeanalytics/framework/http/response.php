<?php
// namespace administrator\components\com_jrealtimeanalytics\framework\http;
/**
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage http
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * HTTP response object
 * 
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage http
 * @since 2.0
 */
class JRealtimeHttpResponse {
	/**
	 * @var    integer  The server response code.
	 * @since 2.0
	 */
	public $code;

	/**
	 * @var    array  Response headers.
	 * @since 2.0
	 */
	public $headers = array();

	/**
	 * @var    string  Server response body.
	 * @since 2.0
	 */
	public $body;
}
