<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_7_0 extends mUpgrade
{
	function upgrade() {
		$database = JFactory::getDBO();

		// Adds Telephone fieldtype.
		$database->setQuery(
			"INSERT INTO `#__mt_fieldtypes` (`field_type`, `ft_caption`, `ft_version`, `ft_website`, `ft_desc`, `use_elements`, `use_size`, `use_columns`, `use_placeholder`, `is_file`, `taggable`, `iscore`)"
			.   " VALUES ('mtelephone', 'Telephone', '1.0.0', '', '', 0, 1, 0, 1, 0, 0, 0);"
		);
		$database->execute();

		// Adds Listings fieldtype.
		$database->setQuery(
			"INSERT INTO `#__mt_fieldtypes` (`field_type`, `ft_caption`, `ft_version`, `ft_website`, `ft_desc`, `use_elements`, `use_size`, `use_columns`, `use_placeholder`, `is_file`, `taggable`, `iscore`)"
			.   " VALUES ('listings', 'Listings', '1.0.3', 'www.mosets.com', 'Allows you to select listings within your directory and show them as part of a listing.', 0, 0, 0, 0, 0, 0, 0);"
		);
		$database->execute();

		// Adds Multiple Dates fieldtype.
		$database->setQuery(
			"INSERT INTO `#__mt_fieldtypes` (`field_type`, `ft_caption`, `ft_version`, `ft_website`, `ft_desc`, `use_elements`, `use_size`, `use_columns`, `use_placeholder`, `is_file`, `taggable`, `iscore`)"
			.   " VALUES ('multipledates', 'Multiple Dates', '1.0.2', 'www.mosets.com', 'Show multiple dates.', 0, 1, 0, 0, 0, 0, 0);"
		);
		$database->execute();

		// Adds 3 new configs defining group IDs that are allowed to create, edit or delete listings in front-end
		$database->setQuery(
			"INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES "
			.   "('group_ids_authorised_to_create_listing', 'permission', '8', '8', 'user_groups', '13050', '1', '1'), "
			.   "('group_ids_authorised_to_edit_listing', 'permission', '8', '8', 'user_groups', '13100', '1', '1'), "
			.   "('group_ids_authorised_to_delete_listing', 'permission', '8', '8', 'user_groups', '13200', '1', '1');"
		);
		$database->execute();

		$database->setQuery(
			"INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) "
			.   " VALUES ('note_managers', 'permission', '', '', 'note', '13000', '1', '1');"
		);
		$database->execute();

		// Add new 'Permission' config group
		$database->setQuery(
			"INSERT INTO `#__mt_configgroup` (`groupname`, `ordering`, `displayed`, `overridable_by_category`)"
			.   " VALUES ('permission', 200, 1, 1);"
		);
		$database->execute();

		// Remove Permission note
		$database->setQuery( "DELETE FROM `#__mt_config` WHERE `varname` IN ('note_permission');");
		$database->execute();

		// Move 'link_to_configure_permission' config to 'permission' group
		$database->setQuery( "UPDATE `#__mt_config` SET `groupname` = 'permission' WHERE `varname` = 'link_to_configure_permission';");
		$database->execute();

		// Show limit_min_chars
		$database->setQuery( "UPDATE `#__mt_config` SET `displayed` = '1' WHERE `varname` = 'limit_min_chars';");
		$database->execute();

		// Update second_listing_order configcode to second_listing_order
		$database->setQuery( "UPDATE `#__mt_config` SET `configcode` = 'second_listing_order' WHERE `varname` = 'second_listing_order1';");
		$database->execute();

		// Add new configs
		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) '
			. ' VALUES '
			. ' (\'show_previous_next_listing_in_listing_details\', \'listing\', \'0\', \'1\', \'yesno\', \'2550\', \'1\', \'1\'), '

			. ' (\'sef_adjacentlisting_next\', \'sef\', \'next-listing\', \'next-listing\', \'text\', \'1651\', \'1\', \'0\'), '
			. ' (\'sef_adjacentlisting_previous\', \'sef\', \'previous-listing\', \'previous-listing\', \'text\', \'1652\', \'1\', \'0\'), '

			. ' (\'rss_firstname\', \'rss\', \'0\', \'0\', \'yesno\', \'1075\', \'1\', \'1\'), '
			. ' (\'rss_lastname\', \'rss\', \'0\', \'0\', \'yesno\', \'1076\', \'1\', \'1\'), '

			. ' (\'rss_contactperson\', \'rss\', \'0\', \'0\', \'yesno\', \'1080\', \'1\', \'1\'), '
			. ' (\'rss_mobile\', \'rss\', \'0\', \'0\', \'yesno\', \'1090\', \'1\', \'1\'), '
			. ' (\'rss_date\', \'rss\', \'0\', \'0\', \'yesno\', \'1091\', \'1\', \'1\'), '
			. ' (\'rss_year\', \'rss\', \'0\', \'0\', \'yesno\', \'1092\', \'1\', \'1\'), '

			. ' (\'fe_num_of_random\', \'listing\', \'20\', \'20\', \'text\', \'6710\', \'1\', \'1\'), '
			. ' (\'random_listings_shuffle_frequency\', \'listing\', \'3600\', \'3600\', \'random_listings_shuffle_frequency\', \'3530\', \'1\', \'0\'), '
			. ' (\'sef_random\', \'sef\', \'random\', \'random\', \'text\', \'2650\', \'1\', \'0\'), '
			. ' (\'fe_num_of_owners\', \'listing\', \'\', \'20\', \'text\', \'6730\', \'0\', \'0\'), '
			. ' (\'search_completion_max_listings\', \'search\', \'\', \'8\', \'text\', \'2300\', \'0\', \'0\'), '
			. ' (\'max_num_of_listings_per_user\', \'listing\', \'\', \'0\', \'text\', \'3590\', \'1\', \'0\'), '
			. ' (\'show_add_listing_link\', \'listing\', \'\', \'1\', \'show_requirements\', \'3520\', \'1\', \'1\'); '
		);

		$database->execute();

		// Add 2 new core fields: firstname, lastname
		$database->setQuery( 'SHOW COLUMNS FROM `#__mt_links` LIKE \'lastname\'' );
		$database->execute();
		if( $database->getNumRows() == 0 )
		{
			$database->setQuery(
				'ALTER TABLE #__mt_links ADD lastname VARCHAR(255) NOT NULL DEFAULT \'\' AFTER link_visited;'
			);
			$database->execute();
		}

		$database->setQuery( 'SHOW COLUMNS FROM `#__mt_links` LIKE \'firstname\'' );
		$database->execute();
		if( $database->getNumRows() == 0 )
		{
			$database->setQuery(
				'ALTER TABLE #__mt_links ADD firstname VARCHAR(255) NOT NULL DEFAULT \'\' AFTER link_visited;'
			);
			$database->execute();
		}

		$database->setQuery(
			'INSERT IGNORE INTO `#__mt_customfields` (`field_type`, `caption`, `alias`, `default_value`, `size`, `field_elements`, `prefix_text_mod`, `suffix_text_mod`, `prefix_text_display`, `suffix_text_display`, `placeholder_text`, `cat_id`, `ordering`, `hidden`, `required_field`, `published`, `view_access_level`, `hide_caption`, `advanced_search`, `simple_search`, `tag_search`, `filter_search`, `details_view`, `summary_view`, `search_caption`, `params`, `iscore`) '
			. ' VALUES '
			. " ('corefirstname', 'First Name', 'first-name', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1), "
			. " ('corelastname', 'Last Name', 'last-name', '', 0, '', '', '', '', '', '', 0, 9, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, '', '', 1);"
		);
		$database->execute();

		// Add new #__mt_customfields column for `shown_in_backend_listings_column`
		$database->setQuery(
			'ALTER TABLE #__mt_customfields ADD `shown_in_backend_listings_column` TINYINT(3)  UNSIGNED  NOT NULL  DEFAULT \'0\'  AFTER `edit_access_level`;'
		);
		$database->execute();

		// Add a new INDEX to speed up retrieval of large number of records in #__mt_images. eg: when outputting RSS feed.
		$database->setQuery(
			'ALTER TABLE #__mt_images ADD INDEX `ordering` (`ordering`);'
		);
		$database->execute();

		// Update selectmultiple field_type name to mselectmultiple
		$database->setQuery( "UPDATE `#__mt_customfields` SET `field_type` = 'mselectmultiple' WHERE `field_type` = 'selectmultiple';");
		$database->execute();

		$database->setQuery(
				"INSERT INTO `#__mt_fieldtypes` (`field_type`, `ft_caption`, `ft_version`, `ft_website`, `ft_desc`, `use_elements`, `use_size`, `use_columns`, `use_placeholder`, `is_file`, `taggable`, `iscore`)"
				. " VALUES ('mselectmultiple', 'Select Multiple', '1.0.0', 'www.mosets.com', 'Select Multiple field allows you to select one or more value in a multiple select list.', 1, 1, 0, 0, 0, 1, 0);"
		);
		$database->execute();

		updateVersion(3,7,0);
		$this->updated = true;
		return true;
	}
}