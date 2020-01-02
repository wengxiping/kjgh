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
 * InviteX EasySocial events invitation app view
 *
 * @since  1.0
 */
class Easysocial_Invitex_EventViewEvents extends SocialAppsView
{
	/**
	 * Method to initialize view
	 *
	 * @param   INT     $eventId  event id
	 *
	 * @param   STRING  $docType  document type
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($eventId = null, $docType = null)
	{
		// Load up the event
		$event = FD::event($eventId);

		$lang = JFactory::getLanguage();
		$lang->load('com_invitex', JPATH_SITE);

		// Only allow event members access here.
		if (!$event->getGuest()->isGuest())
		{
			echo "<h4 class='easysocial_inv_msg'>" . JText::_('COM_INVITEX_ATTEND_EVENT_MSG') . "</h4>";

			return;
		}

		$event_nm   = $event->getName();
		$event_link = $event->getPermalink();

		// This option is totally optional, you can use your own theme object to output files.
		$path      = JPATH_ROOT . '/components/com_invitex/models/invites.php';
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

		// Load up the model
		$model = $this->getModel('Easysocial_Invitex_event');

		// Get the list of textbooks created by the user.
		$result = $model->getInvitexView($event_nm, $event_link);

		echo parent::display('events/default');
	}
}
