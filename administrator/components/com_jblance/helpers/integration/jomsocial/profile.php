<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	07 June 2012
 * @file name	:	helpers/integration/jomsocial/profile.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

class JoombriProfileJomSocial extends JoombriProfile {
	protected $integration = null;

	public function __construct() {
		$this->integration = JoomBriIntegration::getInstance ('jomsocial');
		if (! $this->integration || ! $this->integration->isLoaded())
			return;
		$this->priority = 50;
	}

	public function getUserListURL($action='', $xhtml = true){
		return CRoute::_('index.php?option=com_community&view=search&task=browse', $xhtml);
	}

	public function getProfileURL($userid, $task='', $xhtml = true){
		if ($userid == 0) return false;
		// Get CUser object
		$user = CFactory::getUser($userid);
		if($user === null) return false;
		return CRoute::_('index.php?option=com_community&view=profile&userid='.$userid, $xhtml);
	}

	public function getEditURL(){
		return CRoute::_('index.php?option=com_community&view=profile&task=edit');
	}

	public function showProfile($userid, &$msg_params) {}
}
