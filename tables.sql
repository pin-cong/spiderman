-- --------------------------------------------------------




--
CREATE TABLE IF NOT EXISTS `aws_imageboard_index` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`thread_id` INT(11) NOT NULL DEFAULT 0,
	`last_post_id` INT(11) NOT NULL DEFAULT 0,
	`reply_count` INT(11) NOT NULL DEFAULT 0,
	`recent_reply_ids` TEXT NOT NULL DEFAULT '',
	`sort` TINYINT(2) NOT NULL DEFAULT 0,
	`status` TINYINT(2) NOT NULL DEFAULT 0,
	`sage` TINYINT(2) NOT NULL DEFAULT 0,
	`locked` TINYINT(2) NOT NULL DEFAULT 0,
	`masked` TINYINT(2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `thread_id` (`thread_id`),
	KEY `last_post_id` (`last_post_id`),
	KEY `reply_count` (`reply_count`),
	KEY `sort` (`sort`),
	KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;





--
CREATE TABLE IF NOT EXISTS `aws_imageboard_post` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`uid` INT(11) NOT NULL DEFAULT 0,
	`thread_id` INT(11) NOT NULL DEFAULT 0,
	`time` INT(11) NOT NULL DEFAULT 0,
	`status` TINYINT(2) NOT NULL DEFAULT 0,
	`subject` TEXT NOT NULL DEFAULT '',
	`body` TEXT NOT NULL DEFAULT '',
	`file` TEXT NOT NULL DEFAULT '',
	`file_type` TINYINT(2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `uid` (`uid`),
	INDEX `thread_id` (`thread_id`),
	INDEX `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;





--
