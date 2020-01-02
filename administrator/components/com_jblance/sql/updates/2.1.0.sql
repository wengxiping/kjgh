ALTER TABLE `#__jblance_paymode` ADD (`is_subscription` tinyint(1) NOT NULL DEFAULT '1');
ALTER TABLE `#__jblance_paymode` ADD (`is_deposit` tinyint(1) NOT NULL DEFAULT '1');
ALTER TABLE `#__jblance_paymode` CHANGE `withdraw` `is_withdraw` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__jblance_plan` CHANGE `adwords` `option_params` mediumtext NOT NULL;