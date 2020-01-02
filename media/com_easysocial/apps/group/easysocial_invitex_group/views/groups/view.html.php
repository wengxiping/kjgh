<?php
/**
 * @package     InviteX
 * @subpackage  Easysocial_invitex_event
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Profile view for invitex group invitation app
 *
 * @since  1.0
 */
class Easysocial_Invitex_GroupViewGroups extends SocialAppsView
{
	/**
	 * Method to initialize view
	 *
	 * @param   INT     $groupId  event id
	 *
	 * @param   STRING  $docType  document type
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($groupId = null , $docType = null)
	{
		$group = Foundry::group($groupId);

		// Check if the viewer is allowed here.
		$user = JFactory::getUser();

		if (!in_array($user->id, $group->members))
		{
			echo "<h4 class='easysocial_inv_msg'>" . JText::_('COM_INVITEX_ATTEND_GROUP_MSG') . "</h4>";

			return;
		}

		$grp_nm     = $group->getName();
		$group_link = $group->getPermalink();

		// Get app params
		$params = $this->app->getParams();

		// Assign the textbooks to the theme files.

		// This option is totally optional, you can use your own theme object to output files.

		$path = JPATH_ROOT . '/components/com_invitex/models/invites.php';
		$lang      = JFactory::getLanguage();
		$extension = 'com_invitex';
		$base_dir  = JPATH_SITE;

		$lang->load($extension, $base_dir);

		if (!class_exists('InvitexModelInvites'))
		{
			JLoader::register('InvitexModelInvites', $path);
			JLoader::load('InvitexModelInvites');
		}

		$pathhelper = JPATH_ROOT . '/components/com_invitex/helper.php';

		if (!class_exists('cominvitexHelper'))
		{
			JLoader::register('cominvitexHelper', $pathhelper);
			JLoader::load('cominvitexHelper');
		}

		$input              = JFactory::getApplication()->input;
		$document           = JFactory::getDocument();
		$this->invhelperObj = new cominvitexHelper;
		$invitex_params     = $this->invhelperObj->getconfigData();
		$this->set('invitex_params', $invitex_params);

		// Set the page title. You can use JFactory::getDocument()->setTitle( 'title' ) as well.

		// Load up the model
		$model = $this->getModel('Easysocial_Invitex_group');

		// Get the list of textbooks created by the user.
		$result = $model->getInvitexView($grp_nm, $group_link);

		echo parent::display('groups/default');
	}
}
