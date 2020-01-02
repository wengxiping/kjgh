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

class PPRegistrationEasySocial extends PPRegistrationAbstract
{
	public $type = 'easysocial';
	public $url = null;

	public function __construct()
	{
		parent::__construct();

		if (!$this->exists()) {
			return;
		}

		$this->url = ESR::registration(array('layout' => 'steps', 'step' => 1), false);
	}

	/**
	 * Determines if Easysocial exists
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function exists()
	{ 
		if (!PP::easysocial()->exists()) {
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
		if (!$this->exists()) {
			return;
		}
		
		$url = $this->getRegistrationUrl($invoice);
		$userId = $this->getNewUserId();

		// handle for the social login because social login process didn't go through the register view so unable to set that plan id
		// so we have to manually set the current user subscribe to which plan id
		$plan = $invoice->getPlan();
		$planId = $plan->getId();

		// set the plan id session during subscribe
		if ($planId) {
			$this->setPlanId($planId);
		}

		$sso = ES::sso();
		$this->set('url', $url);
		$this->set('sso', $sso);
		$this->set('userId', $userId);
		
		$output = $this->display('default');

		return $output;
	}

	/**
	 * Determines if the current url is a registration complete page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isOnRegistrationCompletePage()
	{
		return true;
	}

	/**
	 * Determines if the current url is a registration page
	 * If the registration page doesn't have detect any plan id then redirect to the plan listing page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationUrl()
	{
		if (!$this->exists()) {
			return false;
		}

		// Set the session if current action is social login from Easysocial
		$this->isOauthRegistration();

		// Determine whether this is Easysocial registration page
		$isRegistrationPage = $this->isRegistrationPage();

		if ($isRegistrationPage) {
			return true;
		}

		// Set the session if current action is trying to submit the registration form
		$this->isRegistrationSaveStep();


		return false;
	}

	/**
	 * Determines if the current action is oauth registration
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function isOauthRegistration()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// Set the session here if user register via social network
		if ($option == 'com_easysocial' && ($view == 'registration' && $layout == 'oauthDialog') || 
			($controller == 'registration' && ($task == 'oauthLinkAccount' || $task == 'oauthSignup'))) {

			$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 1);
			$this->session->set('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN', 1);
			$this->session->set('PP_EASYSOCIAL_REGISTRATION_OAUTH_REGISTRATION', 1);
		}

		// Check for jfbconnect integration
		if ($option == 'com_jfbconnect' && $task == 'authenticate.login') {
			$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 1);
			$this->session->set('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN', 1);
			$this->session->set('PP_EASYSOCIAL_REGISTRATION_JFBC', 1);
		}
	}

	/**
	 * Determines if the current action is oauth registration
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function isSelectingProfileType()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$task = $this->input->get('task', '', 'default');

		if ($option == 'com_easysocial' && $controller == 'registration' && $task == 'selectType' && $this->session->get('PP_EASYSOCIAL_REGISTRATION_JFBC') == 1) {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether current user stop to proceed registration or not
	 *
	 * @since	4.0.11
	 * @access	public
	 */
	public function isStopProceedRegistrationProcess()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$task = $this->input->get('task', '', 'default');
		$profile_id = $this->input->get('profile_id', '', 'default');

		// Redirect user to plan listing page if user decide to click switch profile type or registration link on the page.
		if ($option == 'com_easysocial' 
			&& $view == 'registration' 
			&& !$task 
			&& !$controller 
			&& !$layout 
			&& (!is_null($this->session->get('PP_EASYSOCIAL_REGISTRATION')) || !is_null($this->session->get('REGISTRATION_PLAN_ID'))) 
			&& (($profile_id == 'browse' || !empty($profile_id)) || (!$profile_id))
		) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current action is Easysocial registration page
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function isRegistrationPage()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');

