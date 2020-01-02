<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * Config view controller
 *
 * @package  InviteX
 *
 * @since    1.0
 */
class InvitexControllerConfig extends InvitexController
{
	/**
	 * Saves a menu item
	 *
	 * @return  null
	 */
	public function save()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');
		$flag = 0;
		$cache = JFactory::getCache('com_invitex');
		$cache->clean();

		$model = $this->getModel('config');
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		// Allow name only to contain html
		$post['name'] = JFactory::getApplication()->input->get('name', '', 'post', 'string', JREQUEST_ALLOWHTML);
		$model->setState('request', $post);

		if ($model->store())
		{
			$msg = JText::_('INV_DATA_SAVED');
		}
		else
		{
			$msg = JText::_('INV_ERROR_SAVING_DATA');
		}

		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);
		$this->setRedirect('index.php?option=com_invitex&view=config&layout=templates', $msg);
	}

	/**
	 * Method to cancel config changes
	 *
	 * @return ''
	 *
	 * @since 2.9
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_invitex', $msg);
	}
}
