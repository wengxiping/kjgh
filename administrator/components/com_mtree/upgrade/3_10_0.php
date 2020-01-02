<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2017-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_10_0 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('index_search_by', 'category', '', '', 'taggable_fields', 3190, 1, 1), "
			.	"('show_share_with_whatsapp', 'sharing', '1', '1', 'yesno', '700', '1', '1')"
			.   ";"
		);
		$database->execute();

		// Add new template 'Banyan'
		$database->setQuery(
				"INSERT IGNORE INTO `#__mt_templates` (`tem_name`) "
				.   " VALUES "
				.   "('banyan')"
				.   ";"
		);
		$database->execute();

		// Update config to set 'banyan' as the default fallback template
		$database->setQuery(
				"UPDATE `#__mt_config` SET `default` = 'banyan' WHERE `varname` = 'template'"
				.   ";"
		);
		$database->execute();

		// Set alias for Tags field
		$database->setQuery(
			"UPDATE `#__mt_customfields` SET `alias` = 'tags' WHERE `cf_id` = '28' AND `field_type` = 'mtags';"
		);
		$database->execute();

		updateVersion(3,10,0);
		$this->updated = true;
		return true;
	}
}