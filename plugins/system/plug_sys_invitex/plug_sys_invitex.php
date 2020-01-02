<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.application.application');

// Important  Techjoomla libraries
jimport('techjoomla.jsocial.jsocial');
jimport('techjoomla.jsocial.joomla');
jimport('techjoomla.jsocial.jomsocial');
jimport('techjoomla.jsocial.easysocial');
jimport('techjoomla.jsocial.cb');
jimport('techjoomla.jsocial.jomwall');
jimport('techjoomla.jsocial.alphauserpoints');

/*load language file for plugin frontend*/
$lang = JFactory::getLanguage();
$lang->load('plug_sys_invitex', JPATH_ADMINISTRATOR);

/**
 * Invitex system Plugin
 *
 * @package     Invitex
 * @subpackage  Plg_sys_invitex
 * @since       1.0
 */
class PlgSystemplug_Sys_Invitex extends JPlugin
{
	/**
	 * function called on plugin trigger
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		$mainframe = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		if ($mainframe->isAdmin())
		{
			return;
		}

		$filename = JPATH_SITE . '/components/com_invitex/helper.php';

		// If Invitex helper file exists only then only create its instance*
		if (JFile::exists($filename))
		{
			if (!class_exists('cominvitexHelper'))
			{
				// Require_once $path;
				JLoader::register('cominvitexHelper', $filename);
				JLoader::load('cominvitexHelper');
			}

			$invhelperObj         = new cominvitexHelper;
			$this->invitex_params = $invhelperObj->getconfigData();

			$option     = $input->get('option');
			$view       = $input->get('view');
			$layout     = $input->get('layout');
			$task       = $input->get('task');
			$controller = $input->get('controller');

			// Itemid of Invitex
			$user     = JFactory::getUser();
			$database = JFactory::getDbo();

			$itemid = $invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

			if ($option == 'com_easysocial' and $view == 'friends' and $layout == 'invite' and $this->invitex_params->get('override_easysocial_invitemenu'))
			{
				$mainframe->redirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false));
			}

			// Reference_id is the variable that we append while redirecting use from sign_up function
			$reference_id = $input->get('reference_id');

			// Inviter_id is set in cookie when invitee uses referal link of a site user
			$inviter_id = $input->get('inviter_id', '', 'get');

			// Check for the all registration processes
			if (($input->get('option') == 'com_user' && $input->get('view') == 'register')
				|| ($input->get('option') == 'com_users' && $input->get('view') == 'registration')
				|| ($input->get('option') == 'com_community' && $input->get('view') == 'register')
				|| ($input->get('option') == 'com_comprofiler' && $input->get('task') == 'registers')
				|| ($input->get('option') == 'com_virtuemart' && $input->get('view') == 'user'
				&& $input->get('layout') == 'editaddress') || ($input->get('option') == 'com_easysocial'
				&& $input->get('view') == 'registration'))
			{
				// Validate reference id from URL
				if (isset($reference_id) && $reference_id != '')
				{
					$query = "SELECT * FROM `#__invitex_imports_emails` as ie WHERE MD5(ie.id) = '$reference_id' ";
					$database->setQuery($query);
					$result = $database->loadObject();
					$jinput = $input;
					$method_of_invite = $jinput->get('method_of_invite', '', 'STRING');

					if ($method_of_invite != "invite_by_url")
					{
						if (!$result)
						{
							$msg = JText::_("INCORRECT_LINK_MSG");
							$mainframe->redirect(JURI::base(), $msg);

							return;
						}
						elseif ($result->invitee_id != 0)
						{
							$msg = JText::_("ALREADY_USED_LINK_MSG");
							$mainframe->redirect(JURI::base(), $msg);

							return;
						}
					}
				}

				// Validate inviter_id id
				elseif (isset($inviter_id) && $inviter_id != '')
				{
					$query = "SELECT name FROM `#__users` WHERE MD5(id) = '$inviter_id' ";
					$database->setQuery($query);
					$result = $database->loadResult();

					if (!$result)
					{
						$msg = JText::_("NO_INVITER_ON_SITE_MSG");
						$mainframe->redirect(JURI::base(), $msg);

						return;
					}
				}

				$invite_only = $this->invitex_params->get("invite_only");

				// Check if Site is set for Registration only on Invitation
				if ($invite_only == "1")
				{
					/* If the rendered page is from the registration process then skip that page from the check of 'Registration only on invitation'
					as we cannot carrt reference_id if there are multiple steps in registration process*/
					if (($option == "com_easysocial" && $task == "approveUser" && $controller == "registration")
						|| ($option == "com_easysocial" && $layout == "steps" && $view == "registration")
						|| ($option == "com_easysocial" && $layout == "completed" && $view == "registration")
						|| ($input->get('option') == 'com_community' && $input->get('view') == 'register'
						&& $input->get('task') == 'activation') || ($input->get('option') == 'com_community'
						&& $input->get('view') == 'register' && $input->get('task') == 'registerProfile')
						|| ($input->get('option') == 'com_community' && $input->get('view') == 'register'
						&& $input->get('task') == 'activationResend') || ($input->get('option') == 'com_community'
						&& $input->get('view') == 'register' && $input->get('task') == 'registerUpdateProfile')
						|| ($input->get('option') == 'com_community' && $input->get('view') == 'register'
						&& $input->get('task') == 'registerAvatar') || ($input->get('option') == 'com_community'
						&& $input->get('view') == 'register' && $input->get('task') == 'registerSucess')
						|| ($input->get('option') == 'com_community' && $input->get('view') == 'register'
						&& $input->get('task') == 'registerProfileType') || ($input->get('option') == 'com_virtuemart' && $input->get('view') == 'user'
						&& $input->get('layout') == 'editaddress' && $user->id))
					{
					}
					else
					{
						if (!$reference_id && !$inviter_id)
						{
							$msg = JText::_("REG_ON_INV_MSG");
							$mainframe->redirect(JURI::base(), $msg);
						}
					}
				}
			}

			// Show Invitex in the registration process
			if ($this->invitex_params->get("invitation_during_reg"))
			{
				// If Invitex is already shown in registration process do not show it again
				if (!(isset($_COOKIE['invitex_visited']) && $_COOKIE['invitex_visited'] != ''))
				{
					if ((($input->get('option') == 'com_user' && $input->get('view') == 'register' && $input->get('layout') == 'complete')
						|| ($input->get('option') == 'com_users' && $input->get('view') == 'registration' && $input->get('layout') == 'complete')
						|| ($input->get('option') == 'com_community' && $input->get('view') == 'register' && $input->get('task') == 'registerSucess')
						|| ($input->get('option') == 'com_payplans' && $input->get('view') == 'invoice'
						&& $input->get('task') == 'confirm' && JFactory::getSession()->get('REGISTRATION_NEW_USER_ID', false, 'payplans'))
						|| ($input->get('option') == 'com_virtuemart' && $input->get('view') == 'user'
						&& $input->get('layout') == 'default' && $user->guest == 1)
						|| ($input->get('option') == 'com_easysocial' && $input->get('view') == 'registration' && $input->get('layout') == 'completed')))
					{
						$expire = time() + 3600 * 24 * 30;
						setcookie("invitex_visited", '1', $expire, "/");
						$msg     = JText::_("INV_IN_REG_MSG");
						$session = JFactory::getSession();

						if (($input->get('option') == 'com_easysocial'
							&& $input->get('view') == 'registration'
							&& $input->get('layout') == 'completed'))
						{
							$session->set('user_es', $input->get('userid'));
						}

						if ($input->get('option') == 'com_payplans'
							&& $input->get('view') == 'invoice'
							&& $input->get('task') == 'confirm'
							&& $input->get('invoice_key') != '')
						{
							$session->set('payplans_invoice_key', $input->get('invoice_key', ''));
						}

						$mainframe->redirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $itemid, false), $msg);
					}
				}
			}

			// Send Emails using system plugin is config says so
			$use_system_plugin_cron = $this->invitex_params->get('use_sys');

			if ($use_system_plugin_cron)
			{
				$private_key_cronjob = $this->invitex_params->get('private_key_cronjob');

				// Call cron job function
				$r                   = $this->process_email_queue($private_key_cronjob);
			}
		}
	}

	/**
	 * This method processes emails
	 *
	 * @param   array  $private_key_cronjob  cron key
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.5
	 */
	public function process_email_queue($private_key_cronjob)
	{
		$input = JFactory::getApplication()->input;
		$crontime = $this->params->get('crontime');
		$database = JFactory::getDbo();
		$query    = "SELECT max(sent_at) AS last_email_date FROM #__invitex_imports_emails WHERE sent=1";
		$database->setQuery($query);

		// Get last email sent date
		$last_email_date = $database->loadResult();

		// @date("Y-m-d H:i:s");//get present date
		$present_time    = time();

		// Calculate future time to send mails
		$future_time     = $last_email_date + ($crontime * 60);
		$result          = "";

		if (!$last_email_date)
		{
			$last_email_date = time();
			$present_time    = $future_time = $last_email_date;
		}

		if ($present_time >= $future_time)
		{
			if (!($input->get('option') == 'com_invitex' && $input->get('view') == 'invites'))
			{
				require_once JPATH_SITE . "/components/com_invitex/models/invites.php";
			}

			$plug_call = 1;
			$testcron  = new InvitexModelInvites;
			$input->set('pkey', $private_key_cronjob);
			$private_keyinurl = $input->get('pkey', '');
			$result           = $testcron->mailto($plug_call);
		}

		return $result;
	}

	/**
	 * This method allocate points to users on invites sent
	 *
	 * @param   array  $inviter_id     user id of inviter
	 *
	 * @param   array  $pt_option      points options
	 *
	 * @param   array  $inviter_point  points to be alloted to inviter
	 *
	 * @param   array  $count_people   count of people to whome points are to be allocated
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.5
	 */
	public function onAfterinvitesent($inviter_id, $pt_option, $inviter_point = 0, $count_people = 0)
	{
		// Code to post message for points earned on wall
		$invhelperObj              = new cominvitexHelper;
		$this->invitex_params      = $invhelperObj->getconfigData();
		$integrate_activity_stream = $this->invitex_params->get('integrate_activity_stream');

		if (!empty($integrate_activity_stream))
		{
			if (in_array(2, $integrate_activity_stream))
			{
				$contentdata                       = array();
				$contentdata['user_id']            = $inviter_id;
				$contentdata['integration_option'] = $this->invitex_params->get('reg_direct');
				$contentdata['act_description']    = JText::sprintf("COM_INVITEX_POINTS_EARNED_BY_INVITER_POST", $inviter_point);
				$cominvitexHelper                  = new cominvitexHelper;

				if (!empty($inviter_point))
				{
					$cominvitexHelper->pushtoactivitystream($contentdata);
				}
			}
		}

		// Code to post message for points earned on wall end

		$inviter_user = JFactory::getUser($inviter_id);

		$options = array();

		if ($pt_option == "espt")
		{
			$sociallibraryclass   = new JSocialEasysocial;
			$options['command']   = 'invite_sent';
			$options['extension'] = 'com_invitex';

			// @points: photos.upload
			for ($i = 0; $i < $count_people; $i++)
			{
				$sociallibraryclass->addpoints($inviter_user, $options);
			}
		}
		elseif ($pt_option == "jspt")
		{
			$sociallibraryclass = new JSocialJomsocial;
			$options['command'] = 'com_invitex.user.points';

			for ($i = 0; $i < $count_people; $i++)
			{
				$sociallibraryclass->addpoints($inviter_user, $options);
			}
		}
		elseif ($pt_option == "alta" || $pt_option == "alpha")
		{
			$sociallibraryclass = new JSocialAlphauserpoints;
			$exists             = $sociallibraryclass->checkExists();

			if ($exists)
			{
				$referrerid = $sociallibraryclass->getAnyUserReferreID($inviter_user);
				$options    = array(
					'keyreference' => '',
					'datareference' => JText::_("PUB_AD"),
					'randompoints' => $inviter_point,
					'feedback' => true,
					'force' => '',
					'frontmessage' => '',
					'plugin_function' => 'invitex_aup',
					'referrerid' => $referrerid
				);

				if ($referrerid)
				{
					for ($i = 0; $i < $count_people; $i++)
					{
						$sociallibraryclass->addpoints($inviter_user, $options);
					}
				}
			}
		}
	}

	/**
	 * This method sends notification to imorters
	 *
	 * @param   array  $inviter_id  user id of inviter
	 *
	 * @param   array  $useremail   user mail id
	 *
	 * @param   array  $userid      user id
	 *
	 * @param   array  $username    user name
	 *
	 * @return  boolean True on success
	 *
	 * @since   1.5
	 */
	public function SendNotificationstoImporters($inviter_id, $useremail, $userid, $username)
	{
		$filename = JPATH_ROOT . '/components/com_invitex/helper.php';

		if (JFile::exists($filename))
		{
			require $filename;
			$invhelperObj         = new cominvitexHelper;
			$this->invitex_params = $invhelperObj->getconfigData();
			$to_direct            = $this->invitex_params->get("reg_direct");

			$db = JFactory::getDbo();

			// Check if the config is set
			if ($this->invitex_params->get('joined_friend_notification') == 1)
			{
				// Check if user Email address has been imported by other site Users
				$query = "SELECT importedby FROM `#__invitex_stored_emails` WHERE email='" . $useremail . "' AND notification	!= 1";
				$db->setQuery($query);
				$imported_by_str = $db->loadResult();

				$imported_by = explode(',', $imported_by_str);

				// Remove the $inviter_id from the imported_by as he must have received Notifictaion
				if (!empty($imported_by))
				{
					if ($this->invitex_params->get('invite_accepted_notification') == 1)
					{
						$inviter_key_in_importedBycontacts = array_search($inviter_id, $imported_by);
						unset($imported_by[$inviter_key_in_importedBycontacts]);
						array_values($imported_by);
					}
				}

				// Send notifications to the site users saying that the imported Email has joined contact
				if (!empty($imported_by))
				{
					// For Jomsocial
					if (strcmp($to_direct, "JomSocial") == 0)
					{
						$notification_msg = JText::_('INV_FRIEND_JOIN_SITE');

						foreach ($imported_by as $inviterid)
						{
							$invhelperObj->send_js_notification($userid, $username, $inviterid, $notification_msg);
						}
					}

					// For Joomla
					if (strcmp($to_direct, "Joomla") == 0)
					{
						$notification_msg = JText::_('INV_ACCEPTED_REQUEST_EMAIL');

						foreach ($imported_by as $inviterid)
						{
							$invhelperObj->send_notification_emails($userid, $username, $inviterid, $notification_msg);
						}
					}

					// For EasySocial
					if (strcmp($to_direct, "EasySocial") == 0)
					{
						$notification_msg = JText::_('INV_FRIEND_JOIN_SITE');

						foreach ($imported_by as $inviterid)
						{
							$invhelperObj->send_es_notification($userid, $username, $inviterid, $notification_msg);
						}
					}

					$sql = "UPDATE `#__invitex_stored_emails`
							SET `notification` = 1
							WHERE  email='" . $useremail . "'";
					$db->setQuery($sql);
					$db->query();
				}
			}
		}
	}
}
