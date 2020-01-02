<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialVerification extends EasySocial
{
	/**
	 * Approves a verification request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function approve($id)
	{
		$request = ES::table('Verification');
		$request->load($id);

		$request->state = ES_VERIFICATION_APPROVED;
		$request->store();

		// We need to update the user's verified state now
		if ($request->type == SOCIAL_TYPE_USER) {
			$table = ES::table('Users');
			$table->load($request->uid);

			$table->verified = true;
			$table->store();
		}

		return $request;
	}

	/**
	 * Determines if user is allowed to request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canRequest()
	{
		$enabled = $this->config->get('users.verification.enabled');
		$allowed = $this->config->get('users.verification.requests');

		if (!$allowed || !$enabled || $this->my->verified || $this->hasRequested()->state) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if user has previously requested before
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function hasRequested($uid = null, $type = SOCIAL_TYPE_USER)
	{
		// For now, we only have verifications for profile
		$user = ES::user($uid);

		$request = ES::table('Verification');
		$exists = $request->load(array('uid' => $user->id, 'type' => $type));

		// retrieve the verification state for this user
		$verifyState = $request->state;

		// Ensure this user set verified by admin from backend
		// Because the current process only update that verified state on the user table
		if (!$exists) {
			$userTbl = ES::table('Users');
			$exists = $userTbl->load(array('user_id' => $user->id, 'verified' => true));

			// retrieve the user verification state for this user
			$verifyState = $userTbl->verified;
		}

		$results = new stdClass();
		$results->state = $exists;
		$results->message = $verifyState ? 'COM_ES_ALREADY_VERIFIED_THIS_USER' : 'COM_ES_ALREADY_REQUEST_VERIFICATION';

		return $results;
	}

	/**
	 * Rejects a verification request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function reject($id)
	{
		$request = ES::table('Verification');
		$request->load($id);

		$request->delete();
		
		return $request;
	}

	/**
	 * Generates a new request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function request($message, $ip = null, $uid = null, $type = SOCIAL_TYPE_USER)
	{
		$user = ES::user($uid);

		$request = ES::table('Verification');
		$request->uid = $user->id;
		$request->type = $type;
		$request->created_by = $this->my->id;
		$request->message = $message;
		$request->created = JFactory::getDate()->toSql();
		$request->state = ES_VERIFICATION_REQUEST;
		$request->ip = $ip;
		$request->store();

		// Notify admin
		$this->notify($request);

		return $request;
	}

	/**
	 * Notify admin on new verification request
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notify($request)
	{
		$params = array(
					'requester' => $this->my->getName(),
					'verificationMessage' => $request->message,
					'avatar' => $this->my->getAvatar(SOCIAL_AVATAR_LARGE),
					'userPermalink' => $this->my->getPermalink(true, true),
					'permalink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=verifications',
					'alerts' => false
				);

		// Set the e-mail title
		$title = JText::sprintf('COM_ES_EMAILS_USER_VERIFICATION_REQUEST_SUBHEADING', $this->my->getName());

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			// Immediately send out emails
			$mailer = ES::mailer();

			// Set the admin's name.
			$params['adminName'] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$mailTemplate->setTemplate('site/user/verification.request', $params);

			// We need it to be sent out immediately
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}
}