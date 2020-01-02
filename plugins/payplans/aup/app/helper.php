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

jimport('joomla.filesystem.file');

class PPHelperAup extends PPHelperStandardApp
{
	protected $_location = __FILE__;
	protected $_resource	= '';

	/**
	 * Determines if AUP exists
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function exists()
	{
		return PP::aup()->exists();
	}

	/**
	 * Determines the total points to be used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPoints(PPPlan $plan)
	{
		static $data = array();

		$key = $plan->getId();

		if (!isset($data[$key])) {
			$availableApps = PPHelperApp::getAvailableApps('aup');
			$apps = array();
			$points = 0;

			// Check which app allow category, which not allow and which app do nothing
			foreach ($availableApps as $app) {

				if ($app->getParam('applyAll', false) != false) {
					$points += $app->getAppParam('addPoints', 0);
					continue;
				}
				
				// If there are plans associated with the app, we need to update the points
				$appPlans = $app->getPlans();
			
				if (array_intersect(array($plan->getId()), $appPlans) != false) {
					$points += $app->getAppParam('addPoints', 0);
					continue;
				}
			}

			$data[$key] = $points;
		}

		return $data[$key];
	}

	/**
	 * Determines if we should remove points when refunded
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function removePointsOnRefund()
	{
		static $remove = null;

		if (is_null($remove)) {
			$remove = $this->params->get('removePointsOnRefund');
		}

		return $remove;
	}

	/**
	 * Adds points for AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function add($points, PPUser $user, $subscriptionId)
	{
		if (!$this->exists()) {
			return false;
		}
		
		$id = AltaUserPointsHelper::getAnyUserReferreID($user->getId());

		if(!$id) {
			$id = $user->getId();
		}

		return AltaUserPointsHelper::newpoints('aup', $id, '', '', $points, '', 1);
	}

	/**
	 * Removes points from AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function remove($points, PPUser $user, $subscriptionId)
	{
		if (!$this->exists()) {
			return false;
		}

		$id = AltaUserPointsHelper::getAnyUserReferreID($user->getId());
		
		//this should use the aup methods for adding points, it should bypass any level restrictions etc...
		return AltaUserPointsHelper::newpoints('aup', $id, '', '', -$points, '', 1);
	}
}