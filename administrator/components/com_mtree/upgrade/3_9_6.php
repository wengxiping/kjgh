<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2017-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_9_6 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('access_key', 'subscription', '', '', 'text', '100', '0', '0'), "
			.   "('subs_first_name', 'subscription', '', '', 'text', '100', '1', '0'), "
			.   "('subs_last_name', 'subscription', '', '', 'text', '200', '1', '0'), "
			.   "('subs_url', 'subscription', '', '', 'text', '300', '1', '0'), "
			.   "('subs_expiry', 'subscription', '', '', 'text', '400', '1', '0'), "
			.   "('subs_last_checked', 'subscription', '', '', 'text', 500, 1, 0), "
			.   "('subs_last_checked_status', 'subscription', '', '', 'text', 600, 1, 0), "
			.   "('subs_last_checked_verified', 'subscription', '', '', 'text', 700, 1, 0) "
			.   ";"
		);
		$database->execute();

		$database->setQuery(
			"INSERT INTO `#__mt_configgroup` (`groupname`, `ordering`, `displayed`, `overridable_by_category`) VALUES ('subscription', '999', '0', '0');"
		);
		$database->execute();

		updateVersion(3,9,6);
		$this->updated = true;
		return true;
	}
}
