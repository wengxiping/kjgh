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

class PPAppEasySocialPoints extends PPApp
{
	protected $_resource = 'com_easysocial.point';
	
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname = '')
	{
		return PP::easysocial()->exists();
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if (($new->getStatus() == PP_NONE) || ($prev != null && $prev->getStatus() == $new->getStatus())) {
			return true;
		}

		if (!PP::easysocial()->exists()) {
			return true;
		}

		$params = $this->getAppParams();
		$points = $params->get('points');

		if ($new->isActive() && $points) {
			$point = ES::points();
			$point->assignCustom($new->getBuyer()->getId(), $points, JText::sprintf('User purchased %1$s points from the plan %2$s', $points, $new->getTitle()));

			$this->_addToResource($new->getId(), $new->getBuyer()->getId(), $points, 'com_easysocial.point');
		}
		
		return true;
	 }	
}