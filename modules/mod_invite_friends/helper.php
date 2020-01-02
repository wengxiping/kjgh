<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/**
 * Helper for mod_post_to_twitter
 *
 * @package     Joomla.Site
 * @subpackage  mod_post_to_twitter
 *
 * @since       1.5
 */
class ModInviteFriends
{
	/**
	 * Get all jtext for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		JText::script('MOD_INVITE_FRIENDS_NO_FRIENDS_EMAIL_FOUND', true);
		JText::script('MOD_INVITE_FRIENDS_INVITATION_SUCCESS', true);
		JText::script('MOD_INVITE_FRIENDS_MAIL_CONTENT_MESSAGE', true);
		JText::script('MOD_INVITE_FRIENDS_MAIL_CONTENT_WRONG', true);
		JText::script('MOD_INVITE_FRIENDS_SENDING_INVITATIONS', true);
		JText::script('MOD_INVITE_FRIENDS_INVITE_SUBMIT', true);
		JText::script('MOD_INVITE_FRIENDS_GUEST_NAME_ERROR', true);
		JText::script('MOD_INVITE_FRIENDS_SELF_INVITE', true);
	}
}
