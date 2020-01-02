<?php
/**
 * @package    InviteX-User_Plugin
 * @copyright  Copyright (C) 2010-2015 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

// Important  Jsocial libraries
jimport('techjoomla.jsocial.jsocial');
jimport('techjoomla.jsocial.joomla');
jimport('techjoomla.jsocial.jomsocial');
jimport('techjoomla.jsocial.easysocial');
jimport('techjoomla.jsocial.cb');
jimport('techjoomla.jsocial.jomwall');
jimport('techjoomla.jsocial.alphauserpoints');

$filename = JPATH_ROOT . '/components/com_invitex/helper.php';

if (JFile::exists($filename))
{
	require $filename;
}

$lang = JFactory::getLanguage();
$lang->load('plug_user_invitex', JPATH_ADMINISTRATOR);

/**
 * Invitex User Plugin
 *
 * @package     Invitex
 * @subpackage  PlgUserplug_user_invitex
 * @since       1.0
 */
class PlgUserplug_User_Invitex extends JPlugin
{
	/**
	 * Utility method to act before user has been saved.
	 *
	 * @param   array    $user   Holds the new user data.
	 * @param   boolean  $isnew  True if a new user is stored.
	 * @param   boolean  $data   data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{
		$jinput = JFactory::getApplication()->input;
		$invhelperObj = new cominvitexHelper;
		$this->invitex_params = $invhelperObj->getconfigData();
		$reg_only_invite_email = $this->invitex_params->get("reg_only_invite_email");

		if (!empty($_COOKIE['refid']))
		{
			$refid = $_COOKIE['refid'];
		}

		if (!empty($reg_only_invite_email))
		{
			// Validate reference id from URL
			if (!empty($refid))
			{
				$db = JFactory::getDbo();
				$query = "SELECT * FROM `#__invitex_imports_emails` WHERE  md5(`id`)  = '" . $refid . "'";
				$db->setQuery($query);
				$inviteDetails = $db->loadObject();

				if ($inviteDetails->invitee_email != $data['email'])
				{
					throw new InvalidArgumentException(JText::_('INV_CHECK_MAIL_ID_FAIL'));

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * @param   array    $user    Holds the new user data.
	 * @param   boolean  $isnew   True if a new user is stored.
	 * @param   boolean  $succes  True if user was succesfully stored in the database.
	 * @param   string   $msg     Message.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onUserAfterSave($user, $isnew, $succes, $msg)
	{
		if ( !$isnew )
		{
			return false;
		}

		$mainframe            = JFactory::getApplication();
		$invhelperObj         = new cominvitexHelper;
		$this->invitex_params = $invhelperObj->getconfigData();
		$to_direct            = $this->invitex_params->get("reg_direct");
		$inv_success          = 0;
		$db                   = JFactory::getDbo();

		if (isset($_COOKIE['refid']) && $_COOKIE['refid'] != '')
		{
			$userid       = $user['id'];
			$ip_address   = $_SERVER['REMOTE_ADDR'];
			$current_date = time();
			$refid        = $_COOKIE['refid'];
			$sql          = "UPDATE `#__invitex_imports_emails`
																			SET `invitee_id` = $userid , ip = '$ip_address' , modified = $current_date
																			WHERE  md5(`id`)  = '$refid'";
			$db->setQuery($sql);
			$db->query();

			$query = "SELECT inviter_id FROM `#__invitex_imports_emails` WHERE  md5(`id`)  = '$refid'";
			$db->setQuery($query);
			$inviter_id  = $db->loadResult();
			$inv_success = 1;
		}

		if (isset($_COOKIE['inviter_id']) && $_COOKIE['inviter_id'] != '')
		{
			$query = "SELECT id FROM `#__users` WHERE md5(id) = '" . $_COOKIE['inviter_id'] . "'";
			$db->setQuery($query);
			$inviter_id = $db->loadResult();
			$userid     = $user['id'];

			$query = "SELECT * FROM `#__invitex_invite_success` WHERE inviter_id= '$inviter_id' AND invitee_id= '$userid'";
			$db->setQuery($query);
			$result = $db->loadResult();

			if (!$result)
			{
				$success             = new stdClass;
				$success->inviter_id = $inviter_id;
				$success->invitee_id = $userid;
				$success->status     = 0;
				$db->insertObject('#__invitex_invite_success', $success);
			}

			$inv_success = 1;
		}

		$per_user_invitation_limit = $this->invitex_params->get("per_user_invitation_limit");
		$query                     = "SELECT * FROM `#__invitex_invitation_limit` WHERE userid=" . (int) $user['id'];
		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			$data         = new stdClass;
			$data->userid = $user['id'];
			$data->limit  = $per_user_invitation_limit;
			$db->insertObject('#__invitex_invitation_limit', $data, 'id');
		}

		setcookie("invitex_reg_user", '', -time(), "/");
		setcookie("invitex_visited", '', -time(), "/");

		if ($this->invitex_params->get("invitation_during_reg"))
		{
			$expire = time() + 3600 * 24 * 30;
			setcookie("invitex_reg_user", $user['id'], $expire, "/");
		}

		if ($inv_success == 1)
		{
			$invitee_id = $user['id'];
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$result = $dispatcher->trigger('OnInviteSuccessRegister', array(
				$inviter_id,
				$invitee_id)
			);
		}

		// Send user joined notification to the inviter
		if ($this->invitex_params->get('joined_friend_notification') == 1)
		{
			$query = "SELECT importedby FROM `#__invitex_stored_emails` WHERE email='" . $user['email'] . "'";
			$db->setQuery($query);
			$imported_by = $db->loadResult();

			if ($imported_by)
			{
				$imported_by       = explode(',', $imported_by);
				$username          = $user['username'];
				$options           = array();
				$options['cmd']    = 'notif_system_messaging';
				$options['type']   = '0';
				$options['params'] = '';

				switch (strtolower($to_direct))
				{
					case 'jomsocial':
						$sociallibraryclass = new JSocialJomsocial;
						$notification_msg   = JText::_('INV_FRIEND_JOIN_SITE');
						$receiver           = JFactory::getUser($user['id']);

						foreach ($imported_by as $inviterid)
						{
							$invitee_profile_url  = JRoute::_($sociallibraryclass->getProfileUrl($receiver));
							$notification_subject = '<a href="' . $invitee_profile_url . '" >' . $receiver->name . '</a>' . $notification_msg;
							$sender               = JFactory::getUser($inviterid);
							$receiver             = JFactory::getUser($invitee_id);
							$sociallibraryclass->sendNotification($sender, $receiver, $notification_subject, $notification_msg, $options);
						}
						break;

					case 'easysocial':
						$sociallibraryclass = new JSocialJomsocial;
						$notification_msg   = JText::_('INV_FRIEND_JOIN_SITE');
						$receiver           = JFactory::getUser($user['id']);

						foreach ($imported_by as $inviterid)
						{
							$sender = JFactory::getUser($inviterid);
							$sociallibraryclass->sendNotification($sender, $receiver, $notification_msg, $cmd = 0, $type = '', $params = '');
						}
						break;

					default:
						$notification_msg = JText::_('INV_ACCEPTED_REQUEST_EMAIL');

						foreach ($imported_by as $inviterid)
						{
							$invhelperObj->send_notification_emails($id, $username, $inviterid, $notification_msg);
						}
				}
			}
		}
	}

	/**
	 * Utility method to act on a user after he has logged in
	 *
	 * @param   array  $user     Holds the new user data.
	 * @param   mixed  $options  Holds options/configuration for the user
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onUserLogin($user, $options)
	{
		$mainframe = JFactory::getApplication();

		if ($mainframe->isAdmin())
		{
			return;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_INVITEX', JPATH_SITE);
		$invhelperObj         = new cominvitexHelper;
		$this->invitex_params = $invhelperObj->getconfigData();
		$itemid               = $invhelperObj->getitemid('index.php?option=com_invitex&view=invites');
		$mainframe            = JFactory::getApplication();

		setcookie("invitex_reg_user", '', -time(), "/");
		setcookie("invitex_after_login", '', -time(), "/");
		setcookie("refid", '', -time(), "/");
		$to_direct     = $this->invitex_params->get("reg_direct");
		$inviter_point = $this->invitex_params->get("inviter_point");
		$invitee_point = $this->invitex_params->get("invitee_point");
		$pt_option     = $this->invitex_params->get("pt_option");
		$dt            = date('y-m-d');
		$db            = JFactory::getDbo();
		$friendcount   = 1;
		$username      = $user['username'];
		$useremail     = $user['email'];
		$sql           = "SELECT id FROM  #__users WHERE email='$useremail'";
		$db->setQuery($sql);
		$id = $db->loadResult();

		if ($id)
		{
			$friend = '';

			// In case the logged user is an accepted invitee
			$sql    = "SELECT DISTINCT inviter_id FROM  #__invitex_imports_emails WHERE invitee_id =" . $id . " AND friend_count = 0 ";
			$db->setQuery($sql);
			$friend = $db->loadResult();

			if (!$friend)
			{
				$sql = "SELECT inviter_id FROM  #__invitex_invite_success WHERE invitee_id = " . $id . " AND status    = 0 ";
				$db->setQuery($sql);
				$friend_sucess = $db->loadResult();
			}

			if ($friend || $friend_sucess)
			{
				if ($friend)
				{
					// Adding a friend
					$inviter_id = $friend;
					$query      = "UPDATE `#__invitex_imports_emails` SET `friend_count` = '1' WHERE `invitee_id` = $id";
					$db->setQuery($query);
					$db->query();
				}
				else
				{
					$inviter_id = $friend_sucess;
					$query      = "UPDATE `#__invitex_invite_success` SET `status` = '1' WHERE `invitee_id` = $id AND `inviter_id` = $inviter_id ";
					$db->setQuery($query);
					$db->query();
				}

				$invitee_id = $id;
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$result = $dispatcher->trigger('OnInviteSuccessSignin', array(
					$inviter_id,
					$invitee_id)
				);

				// Get Inviter And Invitee User object
				$inviter_user = JFactory::getUser($inviter_id);
				$invitee_user = JFactory::getUser($invitee_id);

				// Code to post message for points earned on wall
				$integrate_activity_stream = $this->invitex_params->get('integrate_activity_stream');

				if (!empty($integrate_activity_stream) && in_array(2, $integrate_activity_stream))
				{
					$contentdata = array();
					$contentdata['user_id'] = $inviter_id;
					$contentdata['integration_option'] = $this->invitex_params->get('reg_direct');
					$contentdata['act_description'] = JText::sprintf("COM_INVITEX_POINTS_EARNED_BY_INVITEE_REGISTRATION", $inviter_point);
					$cominvitexHelper               = new cominvitexHelper;

					if (!empty($inviter_point))
					{
						$cominvitexHelper->pushtoactivitystream($contentdata);
					}

					$contentdata['user_id'] = $invitee_id;
					$contentdata['integration_option'] = $this->invitex_params->get('reg_direct');
					$contentdata['act_description'] = JText::sprintf("COM_INVITEX_POINTS_EARNED_BY_INVITEE_POST", $invitee_point);

					if (!empty($invitee_point))
					{
						$cominvitexHelper->pushtoactivitystream($contentdata);
					}
				}

				// Code to post message for points earned on wall end

				if ($pt_option == 'espt')
				{
					$sociallibraryclass   = new JSocialEasysocial;

					// Assign Points to inviter_user
					$options['command']   = 'Invite_sent_to_accept';
					$options['extension'] = 'com_invitex';
					$sociallibraryclass->addpoints($inviter_user, $options);

					// Assign Points to invitee
					$options['command'] = 'Invite_accpted';
					$sociallibraryclass->addpoints($invitee_user, $options);
				}
				elseif ($pt_option == 'jspt')
				{
					$sociallibraryclass = new JSocialJomsocial;

					// Assign Points to inviter_user
					$options['command'] = 'com_invitex.inviter.points';
					$sociallibraryclass->addpoints($inviter_user, $options);

					// Assign Points to invitee_user
					$options['command'] = 'com_invitex.invitee.points';
					$sociallibraryclass->addpoints($invitee_user, $options);
				} elseif ($pt_option == 'alta' || $pt_option == 'alpha') {
					$sociallibraryclass = new JSocialAlphauserpoints;
					$exists             = $sociallibraryclass->checkExists();

					if ($exists)
					{
						// Find reference ID in Alphauserpoints
						$aupid = $sociallibraryclass->getAnyUserReferreID($invitee_user);

						// Assign points to invitee
						if ($aupid)
						{
							$options = array(
								'keyreference' => '',
								'datareference' => JText::_("PUB_AD"),
								'randompoints' => $invitee_point,
								'feedback' => true,
								'force' => '',
								'frontmessage' => JText::sprintf("EXCHANGE_PTS_INVITEE", $invitee_point),
								'plugin_function' => 'invitex_aup',
								'referrerid' => $aupid
							);
							$sociallibraryclass->addpoints($invitee_user, $options);
						}

						$aupid = $sociallibraryclass->getAnyUserReferreID($inviter_user);

						if ($aupid)
						{
							// Assign points to inviter
							$options = array(
								'keyreference' => '',
								'datareference' => JText::_("PUB_AD"),
								'randompoints' => $inviter_point,
								'feedback' => true,
								'force' => '',
								'frontmessage' => JText::sprintf("EXCHANGE_PTS_INVITEE", $invitee_point),
								'plugin_function' => 'invitex_aup',
								'referrerid' => $aupid
							);
							$sociallibraryclass->addpoints($inviter_user, $options);
						}
					}
				}

				if ((strcmp($to_direct, "JomSocial") == 0)) // For JomSocial
				{
					$sociallibraryclass = new JSocialJomsocial;

					// Set friend count to 1
					// Make inviter and invitees friends
					$sociallibraryclass->addFriend($inviter_user, $invitee_user);

					$options           = array();
					$options['cmd']    = 'notif_system_messaging';
					$options['type']   = '0';
					$options['params'] = '';

					// Notfication for jomsoical
					if ($this->invitex_params->get('invite_accepted_notification') == 1)
					{
						$notification_msg     = JText::_('INV_ACCEPTED_REQUEST');
						$invitee_profile_url  = JRoute::_($sociallibraryclass->getProfileUrl($invitee_user));
						$notification_subject = '<a href="' . $invitee_profile_url . '" >' . $invitee_user->name . '</a>' . $notification_msg;
						$sociallibraryclass->sendNotification($invitee_user, $inviter_user, $notification_subject, $options);
					}
				}

				if (strcmp($to_direct, "Community Builder") == 0) // For CB
				{
					$sociallibraryclass = new JSocialCB;
					$sociallibraryclass->addFriend($inviter_user, $invitee_user);

					if ($this->invitex_params->get('invite_accepted_notification') == 1)
					{
						$notification_msg = JText::_('INV_ACCEPTED_REQUEST_EMAIL');
						$invhelperObj->send_notification_emails($id, $username, $inviter_id, $notification_msg);
					}
				}

				if (strcmp($to_direct, "EasySocial") == 0)
				{
					$sociallibraryclass = new JSocialEasysocial;
					$sociallibraryclass->addFriend($inviter_user, $invitee_user);

					if ($this->invitex_params->get('invite_accepted_notification') == 1)
					{
						$notification_msg = JText::_('INV_ACCEPTED_REQUEST');
						$systemOptions    = array(
							'uid' => 'accepted_invite',
							'actorId' => $inviter_user->id,
							'target_id' => $inviter_user->id,
							'type' => 'Invite',
							'title' => $invitee_user->name . $notification_msg,
							'image' => '',
							'cmd' => 'notify_invite.create'
						);
						$sociallibraryclass->sendNotification($inviter_user, $invitee_user, $notification_msg, $systemOptions);
					}
				}

				if (strcmp($to_direct, "Joomla") == 0) // For JOOMLA
				{
					if ($this->invitex_params->get('invite_accepted_notification') == 1)
					{
						$notification_msg = JText::_('INV_ACCEPTED_REQUEST_EMAIL');
						$invhelperObj->send_notification_emails($id, $username, $inviter_id, $notification_msg);
					}
				}

				// On 1st login
				if ($this->invitex_params->get("select_mothod_for_invite") == 1 && $this->invitex_params->get("invite_after_login") == 1)
				{
					$expire = time() + 3600 * 24 * 30;
					setcookie("invitex_after_login", '1', $expire, "/");
					$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
					$mainframe->redirect($link);
				}
			}

			// For every login
			if ($this->invitex_params->get("select_mothod_for_invite") == 0 && $this->invitex_params->get("invite_after_login") == 1)
			{
				$expire = time() + 3600 * 24 * 30;
				setcookie("invitex_after_login", '1', $expire, "/");
				$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
				$mainframe->redirect($link);
			}

			// For first login of every new user
			if ($this->invitex_params->get("select_mothod_for_invite") == 2 && $this->invitex_params->get("invite_after_login") == 1)
			{
				$user = JFactory::getUser();

				if (!$user->guest && $user->lastvisitDate == "0000-00-00 00:00:00")
				{
					$expire = time() + 3600 * 24 * 30;
					setcookie("invitex_after_login", '1', $expire, "/");
					$link = JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false);
					$mainframe->redirect($link);
				}
			}
		}

		return true;
	}

	/**
	 * This method should handle any logout logic Delete any cookies that are used
	 *
	 * @param   array  $user  holds the user data
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.5
	 */
	public function onLogoutUser($user)
	{
		setcookie("invitex_reg_user", '', -time(), "/");
		setcookie("invitex_after_login", '', -time(), "/");
		setcookie("refid", '', -time(), "/");
		setcookie("invitex_visited", '', -time(), "/");

		return true;
	}

	/**
	 * Gets the corrct Itemid for Invitex
	 *
	 * @access  public
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getItemIdInv()
	{
		$db     = JFactory::getDbo();
		$Itemid = 0;

		if ($Itemid < 1)
		{
			$db->setQuery("SELECT id FROM #__menu WHERE link LIKE '%option=com_invitex%' AND published = 1");
			$Itemid = $db->loadResult();

				if ($Itemid < 1)
				{
					$Itemid = 0;
				}
		}

		return $Itemid;
	}
}
