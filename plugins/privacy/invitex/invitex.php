<?php
/**
 * @package     Invitex
 * @subpackage  Plg_Privacy_Invitex
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die();

JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

use Joomla\CMS\User\User;
use Joomla\CMS\Factory;

/**
 * Invitex Privacy Plugin.
 *
 * @since  3.0.10
 */
class PlgPrivacyInvitex extends PrivacyPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  3.0.10
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.0.10
	 */
	protected $db;

	/**
	 * Reports the privacy related capabilities for this plugin to site administrators.
	 *
	 * @return  array
	 *
	 * @since   3.0.10
	 */
	public function onPrivacyCollectAdminCapabilities()
	{
		$this->loadLanguage();

		return array(
			JText::_('PLG_PRIVACY_INVITEX') => array(
				JText::_('PLG_PRIVACY_INVITEX_PRIVACY_CAPABILITY_USER_DETAIL'),
				JText::_('PLG_PRIVACY_INVITEX_PRIVACY_CAPABILITY_COOKIES_DETAIL')
			)
		);
	}

	/**
	 * Processes an export request for Invitex user data
	 *
	 * This event will collect data for the following tables:
	 *
	 * - #__invitex_imports
	 * - #__invitex_imports_emails
	 * - #__invitex_invitation_limit
	 * - #__invitex_invite_success
	 * - #__invitex_stored_contacts
	 * - #__invitex_stored_emails
	 * - #__invitex_stored_tokens
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   3.0.10
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request, JUser $user = null)
	{
		if (!$user)
		{
			return array();
		}

		/** @var JTableUser $user */
		$userTable = User::getTable();
		$userTable->load($user->id);

		$domains = array();
		$domains[] = $this->createInvitexInvitationsSent($userTable);
		$domains[] = $this->createInvitexInvitationSent($userTable);
		$domains[] = $this->createInvitexInvitationsReceived($userTable);
		$domains[] = $this->createInvitexInvitationLimit($userTable);
		$domains[] = $this->createInvitexSentInvitationsStatus($userTable);
		$domains[] = $this->createInvitexReceivedInvitationsStatus($userTable);
		$domains[] = $this->createInvitexInviterStoredContacts($userTable);
		$domains[] = $this->createInvitexInviteeStoredContacts($userTable);
		$domains[] = $this->createInvitexContactDetails($userTable);
		$domains[] = $this->createInvitexImportedContactDetails($userTable);
		$domains[] = $this->createInvitexStoredTokens($userTable);

		return $domains;
	}

	/**
	 * Create the domain for the Invitex Invitation batch
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInvitationsSent(JTableUser $user)
	{
		$domain = $this->createDomain('Invitations sent', 'Invitations sent by user in a batch');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'inviter_id', 'invites_count', 'provider', 'provider_email', 'date', 'invite_type')))
			->from($this->db->quoteName('#__invitex_imports'))
			->where($this->db->quoteName('inviter_id') . '=' . $user->id);

		$invitationsSent = $this->db->setQuery($query)->loadAssocList();

		if (!empty($invitationsSent))
		{
			foreach ($invitationsSent as $invitationSent)
			{
				$domain->addItem($this->createItemFromArray($invitationSent, $invitationSent['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex sent invitations
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInvitationSent(JTableUser $user)
	{
		$domain = $this->createDomain('Invitation sents', 'Invitations sent by user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'inviter_id', 'invitee_email', 'invitee_name', 'invitee_id', 'friend_count')))
			->from($this->db->quoteName('#__invitex_imports_emails'))
			->where($this->db->quoteName('inviter_id') . '=' . $user->id);

		$invitationsSent = $this->db->setQuery($query)->loadAssocList();

		if (!empty($invitationsSent))
		{
			foreach ($invitationsSent as $invitationSent)
			{
				$domain->addItem($this->createItemFromArray($invitationSent, $invitationSent['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex received invitation
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInvitationsReceived(JTableUser $user)
	{
		$domain = $this->createDomain('Invitation received', 'Received invitation');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'inviter_id', 'invitee_email', 'invitee_name', 'invitee_id', 'friend_count')))
			->from($this->db->quoteName('#__invitex_imports_emails'))
			->where($this->db->quoteName('invitee_id') . '=' . $user->id);

		$invitationsReceived = $this->db->setQuery($query)->loadAssocList();

		if (!empty($invitationsReceived))
		{
			foreach ($invitationsReceived as $invitationReceived)
			{
				$domain->addItem($this->createItemFromArray($invitationReceived, $invitationReceived['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex Invitation limit
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInvitationLimit(JTableUser $user)
	{
		$domain = $this->createDomain('Invitation Limit', 'Invitation limit per user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'userid', 'limit')))
			->from($this->db->quoteName('#__invitex_invitation_limit'))
			->where($this->db->quoteName('userid') . '=' . $user->id);

		$invitationLimit = $this->db->setQuery($query)->loadAssoc();

		if (!empty($invitationLimit))
		{
			$domain->addItem($this->createItemFromArray($invitationLimit, $invitationLimit['id']));
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex Sent Invitations status
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexSentInvitationsStatus(JTableUser $user)
	{
		$domain = $this->createDomain('Sent Invitation Status', 'Status of invitations sent by user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'inviter_id', 'invitee_id', 'status')))
			->from($this->db->quoteName('#__invitex_invite_success'))
			->where($this->db->quoteName('inviter_id') . '=' . $user->id);

		$invitations = $this->db->setQuery($query)->loadAssocList();

		if (!empty($invitations))
		{
			foreach ($invitations as $invitation)
			{
				$domain->addItem($this->createItemFromArray($invitation, $invitation['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex Received Invitations status
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexReceivedInvitationsStatus(JTableUser $user)
	{
		$domain = $this->createDomain('Received Invitation Status', 'Status of invitations received by user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'inviter_id', 'invitee_id', 'status')))
			->from($this->db->quoteName('#__invitex_invite_success'))
			->where($this->db->quoteName('invitee_id') . '=' . $user->id);

		$invitations = $this->db->setQuery($query)->loadAssocList();

		if (!empty($invitations))
		{
			foreach ($invitations as $invitation)
			{
				$domain->addItem($this->createItemFromArray($invitation, $invitation['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex Inviter Stored contacts
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInviterStoredContacts(JTableUser $user)
	{
		$domain = $this->createDomain('Stored Contacts - for PeopeSuggest', 'Contacts imported by inviter');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'Inviter', 'Invitee')))
			->from($this->db->quoteName('#__invitex_stored_contacts'))
			->where($this->db->quoteName('Inviter') . '=' . $user->id);

		$contacts = $this->db->setQuery($query)->loadAssocList();

		if (!empty($contacts))
		{
			foreach ($contacts as $contact)
			{
				$domain->addItem($this->createItemFromArray($contact, $contact['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex Invitee Stored contacts
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexInviteeStoredContacts(JTableUser $user)
	{
		$domain = $this->createDomain('Stored Contacts - for PeopeSuggest', 'Invitee contact');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'Inviter', 'Invitee')))
			->from($this->db->quoteName('#__invitex_stored_contacts'))
			->where($this->db->quoteName('Invitee') . '=' . $user->id);

		$contacts = $this->db->setQuery($query)->loadAssocList();

		if (!empty($contacts))
		{
			foreach ($contacts as $contact)
			{
				$domain->addItem($this->createItemFromArray($contact, $contact['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex contact details
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexContactDetails(JTableUser $user)
	{
		$domain = $this->createDomain('Contact Details of user', 'Imported Contact details');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'email', 'name', 'importedby', 'unsubscribe')))
			->from($this->db->quoteName('#__invitex_stored_emails'))
			->where($this->db->quoteName('email') . '=' . $this->db->quote($user->email));

		$contacts = $this->db->setQuery($query)->loadAssocList();

		if (!empty($contacts))
		{
			foreach ($contacts as $contact)
			{
				$domain->addItem($this->createItemFromArray($contact, $contact['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex imported contact details
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexImportedContactDetails(JTableUser $user)
	{
		$domain = $this->createDomain('Contacts Imported by a user', 'Contacts details imported by a user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'email', 'name', 'importedby', 'unsubscribe')))
			->from($this->db->quoteName('#__invitex_stored_emails'))
			->where("FIND_IN_SET(" . $user->id . ", " . $this->db->quoteName('importedby') . ")");

		$contacts = $this->db->setQuery($query)->loadAssocList();

		if (!empty($contacts))
		{
			foreach ($contacts as $contact)
			{
				$domain->addItem($this->createItemFromArray($contact, $contact['id']));
			}
		}

		return $domain;
	}

	/**
	 * Create the domain for the Invitex stored OAuth tokens
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.0.10
	 */
	private function createInvitexStoredTokens(JTableUser $user)
	{
		$domain = $this->createDomain('OAuth Tokens', 'Stored OAuth tokens of user');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'user_id', 'import_id')))
			->from($this->db->quoteName('#__invitex_stored_tokens'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->id));

		$tokens = $this->db->setQuery($query)->loadAssocList();

		if (!empty($tokens))
		{
			foreach ($tokens as $token)
			{
				$domain->addItem($this->createItemFromArray($token, $token['id']));
			}
		}

		return $domain;
	}

	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * This event will not allow a super user account to be removed
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 *
	 * @since   3.0.10
	 */
	public function onPrivacyCanRemoveData(PrivacyTableRequest $request, JUser $user = null)
	{
		$status = new PrivacyRemovalStatus;

		if (!$user)
		{
			return $status;
		}

		return $status;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * This event will pseudoanonymise the user account
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   JUser                $user     The user account associated with this request if available
	 *
	 * @return  void
	 *
	 * @since   3.0.10
	 */
	public function onPrivacyRemoveData(PrivacyTableRequest $request, JUser $user = null)
	{
		// This plugin only processes data for registered user accounts
		if (!$user)
		{
			return;
		}

		// If there was an error loading the user do nothing here
		if ($user->guest)
		{
			return;
		}

		$db = $this->db;

		// 1. Delete recommendations data from __invitex_invitation_limit
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_invitation_limit'))
			->where($db->quoteName('userid') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 2. Delete recommendations data from __invitex_invite_success
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_invite_success'))
			->where($db->quoteName('inviter_id') . '=' . $user->id . ' OR ' . $db->quoteName('invitee_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 3. Delete recommendations data from __invitex_stored_contacts
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_stored_contacts'))
			->where($db->quoteName('Inviter') . '=' . $user->id . ' OR ' . $db->quoteName('Invitee') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 4. Delete recommendations data from __invitex_stored_emails (where only one user had imported that contact)
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__invitex_stored_emails'));
		$query->where('(' . $db->quoteName('email') . '=' . $db->quote($user->email) . ' OR ' . $db->quoteName('importedby') . '=' . $user->id
		. ') AND ' . $db->quoteName('importedcount') . '=1');

		$db->setQuery($query);
		$db->execute();

		// 5. Delete recommendations data from __invitex_stored_emails (where more than one user had imported that contact)
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__invitex_stored_emails'))
			->where("FIND_IN_SET(" . $user->id . ", " . $db->quoteName('importedby') . ")");

		$db->setQuery($query);
		$contacts = $db->loadObjectList();

		foreach ($contacts as $contact)
		{
			$importedBy = explode(',', $contact->importedby);
			$key = array_search($user->id, $importedBy);
			unset($importedBy[$key]);

			$contact->importedcount = count($importedBy);
			$contact->importedby = implode(",", $importedBy);

			// Update their details in the stored emails table.
			$result = Factory::getDbo()->updateObject('#__invitex_stored_emails', $contact, 'id');
		}

		// 6. Delete recommendations data from __invitex_stored_tokens
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_stored_tokens'))
			->where($db->quoteName('user_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 7. Delete recommendations data from __invitex_imports_emails
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_imports_emails'))
			->where($db->quoteName('inviter_id') . '=' . $user->id . ' OR ' . $db->quoteName('invitee_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();

		// 8. Delete recommendations data from __invitex_imports
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__invitex_imports'))
			->where($db->quoteName('inviter_id') . '=' . $user->id);

		$db->setQuery($query);
		$db->execute();
	}
}
