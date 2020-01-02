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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_payplans/includes/payplans.php';
$exists = JFile::exists($file);

if (!$exists) {
	return;
}

require_once($file);

class plgPayplansRegistration extends PPPlugins
{
	/**
	 * Triggered during Joomla's onAfterRoute trigger
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		if ($this->app->isAdmin() || $this->my->id) {
			return;
		}

		$registration = PP::registration();
		$registration->onAfterRoute();
	}

	/**
	 * Joomla 1.6 compatibility
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if ($this->app->isAdmin()) {
			return;
		}

		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}

	/**
	 * Joomla 1.6 compatibility
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onUserBeforeSave($user, $isnew)
	{
		if ($this->app->isAdmin()) {
			return;
		}

		return $this->onBeforeStoreUser($user, $isnew);
	}

	/**
	 * Trigger registration library
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onBeforeStoreUser($user, $isnew)
	{
		if ($this->app->isAdmin()) {
			return;
		}

		$registration = PP::registration();
		$registration->onBeforeStoreUser($user, $isnew);

		return true;
	}
	
	/**
	 * Some registrations requires onAfterDispatch
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		$registration = PP::registration();
		$registration->onAfterDispatch();
	}

	/**
	 * Triggered when a new user is created. This is to allow us to facilitate user registrations
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		if ($this->app->isAdmin()) {
			return;
		}

		// Process registration systems
		$registration = PP::registration();
		$registration->onAfterStoreUser($user, $isnew, $success, $msg);
	}

	/**
	 * Performs access check
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
		if ($this->app->isAdmin()) {
			return;
		}

		$registration = PP::registration();
		$registration->onPayplansAccessCheck();
	}
}
