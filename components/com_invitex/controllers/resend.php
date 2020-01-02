<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Invitex Resend controller
 *
 * @since  1.0.0
 */
class InvitexControllerResend extends InvitexController
{
	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct ()
	{
		parent::__construct();
		$this->invhelperObj = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
	}

	/**
	 * Resend invitation
	 *
	 * @return  null
	 */
	public function resend()
	{
		JSession::checkToken() or die('Invalid Token');

		$model = $this->getModel('resend');
		$result = $model->resend();
		$itemid = $this->invhelperObj->getitemid('index.php?option=com_invitex&view=resend');

		if (!empty($result))
		{
			$msg = JText::_('INVITE_SUCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_invitex&view=resend&Itemid=' . $itemid, false), $msg);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_invitex&view=resend&Itemid=' . $itemid, false));
		}
	}
}
