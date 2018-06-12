ALTER TABLE `#__joomgallery_config` ADD `jg_be_exif_rotation` int(1) NOT NULL AFTER `jg_delete_original`;
UPDATE `#__joomgallery_config` SET `jg_be_exif_rotation` = '1';

ALTER TABLE `#__joomgallery_config` ADD `jg_fe_exif_rotation` int(1) NOT NULL AFTER `jg_edit_metadata`;
UPDATE `#__joomgallery_config` SET `jg_fe_exif_rotation` = '1';