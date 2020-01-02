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

class PPAppEasydiscussBadges extends PPApp
{	
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname = '')
	{
		return PP::easydiscuss()->exists();
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if (($new->isNotActive()) || ($prev != null && $prev->getStatus() == $new->getStatus())){
			return true;
		}

		$user = $new->getBuyer();
		$subscriptionId = $new->getId();

		$badges = $this->helper->getAssignedBadges();

		if ($new->isActive()) {
			$ids = $this->helper->assign($user->id, $badges);

			// Keep track of badges that we added for the user
			foreach ($ids as $id) {
				$this->helper->addResource($subscriptionId, $user->id, $id, 'com_easydiscuss.badge');
			}
		}

		if (!$new->isActive()) {
			$badgeValues = $this->helper->getBadgeValues($user->id, $subscriptionId);

			$this->helper->remove($user->id, $badgeValues);

			foreach ($badgeValues as $badgeValue) {
				$this->helper->removeResource($subscriptionId, $user->id, $badgeValue, 'com_easydiscuss.badge');
			}
		}
		
		return true;
	 }
}