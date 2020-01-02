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

class PPMaintenanceScriptMigratePdfInvoiceParams extends PPMaintenanceScript
{
	public static $title = "Migrating PDF Invoice setting into payplans core setting";
	public static $description = "Migrating PDF Invoice setting into payplans core setting.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('pdfinvoice');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;
			$enabled = $plugin->enabled;

			if ($enabled) {
				$query = "delete from `#__payplans_config` where `key` IN ('enable_pdf_invoice')";
				$db->setQuery($query);
				$db->query();

				$query = "insert into `#__payplans_config` (`key`, `value`) values";
				$query .= "(" . $db->Quote('enable_pdf_invoice') . ',' . $db->Quote($enabled) . ")";
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
