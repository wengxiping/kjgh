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
 * InviteX reminder model
 *
 * @since  1.6.1
 */
class InvitexControllerReminder extends InvitexController
{
	/**
	 * Function to send reminder
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function send_reminder()
	{
		$model = $this->getModel('reminder');

		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		if ($model->send_reminder($post))
		{
			$msg = JText::_('REMINDER_SENT');
		}
		else
		{
			$msg = JText::_('WENT_WRONG');
		}

		$this->setRedirect(JURI::base() . "index.php?option=com_invitex&view=reminder", $msg);
	}
}
