ALTER TABLE `#__zhbaidumaps_markers` ADD `access` int(11) NOT NULL DEFAULT '1';

ALTER TABLE `#__zhbaidumaps_maps` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_createdbyuser` (`createdbyuser`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_access` (`access`);

ALTER TABLE `#__zhbaidumaps_routers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_routers` ADD INDEX `idx_mapid` (`mapid`);

ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_markergroup` (`markergroup`);

ALTER TABLE `#__zhbaidumaps_markergroups` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhbaidumaps_maptypes` ADD INDEX `idx_catid` (`catid`);
