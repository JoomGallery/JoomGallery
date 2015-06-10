ALTER TABLE `#__joomgallery_config` ADD `jg_motionminiLimit` int(2) NOT NULL DEFAULT 0 AFTER `jg_motionminiHeight`;

ALTER TABLE `#__joomgallery_config` DROP `jg_cooliris`;
ALTER TABLE `#__joomgallery_config` DROP `jg_coolirislink`;
