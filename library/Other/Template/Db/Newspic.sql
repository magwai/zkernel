-- Новости с картинкой: title, stitle, date, pic, message

CREATE TABLE `%name%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `stitle` varchar(255) DEFAULT '',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pic` varchar(255) DEFAULT '',
  `message` mediumtext,
  PRIMARY KEY (`id`),
  KEY `i_date` (`date`),
  KEY `i_stitle` (`stitle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;