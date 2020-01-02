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

class PPAppMtree extends PPApp
{
	protected $_resource1 = 'com_mtree.publish';
	protected $_resource2 = 'com_mtree.feature';
	
	public function isApplicable($refObject = null, $eventName='')
	{
		if ($eventName === 'onPayplansAccessCheck' || $eventName === 'onPayplansUpdateFeaturing') {
			return true;
		}

		return parent::isApplicable($refObject, $eventName);
	}
	
	// applicable only if mosets tree exist
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname = '')
	{
		return $this->helper->exists();
	}

	/**
	 * Triggered from the plugin level
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansUpdateFeaturing($linkId)
	{
		$user = PP::user()->getId();

		$existingFeaturedLists = $this->helper->getTotalFeaturedItems($user);
		$resource = $this->_getResource($user, 0, $this->_resource2);
		$count = 0;

		if (!empty($resource)) {
			$count = $resource->count;
		}
		
		//mark list as featured only when allowed
		if ($existingFeaturedLists < $count) {
			$query =  ' UPDATE #__mt_links'
						. ' SET `link_featured` = 1'
						. ' WHERE `link_id` ='. $linkId
						;
		
			$db->setQuery($query);
			$db->query();
		}

		return true;	
	}

	/**
	 * Triggered after a subscription is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */	
	public function onPayplansSubscriptionAfterSave($previous, $new)
	{
		$user = $new->getBuyer();
		$userId = $user->getId();
		$subscriptionId = $new->getId();

		if ($previous == null) {
			return true;
		}

		$newStatus = $new->getStatus();
		$prevStatus = $previous->getStatus();

		// No change in status, we shouldn't be doing anything
		if ($prevStatus == $newStatus) {
			return true;
		}

		$restrictionType = $this->helper->getRestrictionType();
		$restrictedCategories = $this->helper->getRestrictedCategories();

		$publishCount = $this->helper->getTotalAllowedToPublish();
		$featureCount = $this->helper->getTotalAllowedToFeature();

		// Subscription is activated
		if ($new->isActive()) {
			$action = 1;

			if ($restrictionType != 'restrict_specific') {
				$this->addResources($subscriptionId, $userId, 0);
				$this->publishList($new, $userId);

				return true;
			}
			
			foreach ($restrictedCategories as $category) {
				$this->addResources($subscriptionId, $userId, $category);
				$this->publishList($new, $userId);
			}
			
			return true;
		}

		// Refunded
		if ($previous->isActive() && $new->isOnHold()) {
			
			if ($restrictionType != 'restrict_specific') {
				$this->removeResources($subscriptionId, $userId, 0);
				$this->publishList($new, $userId, 'unpublish');
				return true;
			}

			foreach ($restrictedCategories as $category) {
				$this->removeResources($subscriptionId, $userId, $category);
				$this->publishList($new, $userId, 'unpublish');
			}

			return true;			
		}
			
		// New subscription is not active
		if ($previous->isActive() && !$new->isActive()) {
			
			if ($restrictionType != 'restrict_specific') {
				$this->removeResources($subscriptionId, $userId, 0);
				$this->publishList($new, $userId, 'unpublish');

				return true;
			}

			foreach ($restrictedCategories as $category){
				$this->removeResources($subscriptionId, $userId, $category);
				$this->publishList($new, $userId, 'unpublish');
			}
			
			return true;			
		}
	}

	/**
	 * Publish or unpublish a list of items
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function publishList(PPSubscription $subscription, $userId, $action = 'publish')
	{
		$publishCount = $this->helper->getTotalAllowedToPublish();
		$featureCount = $this->helper->getTotalAllowedToFeature();

		$total = $this->helper->getTotalItems($userId);
		$totalFeatured = $this->helper->getTotalFeaturedItems($userId);

		$result = $this->_getResource($userId, 0, $this->_resource1);
		$publishResource = $result->count;
		
		$result = $this->_getResource($userId, 0, $this->_resource2);
		$featuredResource = $result->count;
		
		$status = $subscription->getStatus();

		if ($subscription->isExpired() || $subscription->isOnHold()) {
			//if allowed list is equals or greater then existing then dont unpublish the existing list
			if ($publishResource <= $total && $featuredResource <= $totalFeatured) {
				return true;
			}

			$publishCount = $publishResource - $total;
			$featureCount = $featuredResource - $totalFeatured;
		}
		
		$actionState = $action == 'publish' ? 1 : 0;
		$complement = 1 - $actionState;
		
		// NOTE: only published lists can be marked as featured
		// and a featured list can not be unpublished, list must be 
		//unfeatured first and then it can be unpublished 
		$query1 =  ' UPDATE #__mt_links'
						. ' SET link_featured = ' . $actionState
						. ' WHERE `user_id` ='. $userId.' AND `link_published` = 1 AND `link_featured` = '.$complement
						. ' LIMIT '. $featureCount
						;
								
		$query2 =  ' UPDATE #__mt_links'
							. ' SET link_published  = '. $actionState
							. ' WHERE `user_id` ='. $userId . ' AND `link_published` = '.$complement. ' AND `link_featured` = 0'
							. ' LIMIT '. $publishCount
							;
		//in case of active status we need to run query 
		//for featuring after executing query for publishing list
		// as an unpublished list can not be marked as featured			
		if ($subscription->isActive()) {
			$query =  $query1;
			$query1 =  $query2;
			$query2 =  $query;
		}
		
		$db = PP::db();

		$db->setQuery($query1);
		$db->query();
		
		$db->setQuery($query2);
		$db->query();
		
		return true;
	}

	/**
	 * Add necessary resources
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function addResources($subscriptionId, $userId, $category)
	{
		$publishCount = $this->helper->getTotalAllowedToPublish();
		$featureCount = $this->helper->getTotalAllowedToFeature();

		$this->_addToResource($subscriptionId, $userId, $category, $this->_resource1, $publishCount);
		$this->_addToResource($subscriptionId, $userId, $category, $this->_resource2, $featureCount);
	}

	/**
	 * Remove resources
	 *
	 * @since	4.0.0
	 * @access	private
	 */
	private function removeResources($subscriptionId, $userId, $category)
	{
		$publishCount = $this->helper->getTotalAllowedToPublish();
		$featureCount = $this->helper->getTotalAllowedToFeature();

		$this->_removeFromResource($subscriptionId, $userId, $category, $this->_resource1, $publishCount);
		$this->_removeFromResource($subscriptionId, $userId, $category, $this->_resource2, $featureCount);
	}
}