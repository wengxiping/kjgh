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

class PPAppAup extends PPApp
{	
	/**
	 * Applicable only when AUP is installed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventName = '')
	{
		return $this->helper->exists();
	}

	/**
	 * Triggered after an invoice is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansInvoiceAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		// check if have to remove aup points on refund and current invoice status is refund 
		// then refund aup points and return
		if ($new->getStatus() != PP_INVOICE_REFUNDED || !$this->helper->removePointsOnRefund()) {
			return;
		}

		$plan = array_shift($new->getPlans());
		$order = $new->getReferenceObject();
		$subscription = $order->getSubscription();
		$subscriptionId = $subscription->getId();
		$user = $new->getBuyer();

		$this->helper->remove($this->helper->getPoints($plan), $user, $subscriptionId);

		// Remove from resource
		$this->_removeFromResource($subscriptionId, $user->getId(),0, 'com_altauserpoints.points', $this->helper->getPoints());
		return true;
	}

	/**
	 * Triggered after a subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// No need to trigger if previous and current state is same
		if ($prev != null && $prev->getStatus() == $new->getStatus()) {
			return true;
		}

		// check the status if not active then return
		if ($new->getStatus() != PP_SUBSCRIPTION_ACTIVE) {
			return true;
		}

		$plan = $new->getPlan();
		$order = $new->getOrder();
		$buyer = $new->getBuyer();
		
		// is it upgrading from some plan ?
		$upgradingFrom = $order->getParam('upgrading_from', 0);
		$newPoints = $this->helper->getPoints($plan);

		if (!$upgradingFrom) {
			$this->helper->add($newPoints, $buyer, $new->getId());
			$this->_addToResource($new->getId(), $buyer->getId(), 0, 'com_altauserpoints.points', $newPoints);

			return true;
		}

		// Get old subscription
		$previousSubscription = PP::subscription($upgradingFrom);
		$previousPlan = $previousSubscription->getPlan();

		// Get points for the previous plans
		$previousPoints = $this->helper->getPoints($previousPlan);
		

		$points = 0;

		// if points for new plan is greater than old plan then
		// assign points by subtracting old points from new points
		if ($newPoints >= $previousPoints) {
			$points = $newPoints - $previousPoints;
		}

		return $this->helper->add($points, $buyer, $new->getId());
	}
}
	
