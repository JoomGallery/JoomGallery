ALTER TABLE `#__joomgallery_config` ADD `jg_alternative_layout` varchar(255) NOT NULL AFTER `jg_report_hint`;

ALTER TABLE `#__joomgallery_config` CHANGE `jg_bigpic_open` `jg_bigpic_open` varchar(50) NOT NULL;
ALTER TABLE `#__joomgallery_config` CHANGE `jg_detailpic_open` `jg_detailpic_open` varchar(50) NOT NULL;

ALTER TABLE `#__joomgallery_config` ADD `jg_useruploaddefaultcat` INT( 1 ) NOT NULL AFTER `jg_userspace`;

ALTER TABLE `#__joomgallery_config` ADD `jg_unregistered_permissions` int(1) NOT NULL AFTER `jg_approve`;

ALTER TABLE `#__joomgallery_config` ADD `jg_useruploadajax` int(1) NOT NULL AFTER `jg_maxuploadfields`;