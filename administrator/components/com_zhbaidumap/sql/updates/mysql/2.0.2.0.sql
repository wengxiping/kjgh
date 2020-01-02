UPDATE `#__zhbaidumaps_maps` SET `markerlistpos`=0 WHERE `markerlistpos`=100;

ALTER TABLE `#__zhbaidumaps_maps` CHANGE `markerlistpos` `markerlistpos` tinyint(1) NOT NULL default '0';