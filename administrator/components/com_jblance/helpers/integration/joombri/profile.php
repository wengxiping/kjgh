<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	07 June 2012
 * @file name	:	helpers/integration/joombri/profile.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

class JoombriProfileJoombri extends JoombriProfile {
	public function __construct() {
		$this->priority = 25;
	}

	public function getUserListURL($action='', $xhtml = true){
		
		return JRoute::_("index.php?option=com_jblance&view=user&layout=userlist", $xhtml);
	}

	public function getProfileURL($userid, $task='', $xhtml = true){
		if($userid == 0) return false;
		$my = JFactory::getUser();
		$id = ($my->id != $userid) ? "&id={$userid}" : '';
		return JRoute::_("index.php?option=com_jblance&view=user&layout=viewprofile{$id}", $xhtml);
	}

	public function getEditURL(){
		return JRoute::_('index.php?option=com_jblance&view=user&layout=editprofile');
	}

	public function showProfile($userid, &$msg_params) {}
}
