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

class PPMaintenanceScriptMigrateEasyBlogSubmission extends PPMaintenanceScript
{
	public static $title = "Migrate EasyBlog Submission as standalone app";
	public static $description = "Migrate EasyBlog Submission as standalone app.";

	public function main()
	{
		$db = PP::db();

		$query = "select count(1) from `#__payplans_app`";
		$query .= " where `type` = " . $db->Quote('easyblogsubmission');
		$query .= " and `published` = 1";
		$db->setQuery($query);

		$count = $db->loadResult();

		if ($count) {
			// we need to make sure the new easyblog submission plugin enabled in the system.
			$query = "UPDATE `#__extensions` set `enabled` = 1";
			$query .= " WHERE `folder` = " . $db->Quote('payplans');
			$query .= " AND `element` = " . $db->Quote('easyblogsubmission');
			$query .= " AND `type` = " . $db->Quote('plugin');

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

}
