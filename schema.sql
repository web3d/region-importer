CREATE TABLE IF NOT EXISTS `region` (
    `id` int(10) unsigned NOT NULL,
    `parent_id` int(10) unsigned NOT NULL,
    `name` varchar(20) NOT NULL,
    `level` tinyint(2) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB;
