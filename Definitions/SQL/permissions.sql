CREATE TABLE `permissions` (
  `permissionID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(240) DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`permissionID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8