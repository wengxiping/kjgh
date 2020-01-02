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

require_once(PP_LIB . '/abstract.php');

class PPLimitsubscription extends PPAbstract
{
	/**
	 * Filter the plans that already limited
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function filterPlans($plans)
	{
		$user = PP::user();
		$newPlans = array();

		foreach ($plans as $key => $plan) {
			$planId = $plan->getId();

			if (self::canSubscribe($user, $planId)) {
				$newPlans[$key] = $plan;
			}
		}

		return $newPlans;
	}

	/**
	 * Determine if user is allowed to subscribe to the given plan
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function canSubscribe($user, $planId)
	{
		$apps = self::getAppInstance();
		$plans = array();

		if (!$apps) {
			return true;
		}

		foreach ($apps as $app) {
			$appPlans = is_array($app->_appplans) ? $app->_appplans : array($app->_appplans);
			$subscriptionCount = 0;

			if ($app->getParam('applyAll', false) || in_array($planId, $appPlans)) {
				$limit = $app->getAppParam('limit', 0);
				$param_status = $app->getAppParam('consider_status', null);
				$consider_status = is_array($param_status) ? $param_status : array($param_status);

				//fetch user's subscribed plans as per consider status
				foreach ($consider_status as $status) {
					$userPlans = $user->getPlans($status);
					$userPlanIds = array();

					if ($userPlans) {
						foreach ($userPlans as $userPlan) {
							$userPlanIds[] = $userPlan->getId();
						}
					}

					// when there are no plans then blank array returned so ignore that
					// and count subscription of the plan for which app is triggered
					if (!empty($userPlanIds) && in_array($planId, $userPlanIds)) {
						$user_plans = array_count_values($userPlanIds);
						$subscriptionCount += $user_plans[$planId];
					}
				}

				//if counter is reached then redirect user to dashboard
				if ($subscriptionCount >= $limit) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Retrieve the app instance for limitsubscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getAppInstance()
	{
		static $apps = null;

		if (is_null($apps)) {

			$model = PP::model('App');
			$options = array('published' => 1, 'type' => 'limitsubscription');
			$results = $model->loadRecords($options);

			$apps = array();

			if ($results) {
				foreach ($results as $app) {
					$apps[] = PP::app($app->app_id);
				}
			}
		}

		return $apps;
	}
}
