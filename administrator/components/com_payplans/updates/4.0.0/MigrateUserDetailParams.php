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

class PPMaintenanceScriptMigrateUserDetailParams extends PPMaintenanceScript
{
	public static $title = "Migrating user custom details as core feature";
	public static $description = "Migrating user custom details as core feature.";

	public function main()
	{
		$db = PP::db();

		// 1. get all usedetails app instance.

		$query = "select * from `#__payplans_app` where `type` = " . $db->Quote('userdetail');
		$db->setQuery($query);

		$items = $db->loadObjectList();

		$now = PP::date();

		$tobeDelete = array();

		if ($items) {
			foreach ($items as $item) {

				$table = PP::table('Customdetails');

				$table->title = $item->title;
				$table->type = 'user';
				$table->created = $now->toSql();
				$table->published = $item->published;
				$table->params = $item->core_params;
				$table->data = '';

				$param = @json_decode($item->app_params);

				if ($param && $param->additional) {
					$table->data = $param->additional;
				}

				$state = $table->store();

				if ($state) {
					$tobeDelete[] = $item->app_id;
				}
			}

			// 2. now we remove userdetail app instance from payplans.
			if ($tobeDelete) {
				$query = "delete from `#__payplans_app` where `app_id` IN (" . implode(',', $tobeDelete) . ")";
				$db->setQuery($query);
				$db->query();
			}
		}

		return true;
	}

}
