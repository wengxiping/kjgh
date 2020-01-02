<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	07 June 2012
 * @file name	:	helpers/integration/communitybuilder/profile.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');

class JoombriProfileCommunityBuilder extends JoombriProfile
{
	protected $integration = null;

	public function __construct() {
		$this->integration = JoombriIntegration::getInstance ('communitybuilder');
		if (! $this->integration || ! $this->integration->isLoaded())
			return;
		$this->priority = 50;
	}

	public function open()
	{
		$this->integration->open();
	}

	public function close()
	{
		$this->integration->close();
	}

/* TODO: do we need this anymore:
	public function getForumTabURL()
	{
		return cbSef( 'index.php?option=com_comprofiler&amp;tab=getForumTab' . getCBprofileItemid() );
	}
*/

	public function getUserListURL($action='', $xhtml = true){	
		return cbSef('index.php?option=com_comprofiler&amp;task=usersList', $xhtml);
	}

	public function getProfileURL($userid, $task='', $xhtml = true){
		if ($userid == 0) return false;
		// Get CUser object
		$cbUser = CBuser::getInstance($userid);
		if($cbUser === null) return false;
		return cbSef( 'index.php?option=com_comprofiler&task=userProfile&user='.$userid.getCBprofileItemid(), $xhtml);
	}

	public function showProfile($user, &$msg_params){
		global $_PLUGINS;

		$kunenaConfig = KunenaFactory::getConfig();
		$user = KunenaFactory::getUser($user);
		$_PLUGINS->loadPluginGroup('user');
		return implode( '', $_PLUGINS->trigger( 'forumSideProfile', array( 'kunena', null, $user->userid,
			array( 'config'=> &$kunenaConfig, 'userprofile'=> &$user, 'msg_params'=>&$msg_params) ) ) );
	}

	public function trigger($event, &$params)
	{
		return $this->integration->trigger($event, $params);
	}

	public function getEditURL(){
		return cbSef('index.php?option=com_comprofiler&task=userDetails'.getCBprofileItemid());
	}
}
