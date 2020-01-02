ALTER TABLE `#__zhbaidumaps_maps` ADD `useajaxobject` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `ajaxbufferplacemark` int(5) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `ajaxbufferpath` int(5) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `ajaxbufferroute` int(5) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_maps` ADD `ajaxgetplacemark` tinyint(1) NOT NULL default '0';