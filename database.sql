CREATE TABLE IF NOT EXISTS `users_oauth` (
  `uo_usr_id` int(11) unsigned NOT NULL COMMENT 'The user ID from your users table',
  `oauth_provider` varchar(10) NOT NULL COMMENT 'The provider: Twitter, Tumblr, LinkedIn, etc.',
  `oauth_provider_usr_id` varchar(99) NOT NULL COMMENT 'The user\'s ID from the provider',
  `oauth_username` varchar(99) default NULL COMMENT 'The user\'s username from the provider',
  `oauth_token` varchar(99) NOT NULL,
  `oauth_secret` varchar(99) NOT NULL,
  PRIMARY KEY  (`uo_usr_id`,`oauth_provider`),
  KEY `uo_usersid` (`oauth_provider_usr_id`),
  KEY `oauth_username` (`oauth_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;