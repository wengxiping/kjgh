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

class PPAppMailchimp extends PPApp
{
	protected $_resource = 'com_mailchimp.list';

	/**
	 * After subscription saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($pre, $new)
	{
		// no need to trigger if previous and current state is same
		if ($pre != null && $pre->getStatus() == $new->getStatus()) {
			return true;
		}

		$subscriptionId = $new->getId();
		$user = $new->getBuyer();
		 
		$remove = array();

		// when subscription status is active add to list
		if ($new->getStatus() == PP_SUBSCRIPTION_ACTIVE){
			$addToList = $this->helper->getActiveLists();

			$list = $this->helper->getHoldLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}

			$list = $this->helper->getExpiredLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}

			$this->helper->insert($addToList, $user, $subscriptionId);

			// Remove the user from the hold and expired list
			$this->helper->remove($remove, $user, $subscriptionId);

			return true;
		}
		
		// when subscription status is hold
		if ($new->getStatus() == PP_SUBSCRIPTION_HOLD) {
			$addToList = $this->helper->getHoldLists();

			$list = $this->helper->getActiveLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}

			$list = $this->helper->getExpiredLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}

			$this->helper->insert($addToList, $user, $subscriptionId);

			// Remove the user from the active and expired list
			$this->helper->remove($remove, $user, $subscriptionId);

			return true;
		}
		
		// when subscription status is expire
		if ($new->getStatus() == PP_SUBSCRIPTION_EXPIRED) {
			$addToList = $this->helper->getExpiredLists();

			$list = $this->helper->getActiveLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}

			$list = $this->helper->getHoldLists();
			if ($list) {
				$remove = array_merge($remove, $list);
			}


			$this->helper->insert($addToList, $user, $subscriptionId);

			// Remove the user from the hold and active list
			$this->helper->remove($remove, $user, $subscriptionId);

			return true;
		}
	
		return true;

	}

}