CREATE TABLE IF NOT EXISTS `#__invitex_config` (
`id` int(11) NOT NULL auto_increment,
`namekey` TEXT,
`value` TEXT,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_imports` (
  `id` int(11) NOT NULL auto_increment,
  `inviter_id` int(11) NOT NULL,
  `provider_email` varchar(50) NOT NULL,
  `message` text,
  `provider` varchar(50) NOT NULL,
  `invites_count` int(11) NOT NULL,
  `date` int(11) NOT NULL,
	`invite_type` int(11) default NULL,
  `invite_url` text,
  `catch_act` text,
  `invite_type_tag` text,
  `message_type` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_imports_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `import_id` int(11) NOT NULL,
  `inviter_id` int(11) NOT NULL,
  `guest` varchar(255) NOT NULL,
  `invitee_email` varchar(400) NOT NULL,
  `invitee_name` text NOT NULL,
  `expires` int(11) NOT NULL,
  `sent` tinyint(4) NOT NULL,
  `sent_at` int(11) NOT NULL,
  `invitee_id` int(11) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `friend_count` int(11) NOT NULL,
  `click_count` int(11) NOT NULL,
  `unsubscribe` tinyint(2) NOT NULL,
  `modified` int(11) NOT NULL,
  `resend` int(11) NOT NULL,
  `resend_count` int(11) NOT NULL,
  `remind` tinyint(4) NOT NULL,
  `remind_count` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_invitation_limit` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `limit` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_inviter_url` (
  `id` int(11) NOT NULL auto_increment,
  `inviter_id` int(11) NOT NULL,
  `clicks` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_invite_success` (
  `id` int(11) NOT NULL auto_increment,
  `inviter_id` int(11) NOT NULL,
  `invitee_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_stored_contacts` (
  `id` int(11) NOT NULL auto_increment,
  `Inviter` int(11) NOT NULL,
  `Invitee` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_stored_emails` (
	`id` int(11) NOT NULL auto_increment,
	`email` text NOT NULL,
	`name` text NOT NULL,
	`importedby` text NOT NULL,
	`importedcount` int(11) NOT NULL,
	`sent_count` int(11) NOT NULL,
	`last_sent_date` text NOT NULL,
	`notification` int(11) NOT NULL,
	`unsubscribe` tinyint(2) NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__techjoomlaAPI_users` (
  `id` int(11) NOT NULL auto_increment,
  `api` varchar(200) NOT NULL,
  `token` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `client` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` TEXT NOT NULL,
	`internal_name` TEXT NOT NULL,
  `description` TEXT NOT NULL,
  `personal_message` TEXT DEFAULT NULL,
  `template_html` TEXT NOT NULL,
	`template_html_subject` TEXT NOT NULL,
  `template_text` TEXT NOT NULL,
	`template_text_subject` TEXT NOT NULL,
	`common_template_text` TEXT NOT NULL,
	`common_template_text_subject` TEXT NOT NULL,
	`template_twitter` TEXT NOT NULL,
	`template_fb_request` TEXT NOT NULL,
	`template_sms` TEXT NOT NULL,
  `invite_methods` TEXT NOT NULL,
	`invite_apis` TEXT NOT NULL,
	`integrate_activity_stream` INT(11) NOT NULL,
	`activity_stream_text` TEXT NOT NULL,
	`widget` TEXT NOT NULL,
  `catch_action` TEXT NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invitex_stored_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `import_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__invite_sms_delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `import_email_id` int(11) NOT NULL,
  `apisms_id` varchar(255) NOT NULL,
  `delivered` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;