<?php
/**
 * @package    InviteX
 * @copyright  Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access');

// We want to import our app library
Foundry::import('admin:/includes/apps/apps');

/**
 * Some application for EasySocial. Take note that all classes must be derived from the `SocialAppItem` class
 *
 * @since  1.0
 */
class SocialUserAppEasysocial_Invitex extends SocialAppItem
{
	/**
	 * Class constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct()
	{
		// Load language file for plugin
		$lang = JFactory::getLanguage();
		$extension = 'com_invitex';
		$base_dir = JPATH_SITE;
		$lang->load($extension, $base_dir);
		$lang->load('plg_app_user_easysocial_invitex', JPATH_ADMINISTRATOR);
		JHtml::_('behavior.modal');
		require_once JPATH_SITE . '/components/com_invitex/helper.php';
		$cominvitexHelper = new cominvitexHelper;

		jimport('joomla.filesystem.file');
		$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

		if (JFile::exists($tjStrapperPath))
		{
			require_once $tjStrapperPath;
			TjStrapper::loadTjAssets('com_invitex');
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'media/com_invitex/css/invitex.css');
		$document->addStyleSheet(JUri::base() . 'media/com_invitex/css/bootstrap-tokenfield.min.css');
		$document->addStyleSheet(JUri::base() . 'media/com_invitex/css/tokenfield-typeahead.min.css');

		parent::__construct();

		// We want the user object from EasySocial so we can do funky stuffs.
	}

	/**
	 * Triggers the preparation of stream.
	 *
	 * If you need to manipulate the stream object, you may do so in this trigger.
	 *
	 * @return	none
	 */
	public function onAfterStorySave()
	{
	}

	/**
	 * Triggers the preparation of activity logs which appears in the user's activity log.
	 *
	 * @param   Object  &$item           SocialStreamItem	The stream object.
	 * @param   bool    $includePrivacy  Determines if we should respect the privacy
	 *
	 * @return	none
	 */
	public function onPrepareActivityLog( SocialStreamItem &$item, $includePrivacy = true )
	{
	}

	/**
	 * Triggers after a like is saved.
	 *
	 * This trigger is useful when you want to manipulate the likes process.
	 *
	 * @param   Object  &$likes  SocialTableLikes	The likes object.
	 *
	 * @return	none
	 */
	public function onAfterLikeSave( &$likes )
	{
	}

	/**
	 * Triggered when a comment save occurs.
	 *
	 * This trigger is useful when you want to manipulate comments.
	 *
	 * @param   Object  &$comment  SocialTableComments	 The comment object
	 *
	 * @return  void
	 *
	 * @since	1.0
	 */
	public function onAfterCommentSave( &$comment )
	{
	}
}
