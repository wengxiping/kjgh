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

class PPPaywithpointsAup extends PPPaywithpointsLibAbstract
{
	protected $file = JPATH_ROOT . '/components/com_altauserpoints/helper.php';

	/**
	 * Deduct points from aup
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deduct($points)
	{
		if (!$this->exists('com_altauserpoints')) {
			return false;
		}

		$aupId = AltaUserPointsHelper::getAnyUserReferreID($this->user->getId());

		return AltaUserPointsHelper::newpoints('paywithpoints', $aupId, '', '', $points, '', 1);
	}

	/**
	 * Retrieve points for a user from AUP
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPoints()
	{
		if (!$this->exists('com_altauserpoints')) {
			return false;
		}

		$aupid = AltaUserPointsHelper::getAnyUserReferreID($this->user->getId());
		$userInfo = AltaUserPointsHelper::getUserInfo($aupid);
		$points = (float) $userInfo->points;

		return $points;
	}
}