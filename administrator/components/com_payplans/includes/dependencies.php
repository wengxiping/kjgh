<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/constants.php');
require_once(__DIR__ . '/formats.php');
require_once(__DIR__ . '/helpers/plan.php');
require_once(__DIR__ . '/helpers/modifier.php');
require_once(__DIR__ . '/helpers/app.php');

// Require helpers
require_once(__DIR__ . '/plugins.php');
require_once(__DIR__ . '/formatter.php');

// Router
require_once(PP_LIB . '/router.php');

// Require interfaces
require_once(PP_LIB . '/interfaces/interfaces.php');
require_once(PP_LIB . '/app/app.php');

// Include dependencies that is required site wide
PP::load('event');

jimport('joomla.filesystem.file');

if (!function_exists('dump')) {
	function dump()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			var_dump($arg);
		}
		echo '</pre>';
		exit;
	}
}

if (!function_exists('pdump')) {
	function pdump()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			print_r($arg);
		}
		echo '</pre>';
		exit;
	}
}

static $languageLoaded = null;

if (is_null($languageLoaded)) {
	//Load language file for plugins
	//loading this before com_payplans.ini to overcome the problem of translation
	$filename = 'com_payplans_plugins';
	$language = JFactory::getLanguage();
	$language->load($filename, JPATH_SITE);
	
	//load language file
	$filename = 'com_payplans';
	$language->load($filename, JPATH_ADMINISTRATOR);

	//load language file
	$filename = 'com_payplans';
	$language->load($filename, JPATH_SITE);

	$languageLoaded = true;
}

class PayPlans
{
	public $app = null;
	public $my = null;
	public $config = null;
	public $input = null;

	protected $error = null;

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->config = PP::config();
		$this->my = JFactory::getUser();
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

			return $doc;
		}
	}

	public function setError($message)
	{
		$this->error = JText::_($message);
	}

	public function getError()
	{
		if (!$this->error) {
			return false;
		}

		return JText::_($this->error);
	}
}
