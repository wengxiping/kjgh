<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class plgPayplansModuleaccess extends PPPlugins
{
	public function onAfterModuleList(&$modules)
	{
		// Get all user's plans
		$user = PP::user();
		$userSubscriptions = $user->getSubscriptions(PP_SUBSCRIPTION_ACTIVE);
		
		// Get all module-access type apps
		$moduleAccessApp = PPHelperApp::getAvailableApps('moduleaccess');

		// Get those modules which we want to display or hide according to app
		$display = array();
		$hide = array();

		foreach ($moduleAccessApp as $moduleAccessAppId => $app) {
			$applyAll = $app->getParam('applyAll', 0);
			$app_modules = $app->getAppParam('AllowedModules');

			if ($applyAll || empty($userSubscriptions)) {
				if (!empty($userSubscriptions)) {
					$display = array_merge($display, $app_modules);
				} else {
					$hide = array_merge($hide, $app_modules);
				}
			} else {
				$app_plans = $app->getPlans();

				foreach ($userSubscriptions as $subscription) {
					if (in_array($subscription->plan_id, $app_plans)) {

						$display = array_merge($display, $app_modules);
					} else {
						$hide = array_merge($hide, $app_modules);
					}
				}
			}
		}
		
		// Remove allowed modules from hide list
		$hide = array_diff($hide, $display);
		
		// Remove modules which are in hide list 
		foreach ($modules as $key => $module) {
			if (in_array($module->id, $hide)) {
				unset($modules[$key]);
			}
		}
		$modules = array_values($modules);
	}
}

