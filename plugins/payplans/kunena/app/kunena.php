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

class PPAppKunena extends PPApp
{
	/**
	 * When a subscription is updated, we need to unsubscribe user from the categories
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
		
		if ($new->getStatus() == PP_SUBSCRIPTION_ACTIVE || $new->getStatus() == PP_NONE) {
			return true;
		}

		$user = $new->getBuyer();
		$categories = $this->helper->getCategories();

		// Update subscriptions
		$this->helper->updateKunenaSubscriptions($user, $categories);
	}
}