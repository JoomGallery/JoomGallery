ALTER TABLE `#__joomgallery_config` ADD `jg_watermarksize` int(1) NOT NULL AFTER `jg_watermarkpos`;
UPDATE `#__joomgallery_config` SET `jg_watermarksize` = 15;

ALTER TABLE `#__joomgallery_config` ADD `jg_watermarkzoom` int(1) NOT NULL AFTER `jg_watermarksize`;
UPDATE `#__joomgallery_config` SET `jg_watermarkzoom` = 1;
