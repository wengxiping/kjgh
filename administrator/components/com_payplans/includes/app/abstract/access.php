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

require_once(PP_LIB . '/app/app.php');

abstract class PPAppAccess extends PPApp
	implements PayplansIfaceAppAccess
{
	protected $applicableOn = array('self' => false, 'admin'=>false);

	abstract public function getResource();
	abstract public function getResourceOwner();
	abstract public function isViolation();

	/**
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResourceAccessor()
	{
		// mostly logged in user
		return JFactory::getUser()->id;
	}

	/**
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getResourceCount()
	{
		// default 0
		return 0;
	}


	/**
	 * this function should be overide by and app
	 * handling might be in various ways
	 * Ajax reply / html reply / redirect
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function handleViolation()
	{
		// handling might be in various ways
		// Ajax reply / html reply / redirect
	}
}
