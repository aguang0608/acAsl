CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time',
  `post_id` int(11) NOT NULL COMMENT 'post_id',
  `reply_id` int(11) NOT NULL COMMENT 'reply_id',
  `name` varchar(1024) COLLATE utf8_bin NOT NULL COMMENT 'name',
  `email` varchar(1024) COLLATE utf8_bin NOT NULL COMMENT 'email',
  `page` varchar(1024) COLLATE utf8_bin NOT NULL COMMENT 'page',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'content',
  PRIMARY KEY (`id`),
  KEY `time` (`time`,`post_id`,`reply_id`,`name`(255),`email`(255),`page`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='comment' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'post-id',
  `title` varchar(1024) COLLATE utf8_bin NOT NULL COMMENT 'post-title',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'post-time',
  `content` text COLLATE utf8_bin NOT NULL COMMENT 'post-content',
  PRIMARY KEY (`id`),
  KEY `title` (`title`(255),`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='post' AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `relation` (
  `tag_id` int(11) NOT NULL COMMENT 'tag_id',
  `post_id` int(11) NOT NULL COMMENT 'post_id',
  KEY `tag_id` (`tag_id`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='relation';

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time',
  `name` varchar(1024) COLLATE utf8_bin NOT NULL COMMENT 'name',
  PRIMARY KEY (`id`),
  KEY `time` (`time`,`name`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='tag' AUTO_INCREMENT=2 ;
