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
 * InviteX unsubscribed list controller
 *
 * @since  1.6
 */
class InvitexControllerUnsubscribe_List extends InvitexController
{
	/**
	 * Method to cancel adding user to unsubscribed list
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_invitex');
	}

	/**
	 * Method to add user to unsubscribed list
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function add()
	{
		$input = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$post = $input->post;
		$this->getModel('unsubscribe_list')->manage_UnsubList($post, 'add');
		$msg = JText::_("UNSUB_LIST_UPDATE_SUCCESS");
		$this->setRedirect("index.php?option=com_invitex&view=unsubsribe_list", $msg);
	}

	/**
	 * Method to remove user from unsubscribed list
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function remove()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;
		$this->getModel('unsubscribe_list')->manage_UnsubList($post, 'remove');
		$msg = JText::_("UNSUB_LIST_UPDATE_SUCCESS");
		$this->setRedirect('index.php?option=com_invitex&view=unsubsribe_list', $msg);
	}
}
