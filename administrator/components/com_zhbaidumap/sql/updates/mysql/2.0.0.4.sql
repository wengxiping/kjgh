ALTER TABLE `#__zhbaidumaps_markers` ADD `articleid` int(11) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `hrefcontact` text NOT NULL;
ALTER TABLE `#__zhbaidumaps_markers` ADD `hrefarticle` text NOT NULL;
ALTER TABLE `#__zhbaidumaps_markers` ADD `hrefdetail` text NOT NULL;
ALTER TABLE `#__zhbaidumaps_markers` ADD `userorder` int(11) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `iframearticleclass` varchar(250) NOT NULL default '';

ALTER TABLE `#__zhbaidumaps_markers` ADD `toolbarcontact` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `toolbararticle` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `toolbardetail` tinyint(1) NOT NULL default '0';
ALTER TABLE `#__zhbaidumaps_markers` ADD `attributesdetail` text NOT NULL;

ALTER TABLE `#__zhbaidumaps_markergroups` ADD `userorder` int(11) NOT NULL default '0';