<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_8_0 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Add 3 new configs (and a note)
		$database->setQuery(
			"INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('all_listings_sort_by2', 'listing', 'none', 'none', 'sort2', 100, 1, 1), "
			.   "('note_filter_search', 'search', '', '', 'note', '2400', '1', '1'), "
			.   "('filter_show_keyword_search', 'search', '1', '1', 'yesno', '2500', '1', '1'), "
			.   "('redirect_url_needapproval_addlisting', 'listing', '', '', 'text', '3640', '0', '1'), "
			.   "('sef_attachment', 'sef', 'attachment', 'attachment', 'text', 250, 1, 0);"
		);
		$database->execute();

		// Re-order config: all_listings_sort_by_options
		$database->setQuery(
				"UPDATE `#__mt_config` SET `ordering` = '200' WHERE `varname` = 'all_listings_sort_by_options';"
		);
		$database->execute();

		// Let coreyear to use elements
		$database->setQuery(
			"UPDATE `#__mt_fieldtypes` SET `use_elements` = '1' WHERE `field_type` = 'coreyear';"
		);
		$database->execute();

		// Adds Audio Player 2.0 fieldtype.
		$database->setQuery(
				"INSERT INTO `#__mt_fieldtypes` (`field_type`, `ft_caption`, `ft_version`, `ft_website`, `ft_desc`, `use_elements`, `use_size`, `use_columns`, `use_placeholder`, `is_file`, `taggable`, `iscore`)"
				.   " VALUES ('audioplayer2', 'Audio Player 2.0', '1.0.0', '', 'Audio Player allows users to upload audio files and play the music from within the listing page. Provides basic playback options such as play, pause and volumne control. Made possible by http://mediaelementjs.com/.', '0', '0', '0', '0', '1', '0', '0');"
		);
		$database->execute();

		updateVersion(3,8,0);
		$this->updated = true;
		return true;
	}
}

