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

class PPMaintenanceScriptMigrateSocialDiscountSettings extends PPMaintenanceScript
{
	public static $title = "Migrating social discount app params into payplans setting";
	public static $description = "Migrating social discount app params into payplans setting.";

	public function main()
	{
		$db = PP::db();

		$query = "select * from `#__extensions`";
		$query .= " where `folder` = " . $db->Quote('payplans');
		$query .= " and `element` = " . $db->Quote('socialdiscount');
		$query .= " and `type` = " . $db->Quote('plugin');

		$db->setQuery($query);
		$plugin = $db->loadObject();

		if ($plugin) {

			$eid = $plugin->extension_id;
			$paramStr = $plugin->params;

			$pluginInstalled = JPluginHelper::getPlugin('payplans', 'socialdiscount');

			$params = new JRegistry();
			$params->loadString($paramStr);

			$facebook = 0;
			$facebookUrl = '';
			$facebookCode = '';
			$facebookAppid = '';

			$twitter = 0;
			$twitterUrl = '';
			$twitterCode = '';

			if (!is_null($params)) {
				$facebook = $params->get('facebook_pagelike', 0);
				$facebookUrl = $params->get('facebook_pagelike_pageurl', '');
				$facebookCode = $params->get('facebook_pagelike_discount', '');
				$facebookAppid = $params->get('facebook_pagelike_app', '');

				$twitter = $params->get('twitter_follow', 0);
				$twitterUrl = $params->get('twitter_follow_pageurl', '');
				$twitterCode = $params->get('twitter_follow_discount', '');
			}

			$query = "delete from `#__payplans_config` where `key` IN (";
			$query .= "'discounts_facebook', 'discounts_facebook_url', 'discounts_facebook_code', 'discounts_facebook_appid'";
			$query .= ", 'discounts_twitter', 'discounts_twitter_url', 'discounts_twitter_code'";
			$query .= ")";
			$db->setQuery($query);
			$db->query();

			$query = "insert into `#__payplans_config` (`key`, `value`) values";
			$query .= "(" . $db->Quote('discounts_facebook') . ',' . $db->Quote($facebook) . ")";
			$query .= ",(" . $db->Quote('discounts_facebook_url') . ',' . $db->Quote($facebookUrl) . ")";
			$query .= ",(" . $db->Quote('discounts_facebook_code') . ',' . $db->Quote($facebookCode) . ")";
			$query .= ",(" . $db->Quote('discounts_facebook_appid') . ',' . $db->Quote($facebookAppid) . ")";
			$query .= ",(" . $db->Quote('discounts_twitter') . ',' . $db->Quote($twitter) . ")";
			$query .= ",(" . $db->Quote('discounts_twitter_url') . ',' . $db->Quote($twitterUrl) . ")";
			$query .= ",(" . $db->Quote('discounts_twitter_code') . ',' . $db->Quote($twitterCode) . ")";

			$db->setQuery($query);
			$db->query();

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