		// redirect to the plan listing page if detected someone trying to view it
		// Unless there got any plan selected before
		if ($option == 'com_easysocial' && $view == 'registration' && !$layout && !$controller) {
			return true;
		}
	}

	/**
	 * Determines if the current action is trying to submit the registration form
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function isRegistrationSaveStep()
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// While using Auto login then set session variable
		// If user site has create registration menu for Easysocial then that view will become registration
		if ($option == 'com_easysocial' && ($view == 'dashboard' || $view == 'registration') && $controller == 'registration' && $task == 'saveStep') {

			$session = JFactory::getSession();
			$registration = ES::table('Registration');
			$state = $registration->load($session->getId());

			$profile = ES::table('Profile');
			$profile->load($registration->get('profile_id'));

			// Get the sequence
			$sequence = $profile->getSequenceFromIndex($registration->get('step'), SOCIAL_PROFILES_VIEW_REGISTRATION);

			// set this session if the sequence always got 1 even there registration form only got 1 step
			// For now this session only handle for the auto login registration type
			if ($sequence) {
				$this->session->set('PP_EASYSOCIAL_REGISTRATION_HAS_STEP', 1);
			}

			// Load the current step.
			$step = ES::table('FieldStep');
			$step->loadBySequence($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES, $sequence);
		
			$completed = $step->isFinalStep(SOCIAL_PROFILES_VIEW_REGISTRATION);

			// set the session if that is final step and this profile registration type is autologin
			if ($completed && $profile->getRegistrationType() == 'auto') {
				$this->session->set('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN', 1);
			}
		}
	}

	/**
	 * Set necessary parameters before redirecting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function beforeStartRedirection()
	{
		$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 1);
	}

	/**
	 * Check if user tries to bypass payplans plan page and register via EasySocial
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
		if ($this->my->id && $this->session->get('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN') != 1) {
			return true;
		}

		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$step = $this->input->get('step', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// Determine this registration from Payplans area
		$registerFromPayplans = $this->session->get('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 0);

		if ($registerFromPayplans) {
			$this->session->clear('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS');
		}

		// Determine this oauth registration from Easysocial area
		$oauthRegisterFromEasysocial = $this->session->get('PP_EASYSOCIAL_OAUTH_REGISTRATION', 0);

		if ($oauthRegisterFromEasysocial) {
			$this->session->clear('PP_EASYSOCIAL_OAUTH_REGISTRATION');
		}

		if ($option == 'com_easysocial') {

			if ($this->isSelectingProfileType()) {
				return true;
			}

			// Registration page
			if ($this->checkRegistrationAccess($registerFromPayplans, $oauthRegisterFromEasysocial)) {
				return true;
			}

			// Dashboard page
			if ($this->checkDashboardAccess($registerFromPayplans, $oauthRegisterFromEasysocial)) {
				return true;
			}
		}

		$this->processLoginRedirection();

		// Do not leave any session here after the payment is done.
		if ($option == 'com_payplans' && $view == 'thanks') {
			$this->clearSession();
		}

		// Redirect user to plan listing page if user decide to click switch profile type or registration link on the page.
		if ($this->isStopProceedRegistrationProcess()) {

			// Clear all the session because everything need to start over again if start on the plan listing page.
			$this->clearSession();

			return $this->redirectToPlans();
		}

		return true;
	}

	/**
	 * Branch method to process registration access
	 *
	 * @since	3.1.0
	 * @access	private
	 */
	private function checkRegistrationAccess($registerFromPayplans, $oauthRegisterFromEasysocial)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$step = $this->input->get('step', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// Registration page
		if ($view == 'registration') {

			if (!$task && $this->session->get('PP_EASYSOCIAL_REGISTRATION_JFBC') == 1) {
				$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 1);
				$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_JFBC', 1);
				return true;
			}

			// User is trying to activate their registration
			if ($task == 'activate') {
				return true;
			}

			// Ensure that the oauth registration will always contain plan id
			if ($layout == 'oauth' && !$this->getPlanId()) {
				return $this->redirectToPlans();
			}

			// Set proper session when registering from payplans
			if ($registerFromPayplans) {
				$this->session->set('PP_EASYSOCIAL_REGISTRATION', 1);
			}

			// App integration
			// Prevent if the site setup Facebook Automatically Link & Create Account, because it will go inside this checking.
			if ($layout === '' && $step === '' && !$this->input->get('profileId') && ($controller == 'registration' && $task != 'oauthLinkAccount')) {
				$app = PPHelperApp::getAvailableApps('easysocialprofiletype');

				if ($app) {
					return $this->redirectToPlans();
				}
			}

			if ($layout == 'steps') {

				// Check for step 1
				if ($step == 1) {
					if ($this->session->get('PP_EASYSOCIAL_REGISTRATION') == 1) {
						$this->session->clear('PP_EASYSOCIAL_REGISTRATION', 1);
					}

					if (!$registerFromPayplans && $this->session->get('PP_EASYSOCIAL_REGISTRATION') != 1) {
						return $this->redirectToPlans();
					}
				}

				// skip this if the current registration workflow more than 1 step
				if ($step > 1) {
					return true;
				}
			}

			if (!$registerFromPayplans && $layout === 'BLANK' && $controller === 'BLANK') {
				return $this->redirectToPlans();
			}

			// Set profile_id according to plan during user registration according to plan
			if ($registerFromPayplans && $this->session->get('PP_EASYSOCIAL_PROFILE') == 1 && $this->session->get('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN', 0) != 1) {
				$this->session->clear('PP_EASYSOCIAL_PROFILE');

				// Simulate EasySocial to pick a profile type
				$session = JFactory::getSession();
				$profileId = $this->session->get('profile_id', '', SOCIAL_SESSION_NAMESPACE);
				$session->set('profile_id', $profileId, SOCIAL_SESSION_NAMESPACE);

				// Try to load more information about the current registration procedure.
				$registration = ES::table('Registration');
				$registration->set('session_id', $session->getId());
				$registration->profile_id = $profileId;

				// When user accesses this page, the following will be the first page
				$registration->set('step', 1);

				// Add the first step into the accessible list.
				$registration->addStepAccess(1);
				$registration->store();
				$this->session->set('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS', 1);

				return $this->redirect();
			}

			// just in case if the user already automatically login on the site after registration then redirect to the registration view
			// then we need to capture it and redirect it to checkout directly since this subscription haven't done yet.
			// This only happen if the site set his registration menu to guest access
			if ($this->my->id && !$task && (!$layout || $layout == 'completed') && !$controller && !$step && $this->session->get('PP_EASYSOCIAL_REGISTRATION') == 1 && $this->session->get('REGISTRATION_PLAN_ID')) {
				return $this->redirectToCheckout();
			}
		}

		return false;
	}

	/**
	 * Branch method to process dashboard access
	 *
	 * @since	3.1.0
	 * @access	private
	 */
	private function checkDashboardAccess($registerFromPayplans, $oauthRegisterFromEasysocial)
	{
		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$step = $this->input->get('step', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// Dashboard page
		if ($view == 'dashboard') {

			// Skip this if the registration type is auto and the registration workflow has more than 1 step
			if ($controller == 'registration' && $task == 'saveStep' && $this->session->get('PP_EASYSOCIAL_REGISTRATION_HAS_STEP') == 1 && $this->session->get('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN') == 1) {
				return true;
			}

			// Skip this if the Oauth registration processing link back their existing registered user account 
			if ($controller == 'registration' && $task == 'oauthLinkAccount') {
				return true;
			}

			// Need to avoid this if user enter wrong username/password under auth client page (ask for link or create account)
			// Because it will redirect to Easysocial user dashboard first before redirect back to the auth client page
			if ($this->session->get('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN') == 1 && $task != 'oauthLinkAccount') {

				// Avoid social login come in this process if they already registered on the site.
				if (!$this->getPlanId()) {
					return true;
				}

				// Redirect only if it's coming from payplans registration.
				if ($this->getInvoiceId()) {

					$this->session->clear('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN');

					// if detected there got a session for this, we should skip this
					// because if it reached here mean that checkout process already done.
					if ($oauthRegisterFromEasysocial) {
						$this->session->clear('PP_EASYSOCIAL_OAUTH_REGISTRATION');
						return true;
					}

					// Check for jfbconnect integration
					if ($this->session->get('PP_EASYSOCIAL_REGISTRATION_FROM_JFBC') == 1) {
						$this->session->set('PP_EASYSOCIAL_REGISTRATION_SUCCESS_JFBC', 1);
						return true;
					}

					return $this->redirectToCheckout();
				} else {
					// if it reached here mean user already logged in on the site
					// check if user require a subscription or not.
					$user = PP::user();
					$userPlans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);
					$userPlanIds = array();

					if ($userPlans) {
						foreach ($userPlans as $userPlan) {
							$userPlanIds[] = $userPlan->getId();
						}
					}

					// If the current new user doesn't have any plan, redirect to the plan listing page
					if (!$userPlanIds) {

						// This session use to determine that registration via social network
						$this->session->set('PP_EASYSOCIAL_OAUTH_REGISTRATION', 1);
						return $this->redirectToPlans();
					}
				}
			}
		}

		return false;
	}

	/**
	 * Branch method to process login redirection
	 *
	 * @since	3.1.0
	 * @access	private
	 */
	private function processLoginRedirection()
	{
		// skip this if the current process is not during registration
		if ($this->session->get('PP_EASYSOCIAL_REGISTRATION') != 1 && !$this->session->get('REGISTRATION_PLAN_ID')) {
			return true;
		}

		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$controller = $this->input->get('controller', '', 'default');
		$step = $this->input->get('step', '', 'default');
		$task = $this->input->get('task', '', 'default');

		// Retrieve redirection upon registration menu item id
		$redirectionUponRegistrationMenuItemId = $this->getRedirectionUponRegistrationMenuItem();

		// Determine whether the current page is registration auth layout page
		$isOauthRegistrationLayoutPage = $this->isOauthRegistrationLayoutPage();

		// Handle for if the admin set the login redirection from Easysocial and the registration type set to auto login
		// Need to redirect back to the invoice confirmation page 
		// Skip this if the current page is registration auth layout page
		if ($this->session->get('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN') == 1 && ($redirectionUponRegistrationMenuItemId && $redirectionUponRegistrationMenuItemId != 'null') && !$isOauthRegistrationLayoutPage) {

			// Retrieve the current page menu item id
			$currentPageMenuItemId = $this->input->get('Itemid', 0, 'int');

			// Determine the current request is ajax call or not
			// Need to skip this if system perform an ajax call during registration call
			// e.g. /index.php?option=com_easysocial&lang=&Itemid=154&_ts=1561695777557
			$isAjaxCall = $this->input->get('_ts', '', 'default');

			// If the redirection upon registration is not set to default behaviour
			// It should redirect back to the invoice confirm page if detected the current redirect page match with the setting. 
			if (empty($isAjaxCall) && $redirectionUponRegistrationMenuItemId && ($redirectionUponRegistrationMenuItemId == $currentPageMenuItemId)) {
				return $this->redirectToCheckout();
			}
		}
	}

	/**
	 * Clear all the related session
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function clearSession()
	{
		$this->session->clear('PP_EASYSOCIAL_OAUTH_REGISTRATION_FROM_EASYSOCIAL_WITHOUT_CHOOSE_PLAN');
		$this->session->clear('PP_EASYSOCIAL_OAUTH_REGISTRATION');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION_FROM_PAYPLANS');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION_TYPE_AUTOLOGIN');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION_HAS_STEP');
		$this->session->clear('REGISTRATION_PLAN_ID');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION_JFBC');
		$this->session->clear('PP_EASYSOCIAL_REGISTRATION_OAUTH_REGISTRATION');
	}

	/**
	 * Retrieve redirection upon registration menu item id
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getRedirectionUponRegistrationMenuItem()
	{
		// Retrieve the profile type id during registration process
		$profileId = $this->session->get('profile_id', '', SOCIAL_SESSION_NAMESPACE);
		$config = ES::config();

		// Load the profile object.
		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Retrieve this profile type params
		$params = $profile->getParams();

		// Retrieve redirection upon registration for auto login registration type
		$redirectionUponRegistrationMenuItemId = $params->get('registration_success');

		// if the profile type redirection upon registation set to default behaviour
		// It should respect on the global login redirection setting
		
		// This PP_EASYSOCIAL_REGISTRATION_OAUTH_REGISTRATION session use to determine that this user register via social network
		// If there is Oauth Social Registration then only retrieve the login redirection from global login redirection setting
		if ($redirectionUponRegistrationMenuItemId == 'null' || !$redirectionUponRegistrationMenuItemId || $this->session->get('PP_EASYSOCIAL_REGISTRATION_OAUTH_REGISTRATION') == 1) {
			$redirectionUponRegistrationMenuItemId = $config->get('general.site.login');
		}

		return $redirectionUponRegistrationMenuItemId;
	}

	/**
	 * Determine whether this is registration auth layout page 
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function isOauthRegistrationLayoutPage()
	{
		$view = $this->input->get('view', '', 'default');
		$layout = $this->input->get('layout', '', 'default');
		$component = $this->input->get('option', '', 'default');
		$oauth = array('oauth', 'oauthPreferences', 'oauthCreateAccount', 'oauthRequestToken', 'oauthDialog');

		if ($component == 'com_easysocial' && $view == 'registration' && in_array($layout, $oauth)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the registration requires activation
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function requireActivation()
	{
		// Retrieve the current subscribed user profile type id
		$profileId = $this->session->get('profile_id', '', SOCIAL_SESSION_NAMESPACE);

		$profile = ES::table('Profile');
		$state = $profile->load($profileId);

		if ($state) {

			// Retrieve what registration type currently set to
			$registrationType = $profile->getRegistrationType();

			// If that is require user to activation
			if ($registrationType == 'verify') {
			 	return true;
			}
		}

		return false;
	}

	/**
	 * Determines if the registration requires admin for approval
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function requireAdminActivation()
	{
		// Retrieve the current subscribed user profile type id
		$profileId = $this->session->get('profile_id', '', SOCIAL_SESSION_NAMESPACE);

		$profile = ES::table('Profile');
		$state = $profile->load($profileId);

		if ($state) {

			// Retrieve what registration type currently set to
			$registrationType = $profile->getRegistrationType();

			// If that is require admin for approval
			if ($registrationType == 'approvals') {
			 	return true;
			}
		}

		return false;
	}	
}