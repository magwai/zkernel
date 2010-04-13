-- Подчиненная лента с картинкой: title, stitle, parentid, orderid, pic, message

CREATE TABLE `%name%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `stitle` varchar(255) DEFAULT '',
  `parentid` int DEFAULT 0,
  `orderid` int DEFAULT 0,
  `pic` varchar(255) DEFAULT '',
  `message` mediumtext,
  PRIMARY KEY (`id`),
  KEY `i_parentid` (`parentid`),
  KEY `i_orderid` (`orderid`),
  KEY `i_stitle` (`stitle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;