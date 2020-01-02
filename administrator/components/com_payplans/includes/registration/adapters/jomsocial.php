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

class PPRegistrationJomsocial extends PPRegistrationAbstract
{
	public $type = 'jomsocial';
	public $url = null;

	protected $file = JPATH_ROOT . '/components/com_community/libraries/core.php';

	public function __construct()
	{
		parent::__construct();

		$this->url = 'index.php?option=com_community&view=register&fromPayplans=1';
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
			PP::info()->set('Jomsocial is not installed on the site. Please update your settings to pick the correct registration integrations', 'error');

			return $this->redirectToCheckout();
		}		 
		
		$this->url = CRoute::_('index.php?option=com_community&view=register', false);
		$this->session->set('fromPayplans', 1);
	}

	/**
	 * Determines if jomsocial exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		$enabled = JComponentHelper::isEnabled('com_community');
		$exists = JFile::exists($this->file);

		if (!$exists || !$enabled) {
			return false;
		}
		
		require_once($this->file);

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
		$task = $this->input->get('task', '', 'default');

		if ($option == 'com_community' && $view == 'register' && !$task) {
			return true;
		}
		
		return false;
	}

	public function isOnRegistrationCompletePage()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');

		if ($option == 'com_community' && $view == 'register' && $task == 'registerSucess') {
			return true;
		}
		return false;
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
		$task = $this->input->get('task', '', 'default');
		$profileType = $this->input->get('profileType', '', 'default');

		$fromPayplans = $this->session->get('fromPayplans', 0);

		if ($option == 'com_community' && $view == 'register' && !$task && !$fromPayplans) {
			return $this->redirectToPlans();
		}
		
		// Task is register when register through menu item
		if ($option == 'com_community' && $view == 'register' && $task == 'register' && !$fromPayplans) {
			return $this->redirectToPlans();
		}

		// Check profiletype selection skipped
		if ($this->config->get('registration_skip_ptype') ) {
			if ($option == 'com_community' && $view == 'register' && $task == 'registerProfileType') {

				$defaultProfileType = $this->config->get('js_default_profiletype', 0);

				$apps = PPHelperApp::getAvailableApps('jsmultiprofile');
				if (!empty($app)) {
					foreach ($apps as $app) {
						
						// if app is applied to selected plan, on active status
						if ( $app->getAppParam('jsmultiprofileOnActive') && ( $app->getAppParam('applyAll', 0) == true || in_array($this->getPlanId(), $app->getPlans())) ) {
							
							// get profiletype from app
							$defaultProfileType = $app->getAppParam('jsmultiprofileOnActive', $defaultProfileType);
						}
					}
				}

				if ($defaultProfileType) {
					require_once(JPATH_ROOT.'/components/com_community/libraries/core.php');
					$redirect = CRoute::_('index.php?option=com_community&view=register&task=registerProfile&profileType='.$defaultProfileType, false);

					$this->app->redirect($redirect);
					return $this->app->close();
				}
			}
		}
		

		// Check when user is a new user for a site
		if ($option == 'com_community' && $this->session->get('PAYPLANS_JS_FB_CONNECT_REG', false) == true) {
			$this->session->clear('PAYPLANS_JS_FB_CONNECT_REG');
			
			// If plan is not set then redirect to subscribe page
			$planId = $this->getPlanId();

			if (!$planId) {
				$this->session->clear('PAYPLANS_JS_FACEBOOK_CONNECT_NEW_USER');
				return $this->redirectToPlans();
			}
			
			return $this->redirectToCheckout();
		}
		
		// Check when user is doing login to the site
		if ($option == 'com_community' && $this->session->get('PAYPLANS_JS_FACEBOOK_CONNECT_LOGIN', false)) {
			$this->session->clear('PAYPLANS_JS_FACEBOOK_CONNECT_LOGIN');
			
			// if plan is not set then redirect to profile page
			$planId = $this->getPlanId();

			if (!$planId && !$this->session->get('PAYPLANS_JS_FACEBOOK_CONNECT_NEW_USER', false)) {
				$this->session->clear('PAYPLANS_JS_FACEBOOK_CONNECT_NEW_USER');
			}

			return true;
		}
	}
}
