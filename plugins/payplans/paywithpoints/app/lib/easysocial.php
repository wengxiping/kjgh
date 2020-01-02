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

class PPPaywithpointsEasysocial extends PPPaywithpointsLibAbstract
{
	protected $file = JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php';

	/**
	 * Deduct EasySocial points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function deduct($points)
	{
		if (!$this->exists('com_easysocial')) {
			return false;
		}

		$lib = ES::points();
		$lib->assignCustom($this->user->getId(), $points, 'Deducted from plan purchase');

		return true;
	}

	/**
	 * Retrieves EasySocial points
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getPoints()
	{
		if (!$this->exists('com_easysocial')) {
			return false;
		}

		$esUser = ES::user($this->user->getId());
		$points = $esUser->getPoints();

		return $points;
	}
}