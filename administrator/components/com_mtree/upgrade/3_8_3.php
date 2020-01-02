<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_8_3 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('show_listingreviewsrss', 'rss', '1', '1', 'yesno', '250', '1', '1'), "
			.   "('sef_rss_listingreviews', 'sef', 'listing-reviews', 'listing-reviews', 'text', '3650', '1', '0');"
		);
		$database->execute();

		updateVersion(3,8,3);
		$this->updated = true;
		return true;
	}
}

