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

require_once(PP_LIB . '/maintenance/dependencies.php');

class PPMaintenanceScriptMigrateFixedDateExpiration extends PPMaintenanceScript
{
	public static $title = "Migrating Fixed Date Expiration data into respected plans parameters.";
	public static $description = "Migrating Fixed Date Expiration data into respected plans parameters..";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('fixeddateexpiration');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;

			// now uninstall this plugin.
			if ($eid) {
				$installer = JInstaller::getInstance();
				$state = $installer->uninstall('plugin', $eid);

				if (!$state) {
					// uninstallation failed. lets just unpublish this plugin.
					$query = "update `#__extensions` set `enabled` = 0";
					$query .= " where `extension_id` = " . $db->Quote($eid);

					$db->setQuery($query);
					$db->query();
				}
			}

			// Retrieve the data from app table
			$query = 'SELECT * FROM `#__payplans_app` WHERE `type` = ' . $db->Quote('fixeddateexpiration') . ' AND `published` = ' . $db->Quote('1');
			$query .= ' ORDER BY ordering ASC';

			$db->setQuery($query);
			$appInstances = $db->loadObjectList();

			if (!$appInstances) {
				return true;
			}

			// Get all plans
			$query = 'SELECT `plan_id` FROM `#__payplans_plan`';
			$db->setQuery($query);

			$plans = $db->loadColumn();

			if (!$plans) {
				return true;
			}

			$params = array();

			foreach ($appInstances as $app) {
				$coreParams = new JRegistry($app->core_params);

				if ($coreParams->get('applyAll') == '1') {
					$params['all'] = $app->app_params;
				} else {

					// Map each params
					$query = 'SELECT `plan_id` FROM `#__payplans_planapp` WHERE `app_id` = ' . $db->Quote($app->app_id);
					$db->setQuery($query);

					$planIds = $db->loadColumn();

					if ($planIds) {
						foreach ($planIds as $planId) {
							$params[$planId] = $app->app_params;
						}
					}
				}
			}

			foreach ($plans as $plan) {
				$table = PP::table('Plan');
				$table->load(array('plan_id' => $plan));

				if (!$table->plan_id) {
					continue;
				}

				if (!isset($params[$table->plan_id]) && !isset($params['all'])) {
					continue;
				}

				$storedParams = isset($params[$table->plan_id]) ? $params[$table->plan_id] : $params['all'];

				$planParams = new JRegistry($table->params);
				$newParams = new JRegistry($storedParams);

				$planParams->merge($newParams);

				$planParamsArray = $planParams->toArray();
				$planParamsArray['enable_fixed_expiration_date'] = $planParamsArray['extend_subscription'];

				unset($planParamsArray['extend_subscription']);

				$planParams = new JRegistry($planParamsArray);

				$table->params = $planParams->toString();
				$table->store();
			}

			$query = "DELETE FROM `#__payplans_app`";
			$query .= " WHERE `type` IN ('fixeddateexpiration')";

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
