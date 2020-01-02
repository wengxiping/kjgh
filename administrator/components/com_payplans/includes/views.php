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

class PayPlansView extends JViewLegacy
{
	/**
	 * Stores the view's message queue.
	 * @var	stdClass
	 */
	protected $message = null;

	/**
	 * Stores the theme object.
	 * @var	SocialThemes
	 */
	protected $theme = null;

	/**
	 * Determines if there's any errors on this view.
	 * @var	boolean
	 */
	protected $errors = false;

	protected $breadcrumbs	= null;
	protected $app = null;

	public function __construct($config = array())
	{
		// Load Joomla's app
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->doc = JFactory::getDocument();
		$this->theme = PP::themes();
		$this->jconfig = PP::jconfig();
		$this->my = JFactory::getUser();
		$this->info = PP::info();
		$this->config = PP::config();

		// If the request is an ajax request, we should prepare the ajax library for the caller
		if ($this->doc->getType() == 'ajax') {
			$this->ajax = PP::ajax();
		}

		parent::__construct($config);
	}

	/**
	 * Determines if there's any errors on this view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasErrors()
	{
		return $this->errors;
	}

	/**
	 * Triggers plugins in the view
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function triggerPlugins($event)
	{
		// Trigger plugins
		$layout = $this->getLayout();
		$args = array(&$this, &$layout);

		$result = PP::event()->trigger($event, $args, '', $this);

		// dump($result);
		// $pluginResult = $this->_filterPluginResult($pluginResult);

		// // now get html from different plugins and views
		// $olddata = $this->get('plugin_result');
		// $pluginResult = $this->_mergePluginsData($pluginResult, $olddata);

		// $this->assign('plugin_result', $pluginResult);
	}

	/**
	 * Formats triggers from plugins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatPluginResult($pluginResults)
	{
		$result = array('default' => '');

		if (!$pluginResults) {
			return $result;
		}

		foreach ($pluginResults as $pluginResult) {

			// ignore empty, true and false
			if (is_bool($pluginResult) || !isset($pluginResult) || !$pluginResult) {
				continue;
			}

			$position = 'default';
			$html = '';

			// want to set on position
			if (is_array($pluginResult)) {


				foreach ($pluginResult as $position => $html) {

					// if string, then need to display on certain position
					if (is_string($position)) {

						if (!isset($result[$position])) {
							$result[$position] = '';
						}

						$result[$position] .= $html;
					}

					// if nothing specified then echo on default position
					if (is_numeric($position)) {
						$result['default'] .= $html;
					}
				}

				continue;
			}
			// no position mentioned, display it on default
			$result['default'] .= $$pluginResult;
		}

		return $result;
	}

	/**
	 * Main method to output the contents
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$format = $this->input->get('format', 'html', 'word');

		if ($format == 'embed') {
			header('Content-type: text/html; charset=utf-8');
			echo $this->theme->output($tpl);
			exit;
		}

		if ($format == 'oembed') {
			header('Content-type: application/json; UTF-8');
			echo $this->theme->toJSON();
			exit;
		}

		if ($format == 'json') {
			header('Content-type: application/json; UTF-8');
			echo $this->theme->toJSON();
			exit;
		}

		/**
		 * For 'raw' types of output, we need to exit it after that
		 * as we do not want to process anything apart from our codes only.
		 */
		if ($format == 'raw') {
			echo $this->theme->output($tpl);
			return;
		}

		if ($format == 'ajax') {
			return $this->theme->output($tpl);
		}

		if ($format == 'html') {
			echo $this->theme->output($tpl);
			return;
		}

		return parent::display($tpl);
	}

	/**
	 * Calls a specific method from the view.
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function call($method)
	{
		if (!method_exists($this, $method)) {
			return false;
		}

		// Get a list of arguments since we do not know
		// how many arguments are passed in here.
		$args = func_get_args();

		// Remove the first argument since the first argument is the method.
		array_shift($args);

		// Before calling the view's method, we try to detect if there are any error messages in the queue
		if ($this->hasErrors() && $this->doc->getType() == 'ajax') {
			return $this->ajax->reject($this->getMessage());
		}

		// Set the info message here if needed
		if ($this->doc->getType() == 'html') {
			$message = $this->getMessage();

			$this->info->set($message);
		}

		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * Simulates the template variable assignment
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function set($key, $value = null)
	{
		return $this->theme->set($key, $value);
	}

	/**
	 * Simulates the template variable fetch
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function get($key, $default = null)
	{
		return $this->theme->get($key, $default);
	}

	/**
	 * Allows caller to get the id by specifying the key counterpart
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getKey($key)
	{
		return PP::getIdFromInput($key);
	}

	/**
	 * Simulates the template variable assignment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name)) {
			$r = null;
			if (!preg_match('/View(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "PPView::getName() : Can't get or parse class name.");
			}
			$name = strtolower( $r[1] );
		}

		return $name;
	}

	/**
	 * Allows caller to invoke an exception
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exception($message)
	{
		$message = JText::_($message);

		if ($this->doc->getType() == 'ajax') {
			return $this->ajax->reject($message);
		}

		// Handle standard error messages here
		throw new Exception($message);

		return;
	}

	/**
	 * Rejects an xhr request by sending a reject response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reject()
	{
		if (!isset($this->ajax) || !$this->ajax) {
			return false;
		}

		return call_user_func_array(array($this->ajax, __FUNCTION__), func_get_args());
	}

	/**
	 * Resolves an xhr request by sending the contents back
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function resolve()
	{
		if (!isset($this->ajax) || !$this->ajax) {
			return false;
		}

		return call_user_func_array(array($this->ajax, __FUNCTION__), func_get_args());
	}

	/**
	 * Redirects to a specific view
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectToView($view, $layout = '')
	{
		$link = 'index.php?option=com_payplans&view=' . $view;

		if ($layout) {
			$link .= '&layout=' . $layout;
		}

		$url = JRoute::_($link, false);

		return PP::redirect($url);
	}

	/**
	 * Allows caller to set some message to the info queue
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function setMessage($message, $messageType = PP_MSG_SUCCESS)
	{
		// Accepts PPException instance
		// if ($message instanceof PPException) {
		// 	$messageType = $message->type;
		// 	$message = $message->message;
		// }

		$obj = new stdClass();
		$obj->message = JText::_($message);
		$obj->type = $messageType;

		if ($obj->type == PP_MSG_ERROR) {
			$this->errors = true;
		}

		$format = $this->input->get('format', 'html', 'cmd');

		if ($format == 'ajax') {
			$this->ajax->notify($obj->message, $obj->type);
		}

		$this->message = $obj;

		return true;
	}

	/**
	 * Returns the message queue.
	 *
	 * @since	3.7
	 * @access	public
	 */
	public function getMessage()
	{
		if (!$this->message) {
			return false;
		}

		$message = $this->message;

		// After getting the message, we need to empty this to prevent duplicate messages
		$this->message = null;

		return $message;
	}

}
