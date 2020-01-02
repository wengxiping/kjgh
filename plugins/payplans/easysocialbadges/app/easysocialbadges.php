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

class PPAppEasySocialBadges extends PPApp
{
	public $_location = __FILE__;
	protected $_resource = 'com_easysocial.badge';
	
	public function _isApplicable(PPAppTriggerableInterface $refObject, $eventname='')
	{
		return PP::easysocial()->exists();
	}

	public function onPayplansSubscriptionAfterSave($prev, $new)
	{
		// no need to trigger if previous and current state is same
		if (($new->getStatus() == PP_NONE) || ($prev != null && $prev->getStatus() == $new->getStatus())) {
			return true;
		}

		$badges = $this->helper->getBadges();

		// Do nothing if badge no selected in app
		if (!$badges) {
			return true;
		}
	
		if ($new->isActive()) {
			foreach ($badges as $badgeId) {
				$this->helper->assignBadge($new, $badgeId);
			}
		}

		if (!$new->isActive() && $this->helper->shouldRemoveBadge()) {

			foreach ($badges as $badgeId) {
				$this->helper->remove($new, $badgeId);
			}
		} 

		return true;
	}			
}