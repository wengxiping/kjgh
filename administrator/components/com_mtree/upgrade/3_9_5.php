<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_9_5 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('cluster_map_max_zoom', 'map', '', '14', 'text', 4111, 1, 1) "
			.   ";"
		);
		$database->execute();

		// Remove elements support in Web link and unused column value
		$database->setQuery(
			"UPDATE `#__mt_fieldtypes` SET `use_elements` = '0', `use_columns` = '0' WHERE `field_type` = 'mweblink';"
		);
		$database->execute();

		updateVersion(3,9,5);
		$this->updated = true;
		return true;
	}
}
