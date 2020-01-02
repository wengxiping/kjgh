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

class PPAppEasyBlogSubmission extends PPApp
{
	private $_resource = 'com_easyblog.submission';

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		if ($prev == null || ($prev->getStatus() == $new->getStatus())) {
			return true;
		}

		$restrictionType = $this->helper->getRestrictionType();
		$subscriptionId = $new->getId();
		$user = $new->getBuyer();
		$userId = $user->id;
		$total = $this->helper->getTotalSubmission();

		$restrictionCategories = $this->helper->getRestrictionCategories();

		// Addition
		if ($new->isActive()) {

			if ($restrictionType != 'restrict_specific') {
				$this->_addToResource($subscriptionId, $userId, 0, $this->_resource, $total);
				return true;
			}

			if ($restrictionCategories) {
				foreach ($restrictionCategories as $category) {
					$this->_addToResource($subscriptionId, $userId, $category, $this->_resource, $total);
				}
			}

			return true;
		}
		
		// Removal
		if ($prev->isActive() && !$new->isActive()) {
			if ($restrictionType != 'restrict_specific') {
				$this->_removeFromResource($subscriptionId, $userId, 0, $this->_resource, $total);
				return true;
			}

			if ($restrictionCategories) {
				foreach ($restrictionCategories as $category) {
					$this->_removeFromResource($subscriptionId, $userId, $category, $this->_resource, $total);
				}
			}
		}
		
		return true;
	}
}