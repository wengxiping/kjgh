<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(PP_LIB . '/maintenance/dependencies.php');

class PPMaintenanceScriptMergePlanLayoutTypeWithColumnSetting extends PPMaintenanceScript
{
	public static $title = "Merge plan layout type and column per row setting";
	public static $description = "Standardized plan layout type and column per row setting.";

	public function main()
	{
		$db = PP::db();

		// find the config what value it stored right now
		$query = "SELECT * FROM `#__payplans_config`";
		$query .= " WHERE `key` = " . $db->Quote('layout');
		$query .= " AND `value` = " . $db->Quote('vertical');

		$db->setQuery($query);
		$result = $db->loadObject();

		// If set to vertical layout then need to update 'row_plan_counter' to 1
		if ($result) {
			$query = "UPDATE `#__payplans_config`";
			$query .= " SET `value` = " . $db->Quote('1');
			$query .= " WHERE `key` = " . $db->Quote('row_plan_counter');

			$db->setQuery($query);
			$db->query();
		}

		// After updated, then need to delete 'layout' row data since we no longer need this setting anymore.
		$query = "DELETE FROM `#__payplans_config`";
		$query .= " WHERE `key` = " . $db->Quote('layout');

		$db->setQuery($query);
		$db->query();

		return true;
	}

}


