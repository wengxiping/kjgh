<?php
/**
 * @package     Invitex
 * @subpackage  Plg_Actionlog_Invitex
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * InviteX Actions Logging Plugin.
 *
 * @since  3.0.10
 */
class PlgActionlogInviteX extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.0.10
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.0.10
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.0.10
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');

		/* @var ActionlogsModelActionlog $model */
		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog($messages, $messageLanguageKey, $context, $userId);
	}

	/**
	 * On after user unsubscribe
	 *
	 * Method is called after a user unsubscribes to invitation mails
	 *
	 * @param   OBJECT  $userId  user id
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterUserUnsubscribe($userId)
	{
		if (!$this->params->get('logActionForUnsubscribe', 1))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser($userId);

		$action   = 'userunsubscribe';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_USER_UNSUBSCRIBE';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after invitation sent
	 *
	 * Method is called after a invitation email is sent
	 *
	 * @param   OBJECT  $inviterId     User id of inviter
	 * @param   OBJECT  $pointOption   Points config option
	 * @param   OBJECT  $inviterPoint  Points to be assigned to the inviter
	 * @param   OBJECT  $countPeople   No. of invitations sent
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function onAfterinvitesent($inviterId, $pointOption, $inviterPoint = 0, $countPeople = 0)
	{
		if (!$this->params->get('logActionForSendingInvitations', 1))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser($inviterId);

		$action   = 'invitationsent';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_INVITATION_SENT';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'invitationCount'  => $countPeople
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after importing contacts
	 *
	 * Method is called after a user imports his contacts in InviteX
	 *
	 * @param   OBJECT  $importedEmails  array of imported email ids
	 * @param   OBJECT  $method          import method
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function OnAfterInvitexImport($importedEmails, $method)
	{
		if (!$this->params->get('logActionForImportingContacts', 1))
		{
			return;
		}

		$invitexParams = JComponentHelper::getParams('com_invitex');
		$storeContacts = $invitexParams->get('store_contact', 0, 'INT');

		if (empty($storeContacts))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'importcontacts';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_IMPORT_CONTACTS';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'method'           => $method,
			'count'            => count($importedEmails)
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after delete invitation
	 *
	 * Method is called after a user deletes invitation
	 *
	 * @param   ARRAY  $invitationData  array of invitation data
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterDeleteInvitation($invitationData)
	{
		if (!$this->params->get('logActionForDeleteInvitation', 1) || empty($invitationData['id']))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'deleteinvitations';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_DELETE_INVITATIONS';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'email'            => $invitationData['invitee_email']
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after save invite's template config
	 *
	 * Method is called after a user saves invite's template config
	 *
	 * @param   ARRAY  $data  array of config
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterSaveInviteTemplateConfig($data)
	{
		if (!$this->params->get('logActionForSaveInviteTemplateConfig', 1) || empty($data))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'saveinvitetemplateconfig';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_SAVE_INVITE_TEMPLATE_CONFIG';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after user unsubscribe
	 *
	 * Method is called after a user is added to unsubscribers list
	 *
	 * @param   STRING  $email  email id to be added to unsubscribers list
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterUnsubscribe($email)
	{
		if (!$this->params->get('logActionForUnsubscribe', 1) || empty($email))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'userunsubscribe';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_USER_ADDED_TO_UNSUBSCRIBERS_LIST';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'email'        => $email
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after user resubscribe
	 *
	 * Method is called after a user is removed from unsubscribers list
	 *
	 * @param   STRING  $email  email id
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterUserResubscribe($email)
	{
		if (!$this->params->get('logActionForUserResubscribe', 1) || empty($email))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'userresubscribe';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_USER_RESUBSCRIBE';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'email'        => $email
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}

	/**
	 * On after send reminder
	 *
	 * Method is called after invitation reminder is sent to a user
	 *
	 * @param   ARRAY  $data  invitation data
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function invitexOnAfterSendReminder($data)
	{
		if (!$this->params->get('logActionForManualReminder', 1) || empty($data))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$user   = Factory::getUser();

		$action   = 'sendreminder';
		$userId   = $user->id;
		$userName = $user->username;

		$messageLanguageKey = 'PLG_ACTIONLOG_INVITEX_SEND_REMINDER';

		$message = array(
			'action'           => $action,
			'actorAccountLink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'actorName'        => $userName,
			'email'        => $data['invitee_email']
		);

		$this->addLog(array($message), $messageLanguageKey, $option, $userId);
	}
}
