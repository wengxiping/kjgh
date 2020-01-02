<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 March 2012
 * @file name	:	helpers/user.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class UserHelper {
	
	protected static $_online 		= null;
	protected static $jbprofile 	= array();
	protected static $jbusergroup 	= array();
	
	public function __construct($identifier = 0) {
		$this->_app = JFactory::getApplication ();
		$this->_session_timeout = time() - $this->_app->get('lifetime', 15) * 60;
	}
	
	public static function getUser($user_id){
		$profile_list = self::getJbUserList();
		
		$user = $profile_list[$user_id];
		
		return $user;
		
		/* $db = JFactory::getDbo();
		$query = "SELECT ju.*, u.name, u.username, u.email FROM #__jblance_user ju".
				 " LEFT JOIN #__users u ON u.id=ju.user_id". 
				 " WHERE ju.user_id=".$db->quote($userid);
		$db->setQuery($query);
		$user = $db->loadObject();
		return $user; */
	}
	
	public function getUserGroupInfo($userid = null, $groupid = null){
		$db = JFactory::getDbo();
		
		if(!empty($userid)){
			$userType = self::getUserType($userid);
			if($userType->guest || $userType->joomlauser)
				return null;
		}
		
		if(!empty($userid)){		// get the group info by user id
			/* $query = "SELECT ug.id,ug.name,ug.approval,ug.params FROM #__jblance_user u
					  LEFT JOIN #__jblance_usergroup ug ON u.ug_id = ug.id
					  WHERE u.user_id = ".$db->quote($userid)." AND ug.published=1"; */
			$profile_list = self::getJbUserList();
			$info = $profile_list[$userid];
		}
		elseif(!empty($groupid)){	// get the group info by group id
			/* $query = "SELECT ug.id,ug.name,ug.approval,ug.params,ug.skipPlan FROM #__jblance_usergroup ug
					  WHERE ug.id = ".$db->quote($groupid)." AND ug.published=1"; */
			$usergroup_list = self::getJbUsergroupList();
			$info = $usergroup_list[$groupid];
		}
		else 
			return null;
		
		//$db->setQuery($query);
		//$info = $db->loadObject();
		
		//convert the params to object
		$registry = new JRegistry;
		$registry->loadString($info->ug_params);
		$params = $registry->toObject();
		
		//bind the $params object to $plan and make one object
		foreach($params as $k => $v){
			$info->$k = $v;
		}
		
		return $info;
	}
	
	public function getPlanInfo($planId){
		$db = JFactory::getDbo();
		
		// get the group info by user id
		$query = "SELECT p.id, p.ug_id, p.params FROM #__jblance_plan p ".
				 "WHERE p.id = ".$db->quote($planId);
		$db->setQuery($query);
		$plan = $db->loadObject();
		
		//convert the params to object
		$registry = new JRegistry;
		$registry->loadString($plan->params);
		$params = $registry->toObject();
		
		//bind the $params object to $plan and make one object
		foreach($params as $k => $v){
			$plan->$k = $v;
		}
		return $plan;
	}
	
	 public function getSearchUserLayout($userid){
		
		//get the billing fields
 		$config = JblanceHelper::getConfig();
		$fields = JblanceHelper::get('helper.fields');	// create an instance of fieldsHelper class
		
		$obj			  = new stdClass();
		$obj->position	  = $fields->getFieldValue($config->searchResPosition, $userid);
		$obj->degreeLevel = $fields->getFieldValue($config->searchResDegLevel, $userid);
	
		return $obj;
	}
	
	public function isOnline($userid){
		$online = false;
		$onlineList = self::getOnlineUsers();
		
		if(self::$_online === null){
			self::getOnlineUsers();
		}
		$online = isset($onlineList [$userid]) ? ($onlineList [$userid]->time > $this->_session_timeout) : false;
		
		return $online ? true : false;
	}
	
	public static function getOnlineUsers(){
		if(self::$_online === null) {
			$db = JFactory::getDbo();
			$query = "SELECT s.userid, s.time FROM #__session s ".
					  "INNER JOIN #__jblance_user u ON u.user_id = s.userid ".
					  "WHERE s.client_id = 0 AND s.userid > 0 ".
					  "GROUP BY s.userid ".
					  "ORDER BY s.time DESC";
			$db->setQuery($query);
			self::$_online = $db->loadObjectList('userid');
		}
		return self::$_online;
	}
	
	public function getUserType($user_id){
		$user 				   = JFactory::getUser($user_id);
		$user_type 			   = new stdClass();
		$user_type->buyer 	   = false;
		$user_type->freelancer  = false;
		$user_type->guest 	   = false;
		$user_type->joomlauser  = false;		//this means the user is only a Joomla user and doesn't have JoomBri Profile
		$user_type->joombriuser = false;		//this means the user has JoomBri Profile
		
		$user_type->userid = $user_id;
		
		if($user->guest){
			$user_type->guest = true;
			return $user_type;
		}
		else {
			if(!self::hasJbprofile($user_id)){
				$user_type->joomlauser = true;
				return $user_type;
			}
		}
		$userGroup = self::$jbprofile[$user_id];
		
		//convert the params to object
		$registry = new JRegistry;
		$registry->loadString($userGroup->ug_params);
		$params = $registry->toObject();
		
		if($params->allowPostProjects){
			$user_type->buyer = true;
		}
				
		if($params->allowBidProjects){
			$user_type->freelancer = true;
		}
		
		if($params->allowPostProjects || $params->allowBidProjects){
			$user_type->joombriuser = true;
		}
		
		return $user_type;
	}
	
	public function hasJbprofile($user_id){
		$has_profile = false;
		$profile_list = self::getJbUserList();
		
		$has_profile = isset($profile_list[$user_id]) ? true : false;
		
		return $has_profile;
	}
	
	public static function getJbUserList(){
		if(empty(self::$jbprofile)){
			$db = JFactory::getDbo();
			$query = "SELECT ju.*, u.name, u.username, u.email, ug.id ug_id, ug.name ug_name, ug.approval, ug.params ug_params FROM #__jblance_user ju ".
					 "LEFT JOIN #__users u ON u.id = ju.user_id ".
					 "LEFT JOIN #__jblance_usergroup ug ON ju.ug_id = ug.id";
			$db->setQuery($query);
			self::$jbprofile = $db->loadObjectList('user_id');
		}
		return self::$jbprofile;
	}
	
	public static function getJbUsergroupList(){
		if(empty(self::$jbusergroup)){
			$db = JFactory::getDbo();
			$query = "SELECT ug.id, ug.name ug_name, ug.approval, ug.params ug_params, ug.skipPlan FROM #__jblance_usergroup ug ".
					 "WHERE ug.published=1";
			$db->setQuery($query);
			self::$jbusergroup = $db->loadObjectList('id');
		}
		return self::$jbusergroup;
	}
}
