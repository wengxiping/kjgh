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

class AnalyticsWoopra
{
	public function __construct()
	{
		$real_domain = JURI::getInstance()->getHost();

		if (!class_exists('WoopraTracker')) {
			require_once(dirname(__FILE__) . '/woopra_tracker.php');
		}

		$this->tracker = new WoopraTracker();
	}

	/**
	 * Push the data of tracked events to the woopra
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function trackEvent($user_id, $event_name, $args, $is_created = 0)
	{
		$real_domain = JURI::getInstance()->getHost();

		if ($is_created) {
			$t['email'] = $args['email'];
			$this->tracker->identify($t)->push(true);
		}

		$this->tracker->identify(array())->push(true);
		$this->tracker->track($event, $args, true);

		return true;
	}
}
