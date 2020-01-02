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

require_once(__DIR__ . '/formatter.php');

class PPAppJusertype extends PPApp
{
	protected $_location = __FILE__;
	protected $_resource = 'com_user.usergroup';

	/**
	 * Trigger event after user subscription is saved
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function onPayplansSubscriptionAfterSave($prevSubscription, $newSubscription)
	{
		return $this->processSubscription($prevSubscription, $newSubscription);
	}

	/**
	 * Process user subscription
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function processSubscription($prevSubscription, $newSubscription)
	{
		// no need to trigger if previous and current state is same
		if ($prevSubscription != null && $prevSubscription->getStatus() == $newSubscription->getStatus()) {
			return true;
		}

		$subid =  $newSubscription->getId();

		$usersConfig = JComponentHelper::getParams('com_users');
		$defaultUserGroup = $usersConfig->get('new_usertype');

		$active = $this->getAppParam('jusertypeOnActive', array());
		$hold = $this->getAppParam('jusertypeOnHold', array());
		$expire = $this->getAppParam('jusertypeOnExpire', array());
		$removeFromDefault = $this->getAppParam('removeFromDefault', 0);

		$user =  $newSubscription->getBuyer();
		$userid = $user->getId();

		$active = (is_array($active)) ? $active : array($active);
		$hold = (is_array($hold)) ? $hold : array($hold);
		$expire = (is_array($expire)) ? $expire : array($expire);
		$groups = $this->helper->getAllUserGroups();

		// Process active subscription
		if ($newSubscription->isActive()) {

			$holdActiveDiff = array_diff($hold, $active);
			$expireActiveDiff = array_diff($expire, $active);

			$result = $this->setJusertype($userid, $active, $subid, $groups);

			$this->unsetJusertype($userid, $holdActiveDiff, $subid, $groups);
			$this->unsetJusertype($userid, $expireActiveDiff, $subid, $groups);

			if ($removeFromDefault && !in_array($defaultUserGroup, $active)) {
				$this->helper->removeUserFromGroup($userid, $defaultUserGroup);
			}

			//forcefully removes the user from provided user group, irrespective of plan subscription.
			$removeFromListActive = $this->getAppParam('removeFromGroup');

			if (!empty($removeFromListActive)) {
				return $this->helper->removeForcefully($userid, $removeFromListActive, $subid, $groups);
			}

			return $result;
		}

		// Process on hold subscription
		if ($newSubscription->isOnHold()) {

			$activeHoldDiff = array_diff($active, $hold);
			$expireHoldDiff = array_diff($expire, $hold);

			if ($hold[0] == null && $this->helper->isRequiredDefault($userid)) {
				$hold[0] = $defaultUserGroup;
			}

			$result = $this->setJusertype($userid, $hold, $subid, $groups);

			$this->unsetJusertype($userid, $activeHoldDiff, $subid, $groups);
			$this->unsetJusertype($userid, $expireHoldDiff, $subid, $groups);

			return $result;
		}

		// Process expired subscription
		if ($newSubscription->isExpired()) {

			$activeExpireDiff = array_diff($active, $expire);
			$holdExpireDiff = array_diff($hold, $expire);

			if ($expire[0] == null && $this->helper->isRequiredDefault($userid)) {
				$expire[0] = $defaultUserGroup;
			}

			$result = $this->setJusertype($userid, $expire, $subid, $groups);

			$this->unsetJusertype($userid, $activeExpireDiff, $subid, $groups);
			$this->unsetJusertype($userid, $holdExpireDiff, $subid, $groups);

			return $result;
		}

		return true;
	}

	/**
	 * Assign joomla user type to the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setJusertype($userid, $group, $subid, $groups)
	{
		if (!is_array($group) || empty($group)) {
			return true;
		}

		foreach ($group as $groupid) {
			$this->helper->addUserToGroup($userid, $groupid);
			PP::resource()->add($subid, $userid, $groupid, $this->_resource);

			$message = JText::_("COM_PAYPLANS_APP_JUSERTYPE_LOG_ADD_INTO_USERGROUP");
			$content = array('User Name' => $userid, 'Usergroup' => $groups[$groupid]->title,'Subscription Id' => $subid);

			PP::logger()->log(PPLogger::LEVEL_INFO, $message, $this->getId(), 'SYSTEM', $content, 'PayplansAppJusertypeFormatter', md5(serialize($content)));
		}

		return true;
	}

	/**
	 * Unset joomla user type from the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function unsetJusertype($userid, $jusertype, $subid, $groups)
	{
		if (!is_array($jusertype)) {
			return true;
		}

		foreach ($jusertype as $group) {
			if (PP::resource()->remove($subid, $userid, $group, $this->_resource)) {
				$this->helper->removeUserFromGroup($userid, $group);

				$message = JText::_("COM_PAYPLANS_APP_JUSERTYPE_LOG_REMOVE_FROM_USERGROUP");
				$content = array('User Name'=>$userid, 'Usergroup' => $groups[$group]->title, 'Subscription Id'=> $subid);

				PP::logger()->log(PPLogger::LEVEL_INFO, $message, $this->getId(), 'SYSTEM', $content, 'PayplansAppJusertypeFormatter', md5(serialize($content)));
			}
		}

		return true;
	}

	/**
	 * Retrieve the name from the resource
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getNameFromResourceValue($resource, $value)
	{
		// if its a different resource
		if ($resource != $this->_resource) {
			return false;
		}

		$groups = $this->helper->getAllUserGroups();
		return $groups[$value]->title;
	}

	/**
	 * Trigger during cleaning the resources such as deleting subscriptions order
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionCleanResource($sub)
	{
		$userid = $sub->getBuyer();
		$sub_id = $sub->getId();
		$groups = $this->helper->getAllUserGroups();

		$options = array();
		$options['subscription_ids'] = $sub_id;
		$options['title'] = $this->_resource;

		$resourcesModel = PP::model('resource');
		$resources = $resourcesModel->getRecords($options);

		$usersConfig = JComponentHelper::getParams('com_users');
		$defaultUserGroup = $usersConfig->get('new_usertype');
		
		//Imp : before unseting usergroup ensure that user must have any other usergroup attached
		//if no other usergroup is attached apart from the one which is going to be unset then add user to default usergroup
		foreach ($resources as $res) {
			$subscription_ids = explode(',', JString::trim($res->subscription_ids, ','));
			$subscription_ids = array_unique($subscription_ids);

			if (count($subscription_ids) != 1){
				PP::resource()->remove($sub_id, $userid, $res->value, $this->_resource);
				continue;
			}

			$assignedGroups = $this->helper->getJoomlaUserGroups($userid);

			if (count($assignedGroups) == 1 && $assignedGroups[0] == $defaultUserGroup && $assignedGroups[0] == $res->value){
				PP::resource()->remove($sub_id, $userid, $res->value, $this->_resource);
				continue;
			}
			
			if (count($assignedGroups) == 1 && $res->value != $defaultUserGroup){
				$this->helper->setUserGroups($userid, $defaultUserGroup);
			}
			$this->unsetJusertype($userid, array($res->value), $sub_id, $groups);
		}
		
		return true;
	}
}
