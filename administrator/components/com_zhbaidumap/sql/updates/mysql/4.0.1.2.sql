ALTER TABLE `#__zhbaidumaps_markers` ADD `tabid` int(11) NOT NULL default '0';

ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_tabid` (`tabid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_articleid` (`articleid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_contactid` (`contactid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_userorder` (`userorder`);
