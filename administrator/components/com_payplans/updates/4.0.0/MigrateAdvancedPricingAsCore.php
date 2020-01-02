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

class PPMaintenanceScriptMigrateAdvancedPricingAsCore extends PPMaintenanceScript
{
	public static $title = "Migrating advanced pricing app as core feature.";
	public static $description = "Migrating advanced pricing app as core feature in Payplans.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('advancedpricing');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;

			$query = "select a.*, b.`plans`";
			$query .= " from `#__payplans_pricingslab` as a";
			$query .= " inner join `#__payplans_advancedpricing` as b";
			$query .= "		on a.`advancedpricing_id` = b.`advancedpricing_id`";

			$db->setQuery($query);

			$results = $db->loadObjectList();

			if ($results) {
				foreach ($results as $item) {

					$min = $item->min_value;
					$max = $item->max_value;
					$params = $item->details;
					$id = $item->advancedpricing_id;

					// currently the plans is stored as CSV format. we need the value to be stored as array json encoded string.
					$str = $item->plans;
					$arrPlans = explode(',', $str);

					$plans = array();
					foreach ($arrPlans as $pid) {
						if ($pid) {
							$plans[] = $pid;
						}
					}
					$plans = json_encode($plans);

					// now we gather all the required data. lets update advancedpricing table.

					$update = "update `#__payplans_advancedpricing` set";
					$update .= " `plans` = " . $db->Quote($plans);
					$update .= ", `params` = " . $db->Quote($params);
					$update .= ", `units_min` = " . $db->Quote($min);
					$update .= ", `units_max` = " . $db->Quote($max);
					$update .= " where `advancedpricing_id` = " . $db->Quote($id);

					$db->setQuery($update);
					$state = $db->query();

				}
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
				} else {

					// drop the table jos_payplans_pricingslab as this table no longer being used.
					// we only drop the table if the records fro extension table removed. If not, the next time if admin
					// re-run this script, it will hit table not found error.

					$drop = "drop table `#__payplans_pricingslab`";
					$db->setQuery($drop);
					$db->query();
				}
			}

		}

		return true;
	}

}
