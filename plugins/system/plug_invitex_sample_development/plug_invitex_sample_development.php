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

/**
 * Invitex system Plugin
 *
 * @package     Invitex
 * @subpackage  plgSystemplug_invitex_sample_development
 * @since       1.0
 */
class PlgSystemplug_Invitex_Sample_Development extends JPlugin
{
	/**
	 * plugin trigger after invitex import
	 *
	 * @param   array   $data  imported data
	 *
	 * @param   STRING  $type  invite type
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function OnAfterInvitexImport($data, $type)
	{
		return $data;
	}

	/**
	 * plugin trigger on invitex email prepare
	 *
	 * @param   array  $message_body  message body
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function onPrepareInvitexEmail($message_body)
	{
		return $message_body;
	}

	/**
	 * plugin trigger after invite link clicked
	 *
	 * @param   array  $invite_id  invite id
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function OnInvitexLinkClick($invite_id)
	{
		return $invite_id;
	}

	/**
	 * plugin trigger after invitee successful register
	 *
	 * @param   array  $inviter_id  user id of inviter
	 *
	 * @param   array  $invitee_id  user id of invitee
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function OnInviteSuccessRegister($inviter_id, $invitee_id)
	{
		return;
	}

	/**
	 * plugin trigger on invitee successfully sign in
	 *
	 * @param   array  $inviter_id  user id of inviter
	 *
	 * @param   array  $invitee_id  user id of invitee
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function OnInviteSuccessSignin($inviter_id,$invitee_id)
	{
		return;
	}

	/**
	 * plugin trigger after invite URL clicked
	 *
	 * @param   array  $inviter_id  user id of inviter
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function OnInviteURLClk($inviter_id)
	{
		return $inviter_id;
	}

	/**
	 * plugin trigger after invites queued
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function onAfterinvitesqueued()
	{
		return;
	}

	/**
	 * plugin trigger after invites sent
	 *
	 * @param   array  $inviter_id     user id of inviter
	 *
	 * @param   array  $pt_option      points options
	 *
	 * @param   array  $inviter_point  points to be alloted to inviter
	 *
	 * @param   array  $count_people   count of people to whome points are to be allocated
	 *
	 * @return  null
	 *
	 * @since   1.5
	 */
	public function onAfterinvitesent($inviter_id, $pt_option, $inviter_point = 0, $count_people = 0)
	{
		return;
	}
}
