ALTER TABLE `#__joomgallery_config` ADD `jg_motionminiLimit` int(2) NOT NULL DEFAULT 0 AFTER `jg_motionminiHeight`;

ALTER TABLE `#__joomgallery_config` DROP `jg_cooliris`;
ALTER TABLE `#__joomgallery_config` DROP `jg_coolirislink`;

ALTER TABLE `#__joomgallery_config` ADD `jg_edit_metadata` INT(1) NOT NULL AFTER `jg_redirect_after_upload`;

ALTER TABLE `#__joomgallery_config` DROP `jg_showdetailfavourite`;

ALTER TABLE `#__joomgallery_config` ADD `jg_use_listbox_max_user_count` INT(1) NOT NULL AFTER `jg_disableunrequiredchecks`;
UPDATE `#__joomgallery_config` SET `jg_use_listbox_max_user_count` = 25;
