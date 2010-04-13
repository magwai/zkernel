-- Связь: parentid, itemid

CREATE TABLE `%name%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentid` int DEFAULT 0,
  `itemid` int DEFAULT 0
  PRIMARY KEY (`id`),
  KEY `i_parentid` (`parentid`),
  KEY `i_itemid` (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;