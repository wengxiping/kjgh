<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_6_5 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Rename Hits field type caption from Hits to Unique Pageviews.
		// We're leaving the Hits custom field name unchanged for upgrading users
		$database->setQuery(
			"UPDATE `#__mt_fieldtypes` SET `ft_caption` = 'Unique Pageviews' WHERE `field_type` = 'corehits';"
		);
		$database->execute();

		// Rename Visited field type caption from Visited to Website Clicks.
		// We're leaving the Visited custom field name unchanged for upgrading users
		$database->setQuery(
			"UPDATE `#__mt_fieldtypes` SET `ft_caption` = 'Website Clicks' WHERE `field_type` = 'corevisited';"
		);
		$database->execute();

		// Display limit_max_chars config
		$database->setQuery(
			"UPDATE `#__mt_config` SET `displayed` = '1' WHERE `varname` = 'limit_max_chars';"
		);
		$database->execute();

		updateVersion(3,6,5);
		$this->updated = true;
		return true;
	}
}

