<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_8_5 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('show_userfavouritesrss', 'rss', '1', '1', 'yesno', '260', '1', '1'), "
			.   "('sef_rss_userfavourites', 'sef', 'user-favourites', 'user-favourites', 'text', '3660', '1', '0'), "
			.   "('fe_num_of_rss_favourite', 'listing', '100', '100', 'text', '6900', '0', '0');"
		);
		$database->execute();

		updateVersion(3,8,5);
		$this->updated = true;
		return true;
	}
}

