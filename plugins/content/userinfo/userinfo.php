<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @copyright  Copyright (C) 2005 - 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

defined('_JEXEC') or die ('Restricted access');
/**
 * @package		jLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Plugin class to replace content of landing page article
 *
 * @since  1.0.0
 */
class PlgContentUserinfo extends JPlugin
{
	/**
	 * Function to change the content of article for custom landing page in invitex
	 *
	 * @param   String  $context   article content
	 *
	 * @param   String  &$article  article
	 *
	 * @param   Array   &$params   params
	 *
	 * @param   INT     $page      number of page
	 *
	 * @return content of article
	 *
	 * @since   1.0
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
	{
		$lang = JFactory::getLanguage();
		$extension = 'com_invitex';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		$app = JFactory::getApplication();

		if ($app->getName() != 'site')
		{
			return;
		}

		$db	=	JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$option = $input->get('option', '', 'STRING');
		$view = $input->get('view', '', 'STRING');
		$layout = $input->get('layout', '', 'STRING');
		$app = JFactory::getApplication();

		if ($input->get('reference_id') || $input->get('inviter_id'))
		{
			if (($app->scope != 'com_content' AND $context != 'com_content.category') OR ($app->scope != 'com_content' AND $context != 'com_content.article'))
			{
				return;
			}
		}
		else
		{
			return;
		}

		if (isset($_COOKIE['refid']) && $_COOKIE['refid'] != '')
		{
			$helperPath = JPATH_SITE . '/' . 'components' . '/' . 'com_invitex' . '/' . 'helper.php';
			require_once $helperPath;
			$invhelperObj = new cominvitexHelper;
			$invitex_settings	= $invhelperObj->getconfigData();

			$refid	=	$_COOKIE['refid'];
			$query = "SELECT e.id as refid,e.invitee_email,e.invitee_name, u.id, u.email as inviter_email, i.message,
			 e.expires,i.invite_type,i.invite_url,i.invite_type_tag
					FROM #__invitex_imports_emails AS e
					LEFT JOIN #__invitex_imports AS i ON e.import_id = i.id
					LEFT JOIN #__users AS u ON i.inviter_id = u.id
					WHERE MD5(e.id) = '$refid' ";

			$db->setQuery($query);
			$connection_data = $db->loadObjectList();
			$connection_data = $connection_data[0];
			$inviter_id = $connection_data->id;
			$expires = $connection_data->expires;
			$invitee_name = $connection_data->invitee_name;
			$invitee_mail = $connection_data->invitee_email;
			$message = $connection_data->message;
			$invite_type = (INT) $connection_data->invite_type;
			$invite_type_tag = $connection_data->invite_type_tag;
			$original_id	=	$connection_data->refid;

			$mail = $invhelperObj->getMailtagsinarray(
			$inviter_id, $original_id, $message, $invitee_mail, $invitee_name, $expires, $invite_type, $invite_type_tag, 1
			);

			$mail['msg_body'] = $article->text;
			$message_body = $invhelperObj->tagreplace($mail);
			$article->text = $message_body;
		}
		elseif (isset($_COOKIE['inviter_id']) && $_COOKIE['inviter_id'] != '')
		{
			$helperPath = JPATH_SITE . '/' . 'components' . '/' . 'com_invitex' . '/' . 'helper.php';
			require_once $helperPath;
			$invhelperObj = new cominvitexHelper;
			$invitex_settings	= $invhelperObj->getconfigData();
			$itemid	=	$invhelperObj->getitemid('index.php?option=com_invitex&view=invites');

			$inviter_id	=	$_COOKIE['inviter_id'];

			$query = " SELECT * FROM #__users where md5(id)='" . $inviter_id . "'";
			$db->setQuery($query);
			$connection_data = $db->loadObjectList();

			$connection_data = $connection_data[0];
			$inviter_id = $connection_data->id;
			$expires = '';
			$invitee_name = '';
			$invitee_mail = '';
			$message = '';
			$invite_type = '';
			$invite_type_tag = '';
			$original_id = $connection_data->id;

			$mail = $invhelperObj->getMailtagsinarray(
			$inviter_id, $original_id, $message, $invitee_mail, $invitee_name, $expires, $invite_type, $invite_type_tag, 1
			);
			$sign_up_base_url = "index.php?option=com_invitex&task=sign_up&Itemid=";
			$sign_up_URL	=	JRoute::_($sign_up_base_url . $itemid . "&inviter_id=" . md5($inviter_id) . "&custom_landing_page_visited=1");
			$mail['message_register'] = '<a class="btn btn-success" href="' . $sign_up_URL . '">' . JText::_('SIGN_UP') . '</a>';
			$mail['msg_body'] = $article->text;
			$message_body = $invhelperObj->tagreplace($mail);
			$article->text = $message_body;
		}
	}
}
