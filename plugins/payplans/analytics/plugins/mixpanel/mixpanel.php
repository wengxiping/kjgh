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

class AnalyticsMixpanel
{
	public function __construct()
	{
		if (!class_exists('Mixpanel')) {
			require_once(__DIR__ . '/lib/mixpanel/Mixpanel.php');
		}

		$this->token = ANALYTICS_MIXPANEL_TOKEN;
	}

	public function trackEvent($user_id, $event_name, $args, $is_created = 0)
	{
		$mp = Mixpanel::getInstance($this->token);

		if ($is_created) {
			$mp->people->set($user_id, $args);
		}

		$mp->identify($user_id);
		$mp->track($event_name, $args);
	}
}
