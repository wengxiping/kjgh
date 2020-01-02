<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_9_0 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Move existing map related configs to 'map' group
		$database->setQuery("UPDATE #__mt_config SET groupname = 'map' WHERE varname IN ('show_map', 'use_map', 'map_default_country', 'map_default_state', 'map_default_city', 'map_default_lat', 'map_default_lng', 'map_default_zoom');");
		$database->execute();

		// Delete note
		$database->setQuery("DELETE FROM `#__mt_config` WHERE `varname` IN ('note_map', 'note_other_features');");
		$database->execute();

		// Add new 'map' group
		$database->setQuery(
		 "INSERT IGNORE INTO `#__mt_configgroup` (`groupname`, `ordering`, `displayed`, `overridable_by_category`) VALUES ('map', '475', '1', '1');"
		);
		$database->execute();

		// Add configs
		$database->setQuery(
			"INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('google_maps_api_key', 'map', '', '', 'text', '4110', '1', '0'), "
			.   "('show_map_in_category_pages', 'map', '2', '2', 'yesno_default_shown_or_hidden', '4105', '1', '1'), "
			.   "('show_map_in_index_pages', 'map', '2', '2', 'yesno_default_shown_or_hidden', 4106, 1, 1), "
			.   "('show_map_in_search_results_pages', 'map', '2', '2', 'yesno_default_shown_or_hidden', 4107, 1, 1), "
			.   "('show_map_in_top_listings_pages', 'map', '2', '2', 'yesno_default_shown_or_hidden', 4108, 1, 1), "
			.   "('show_map_in_list_all_pages', 'map', '2', '2', 'yesno_default_shown_or_hidden', 4109, 1, 1), "
			.   "('google_maps_type_ids', 'map', 'ROADMAP|SATELLITE|HYBRID|TERRAIN', 'ROADMAP|SATELLITE|HYBRID|TERRAIN', 'map_type_ids', '4115', '1', '0'), "
			.   "('google_maps_styled_map_style_array', 'map', '', '', 'text', 4120, 1, 0), "
			.   "('google_maps_type_id', 'map', 'ROADMAP', 'ROADMAP', 'map_type_id', 4116, 1, 0), "
			.   "('google_maps_marker_image', 'map', '', '/media/com_mtree/images/map-marker-icon-32x32.png', 'text', 4125, 0, 1),"
			.   "('note_listing_expiration', 'listing', '', '', 'note', 6750, 1, 1),"
			.   "('allow_listing_renewal', 'listing', '0', '0', 'yesno', '6820', '1', '0'),"
            .   "('days_remaining_to_renew', 'listing', '30', '30', 'text', '6825', '1', '0'),"
			.   "('show_avl_search', 'search', '0', '0', 'yesno', 2600, 0, 1),"
			.   "('allow_json_output', 'main', '0', '0', 'yesno', 11500, 1, 0)"
			.   ";"
		);
		$database->execute();

		updateVersion(3,9,0);
		$this->updated = true;
		return true;
	}
}


