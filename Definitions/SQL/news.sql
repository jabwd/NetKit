CREATE TABLE `news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` mediumtext COMMENT 'strlen[0,8000]',
  `title` varchar(100) NOT NULL DEFAULT 'Untitled news' COMMENT 'strlen[0,100]',
  `description` mediumtext,
  `authorID` int(11) DEFAULT NULL COMMENT 'int[>0]',
  `sourceURL` varchar(255) NOT NULL DEFAULT 'Anonymous',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `backgroundImage` varchar(200) DEFAULT NULL,
  `commentCount` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `sourceID` int(11) DEFAULT NULL,
  `backgroundColor` varchar(20) NOT NULL DEFAULT 'F0F0F0',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `sourceID` (`sourceID`),
  KEY `created` (`created`),
  KEY `authorID` (`authorID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8