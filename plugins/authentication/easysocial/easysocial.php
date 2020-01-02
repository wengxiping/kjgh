<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class plgAuthenticationEasySocial extends JPlugin
{
	public $name = 'easysocial';

	public function __construct(&$subject, $config)
	{
		$config['name'] = 'EasySocial';

		parent::__construct($subject, $config);
	}

	/**
	 * Tests if EasySocial is installed before this plugin mess things up
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			jimport('joomla.filesystem.file');

			$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';
			$exists = JFile::exists($file);
			
			if (!$exists) {
				$exists = false;

				return $exists;
			}

			include_once($file);

			$exists = true;
		}

		return $exists;
	}

	/**
	 * This method would intercept logins for email, social logins
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onUserAuthenticate(&$credentials, $options, &$response)
	{
		if (!$this->exists()) {
			return;
		}

		$config = ES::config();

		$originalUsername = $credentials['username'];
		$emailAllowed = $config->get('general.site.loginemail');
		$isEmail = JMailHelper::isEmailAddress($credentials['username']);

		// Try to find a valid username if user tries to login with their email.
		if ($emailAllowed && $isEmail) {

			$model = ES::model('Users');
			$username = $model->getUsernameByEmail($credentials['username']);

			// If there's a username, replace the credentials with the username.
			if ($username) {
				$response->type = 'Joomla';
				$credentials['username'] = $username;

				// Avoid using JFactory::getApplication()->login() to prevent inception because login triggers authentication plugin.
				// Get the user id based on the username
				$uid = $model->getUserid('username', $username);

				if (empty($uid)) {
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
				} else {
					// Verify the password
					$match = $model->verifyUserPassword($uid, $credentials['password']);

					if ($match === true) {
						// Bring this in line with the rest of the system
						$user = JUser::getInstance($uid);
						$response->email = $user->email;
						$response->fullname = $user->name;

						$app = JFactory::getApplication();

						if ($app->isAdmin()) {
							$response->language = $user->getParam('admin_language');
						} else {
							$response->language = $user->getParam('language');
						}

						$response->status = JAuthentication::STATUS_SUCCESS;
						$response->error_message = '';
					} else {
						// Invalid password
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
					}
				}
			}
		}

		// Check for two factor authentication
		if ($response->status == JAuthentication::STATUS_SUCCESS) {

			// Retrieve the auth method for current user
			$methods = JAuthenticationHelper::getTwoFactorMethods();

			if (count($methods) >= 1) {
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');
				$model = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));

				if (!array_key_exists('otp_config', $options)) {
					$otpConfig = $model->getOtpConfig($uid);
					$options['otp_config'] = $otpConfig;
				} else {
					$otpConfig = $options['otp_config'];
				}

				// Check if the user has enabled two factor authentication
				if (empty($otpConfig->method) || ($otpConfig->method === 'none')) {
					
					// Warn the user if they are using a secret key but they have not enabled two factor in their account.
					if (!empty($credentials['secretkey'])) {
						
						try {
							$app = JFactory::getApplication();
							$this->loadLanguage();

							$app->enqueueMessage(JText::_('PLG_AUTH_JOOMLA_ERR_SECRET_CODE_WITHOUT_TFA'), 'warning');

						} catch (Exception $exc) {
							return;
						}
					}
				}

				// Validate the OTP
				FOFPlatform::getInstance()->importPlugin('twofactorauth');

				$otpAuthReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorAuthenticate', array($credentials, $options));

				$check = false;

				/*
				 * This looks like noob code but DO NOT TOUCH IT and do not convert
				 * to in_array(). During testing in_array() inexplicably returned
				 * null when the OTEP begins with a zero! o_O
				 */
				if (!empty($otpAuthReplies)) {
					foreach ($otpAuthReplies as $authReply) {
						$check = $check || $authReply;
					}
				}

				// Fall back to One Time Emergency Passwords (OTEP)
				if (!$check) {

					if (empty($otpConfig->otep)) {

						// TFA is not enabled
						if (empty($otpConfig->method) || ($otpConfig->method) === 'none') {
							return;
						} else {

							// No more OTEP left for this account hence the key will be invalid
							$response->status = JAuthentication::STATUS_FAILURE;
							$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_SECRETKEY');

							return;
						}
					}

					// Sanitize and Clean up OTEP
					$otep = $credentials['secretkey'];
					$otep = filter_var($otep, FILTER_SANITIZE_NUMBER_INT);
					$otep = str_replace('-', '', $otep);

					$check = false;

					// Check for valid OTEP
					if (in_array($otep, $otpConfig->otep)) {

						// Remove the used up OTEP since, well, the key can only be used once.
						$otpConfig->otep = array_diff($otpConfig->otep, array($otep));
						$model->setOtpConfig($uid, $otpConfig);

						$check = true;
					}
				}

				// Everything is failed. Invalid OTP and OTEP provided
				if (!$check) {
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_SECRETKEY');
				}
			}
		}

		// User possibly logged in with social client
		$app = JFactory::getApplication();
		$client = $app->input->get('client', '', 'word');

		$table = ES::table('OAuth');
		$state = $table->loadByUsername($originalUsername, $client);

		if ($state) {

			// Now we really need to ensure that they are logged in with their respective oauth client.
			$client = ES::oauth($table->client);
			$client->setAccess($table->token, $table->secret);

			$oauthUserId = $client->getUserId();

			// We cannot match the access token because everytime the user click on the Facebook login button, the tokens are re-generated.
			if ($oauthUserId == $table->oauth_id) {

				$user = ES::user($table->uid);

				// since we are overriding joomla authencation, we need to make sure if this user 
				// is under pending approval or activation.
				if ($user->isBlock() || $user->isBanned()) {
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
					return;
				}

				// We need to update with the new access token here.
				$session = JFactory::getSession();
				$accessToken = $session->get($table->client . '.access', '', SOCIAL_SESSION_NAMESPACE);

				$table->token = $accessToken->token;
				$table->store();

				$response->fullname = $user->getName();
				$response->username = $user->username;
				$response->password = $credentials['password'];
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
				
				return true;
			}
		}

		return false;
	}
}
