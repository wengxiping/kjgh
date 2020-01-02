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

class PPSession
{
	public $session = null;
	
	public function __construct()
	{
		$this->session = JFactory::getSession();
	}

	/**
	 * Determines if a session key exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function has($key)
	{
		return $this->session->has($key);
	}

	public function set($name, $value, $namespace = 'payplans')
	{
		$this->session->set($name, $value, $namespace);
	}
	
	public function get($name, $default=null, $namespace = 'payplans', $clear = false)
	{
		$data = $this->session->get($name, $default, $namespace);

		if ($clear) {
			$this->clear($name, $namespace);
		}

		return $data;
	}
	
	public function clear($name, $namespace = 'payplans')
	{
		return $this->session->clear($name, $namespace);
	}

	public function getId()
	{
		return $this->session->getId();
	}
}
