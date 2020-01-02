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

class PPAppJsmultiprofile extends PPApp
{
	protected $_location = __FILE__;

	public function isApplicable($refObject = null, $eventName='')
	{
		if ($eventName === 'onPayplansAccessCheck') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// No need to trigger if previous and current state is the same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		$user = $new->getBuyer();
		
		if ($user instanceof PPUser) {
			$user = $user->id;
		}
		// If subscription is active
		if($new->isActive()){
			$jsmultiprofile = $this->getAppParam('jsmultiprofileOnActive', 0);
			return $this->helper->setJsmultiprofile($user, $jsmultiprofile);
		}
		
		// If subscription is onhold
		if($new->isOnHold()){
			$jsmultiprofile = $this->getAppParam('jsmultiprofileOnHold', 0);
			return $this->helper->setJsmultiprofile($user, $jsmultiprofile);
		}
		
		// If subscription is expired
		if($new->isExpired()){
			$jsmultiprofile = $this->getAppParam('jsmultiprofileOnExpire', 0);
			return $this->helper->setJsmultiprofile($user, $jsmultiprofile);
		}

		return true;
	}

	/**
	 * To restrict user to change profile type from frontend
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function onPayplansAccessCheck(PPUser $user)
	{
		if (!$user->getId() || $user->isAdmin()) {
			return true;
		}

		if ($this->getAppParam('block_ptype_change', true) == false) {
			return true;
		}

		$option = $this->input->get('option', '', 'default');
		$view = $this->input->get('view', '', 'default');
		$task = $this->input->get('task', '', 'default');
		
		if ($option != 'com_community' || $view != 'multiprofile' || $task != 'changeprofile') {
			return true;
		}

		$profiletype = $this->input->get('profileType', 0, 'int');
		$paidProfiletype = $this->getAppParam('jsmultiprofileOnActive', 0);
		
		if (empty($profiletype) || empty($paidProfiletype) || $profiletype != $paidProfiletype) {
			return true;
		}
		
		$userplans = $user->getPlans();
		
		// When user have no active subscription then return to plan page
		if (empty($userplans)) {
			PP::info()->set('COM_PAYPLANS_APP_JSPROFILETYPE_UPDATE_PLAN_TO_CHANGE_DESC', 'info');
			$redirect = PPR::_('index.php?option=com_payplans&view=plan&task=subscribe', false);
			
			return PP::redirect($redirect);
		}
		
		// When user have no active subscription for the required plan
		if ($this->getParam('applyAll', false) == false) {
			$appplans = $this->getPlans();
			$plans = array_intersect($appplans, $userplans);

			if (count($plans) <= 0) {
				PP::info()->set('COM_PAYPLANS_APP_JSPROFILETYPE_UPDATE_PLAN_TO_CHANGE_DESC', 'info');
				$redirect = PPR::_('index.php?option=com_payplans&view=plan&task=subscribe', false);
				
				return PP::redirect($redirect);
			}
		}

		return true;
	}
}