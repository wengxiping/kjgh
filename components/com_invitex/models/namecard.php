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
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

jimport('joomla.database.database.mysql');

/**
 * Namecard model
 *
 * @since  1.0.0
 */
class InvitexModelNamecard extends JModelLegacy
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
	 * Function to get name card template
	 *
	 * @return  HTML  template
	 *
	 * @since   1.0
	 */
	public function getnamecardtemplates()
	{
		$namecard_tmpl = array();
		$directory = JPATH_SITE . '/components/com_invitex/views/namecard/namecard_templates';
		$templates = JFolder::files($directory, '.html');

		return $templates;
	}

	/**
	 * Function to get name card info
	 *
	 * @param   INT  $uid  user id.
	 *
	 * @return  ARRAY  An array of data items on success
	 *
	 * @since   1.0
	 */
	public function getnamecardinfo($uid)
	{
		$reg_direct = '';
		$reg_direct = $this->invitex_params->get('reg_direct');
		$nuser = JFactory::getUser($uid);
		$database = JFactory::getDBo();
		$namecard_user = array();
		$namecard_user['user_id'] = $nuser->id;
		$namecard_user['name'] = $nuser->name;
		$namecard_user['username'] = $nuser->username;
		$invURL = $this->invhelperObj->getnamecardinviteURL($uid);
		$namecard_user['invURL'] = $this->invhelperObj->givShortURL($invURL);
		$namecard_user['img_source'] = $this->invhelperObj->sociallibraryobj->getAvatar($nuser);
		$namecard_user['user_link'] = JRoute::_($this->invhelperObj->sociallibraryobj->getProfileUrl($nuser));

		return $namecard_user;
	}
}
