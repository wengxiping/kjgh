ALTER TABLE `#__zhbaidumaps_markers` ADD `rating_count` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `labelanchorx` int(5) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `labelanchory` int(5) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `labelstyle` varchar(250) NOT NULL default '';
ALTER TABLE `#__zhbaidumaps_markers` ADD `labelcontent` text NOT NULL;
