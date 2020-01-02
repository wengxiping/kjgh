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

class PPMaintenanceScriptMigrateMenuAccessParams extends PPMaintenanceScript
{
	public static $title = "Migrating menu access plugin params into payplans setting";
	public static $description = "Migrating menu access plugin params into payplans setting.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('menuaccess');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;
			$paramStr = $plugin->params;

			$pluginInstalled = JPluginHelper::getPlugin('payplans', 'menuaccess');

			$params = new JRegistry();
			$params->loadString($paramStr);

			$show404error = 1;
			$showOrhide = 0;
			if (!is_null($params)) {
				$show404error = $params->get('show404error', 1);
				$showOrhide = $params->get('showOrhide', 0);
			}

			$query = "delete from `#__payplans_config` where `key` IN ('show404error', 'showOrhide')";
			$db->setQuery($query);
			$db->query();

			$query = "insert into `#__payplans_config` (`key`, `value`) values";
			$query .= "(" . $db->Quote('show404error') . ',' . $db->Quote($show404error) . ")";
			$query .= ",(" . $db->Quote('showOrhide') . ',' . $db->Quote($showOrhide) . ")";
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

}
