<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	07 June 2012
 * @file name	:	helpers/integration/communitybuilder/avatar.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

class JoombriAvatarCommunityBuilder extends JoombriAvatar
{
	protected $integration = null;

	public function __construct() {
		$this->integration = JoombriIntegration::getInstance ('communitybuilder');
		if (! $this->integration || ! $this->integration->isLoaded())
			return;
		$this->priority = 50;
	}

	public function load($userlist) {
		if (method_exists('CBuser','advanceNoticeOfUsersNeeded')) {
			CBuser::advanceNoticeOfUsersNeeded($userlist);
		}
	}

	public function getEditURL()
	{
		return cbSef( 'index.php?option=com_comprofiler&task=userAvatar' . getCBprofileItemid() );
	}

	protected function _getURL($userid, $type)
	{
		global $_CB_framework;
		$app = JFactory::getApplication ();

		if ( $app->getClientId() == 0 ) $cbclient_id = 1;
		if ( $app->getClientId() == 1 ) $cbclient_id = 2;
		$_CB_framework->cbset( '_ui',  $cbclient_id );
		// Get CUser object
		$cbUser = null;
		if($userid){
			$cbUser = CBuser::getInstance($userid);
		}
		if ( $cbUser === null ) {
			//if ($sizex<=90) return selectTemplate() . 'images/avatar/tnnophoto_n.png';
			return selectTemplate() . 'images/avatar/nophoto_n.png';
		}
		//if ($sizex<=90) return $cbUser->getField( 'avatar' , null, 'csv' );
		return $cbUser->getField( 'avatar' , null, 'csv', 'none', 'profile' );
	}
}
