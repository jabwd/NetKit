CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL DEFAULT 'This is a notification',
  `URL` varchar(255) DEFAULT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `created` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `viewed` (`viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8