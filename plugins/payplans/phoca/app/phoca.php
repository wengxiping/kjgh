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

require_once(JPATH_ROOT . '/plugins/payplans/phoca/app/lib.php');

class PPAppPhoca extends PPApp
{
	/**
	 * Determines if the app should run
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventName='')
	{
		$lib = new PPPhoca();
		return $lib->exists();
	}

	/**
	 * Triggered when subscription is activated
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

		$newStatus = $new->getStatus();
		$prevStatus = ($prev != null) ? $prev->getStatus() : PP_NONE; 

		$subscriptionId = $new->getId();
		$user = $new->getBuyer();
		$userId = $user->getId();

		// if subscription is active
		if ($new->isActive()) {
			$this->updateAccess($userId, $subscriptionId, 'active');
			return true;
		}

		if ($new->isOnHold()) {
			$this->updateAccess($userId, $subscriptionId, 'hold');
			return true;
		}
		
		if ($new->isExpired()) {
			$this->updateAccess($userId, $subscriptionId, 'hold');
			return true;
		}
	
		return true;
	}

	/**
	 * Helper to add / remove access
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function updateAccess($userId, $subscriptionId, $type)
	{
		$accessItems = array(
			'active' => $this->helper->getAccessibleCategories('active'),
			'hold' => $this->helper->getAccessibleCategories('hold'),
			'expire' => $this->helper->getAccessibleCategories('expire')
		);

		$uploadItemss = array(
			'active' => $this->helper->getAccessibleUploads('active'),
			'hold' => $this->helper->getAccessibleUploads('hold'),
			'expire' => $this->helper->getAccessibleUploads('expire')
		);

		foreach ($accessItems as $key => $value) {

			if ($key == $type) {
				$this->helper->addAccess($userId, $value, $subscriptionId);
				$this->helper->addAccess($userId, $value, $subscriptionId, 'upload');
				continue;
			}

			$this->helper->removeAccess($userId, $value, $subscriptionId);
			$this->helper->removeAccess($userId, $value, $subscriptionId, 'upload');
		}
	}	
}

