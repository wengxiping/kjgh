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
 * InviteX invites limit controller
 *
 * @since  1.0
 */
class InvitexControllerInvitation_Limit extends InvitexController
{
	/**
	 * Function to update invitation limit
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function update_limit()
	{
		$model = $this->getModel('invitation_limit');

		if ($model->update_invitation_limit(0))
		{
			$msg = JText::_('LIMIT_UPDATED_SUCCESSFULLY');
		}
		else
		{
			$msg = JText::_('LIMIT_NOT_UPDATED');
		}

		$this->setRedirect(JURI::base() . "index.php?option=com_invitex&view=invitation_limit", $msg);
	}

	/**
	 * Function to update invitation limit
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function batch_process()
	{
		$model = $this->getModel('invitation_limit');

		if ($model->update_invitation_limit(1))
		{
			$msg = JText::_('LIMIT_UPDATED_SUCCESSFULLY');
		}
		else
		{
			$msg = JText::_('LIMIT_NOT_UPDATED');
		}

		$this->setRedirect(JURI::base() . "index.php?option=com_invitex&view=invitation_limit", $msg);
	}
}
