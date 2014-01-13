CREATE TABLE IF NOT EXISTS `tablename` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `related_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id related table',
  `folder` varchar(64) NOT NULL DEFAULT '0' COMMENT 'folder',
  `file_name` varchar(64) NOT NULL DEFAULT '0' COMMENT 'filename',
  `uniqid` varchar(15) NOT NULL DEFAULT '0' COMMENT 'file unique id',
  `size` varchar(15) NOT NULL DEFAULT '0' COMMENT 'filesize, b',
  `extension` varchar(5) NOT NULL DEFAULT '0' COMMENT 'file extension',
  PRIMARY KEY (`id`),
  KEY `related_id` (`related_id`),
  KEY `folder` (`folder`,`file_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Files';
DELETE FROM `tablename`;