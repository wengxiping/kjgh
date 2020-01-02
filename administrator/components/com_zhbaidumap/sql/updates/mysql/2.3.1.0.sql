ALTER TABLE `#__zhbaidumaps_maps` ADD `placemark_rating` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `hovermarker` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `usermarkersinsert` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhbaidumaps_maps` ADD `usermarkersupdate` tinyint(1) NOT NULL default '1';
ALTER TABLE `#__zhbaidumaps_maps` ADD `usermarkersdelete` tinyint(1) NOT NULL default '1';