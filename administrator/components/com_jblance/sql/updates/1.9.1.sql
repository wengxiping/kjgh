ALTER TABLE `#__jblance_deposit` ADD (`tax_percent_dep` float NOT NULL DEFAULT '0');

ALTER TABLE `#__jblance_withdraw` CHANGE `withdrawFee` `withdrawFeeFixed` float NOT NULL;
ALTER TABLE `#__jblance_withdraw` ADD (`withdrawFeePerc` float NOT NULL DEFAULT '0');

ALTER TABLE `#__jblance_paymode` CHANGE `withdrawFee` `withdrawFeeFixed` float DEFAULT '0';
ALTER TABLE `#__jblance_paymode` ADD (`withdrawFeePerc` float NOT NULL DEFAULT '0');