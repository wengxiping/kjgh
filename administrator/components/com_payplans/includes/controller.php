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

class PayPlansControllerMain extends JControllerLegacy
{
	protected $view = null;
	protected $info = null;
	protected $location = null;

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->doc = JFactory::getDocument();
		$this->location = $this->app->isAdmin() ? 'backend' : 'frontend';
		$this->my = JFactory::getUser();
		$this->config = PP::config();
		$this->jconfig = PP::jconfig();
		$this->view = $this->getCurrentView();
		$this->info = PP::info();

		if ($this->doc->getType() == 'ajax') {
			$this->ajax = PP::ajax();
		}

		parent::__construct();
	}

	/**
	 * Checks for token existance
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkToken($method = 'post', $redirect = true)
	{
		return PP::checkToken();
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
	 * Allows caller to get the current view.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCurrentView()
	{
		$className = get_class($this);

		// Remove the EasySocialController portion from it.
		$className = str_ireplace('PayPlansController', '' , $className);
		$backend = $this->location == 'backend' ? true : false;

		// Import the front end's base view
		if (!$backend) {
			PP::import('site:/views/views');
		} else {
			PP::import('admin:/views/views');
		}

		$view = PP::view($className, $backend);

		return $view;
	}

	/**
	 * Allows caller to verify that the user is logged in
	 *
	 * @since	3.7.0
	 * @access	public
	 */
	public function requireLogin()
	{
		return PP::requireLogin();
	}

	/**
	 * Central method to redirect to controller
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectToController($task, $others = '')
	{
		$url = 'index.php?option=com_payplans&task=' . $task;
		
		if ($others) {
			$url .= '&' . $others;
		}

		// $url = JRoute::_($url, false);
		return $this->app->redirect($url);
	}

	/**
	 * Central method to redirect to views
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectToView($view, $layout = '', $others = '')
	{
		$url = 'index.php?option=com_payplans&view=' . $view;

		if ($layout) {
			$url .= '&layout=' . $layout;
		}

		if ($others) {
			$url .= '&' . $others;
		}

		$url = JRoute::_($url, false);
		return $this->app->redirect($url);
	}

	/**
	 * Redirects a given return url if it exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectReturnUrl($key = 'return')
	{
		$returnUrl = $this->input->get($key, '', 'default');

		if ($returnUrl) {
			$returnUrl = base64_decode($returnUrl);
			
			return $this->app->redirect($returnUrl);
		}
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
}
