<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PPAppEasysocialProfiletype extends PPApp
{
	/**
	 * Applicable only when EasySocial is installed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventName = '')
	{
		return $this->helper->exists();
	}

	/**
	 * Triggered after a subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		$user = $new->getBuyer();
		$userId = $user->getId();

		// if subscription is active
		if ($new->isActive()) {
			$easysocialProfileId = $this->getAppParam('esprofiletypeOnactive', 0);
			return $this->helper->setEasysocialprofile($userId, $easysocialProfileId, true);
		}

		// if subscription is hold
		if ($new->isOnHold()) {
			$easysocialProfileId = $this->getAppParam('esprofiletypeOnHold', 0);
			return $this->helper->setEasysocialprofile($userId, $easysocialProfileId, true);
		}

		// if subscription is expired
		if ($new->isExpired()) {
			$easysocialProfileId = $this->getAppParam('esprofiletypeOnExpire', 0);
			return $this->helper->setEasysocialprofile($userId, $easysocialProfileId, true);
		}

		return true;
	}
}