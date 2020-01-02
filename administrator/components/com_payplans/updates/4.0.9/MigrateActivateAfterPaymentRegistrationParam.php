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

class PPMaintenanceScriptMigrateActivateAfterPaymentRegistrationParam extends PPMaintenanceScript
{
	public static $title = "Migrating Activate After Payment registration params into payplans setting";
	public static $description = "Migrating Activate After Payment registration params into payplans setting.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__payplans_config`";
		$query .= " where `key` = " . $db->Quote('registrationType');
		$query .= " and `value` = " . $db->Quote('activateafterpayment');

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result) {
			$query = "delete from `#__payplans_config` where `key` IN ('registrationType')";
			$db->setQuery($query);
			$db->query();

			$query = "insert into `#__payplans_config` (`key`, `value`) values";
			$query .= "(" . $db->Quote('registrationType') . ',' . $db->Quote('auto') . ")";
			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

}
