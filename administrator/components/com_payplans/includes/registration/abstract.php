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

class PPRegistrationAbstract extends PayPlans
{
	private $templateVars = array();
	private $sessionKey = null;
	protected $session = null;

	public function __construct()
	{
		parent::__construct();

		$this->session = PP::session();
	}

	/**
	 * Called by view=register to allow adapters to run it's queries before redirecting
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function beforeStartRedirection()
	{
	}

	/**
	 * Provides assistance to the payment app to output contents.
	 * This is similar to the display method in views
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($namespace)
	{
		$this->processData();

		$namespace = 'site/registration/' . $this->type . '/' . $namespace;

		$theme = PP::themes();
		$theme->setVars($this->templateVars);

		return $theme->output($namespace);
	}

	/**
	 * Retrieves the com_user's global configuration
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function getJoomlaUsersParams()
	{
		static $params = null;

		if (is_null($params)) {
			$params = JComponentHelper::getParams('com_users');
		}

		return $params;
	}

	/**
	 * Retrieves the invoice id from the query or session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	final protected function getInvoiceId()
	{
		$key = $this->input->get('invoice_key', 0, 'default');

		if (!$key) {
			$key = $this->session->get('PP_INVOICE_KEY', 0);
		}

		if (!$key) {
			return false;
		}

		$id = PP::getIdFromKey($key);
		
		return $id;
	}

	/**
	 * Retrieves the plan id from the query or session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	final protected function getPlanId()
	{
		$id = $this->input->get('plan_id', 0, 'int');

		if ($id) {
			return $id;
		}

		// Get the plan id from the session
		$id = (int) $this->session->get('REGISTRATION_PLAN_ID', 0);

		return $id;
	}

	/**
	 * Retrieves the plan id from the query or session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	final protected function getRegistrationUrl(PPInvoice $invoice)
	{
		$plan = $invoice->getPlan();
		$url = PPR::_('index.php?option=com_payplans&view=register&plan_id=' . $plan->getId() . '&invoice_key=' . $invoice->getKey());

		return $url;
	}

	/**
	 * Retrieves the user id from the session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function getUserId()
	{
		return (int) $this->session->get('REGISTRATION_USER_ID', 0);
	}

	/**
	 * Retrieves the user id from the session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getNewUserId()
	{
		return (int) $this->session->get('REGISTRATION_NEW_USER_ID');
	}

	/**
	 * Implemented by child
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationUrl()
	{
		// Implemented by child
	}

	/**
	 * Implemented by child
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isOnRegistrationCompletePage()
	{
		// Implemented by child
	}

	/**
	 * Determines if this is a new user from the session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function isNewUser()
	{
		return $this->session->get('REGISTRATION_NEW_USER', false);	
	}

	/**
	 * Determines if the user has completed their registration
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRegistrationCompleted()
	{
		$userId = $this->getUserId();
		$complete = $this->isOnRegistrationCompletePage();

		if ($userId && $complete) {
			return true;
		}
		
		return false;
	}

	/**
	 * Triggered by registration plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterDispatch()
	{
	}

	/**
	 * This is being called by the system plugin to perform checks on:
	 *
	 * - Registration without plan
	 * - Should Start Registration and complete registration
	 * 
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterRoute()
	{
		// this should be checked all time
		$planId = $this->getPlanId();
		$isRegistrationUrl = $this->isRegistrationUrl();

		// We should check if this plugin is enabled before redirecting user to plan page
		$isPluginEnabled = JPluginHelper::isEnabled('payplans', 'registration');

		// clear session for debugging
		// $session = JFactory::getSession();
		// $session->destroy();

		// if its a direct registration without plan id
		if ($isRegistrationUrl && !$planId && $isPluginEnabled) {
			$redirect = PPR::_('index.php?option=com_payplans&view=plan');

			return PP::redirect($redirect);
		}

		if ($this->isRegistrationCompleted()) {

			// If plan is not selected then do not create order. Force user to register
			if (!$planId) {
				$this->reset();
				return;
			}

			$userId = $this->getUserId();

			$invoice = PP::invoice($this->getInvoiceId());
			$invoice->user_id = $userId;
			$invoice->save();

			$order = $invoice->getOrder();
			$order->buyer_id = $userId;
			$order->save();

			$subscription = $order->getSubscription();
			$subscription->user_id = $userId;
			$subscription->save();

			// Set the new user id on the session
			$this->setNewUserId($userId);

			// Reset the user and plan id
			$this->reset();
			
			return $this->redirectToCheckout();
		}

		return true;
	}

	/**
	 * If new user user is registered then set user id in session for the registration 
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onBeforeStoreUser($user, $isnew)
	{
		if ($isnew) {
			$this->session->set('REGISTRATION_NEW_USER', true);
		}

		return true;
	}

	/**
	 * Whenever a new user account is created on the site, the respective plugins would trigger this
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		if ($this->isNewUser()) {
			$this->setUserId($user['id']);

			$this->setNewUser();
		}

		return true;
	}

	/**
	 * Triggered by registration plugin
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansAccessCheck()
	{
		// Implemented by child
	}

	/**
	 * Set the session key
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setSessionKey($key)
	{
		$this->sessionKey = $key;
	}

	/**
	 * Set the data stored from the previous session
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function processData()
	{
		$default = array(
			'register_name' => '',
			'register_username' => '',
			'register_email' => '',
			'address' => '',
			'city' => '',
			'state' => '',
			'zip' => '',
			'country' => ''
		);

		// Get data from previous session
		$data = $this->session->get($this->sessionKey, '', 'payplans', true);

		if ($data) {
			$mapping = array('name', 'username', 'email', 'password', 'password2');

			foreach ($mapping as $key) {
				if (isset($data[$key])) {

					// We do not want to display the password back to the form
					if ($key == 'password' || $key == 'password2') {
						unset($data[$key]);
						continue;
					}

					$newKey = 'register_' . $key;
					$data[$newKey] = $data[$key];

					unset($data[$key]);
				}
			}
		} else {
			$data = $default;
		}

		$this->set('data', $data);
	}

	/**
	 * Provides assistance to the payment app to set variables which can be extracted with the @display method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function set($key, $value)
	{
		$this->templateVars[$key] = $value;
	}

	/**
	 * Provides assistance to the payment app to set variables which can be extracted with the @display method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setNewUser($isNew = false)
	{
		$this->session->set('REGISTRATION_NEW_USER', false);
	}
	/**
	 * Provides assistance to the payment app to set variables which can be extracted with the @display method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setNewUserId($id)
	{
		$this->session->set('REGISTRATION_NEW_USER_ID', $id);
	}

	/**
	 * Sets the user id after their registration is completed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setUserId($userId)
	{
		$this->session->set('REGISTRATION_USER_ID', $userId);
	}

	/**
	 * Sets the invoice key in session so that we can access it later by adapters
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setInvoiceKey($key)
	{
		$this->session->set('PP_INVOICE_KEY', $key);
	}

	/**
	 * Sets the plan id into the session so that it can be tracked later
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setPlanId($planId)
	{
		$this->session->set('REGISTRATION_PLAN_ID', $planId);
		
		return true;
	}

	/**
	 * Resets the session data
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reset()
	{
		$this->session->set('REGISTRATION_USER_ID', 0);
		$this->session->set('REGISTRATION_NEW_USER', false);
		$this->session->set('REGISTRATION_PLAN_ID', 0);
	}

	/**
	 * Redirects the request to the respective registration page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirect()
	{
		$this->app->redirect($this->url);
		return $this->app->close();
	}

	/**
	 * Redirects user to the checkout page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectToCheckout()
	{
		$id = $this->getInvoiceId();
		$invoice = PP::invoice($id);
		$invoiceKey = $invoice->getKey();

		// Directly go to thanks page for free invoice
		if ($this->config->get('skip_free_invoices') && $invoice->isFree()) {

			$redirect = PPR::_('index.php?option=com_payplans&task=checkout.confirm&invoice_key=' . $invoiceKey . '&app_id=0', false);
			return $this->app->redirect($redirect);
		}

		$redirect = PPR::_('index.php?option=com_payplans&view=checkout&invoice_key=' . $invoiceKey . '&tmpl=component', false);

		$this->app->redirect($redirect);
		return $this->app->close();
	}

	/**
	 * Redirects user to the plan page
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function redirectToPlans()
	{
		$redirect = PPR::_('index.php?option=com_payplans&view=plan', false);

		$this->app->redirect($redirect);
		return $this->app->close();
	}


	/**
	 * Determines if the registration requires activation
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function requireActivation()
	{
		$usersConfig = $this->getJoomlaUsersParams();
		$registrationType = $usersConfig->get('useractivation');

		if ($registrationType == 1) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the registration requires admin for approval
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function requireAdminActivation()
	{
		$usersConfig = $this->getJoomlaUsersParams();
		$registrationType = $usersConfig->get('useractivation');

		if ($registrationType == 2) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the registration requires activation
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function requireUserActivation()
	{
		$usersConfig = $this->getJoomlaUsersParams();
		$registrationType = $usersConfig->get('useractivation');

		if ($registrationType == 1) {
			return true;
		}

		return false;
	}
}