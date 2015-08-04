ALTER TABLE `#__joomgallery_config` ADD `jg_motionminiLimit` int(2) NOT NULL DEFAULT 0 AFTER `jg_motionminiHeight`;

ALTER TABLE `#__joomgallery_config` DROP `jg_cooliris`;
ALTER TABLE `#__joomgallery_config` DROP `jg_coolirislink`;

ALTER TABLE `#__joomgallery_config` ADD `jg_edit_metadata` INT(1) NOT NULL AFTER `jg_redirect_after_upload`;
