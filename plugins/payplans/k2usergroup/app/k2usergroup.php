<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPAppK2Usergroup extends PPApp
{
	protected $_location = __FILE__;

	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventName = '')
	{
		return $this->helper->exists();
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// No need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()){
			return true;
		}

		$newStatus = $new->getStatus();
		$user = $new->getBuyer();
		
		// if subscription is active
		if($new->isActive()){
			$groupid = $this->getAppParam('addToGroupOnActive');
			return $this->helper->addToGroup($user->getId(), $groupid);
		}
		
		//if subscription is hold then add user to 
		if($new->isOnHold()){
			$groupid = $this->getAppParam('addToGroupOnHold');
			return $this->helper->addToGroup($user->getId(), $groupid);
		}
		
		//if subscription is expire
		if ($new->isExpired()){
			$groupid = $this->getAppParam('addToGroupOnExpire');
			return $this->helper->addToGroup($user->getId(), $groupid);
		}

		return true;
	}
}

class PayplansAppK2Formatter extends PayplansAppFormatter
{
	public $template = 'view_log';
	
	public function getVarFormatter()
	{
		$rules = array('_appplans' => array('formatter'=> 'PayplansAppFormatter',
											'function' => 'getAppPlans'),
						'app_params' => array('formatter'=> 'PayplansAppK2Formatter',
											'function' => 'getFormattedParams'));
		return $rules;
	}
	
	public function getFormattedParams($key,$value,$data)
	{
		//do nothing if k2 is not installed
		if (!JFolder::exists(JPATH_SITE . '/components/com_k2')){
			return false;
		}
		
		$value['addToGroupOnActive'] = $this->getGroupName($value['addToGroupOnActive']);
		$value['addToGroupOnHold'] = $this->getGroupName($value['addToGroupOnHold']);
		$value['addToGroupOnExpire'] = $this->getGroupName($value['addToGroupOnExpire']);
		
		$this->template = 'view';
	}
	
	public function getGroupName($values)
	{
		$groups = $this->getK2UserGroups();
		
		foreach ($values as $value){
			$group[] = $groups[$value]->name;
		}
		
		return implode(', ', $group);
	}

	public function getK2UserGroups()
	{
		$db = PP::db();
		$query = 'SHOW COLUMNS FROM ' . $db->qn('#__k2_user_groups') . ' LIKE ' . $db->Quote('groups_id');

		$db->setQuery($query);
		$result = $db->loadResult();

		$column = $result ? 'groups_id' : 'id';

		$query = 'SELECT ' . $db->qn($column) . ' as groups_id, ' . $db->qn('name') . ' FROM ' . $db->qn('#__k2_user_groups');

		$db->setQuery($query);
		
		return $db->loadObjectList('groups_id');
	}
}
