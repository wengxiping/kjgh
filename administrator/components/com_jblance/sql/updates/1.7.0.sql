ALTER TABLE `#__jblance_custom_field` MODIFY COLUMN `value` text NOT NULL;

DELETE FROM `#__jblance_rating` WHERE quality_clarity=0;