<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.html.html');
jimport('joomla.application.module.helper');

JLoader::import('libraries.joomla.form.fields.textarea', JPATH_SITE);

/**
 * Form Field class for Invite type widget.
 *
 * @since  1.0
 */
class JFormFieldInvitetypeswidget extends JFormFieldTextarea
{
	/**
	 * Get field input
	 *
	 * @return  HTML
	 *
	 * @since  1.0
	 */
	protected function getInput()
	{
		$return = "";
		$db = JFactory::getDbo();
		$sql = "SELECT published,params FROM #__modules where module='mod_invite_anywhere'";
		$db->setQuery($sql);
		$res = $db->loadObject();

		if ($res->published)
		{
			$params = json_decode($res->params);

			if ($params)
			{
				$sql = "SELECT widget FROM #__invitex_types where id=$params->invite_type";
				$db->setQuery($sql);
				$res = $db->loadResult();

				$return = '<textarea cols="50" rows="10">' . htmlspecialchars($res, ENT_COMPAT, 'UTF-8') . '</textarea>';

				return $return;
			}
			else
			{
				$return = '<textarea cols="50" rows="10">' . JText::_('Module is not published..') . '</textarea>';

				return $return;
			}
		}
		else
		{
			$return = '<textarea cols="50" rows="10">' . JText::_('Module is not published..') . '</textarea>';

			return $return;
		}
	}
}
