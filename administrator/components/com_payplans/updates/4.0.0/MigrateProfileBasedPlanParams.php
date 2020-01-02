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

require_once(PP_LIB . '/maintenance/dependencies.php');

class PPMaintenanceScriptMigrateProfileBasedPlanParams extends PPMaintenanceScript
{
	public static $title = "Migrating profile based plan app payplans setting";
	public static $description = "Migrating profile based plan app params into payplans setting.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('profilebasedplan');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;
			$paramStr = $plugin->params;

			$pluginInstalled = JPluginHelper::getPlugin('payplans', 'profilebasedplan');

			$params = new JRegistry();
			$params->loadString($paramStr);

			$profileUsed = 'joomla_usertype';
			$signUpPlan = '0';

			if (!is_null($params)) {
				$profileUsed = $params->get('profile_used', 'joomla_usertype');
				$signUpPlan = $params->get('defaultPlan', '0');
			}

			$query = "delete from `#__payplans_config` where `key` IN ('profile_used')";
			$db->setQuery($query);
			$db->query();

			$query = "insert into `#__payplans_config` (`key`, `value`) values";
			$query .= "(" . $db->Quote('profile_used') . ',' . $db->Quote($profileUsed) . ")";

			$db->setQuery($query);
			$db->query();

			if ($signUpPlan != '0') {
				$query = "delete from `#__payplans_config` where `key` IN ('profileplan_default')";
				$db->setQuery($query);
				$db->query();

				$query = "insert into `#__payplans_config` (`key`, `value`) values";
				$query .= "(" . $db->Quote('profileplan_default') . ',' . $db->Quote($signUpPlan) . ")";

				$db->setQuery($query);
				$db->query();
			}

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
		}

		return true;
	}

}
