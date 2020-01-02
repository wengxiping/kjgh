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

class PPHelperEasysocialBadges extends PPHelperStandardApp
{
	/**
	 * Assigns a badge to a user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function assignBadge(PPSubscription $subscription, $badgeId)
	{
		if (!PP::easysocial()->exists()) {
			return false;
		}

		$lib = ES::badges();

		$badge = ES::table('Badge');
		$badge->load($badgeId);

		$user = ES::user($subscription->getBuyer()->getId());
		$title = JText::sprintf('Badge achieved from purchasing a plan %1$s', $subscription->getTitle());
		$lib->create($badge, $user, $title);
		
		$this->addResource($subscription->getId(), $user->id, $badgeId, 'com_easysocial.badge');

		return true;
	}

	/**
	 * Retrieves a list of badges associated with the app
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getBadges()
	{
		$badges = $this->params->get('badges');
		$badges = is_array($badges) ? $badges : array($badges);

		return $badges;
	}

	/**
	 * Removes a badge from user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove(PPSubscription $subscription, $badgeId)
	{
		if (!PP::easysocial()->exists()) {
			return false;
		}

		$user = $subscription->getBuyer();

		$lib = ES::badges();
		$lib->remove($badgeId, $user->getId());

		$this->removeResource($subscription->getId(), $user->getId(), $badgeId, 'com_easysocial.badge');
	}

	/**
	 * Determines if we should remove badges from user
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function shouldRemoveBadge()
	{
		return (bool) $this->params->get('removeBadges');
	}
}