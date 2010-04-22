-- Список: title, stitle, orderid

CREATE TABLE `%name%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `stitle` varchar(255) DEFAULT '',
  `orderid` int DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `i_orderid` (`orderid`),
  KEY `i_stitle` (`stitle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;