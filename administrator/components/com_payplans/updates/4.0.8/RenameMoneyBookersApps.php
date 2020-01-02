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

class PPMaintenanceScriptRenameMoneyBookersApps extends PPMaintenanceScript
{
	public static $title = "Renaming MoneyBookers apps name";
	public static $description = "Renaming MoneyBookers apps name to follow the new name standard.";

	public function main()
	{
		// 1. we need to uninstall plugins from Joomla
		// 2. Make sure the new plugin installed and published.
		// 3. rename the app instance type to new one.

		$db = PP::db();

		// Moneybookers
		$app = new stdClass();
		$app->old = 'moneybookers';
		$app->new = 'skrill';

		$oldName = $app->old;
		$newName = $app->new;

		// check if these app exits or not.
		$query = 'select count(1) from `#__payplans_app` where `type` = ' . $db->Quote($oldName);
		$db->setQuery($query);
		$exists = $db->loadResult();

		if ($exists) {

			// update app instance type.
			$query = 'UPDATE ' . $db->quoteName('#__payplans_app') . ' SET ' . $db->quoteName('type') . '=' . $db->Quote($newName) . ',' . $db->quoteName('group') . ' = ' . $db->Quote('payment');
			$query .= ' WHERE ' . $db->quoteName('type') . ' = ' . $db->Quote($oldName);

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

}
