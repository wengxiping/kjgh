<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * InviteX invites controller
 *
 * @since  1.0
 */
class InvitexControllerInvites extends InvitexController
{
	/**
	 * Function to cancel sending invitation
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_invitex');
	}

	/**
	 * Function to remove
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function remove()
	{
		$input = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$cid = $input->get('cid', '', 'ARRAY');
		$importsid = $input->get('imports', '', 'ARRAY');
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($importsid);

		if ($this->getModel('invites')->remove($cid, $importsid))
		{
			$msg = JText::_("COM_INVITEX_DEL_SUCCESS_MSG");
		}
		else
		{
			$msg = JText::_("COM_INVITEX_DEL_FAIL_MSG");
		}

		$this->setRedirect(JURI::base() . "index.php?option=com_invitex&view=invites", $msg);
	}
}
