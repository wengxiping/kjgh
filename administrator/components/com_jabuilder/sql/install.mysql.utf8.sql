DROP TABLE IF EXISTS `#__jabuilder_pages`;

CREATE TABLE `#__jabuilder_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` text NOT NULL,
  `content` mediumtext NOT NULL,
  `data` mediumtext NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `state` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '1',
  `params` mediumtext NOT NULL,
  `published_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__jabuilder_revisions`;

CREATE TABLE `#__jabuilder_revisions` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `itemid` int(20) NOT NULL DEFAULT '0',
  `itemtype` varchar(10) NOT NULL,
  `data` mediumtext NOT NULL,
  `rev` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `note` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
