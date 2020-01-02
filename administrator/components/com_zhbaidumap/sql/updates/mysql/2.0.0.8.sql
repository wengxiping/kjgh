ALTER TABLE `#__zhbaidumaps_maps` ADD `showcreateinfo` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `override_id` int(11) NOT NULL default '0';

DROP TABLE IF EXISTS `#__zhbaidumaps_text_overrides`;

CREATE TABLE `#__zhbaidumaps_text_overrides` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `placemark_list_title` text NOT NULL,
  `placemark_list_button_title` text NOT NULL,
  `placemark_list_button_hint` text NOT NULL,
  `panelcontrol_hint` text NOT NULL,
  `panel_detail_title` text NOT NULL,
  `panel_placemarklist_title` text NOT NULL,
  `panel_route_title` text NOT NULL,
  `panel_group_title` text NOT NULL,
  `group_list_title` text NOT NULL,
  `placemark_list_search` tinyint(1) NOT NULL default '0',
  `placemark_list_mapping_type` tinyint(1) NOT NULL default '0',
  `placemark_list_accent` text NOT NULL,
  `placemark_list_mapping` text NOT NULL,
  `placemark_list_accent_side` tinyint(1) NOT NULL default '3',
  `group_list_search` tinyint(1) NOT NULL default '0',
  `group_list_mapping_type` tinyint(1) NOT NULL default '0',
  `group_list_accent` text NOT NULL,
  `group_list_mapping` text NOT NULL,
  `group_list_accent_side` tinyint(1) NOT NULL default '3',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

ALTER TABLE `#__zhbaidumaps_maps` ADD INDEX `idx_override` (`override_id`);
ALTER TABLE `#__zhbaidumaps_text_overrides` ADD INDEX `idx_catid` (`catid`);