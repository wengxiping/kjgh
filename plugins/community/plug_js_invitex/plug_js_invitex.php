<?php
/**
 * @version     SVN: <svn_id>
 * @package     Invitex
 * @subpackage  mod_inviters
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

class plgCommunityPlug_js_invitex extends CApplications
{
	function getItemIdInv()
	{
		$db = JFactory::getDbo();
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

	function onBeforeControllerCreate( &$controllerClassName )
	{
		/*
		In this example, we replace default controller object with our own custom class.
		*/
		$mainframe = JFactory::getApplication();
		$task = JFactory::getApplication()->input->get('task');

		if ($controllerClassName == 'CommunityFriendsController')
		{
			switch ($task)
			{
				case 'invite':
				$mainframe->redirect(JRoute::_('index.php?option=com_invitex&view=invites&Itemid=' . $this->getItemIdInv()));

				break;
			}
		}
	}
}
