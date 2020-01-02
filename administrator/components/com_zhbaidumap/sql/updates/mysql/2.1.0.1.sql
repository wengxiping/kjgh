ALTER TABLE `#__zhbaidumaps_paths` ADD `imgurl` text NOT NULL;
ALTER TABLE `#__zhbaidumaps_paths` ADD `imgclickable` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_paths` ADD `imgbounds` varchar(100) NOT NULL default '';
ALTER TABLE `#__zhbaidumaps_paths` ADD `imgopacity` varchar(20) NOT NULL default '';
ALTER TABLE `#__zhbaidumaps_paths` ADD `imgopacitymanage` tinyint(1) NOT NULL default '1';