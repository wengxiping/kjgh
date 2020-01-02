DROP TABLE IF EXISTS `#__zhbaidumaps_maps`;

CREATE TABLE `#__zhbaidumaps_maps` (
  `id` int(11) NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `width` int(5) NOT NULL default '0',
  `height` int(5) NOT NULL default '0',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `zoom` int(3) NOT NULL default '0',
  `minzoom` int(3) NOT NULL default '0',
  `maxzoom` int(3) NOT NULL default '0',
  `draggable` tinyint(1) NOT NULL default '1',
  `doubleclickzoom` tinyint(1) NOT NULL default '0',
  `scrollwheelzoom` tinyint(1) NOT NULL default '0',
  `scalecontrol` tinyint(1) NOT NULL default '0',
  `scalepos` tinyint(1) NOT NULL default '0',
  `scaleofsx` int(5) NOT NULL default '0',
  `scaleofsy` int(5) NOT NULL default '0',
  `maptype` tinyint(1) NOT NULL default '0',
  `maptypecontrol` tinyint(1) NOT NULL default '0',
  `maptypepos` tinyint(1) NOT NULL default '0',
  `maptypeofsx` int(5) NOT NULL default '0',
  `maptypeofsy` int(5) NOT NULL default '0',
  `overviewmapcontrol` tinyint(1) NOT NULL default '0',
  `navigationcontrol` tinyint(1) NOT NULL default '0',
  `navigationpos` tinyint(1) NOT NULL default '0',
  `navigationofsx` int(5) NOT NULL default '0',
  `navigationofsy` int(5) NOT NULL default '0',
  `balloon` tinyint(1) NOT NULL default '0',
  `openballoon` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `markercluster` tinyint(1) NOT NULL default '0',
  `markerclustergroup` tinyint(1) NOT NULL default '0',
  `clusterzoom` int(3) NOT NULL default '0',
  `kmllayer` text NOT NULL,
  `markergroupcontrol` tinyint(1) NOT NULL default '0',
  `markergrouptype` tinyint(1) NOT NULL default '0',
  `markergroupwidth` int(5) NOT NULL default '20',
  `markergroupshowicon` tinyint(1) NOT NULL default '0',
  `markergroupshowiconall` tinyint(1) NOT NULL default '100',
  `markergroupcss` int(5) NOT NULL default '0',
  `markergroupdesc1` text NOT NULL,
  `markergroupdesc2` text NOT NULL,
  `markergrouptitle` varchar(255) NOT NULL default '',
  `markergroupsep1` tinyint(1) NOT NULL default '0',
  `markergroupsep2` tinyint(1) NOT NULL default '0',
  `markergrouporder` tinyint(1) NOT NULL default '0',
  `markergroupsearch` tinyint(1) NOT NULL default '0',
  `markerlist` tinyint(1) NOT NULL default '0',
  `markerlistpos` tinyint(1) NOT NULL default '0',
  `markerlistwidth` int(5) NOT NULL default '0',
  `markerlistheight` int(5) NOT NULL default '0',
  `markerlistbgcolor` text NOT NULL,
  `markerlistaction` tinyint(1) NOT NULL default '0',
  `markerlistcontent` tinyint(1) NOT NULL default '0',
  `markerlistbuttonpos` tinyint(1) NOT NULL default '0',
  `markerlistbuttonofsx` int(5) NOT NULL default '0',
  `markerlistbuttonofsy` int(5) NOT NULL default '0',
  `markerlistbuttontype` tinyint(1) NOT NULL default '0',
  `markerlistsearch` tinyint(1) NOT NULL default '0',
  `markerlistsync` tinyint(1) NOT NULL default '0',
  `headerhtml` text NOT NULL,
  `footerhtml` text NOT NULL,
  `headersep` tinyint(1) NOT NULL default '0',
  `footersep` tinyint(1) NOT NULL default '0',
  `openstreet` tinyint(1) NOT NULL default '0',
  `findcontrol` tinyint(1) NOT NULL default '0',
  `findwidth` int(5) NOT NULL default '0',
  `findpos` tinyint(1) NOT NULL default '0',
  `findofsx` int(5) NOT NULL default '0',
  `findofsy` int(5) NOT NULL default '0',
  `findroute` tinyint(1) NOT NULL default '0',
  `usercontact` tinyint(1) NOT NULL default '0',
  `useruser` tinyint(1) NOT NULL default '0',
  `usermarkers` tinyint(1) NOT NULL default '0',
  `usermarkersfilter` tinyint(1) NOT NULL default '0',
  `usermarkerspublished` tinyint(1) NOT NULL default '0',
  `usermarkersicon` tinyint(1) NOT NULL default '1',
  `usercontactpublished` tinyint(1) NOT NULL default '0',
  `usermarkersinsert` tinyint(1) NOT NULL default '1',
  `usermarkersupdate` tinyint(1) NOT NULL default '1',
  `usermarkersdelete` tinyint(1) NOT NULL default '1',
  `routedraggable` tinyint(1) NOT NULL default '0',
  `routeshowpanel` tinyint(1) NOT NULL default '0',
  `routeaddress` text NOT NULL,
  `autoposition` tinyint(1) NOT NULL default '0',
  `geolocationcontrol` tinyint(1) NOT NULL default '0',
  `geolocationpos` tinyint(1) NOT NULL default '2',
  `geolocationofsx` int(5) NOT NULL default '0',
  `geolocationofsy` int(5) NOT NULL default '0',
  `geolocationbutton` tinyint(1) NOT NULL default '1',
  `lang` varchar(20) NOT NULL default '',
  `custommaptype` tinyint(1) NOT NULL default '0',
  `custommaptypelist` text NOT NULL,
  `usercontactattributes` text NOT NULL,
  `mapstyles` text NOT NULL,
  `css2load` text NOT NULL,
  `js2load` text NOT NULL,
  `cssclassname` text NOT NULL,
  `mapbounds` varchar(100) NOT NULL default '',
  `routedriving` tinyint(1) NOT NULL default '1',
  `routewalking` tinyint(1) NOT NULL default '1',
  `routebicycling` tinyint(1) NOT NULL default '1',
  `routetransit` tinyint(1) NOT NULL default '1',
  `useajax` tinyint(1) NOT NULL default '0',
  `useajaxobject` tinyint(1) NOT NULL default '0',
  `ajaxbufferplacemark` int(5) NOT NULL default '0',
  `ajaxbufferpath` int(5) NOT NULL default '0',
  `ajaxbufferroute` int(5) NOT NULL default '0',
  `ajaxgetplacemark` tinyint(1) NOT NULL default '0',
  `zoombyfind` int(3) NOT NULL default '100',
  `markergroupctlmarker` tinyint(1) NOT NULL default '1',
  `markergroupctlpath` tinyint(1) NOT NULL default '0',
  `markerorder` tinyint(1) NOT NULL default '0',
  `showcreateinfo` tinyint(1) NOT NULL default '0',
  `override_id` int(11) NOT NULL default '0',
  `panelinfowin` tinyint(1) NOT NULL default '0',
  `panelwidth` int(3) NOT NULL default '300',
  `panelstate` tinyint(1) NOT NULL default '0',
  `placemark_rating` tinyint(1) NOT NULL default '0',
  `hovermarker` tinyint(1) NOT NULL default '0',
  `disableautopan` tinyint(1) NOT NULL default '0',
  `mapcentercontrol` tinyint(1) NOT NULL default '0',
  `mapcentercontrolpos` tinyint(1) NOT NULL default '2',
  `overlayopacitycontrol` tinyint(1) NOT NULL default '0',
  `overlayopacitycontrolpos` tinyint(1) NOT NULL default '2',  
  `mapcentercontrolofsx` int(5) NOT NULL default '0',
  `mapcentercontrolofsy` int(5) NOT NULL default '0',
  `overlayopacitycontrolofsx` int(5) NOT NULL default '0',
  `overlayopacitycontrolofsy` int(5) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhbaidumaps_markers`;

CREATE TABLE `#__zhbaidumaps_markers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `latitude` varchar(20) NOT NULL default '',
  `longitude` varchar(20) NOT NULL default '',
  `addresstext` text NOT NULL,
  `mapid` int(11) NOT NULL default '0',
  `openbaloon` tinyint(1) NOT NULL default '0',
  `actionbyclick` tinyint(1) NOT NULL default '1',
  `zoombyclick` int(3) NOT NULL default '100',
  `baloon` tinyint(1) NOT NULL default '0',
  `icontype` varchar(250) NOT NULL default '',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `hrefsite` text NOT NULL,
  `hrefimage` text NOT NULL,
  `hrefimagethumbnail` text NOT NULL,
  `hrefsitename` text NOT NULL,
  `markergroup` int(11) NOT NULL default '0',
  `markercontent` tinyint(1) NOT NULL default '0',
  `contactid` int(11) NOT NULL default '0',
  `createdbyuser` int(11) NOT NULL default '0',
  `showcontact` tinyint(1) NOT NULL default '0',
  `showuser` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `createddate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userprotection` tinyint(1) NOT NULL default '0',
  `params` text NOT NULL,
  `attribute1` text NOT NULL,
  `attribute2` text NOT NULL,
  `attribute3` text NOT NULL,
  `attribute4` text NOT NULL,
  `attribute5` text NOT NULL,
  `attribute6` text NOT NULL,
  `attribute7` text NOT NULL,
  `attribute8` text NOT NULL,
  `attribute9` text NOT NULL,
  `articleid` int(11) NOT NULL default '0',
  `hrefcontact` text NOT NULL,
  `hrefarticle` text NOT NULL,
  `hrefdetail` text NOT NULL,
  `userorder` int(11) NOT NULL default '0',
  `iframearticleclass` varchar(250) NOT NULL default '',
  `toolbarcontact` tinyint(1) NOT NULL default '0',
  `toolbararticle` tinyint(1) NOT NULL default '0',
  `toolbardetail` tinyint(1) NOT NULL default '0',
  `attributesdetail` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rating_value` FLOAT NOT NULL DEFAULT '0',
  `rating_count` int(11) NOT NULL DEFAULT '0',
  `labelanchorx` int(5) NOT NULL default '0',
  `labelanchory` int(5) NOT NULL default '0',
  `labelstyle` varchar(250) NOT NULL default '',
  `labelcontent` text NOT NULL,
  `includeinlist` tinyint(1) NOT NULL default '1',
  `hoverhtml` text NOT NULL,
  `access` int(11) NOT NULL DEFAULT '1',
  `tabid` int(11) NOT NULL default '0',
  `preparecontent` tinyint(1) NOT NULL default '0',
  `tag_show` tinyint(1) NOT NULL default '0',
  `tag_style` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE `#__zhbaidumaps_routers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `providealt` tinyint(1) NOT NULL default '0',
  `optimizewaypoints` tinyint(1) NOT NULL default '0',
  `avoidhighways` tinyint(1) NOT NULL default '0',
  `avoidtolls` tinyint(1) NOT NULL default '0',
  `travelmode` tinyint(1) NOT NULL default '0',
  `unitsystem` tinyint(1) NOT NULL default '0',
  `route` text NOT NULL,
  `routebymarker` text NOT NULL,
  `mapid` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `showtype` tinyint(1) NOT NULL default '0',
  `draggable` tinyint(1) NOT NULL default '0',
  `showpanel` tinyint(1) NOT NULL default '0',
  `showpaneltotal` tinyint(1) NOT NULL default '1',
  `showdescription` tinyint(1) NOT NULL default '0',  
  `suppressmarkers` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

CREATE TABLE `#__zhbaidumaps_paths` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `weight` tinyint(1) NOT NULL default '0',
  `color` varchar(250) NOT NULL default '',
  `hover_color` varchar(250) NOT NULL default '',
  `opacity` varchar(20) NOT NULL default '',
  `path` text NOT NULL,
  `kmllayer` text NOT NULL,
  `mapid` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `descriptionhtml` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `showtype` tinyint(1) NOT NULL default '0',
  `suppressinfowindows` tinyint(1) NOT NULL default '0',
  `geodesic` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `infowincontent` tinyint(1) NOT NULL default '0',
  `actionbyclick` tinyint(1) NOT NULL default '0',
  `objecttype` tinyint(1) NOT NULL default '0',
  `fillcolor` varchar(250) NOT NULL default '',
  `fillopacity` varchar(20) NOT NULL default '',
  `hover_fillcolor` varchar(250) NOT NULL default '',
  `radius` varchar(250) NOT NULL default '',
  `markergroup` int(11) NOT NULL default '0',
  `hrefsite` text NOT NULL,
  `hrefsitename` text NOT NULL,
  `hoverhtml` text NOT NULL,
  `imgurl` text NOT NULL,
  `imgclickable` tinyint(1) NOT NULL default '0',  
  `imgbounds` varchar(100) NOT NULL default '',
  `imgopacity` varchar(20) NOT NULL default '',
  `imgopacitymanage` tinyint(1) NOT NULL default '1',
  `minzoom` int(3) NOT NULL default '0',
  `maxzoom` int(3) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhbaidumaps_markergroups`;

CREATE TABLE `#__zhbaidumaps_markergroups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `icontype` varchar(250) NOT NULL default '',
  `overridegroupicon` tinyint(1) NOT NULL default '0',
  `iconofsetx` tinyint(1) NOT NULL default '0',
  `iconofsety` tinyint(1) NOT NULL default '0',
  `overridemarkericon` tinyint(1) NOT NULL default '0',
  `activeincluster` tinyint(1) NOT NULL default '0',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userorder` int(11) NOT NULL default '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;

DROP TABLE IF EXISTS `#__zhbaidumaps_maptypes`;

CREATE TABLE `#__zhbaidumaps_maptypes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `catid` int(11) NOT NULL default '0',
  `title` varchar(250) NOT NULL default '',
  `description` text NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `gettileurl` text NOT NULL,
  `tilewidth` int(5) NOT NULL default '256',
  `tileheight` int(5) NOT NULL default '256',
  `ispng` tinyint(1) NOT NULL default '1',
  `minzoom` int(3) NOT NULL default '0',
  `maxzoom` int(3) NOT NULL default '18',
  `opacity` varchar(20) NOT NULL default '',
  `layertype` tinyint(1) NOT NULL default '1',
  `projectionglobal` text NOT NULL,
  `projectiondefinition` text NOT NULL,
  `fromlatlngtopoint` text NOT NULL,
  `frompointtolatlng` text NOT NULL,
  `publish_up` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM CHARACTER SET `utf8`;


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

ALTER TABLE `#__zhbaidumaps_maps` ADD INDEX `idx_catid` (`catid`);

ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_markergroup` (`markergroup`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_createdbyuser` (`createdbyuser`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_access` (`access`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_tabid` (`tabid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_articleid` (`articleid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_contactid` (`contactid`);
ALTER TABLE `#__zhbaidumaps_markers` ADD INDEX `idx_userorder` (`userorder`);

ALTER TABLE `#__zhbaidumaps_routers` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_routers` ADD INDEX `idx_mapid` (`mapid`);

ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_mapid` (`mapid`);
ALTER TABLE `#__zhbaidumaps_paths` ADD INDEX `idx_markergroup` (`markergroup`);

ALTER TABLE `#__zhbaidumaps_markergroups` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_markergroups` ADD INDEX `idx_userorder` (`userorder`);

ALTER TABLE `#__zhbaidumaps_maptypes` ADD INDEX `idx_catid` (`catid`);
ALTER TABLE `#__zhbaidumaps_maps` ADD INDEX `idx_override` (`override_id`);
ALTER TABLE `#__zhbaidumaps_text_overrides` ADD INDEX `idx_catid` (`catid`);