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

class PPHelperJusertype extends PPHelperStandardApp
{
	/**
	 * Check if need to assign default user type
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isRequiredDefault($userid)
	{
		$user = PP::user($userid);
		$userPlans = $user->getPlans();

		if (empty($userPlans)) {
			return true;
		}

		$userPlanIds = array();

		foreach ($userPlans as $plan) {
			$userPlanIds[] = $plan->plan_id;
		}

		// get all user type apps
		$userTypeApps = PPHelperApp::getAvailableApps('jusertype');

		foreach ($userTypeApps as $app) {
			$appPlans = $app->getPlans();

			//if any active plan has attached user type app, we don't need default type
			if (array_intersect($userPlanIds, $appPlans)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve user's Joomla user group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getJoomlaUserGroups($userId)
	{
		$model = PP::model('User');
		$usergroups = $model->getUserGroups($userId);

		return $usergroups;
	}

	/**
	 * Method to remove selected user group from the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeForcefully($userid, $removalList, $subid, $groups)
	{
		$removeFromGroups = is_array($removalList) ? $removalList : array($removalList);

		foreach ($removeFromGroups as  $remGrpId) {
			$this->removeUserFromGroup($userid, $remGrpId);

			$message = JText::_("COM_PAYPLANS_APP_JUSERTYPE_LOG_REMOVE_FROM_USERGROUP");
			$content = array('User Name' => $userid, 'Usergroup' => $groups[$remGrpId]->title, 'Subscription Id' => $subid);

			PP::logger()->log(PPLogger::LEVEL_INFO, $message, $this->app->getId(), 'SYSTEM', $content, 'PayplansAppJusertypeFormatter', md5(serialize($content)));
		}

		return true;
	}

	/**
	 * Retrieve all joomla usergroups
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAllUserGroups()
	{
		static $usergroups = null;

		if (is_null($usergroups)) {
			$model = PP::model('User');
			$results = $model->getAllUserGroups();
			$usergroups = array();

			foreach ($results as $group) {
				$usergroups[$group->id] = $group;
			}
		}

		return $usergroups;
	}

	/**
	 * Add user to the usergroup
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function addUserToGroup($userId, $group)
	{
		jimport('joomla.user.helper');
		return JUserHelper::addUserToGroup($userId, $group);
	}

	/**
	 * Set user group for the user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function setUserGroups($userId, $group)
	{
		jimport('joomla.user.helper');
		if (!is_array($group)) {
			$group = (array)$group;
		}

		// if user has any core.admin user group
		// then core.admin groups also be set, remove others
		$usergroups = JUserHelper::getUserGroups($userId);
		foreach ($usergroups as $usergroup) {

			// if its admin group
			if (JAccess::getAssetRules(1)->allow('core.admin', $usergroup)) {
				$group[]= $usergroup;
			}
		}

		return JUserHelper::setUserGroups($userId, $group);
	}

	/**
	 * Remove user from specific user group
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removeUserFromGroup($userId, $group)
	{
		jimport('joomla.user.helper');
		return JUserHelper::removeUserFromGroup($userId, $group);
	}
}