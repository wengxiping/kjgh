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

class PlgUserPayplans extends PPPlugins
{
	/**
	 * Redirect user upon logging in
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onUserLogin($data, $options)
	{
		if (!$this->config->get('users_login_redirection')) {
			return;
		}

		$id = JUserHelper::getUserId($data['username']);
		$user = PP::user($id);

		// Admin then nothing to do.
		if ($user->isAdmin()) {
			return true;
		}
		
		// Get plans of current User 
		$plans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);

		// Set Return Url for Non-Subscriber
		if (!$plans) {
			$this->app->setUserState('users.login.form.return', $this->config->get('users_nonsubscribers_redirect'));
			return true;
		}
		
		// Set Return Url for Subscriber
		$this->app->setUserState('users.login.form.return', $this->config->get('users_subscribers_redirect'));
		return true;
	}

	/**
	 * Upon new registration, check if the user is associated with any profile type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		// Create a new record for PayPlans
		$table = PP::table('User');
		$table->load(array('user_id' => $user['id']));
		$table->user_id = $user['id'];
		$table->store();

		$this->processUserAssignment($user, $isnew);

		return true;
	}


	/**
	 * Used to check if user is doing activation or not.
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{

		// for debug:
		// update jos_users set block = 1, activation = '76c614277bb997a9b0ff663db759fd10' where id = 513;

		if (!$isnew) {

			$app = JFactory::getApplication();
			$input = $app->input;

			$option = $input->get('option', '', 'cmd');
			$task = $input->get('task', '', 'cmd');

			if ($option == 'com_users' && $task == 'activate') {

				$config = PP::config();
				$requireRedirctionUrl = $config->get('activation_redirect_url', '');

				// make sure user really perform activation.
				if ($requireRedirctionUrl && $user['id'] && isset($user['activation']) && $user['activation'] && isset($data['activation']) && $data['activation'] == '') {
					// lets add cookies for later process in onUserAfterSave the activation redirection. 
					PP::session()->set('PP_ACTIVATION_REDIRECTION', $user['id']);
				}
			}
		}

		return true;
	}


	/**
	 * Process assigning plan to user
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function processUserAssignment($user, $isnew)
	{
		if (!$this->config->get('profileplan_enabled') || !$isnew) {
			return true;
		}

		// Don't do anything if this come fromdummy user
		if ($user['name'] == 'Not_Registered') {
			return true;
		}

		$userProfile = $this->getUserProfile($user);

		if (!$userProfile) {
			return true;
		}

		$plans = $this->getPlans($userProfile);

		if (!$plans) {
			return true;
		}

		$this->assignPlans($plans, $user);

		return true;
	}

	/**
	 * Assigning plan to user
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function assignPlans($planIds, $user)
	{
		$user = PP::user($user['id']);

		foreach ($planIds as $planId) {
			// Load the plan
			$plan = PP::plan((int) $planId);

			$order = $plan->subscribe($user->getId(), true);

			// Create an invoice for the order
			$invoice = $order->createInvoice($order->getSubscription());

			// Apply 100% discount
			$modifier = PP::modifier();

			$modifierData = array(
				'message' => 'COM_PAYPLANS_APPLY_PLAN_ON_USER_MESSAGE',
				'invoice_id' => $invoice->getId(),
				'user_id' => 'apply_plan',
				'amount' => -100,
				'percentage' => true,
				'frequency' => PP_MODIFIER_FREQUENCY_ONE_TIME,
				'serial' => PP_MODIFIER_FIXED_DISCOUNT
			);
			
			$modifier->save();

			$invoice->refresh();
			$invoice->save();

			// Create a transaction with 0 amount since the plan is applied by the admin
			$transaction = PP::transaction();
			$transaction->user_id = $invoice->getBuyer()->getId();
			$transaction->invoice_id = $invoice->getId();
			$transaction->amount = $invoice->getTotal();
			$transaction->message = 'COM_PAYPLANS_TRANSACTION_CREATED_FOR_APPLY_PLAN_TO_USER';
			$transaction->save();
		}
	}

	/**
	 * Get profile for this user
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getUserProfile($user)
	{
		// Get the profile source
		$profileSource = $this->config->get('profile_used');

		// Default joomla_usertype
		$userProfile = array_shift($user['groups']);

		if ($profileSource == 'easysocial_profiletype') {

			// Retrieve the profile type id
			$userProfile = $this->getESProfileTypeId();
		}

		if ($profileSource == 'jomsocial_profiletype') {
			$userProfile = $this->input->get('profileType', 0, 'int');
		}

		return $userProfile;
	}

	/**
	 * Get plans that needs to be assigned
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getESProfileTypeId()
	{
		if (!PP::easysocial()->exists()) {
			return false;
		}

		$config = ES::config();

		$clientType = $this->input->get('client', '', 'default');

		// Oauth registration e.g. Facebook, Twitter and Linkedin
		if ($clientType) {

			// If that registration type set to normal, then retrieve that profile id from the POST request.
			$userProfileTypeId = $this->input->get('profile', 0, 'int');

			// Retrieve social registration type e.g. normal or simplified
			$registrationType = $config->get('oauth.' . $clientType . '.registration.type');

			// If that is simplified process without go through selection profile
			if ($registrationType == 'simplified') {

				// Retrieve default profile type id set it from Easysocial
				$userProfileTypeId = $config->get('oauth.' . $clientType . '.profile');
			}

			return $userProfileTypeId;
		}

		// Normal registration without social signup
		$userProfileTypeId = $this->input->get('profileId', 0, 'int');

		return $userProfileTypeId;
	}

	/**
	 * Get plans that needs to be assigned
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getPlans($userProfile)
	{
		// Get all available apps instance for this
		$model = PP::model('App');
		$apps = $model->getAppInstances(array('type' => 'profilebasedplan', 'published' => 1));

		if (!$apps) {
			return false;
		}

		$plans = array();

		foreach ($apps as $app) {
			$app = PP::app((int) $app->app_id);
			
			// Also need to check the profile source must be the same
			$globalSource = $this->config->get('profile_used');
			$profileSource = $app->getAppParam('source', $globalSource);

			if ($globalSource != $profileSource) {
				continue;
			}
			
			// Get the profile type for each app
			$profile = $app->getAppParam('profile_type', array());
			$profile = $profile[0];

			// if this app does't have the same profile as the user, skip.
			if ($profile != $userProfile) {
				continue;
			}

			// Get plans for each app
			$signUpPlans = $app->getAppParam('signup_plans', array());

			if (isset($plans[$profile])) {
				$plans[$profile] = array_merge($plans[$profile], $signUpPlans);
			} else {
				$plans[$profile] = $signUpPlans;
			}
		}

		if (!isset($plans[$userProfile])) {
			return false;
		}

		return $plans[$userProfile];
	}
}
