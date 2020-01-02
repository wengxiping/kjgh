<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_8_2 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add 1 new config
		$database->setQuery(
				"INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
				.   " VALUES "
				.   "('attachments_noindex_nofollow', 'listing', '', '1', 'yesno', '3700', '0', '1'); "
		);
		$database->execute();

		updateVersion(3,8,2);
		$this->updated = true;
		return true;
	}
}

