CREATE TABLE `messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `authorID` int(11) DEFAULT NULL,
  `recipientID` int(11) DEFAULT NULL,
  `title` varchar(140) DEFAULT NULL,
  `content` text,
  `sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8