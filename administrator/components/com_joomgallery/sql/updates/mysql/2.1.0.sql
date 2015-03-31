ALTER TABLE `#__joomgallery_catg` DROP `ordering`;

ALTER TABLE `#__joomgallery_config` ADD `jg_showgeotagging` int(1) NOT NULL AFTER `jg_showexifdata`;
UPDATE `#__joomgallery_config` SET `jg_showgeotagging` = 1 WHERE `jg_geotagging` <> '';
ALTER TABLE `#__joomgallery_config` CHANGE `jg_geotagging` `jg_geotaggingkey` text NOT NULL;
UPDATE `#__joomgallery_config` SET `jg_geotaggingkey` = '';

ALTER TABLE `#__joomgallery_config` ADD `jg_usercatthumbalign` int(1) NOT NULL AFTER `jg_usercatacc`;

ALTER TABLE `#__joomgallery_config` ADD `jg_disableunrequiredchecks` INT( 1 ) NOT NULL AFTER `jg_itemid`;
ALTER TABLE `#__joomgallery_config` ADD `jg_ajaxcategoryselection` INT( 1 ) NOT NULL AFTER `jg_itemid`;

ALTER TABLE `#__joomgallery_config` ADD `jg_allimagesofcategory` INT( 1 ) NOT NULL AFTER `jg_usefavouritesforzip`;

ALTER TABLE `#__joomgallery_config` ADD `jg_votingonlyreg` INT( 1 ) NOT NULL AFTER `jg_votingonlyonce`;
UPDATE `#__joomgallery_config` SET `jg_votingonlyreg` = `jg_votingonlyonce`;
UPDATE `#__joomgallery_config` SET `jg_votingonlyonce` = 0;

CREATE TABLE IF NOT EXISTS `#__joomgallery_image_details` (
  `id` int(11) NOT NULL,
  `details_key` varchar(255) NOT NULL,
  `details_value` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`,`details_key`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_category_details` (
  `id` int(11) NOT NULL,
  `details_key` varchar(255) NOT NULL,
  `details_value` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`,`details_key`)
) DEFAULT CHARSET=utf8;

ALTER TABLE `#__joomgallery_config` DROP `jg_dateformat`;