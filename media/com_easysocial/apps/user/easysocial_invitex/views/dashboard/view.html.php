<?php
/**
 * @package     InviteX
 * @subpackage  Easysocial_invitex_profile
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2018 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Unauthorized Access');

/**
 * Profile Embed view for Textbook app
 *
 * @since  1.0
 */
class Easysocial_InvitexViewDashboard extends SocialAppsView
{
	/**
	 * This method is invoked automatically and must exist on this view.
	 *
	 * The contents displayed here will be returned via an AJAX call from the system.
	 *
	 * @param   int  $userId  The user id that is currently being viewed.
	 *
	 * @since	1.0.0
	 *
	 * @return 	void
	 */
	public function display($userId)
	{
		// Assign the textbooks to the theme files.

		// This option is totally optional, you can use your own theme object to output files.
		$path = JPATH_ROOT . '/components/com_invitex/models/invites.php';

		$lang = JFactory::getLanguage();
		$extension = 'com_invitex';
		$base_dir = JPATH_SITE;

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

		// Requires the viewer to be logged in to access this app
		Foundry::requireLogin();
		$namespace            = 'dashboard/default';
		echo parent::display($namespace);
		$this->invhelperObj   = new cominvitexHelper;
		$this->invitex_params = $this->invhelperObj->getconfigData();
		$model                = $this->getModel('Easysocial_Invitex');

		// Get the Invitex view
		$result = $model->getInvitexView($userId);
	}
}
