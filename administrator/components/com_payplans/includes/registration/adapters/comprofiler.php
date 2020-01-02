<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPRegistrationComprofiler extends PPRegistrationAbstract
{
	public $type = 'comprofiler';
	public $url = null;

	protected $file = JPATH_ROOT . '/components/com_comprofiler/comprofiler.php';

	public function __construct()
	{
		parent::__construct();

		if (!$this->exists()) {
			return;
		}

		include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';

		$this->url = 'index.php?option=com_comprofiler&view=registers&fromPayplans=1';
	}

	/**
	 * Set necessary parameters before redirecting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function beforeStartRedirection()
	{
		if (!$this->exists()) {
			PP::info()->set('Community Builder is not installed on the site. Please update your settings to pick the correct registration integrations', 'error');
			return $this->redirectToCheckout();
		}

		$this->session->set('fromPayplans', 1);
	}

	/**
	 * Determines if community builder exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_comprofiler');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}

		return true;
	}

	/**
	 * Renders the create account portion
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function html(PPInvoice $invoice)
	{
		$url = $this->getRegistrationUrl($invoice);
		$userId = $this->getNewUserId();

		$this->set('userId', $userId);
		$this->set('url', $url);

		$output = $this->display('default');

		return $output;
	}

	/**
	 * Determines if the current url is a registration page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationUrl()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');

		if ($option == 'com_comprofiler' && $view == 'registers') {
			return true;
		}
		
		return false;
	}

	public function isOnRegistrationCompletePage()
	{
		return true;
	}

	/**
	 * Capture when CB completes registration
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterDispatch()
	{
		if ($this->app->isAdmin()) {
			return;
		}

		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');

		if ($option != 'com_comprofiler' && $view != 'saveregisters') {
			return true;
		}

		$userId = $this->getUserId();

		if ($userId) {
			return $this->redirectToCheckout();
		}
	}

	/**
	 * Check if user tries to bypass payplans plan page and register via EasySocial
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$fromPayplans = $this->session->get('fromPayplans', 0);

		if ($option == 'com_comprofiler' && $view == 'registers' && !$fromPayplans) {
			return $this->redirectToPlans();
		}
	}

	/**
	 * Determines if the registration requires activation
	 *
	 * @since	4.0.15
	 * @access	public
	 */
	public function requireActivation()
	{
		// retrieve CB config setting 
		global $ueConfig;

		// registration behavior
		// 0 => refer from the Joomla
		// 1 => refer CB registration behavior
		if (isset($ueConfig['reg_admin_allowcbregistration']) && !$ueConfig['reg_admin_allowcbregistration']) {

			$usersConfig = $this->getJoomlaUsersParams();
			$registrationType = $usersConfig->get('useractivation');

			if ($registrationType == 1) {
				return true;
			}

			return false;
		}

		// it seems like currently I didnt see any setting for activate account during registration
		// but I did see confirmation email setting
		if ($ueConfig['reg_confirmation']) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the registration requires admin for approval
	 *
	 * @since	4.0.15
	 * @access	public
	 */
	public function requireAdminActivation()
	{
		// retrieve CB config setting 
		global $ueConfig;

		// registration behavior
		// 0 => refer from the Joomla
		// 1 => refer CB registration behavior
		if (isset($ueConfig['reg_admin_allowcbregistration']) && !$ueConfig['reg_admin_allowcbregistration']) {

			$usersConfig = $this->getJoomlaUsersParams();
			$registrationType = $usersConfig->get('useractivation');

			if ($registrationType == 2) {
				return true;
			}

			return false;
		}

		// refer CB registration type
		if ($ueConfig['reg_admin_approval']) {
			return true;
		}

		return false;
	}
}

