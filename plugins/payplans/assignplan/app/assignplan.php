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

class PPAppAssignplan extends PPApp
{
	protected $_location = __FILE__;

	public function isApplicable($refObject = PayplansSubscription, $eventName = '')
	{
		if ($eventName === 'onPayplansSubscriptionAfterSave') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		// Retrieve buyer user id
		$userid = $new->getBuyer()->getId();
		
		// Retrieve the current plan what user trying to subscribe now
		$newPlansId = $new->getPlans()->getId();

		// if the apply selected plan is not apply to all
		if (!$this->getParam('applyAll', false)) {

			// Retrieve the plan id which applied on selected plans
			$selectedPlanIds = $this->getPlans();

			// skip it if the current plan user trying to subscribe is not match with the plan id as what admin configured
			if (!in_array($newPlansId, $selectedPlanIds)) {
 				return true;
			}
		}

		$active = $this->getAppParam('assignPlan');
		$active	= (is_array($active)) ? $active : array($active);
		
		$hold = $this->getAppParam('setPlanOnHold');
		$hold = (is_array($hold)) ? $hold : array($hold);
		
		$expire = $this->getAppParam('setPlanOnExpire');
		$expire	= (is_array($expire)) ? $expire : array($expire);

		// if subscription is active
		if ($new->isActive()) {
			return $this->helper->setPlan($userid, $active, $newPlansId);
		}
		
		// if subscription is hold
		if ($new->isOnHold()) {
			return $this->helper->setPlan($userid, $hold, $newPlansId);
		}
		
		// if subscription is expire
		if ($new->isExpired()) {
			return $this->helper->setPlan($userid, $expire, $newPlansId);
		}

		return true;
	}
}
