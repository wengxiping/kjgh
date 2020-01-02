DROP TABLE IF EXISTS `wsycu__helloworld`;

CREATE TABLE `wsycu__helloworld` (
	`id`       INT(11)     NOT NULL AUTO_INCREMENT,
	`greeting` VARCHAR(25) NOT NULL,
	`published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`)
)
	ENGINE =MyISAM
	AUTO_INCREMENT =0
	DEFAULT CHARSET =utf8;

INSERT INTO `wsycu__helloworld` (`greeting`) VALUES
('Hello World!'),
('Good bye World!');
