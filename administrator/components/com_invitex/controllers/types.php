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
 * Invitex Component model
 *
 * @since  1.5
 */
class InvitexControllerTypes extends InvitexController
{
	/**
	 * Saves a menu item
	 *
	 * @return array
	 */
	public function save()
	{
		$flag = 0;
		$cache = JFactory::getCache('com_invitex');
		$cache->clean();

		$model	= $this->getModel('types');

		if ($model->store())
		{
			$msg = JText::_('INV_DATA_SAVED');
		}
		else
		{
			$flag = 1;
			$msg = JText::_('INV_ERROR_SAVING_DATA');
		}

		if ($flag == 1)
		{
			$this->setRedirect('index.php?option=com_invitex&view=types', $msg, 'notice');
		}
		else
		{
			$this->setRedirect('index.php?option=com_invitex&view=types', $msg);
		}
	}

	/**
	 * Function to add
	 *
	 * @return  void
	 */
	public function add()
	{
		$this->setRedirect('index.php?option=com_invitex&view=types&task=new&layout=type');
	}

	/**
	 * Function to cancel
	 *
	 * @return redirect
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_invitex&view=types');
	}

	/**
	 * Function to remove
	 *
	 * @return  array
	 */
	public function remove()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		if ($post)
		{
			$db = JFactory::getDbo();

			foreach ($post['cid'] as $type_id)
			{
				$query = "DELETE FROM #__invitex_types where id=" . $type_id;
				$db->setQuery($query);

				if (JVERSION < 3.0)
				{
					$db->Query();
				}
				else
				{
					$db->execute();
				}
			}

			$msg = JText::_('COM_INVITEX_BODY_TYPE_REMOVED_SUCCESS');
			$this->setRedirect('index.php?option=com_invitex&view=types', $msg);
		}
	}
}
