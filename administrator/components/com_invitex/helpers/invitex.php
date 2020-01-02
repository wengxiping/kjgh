<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

/**
 * Invitex Helper
 *
 * @package     Com_Invitex
 *
 * @subpackage  site
 *
 * @since       1.0
 */
class InvitexHelper
{
	public static $extension = 'com_invitex';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return void
	 */
	public static function addSubmenu($vName='')
	{
		$cp = $statistics = $user = $config = $types = $invitation_limit = '';
		$unsubscribe_list = $reminder = $dashboard = $topinviters = $invites = $urlInvites = '';

		switch ($vName)
		{
			case 'statistics':
				$statistics	=	true;
			break;
			case 'users':
				$user	=	true;
			break;
			case 'config':
				$config	=	true;
			break;
			case 'types':
				$types	=	true;
			break;
			case 'invitation_limit':
				$invitation_limit	=	true;
			break;
			case 'unsubscribe_list':
				$unsubscribe_list	=	true;
			break;
			case 'reminder':
				$reminder	=	true;
			break;
			case 'dashboard':
				$dashboard	=	true;
			break;

			case 'topinviters':
				$topinviters	=	true;
			break;
			case 'invites':
				$invites	=	true;
			break;
			case 'urlinvites':
				$urlInvites	=	true;
			break;
		}

		JHtmlSidebar::addEntry(JText::_('DASHBOARD'), 'index.php?option=com_invitex', $dashboard);
		JHtmlSidebar::addEntry(JText::_('COM_INVITEX_INVITES'), 'index.php?option=com_invitex&view=invites', $invites);
		JHtmlSidebar::addEntry(JText::_('COM_INVITEX_URL_INVITES'), 'index.php?option=com_invitex&view=urlinvites', $urlInvites);
		JHtmlSidebar::addEntry(JText::_('TOP_INVITERS'), 'index.php?option=com_invitex&view=topinviters', $topinviters);
		JHtmlSidebar::addEntry(JText::_('TEMPLATES'), 'index.php?option=com_invitex&view=config&layout=templates', $config);
		JHtmlSidebar::addEntry(JText::_('TYPES'), 'index.php?option=com_invitex&view=types', $types);
		JHtmlSidebar::addEntry(JText::_('INV_LIMIT'), 'index.php?option=com_invitex&view=invitation_limit', $invitation_limit);
		JHtmlSidebar::addEntry(JText::_('UNSUB_LIST'), 'index.php?option=com_invitex&view=unsubscribe_list', $unsubscribe_list);
		JHtmlSidebar::addEntry(JText::_('REMINDER'), 'index.php?option=com_invitex&view=reminder', $reminder);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_invitex';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
