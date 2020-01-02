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

PP::import('admin:/includes/limitsubscription/limitsubscription');

class PayplansControllerCheckout extends PayPlansController
{
	/**
	 * Confirms the invoice and proceed to the payment
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirm()
	{
		// ($invoiceid = null, $userid = null, $appid = null)
		$invoiceKey = $this->input->get('invoice_key', '', 'default');
		$invoiceId = (int) PP::encryptor()->decrypt($invoiceKey);
		$invoice = PP::invoice($invoiceId);
		$session = PP::session();

		if (!$invoiceId || !$invoice->getId()) {
			$this->info->set('COM_PAYPLANS_ORDER_PLEASE_SELECT_A_VALID_PLAN');
			return $this->redirectToView('plan');
		}

		// Process payment since the user would need to choose a payment gateway
		$appId = $this->input->get('app_id', 0, 'int');

		// If there is no app id, we can't process this invoice
		if (!$appId && !$invoice->isFree()) {
			$this->info->set('COM_PAYPLANS_ERROR_INVALID_APP_ID');
			return $this->redirectToView('plan');
		}

		// Determines if we should create the user account or log the user in
		$accountType = $this->input->get('account_type', 'login', 'string');
		$registration = PP::registration();

		if ($accountType == 'login' && !$this->my->id) {
			if ($registration->isBuiltIn() || (!$registration->isBuiltIn() && !$registration->getNewUserId())) {

				$username = $this->input->get('login_username', '', 'default');
				$password = $this->input->get('login_password', '', 'default');
				$secretkey = $this->input->get('secretkey', '', 'default');

				$user = PP::user();
				$state = $user->login($username, $password, $secretkey);

				if (!$state) {
					$this->info->set($user->getError(), 'error');
					return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&account_type=login' . '&tmpl=component');
				}

				// Here we assume the user was able to login successfully.
				// We need to refresh the user object to know their details
				$user = PP::user();
				$this->my = $user;
			}
		}

		// There is a possibility that the user is a guest, we need to link the invoice to the user
		$purchaser = $invoice->getBuyer();

		$userId = (int) $this->my->id;

		if ($userId) {
			$user = PP::user();
		}

		// There is a possibility the user registered via joomla, easysocial or other registration plugins.
		if (!$userId) {
			$userId = $session->get('REGISTRATION_NEW_USER_ID', 0);

			if ($userId) {
				$user = PP::user($userId);
			}
		}
		
		// Ensure that the current viewer can really view this invoice
		if (!$userId && !$purchaser->isPlaceholderAccount()) {
			$this->info->set('COM_PP_NOT_ALLOWED_HERE');
			return $this->redirectToView('plan');
		}

		// We need to handle the login or registration here because the user is currently a guest
		if ($purchaser->isPlaceholderAccount() && !$this->my->id && $this->config->get('registrationType') == 'auto') {
			$model = PP::model('User');

			// Get the preferences
			$preferences = $this->input->get('preference', array(), 'array');

			// Get the account credentials
			$account = array(
				'name' => $this->input->get('register_name', '', 'default'),
				'username' => $this->input->get('register_username', '', 'default'),
				'email' => $this->input->get('register_email', '', 'email'),
				'password' => $this->input->get('register_password', '', 'default'),
				'password2' => $this->input->get('register_password2', '', 'default'),
				'address' => $this->input->get('address', '', 'default'),
				'city' => $this->input->get('city', '', 'default'),
				'state' => $this->input->get('state', '', 'default'),
				'country' => $this->input->get('country', 0, 'int'),
				'zip' => $this->input->get('zip', '', 'default')
			);
			
			// Check for username 
			if (!$this->config->get('show_username')) {
				$account['username'] = $account['email'];
			}

			// Check for name
			if (!$this->config->get('show_fullname')) {
				$account['name'] = $account['username'];
			}

			// Validate e-mail
			$isValid = $model->validateEmail($account['email']);

			if (!$isValid) {
				$this->info->set($model->getError(), 'error');

				PP::session()->set('PP_CHECKOUT_REGISTRATION', $account);
				return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component&account_type=register');
			}

			// Validate e-mail
			$isValid = $model->validateUsername($account['username']);

			if (!$isValid) {
				$this->info->set($model->getError(), 'error');

				PP::session()->set('PP_CHECKOUT_REGISTRATION', $account);
				return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component&account_type=register');
			}

			// Ensure that password matches
			if ($this->config->get('show_confirmpassword') && $account['password'] != $account['password2']) {
				$this->info->set('COM_PP_PASSWORD_DOES_NOT_MATCH', 'error');

				PP::session()->set('PP_CHECKOUT_REGISTRATION', $account);
				return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component&account_type=register');
			}

			// Check for recaptcha validation
			if ($this->config->get('show_captcha')) {
				$response = $this->input->get('g-recaptcha-response', '', 'default');
				$ip = @$_SERVER['REMOTE_ADDR'];

				$captcha = PP::captcha();
				$verified = $captcha->verify($ip, $response);

				if ($verified !== true) {
					$this->info->set($verified->message, 'error');

					PP::session()->set('PP_CHECKOUT_REGISTRATION', $account);
					return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component&account_type=register');
				}
			}

			$user = $model->createUser($invoice, $account, $preferences);

			if ($user === false) {
				$this->info->set($model->getError(), 'error');

				PP::session()->set('PP_CHECKOUT_REGISTRATION', $account);
				return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component&account_type=register');
			}

			// Store the new user id as the current user might still not be logged in yet
			$session = PP::session();
			$session->set('REGISTRATION_NEW_USER_ID', $user->getId());
		}

		// Get the plan associated with the invoice
		$plan = $invoice->getPlan();

		// Check to ensure that plan is really valid 
		if (!$plan->getId() || !$plan->isPublished()) {
			$this->info->set('COM_PAYPLANS_ORDER_PLEASE_SELECT_A_VALID_PLAN');
			return $this->redirectToView('plan');
		}

		// Update the invoice to the correct user
		if ($purchaser->isPlaceholderAccount() || $this->my->id) {
			$invoice->updatePurchaser($user);
		}

		// By now, the user should be able to view the invoice
		if (!$invoice->canView($user->getId())) {
			$this->info->set('COM_PP_NOT_ALLOWED_TO_VIEW_INVOICE', 'error');
			return $this->redirectToView('plan');
		}

		// We need to save the billing details
		$preferences = $this->input->get('preference', array(), 'array');
		if ($preferences) {
			$userPreferences = $user->getPreferences();

			foreach ($preferences as $key => $value) {
				$userPreferences->set($key, $value);
			}

			$purposeValue = 0;
			if ($preferences['business_purpose'] == 'personal') {
				$purposeValue = PP_EUVAT_PURPOSE_PERSONAL;
			}

			if ($preferences['business_purpose'] == 'business') {
				$purposeValue = PP_EUVAT_PURPOSE_BUSINESS;
			}

			$userPreferences->set('business_purpose', $purposeValue);
			$user->setPreferences($userPreferences);
			$user->setCountry($preferences['business_country']);
			$user->save();
		}

		// We need to save user params here
		$params = $this->input->get('userparams', array(), 'array');

		if ($params) {
			$userParams = $user->getParams();
			
			foreach ($params as $key => $value) {
				$userParams->set($key, $value);
			}

			$user->params = $userParams->toString();
			$user->save();
		}

		// Determine if user can really subscribe to this plan or not
		if (!PPlimitsubscription::canSubscribe($user, $plan->getId())) {
			$this->info->set('COM_PP_LIMITSUBSCRIPTION_NOT_ALLOW', 'error');
			return $this->redirectToView('plan');
		}

		// check user can not use his own referral code
		if ($this->config->get('discounts_referral')) {
			$state = $invoice->isReferralApplicable();

			if (!$state) {
				$this->info->set('COM_PAYPLANS_APP_REFERRAL_ERROR_CANNOT_USE_OWN_REFERRAL_CODE');
				return $this->redirectToView('checkout', '', 'invoice_key=' . $invoiceKey . '&tmpl=component');
			}
		}

		// Confirm order and create payment
		$invoice->confirm($appId);

		// There will be also subscription params
		$params = $this->input->get('subscriptionparams', array(), 'array');
		$subs = $invoice->getSubscription();

		// We reload the subscription so that we get the latest data
		$subscription = PP::subscription($subs->getId());

		if ($params) {
			$subsParams = $subscription->getParams();

			foreach ($params as $key => $value) {
				$subsParams->set($key, $value);
			}

			$subscription->params = $subsParams->toString();
			$subscription->save();
		}


		// If invoice is considered as free because it was reduced by discount
		// or the plan is free, redirect user to the thank you page
		if ($invoice->isFree()) {
			$subscription->processModeration();

			return $this->redirectToView('thanks', '', 'invoice_key=' . $invoice->getKey() . '&tmpl=component');
		}

		// Redirect user to the payment view now
		$payment = $invoice->getPayment();
		
		return $this->redirectToView('payment', '', 'payment_key=' . $payment->getKey() . '&tmpl=component');
	}
}
