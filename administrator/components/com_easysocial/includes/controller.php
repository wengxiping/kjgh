<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerMain extends JControllerLegacy
{
	protected $view = null;
	protected $info = null;
	protected $location = null;

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->doc = JFactory::getDocument();
		$this->location = $this->app->isAdmin() ? 'backend' : 'frontend';
		
		$this->my = ES::user();
		$this->config = ES::config();
		$this->jconfig = ES::jconfig();
		$this->info = ES::info();
		$this->view = $this->getCurrentView();

		if ($this->doc->getType() == 'ajax') {
			$this->ajax = ES::ajax();
		}
		
		parent::__construct();

		// Input needs to be overridden later because the parent controller is already assigning the input variable
		$this->input = ES::request();
	}

	/**
	 * Allows caller to get the current view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCurrentView()
	{
		$className = get_class($this);

		// Remove the EasySocialController portion from it.
		$className = str_ireplace('EasySocialController', '' , $className);
		$backend = $this->location == 'backend' ? true : false;

		// Import the front end's base view
		if (!$backend) {
			ES::import('site:/views/views');
		} else {
			ES::import('admin:/views/views');
		}

		$view = ES::view($className, $backend);

		return $view;
	}

	/**
	 * Allows caller to verify that the user is logged in
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function requireLogin()
	{
		return ES::requireLogin();
	}

	/**
	 * Checks for token existance
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function checkToken($method = 'post', $redirect = true)
	{
		return ES::checkToken();
	}
}