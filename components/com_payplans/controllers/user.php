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

class PayPlansControllerUser extends PayPlansController
{
	/**
	 * Simulates the login process for Joomla logins
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function login()
	{
		$planId = $this->input->get('plan_id', 0, 'int');
		$plan = PP::plan($planId);

		if (!$planId || !$plan->getId()) {
			$this->info->set('COM_PAYPLANS_PLAN_PLEASE_SELECT_A_VALID_PLAN', 'error');
			return $this->redirectToView('plan', 'subscribe');
		}

		$username = $this->input->get('pp_username', '', 'default');
		$password = $this->input->get('pp_password', '', 'default');
		
		$credentials = array(
			'username' => $username,
			'password' => $password
		);

		$state = $this->app->login($credentials);

		if ($state === false) {
			return $this->redirectToView('login', '', 'plan_id=' . $plan->getId());
		}
			
		// Ensure that the user is really logged in by now.
		$user = PP::user();

		if (!$user->getId()) {
			return $this->redirectToView('login', '', 'plan_id=' . $plan->getId());	
		}

		return $this->redirectToController('plan.subscribe', 'plan_id=' . $plan->getId());
	}

	/**
	 * Allows users to save their preference and custom details
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		PP::requireLogin();

		if (!$this->config->get('user_edit_preferences') && !$this->config->get('user_edit_customdetails')) {
			$this->info->set('COM_PP_FEATURE_NOT_AVAILABLE', 'error');
			return $this->redirectToView('dashboard');
		}

		$post = $this->input->post->getArray();
		$user = PP::user();

		if ($this->config->get('user_edit_preferences')) {
			$user->name = $this->input->get('name', '', 'default');

			if (!$user->name) {
				$this->info->set('Please provide us with your name', 'error');
				return $this->redirectToView('dashboard', 'preferences');
			}

			$user->email = $this->input->get('email', '', 'email');

			$model = PP::model('User');

			// Validate e-mail address
			$isValid = $model->validateEmail($user->email, $user->getId());

			if (!$isValid) {
				$this->info->set($model->getError(), 'error');
				return $this->redirectToView('dashboard', 'preferences');
			}

			// Validate username
			$username = $this->input->get('username', '', 'default');
			$isValid = $model->validateUsername($username, $user->getId());

			if (!$isValid) {
				$this->info->set($model->getError(), 'error');
				return $this->redirectToView('dashboard', 'preferences');
			}

			$password = $post['password'];
			$password2 = $post['password2'];

			if (utf8_strlen($password) || utf8_strlen($password2)) {
				if ($password != $password2) {
					$this->info->set('COM_PP_PASSWORD_DOES_NOT_MATCH', 'error');
					return $this->redirectToView('dashboard', 'preferences');
				}
			}

			$data = array('password' => $password, 'password2' => $password2);
			$user->bind($data);

			// Set user preferences
			$preferences = $this->input->get('preference', array(), 'array');
			$user->setPreferences($preferences);

			// Set User Country
			$user->country = $this->input->get('country', '', 'default');
		}

		if ($this->config->get('user_edit_customdetails')) {
			$params = $this->input->get('userparams', array(), 'array');
			$user->setParams($params);
		}

		$state = $user->save();
		$message = JText::_('COM_PP_ACCOUNT_PREFERENCES_UPDATED');

		if (!$state) {
			$message = $user->getError();
		}

		$this->info->set($message, $state ? 'success' : 'error');

		return $this->redirectToView('dashboard', 'preferences');
	}
}