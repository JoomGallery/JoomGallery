ALTER TABLE `#__joomgallery_config` ADD `jg_storecommentip` INT(1) NOT NULL AFTER `jg_approvecom`;
UPDATE `#__joomgallery_config` SET `jg_storecommentip` = 1;

ALTER TABLE `#__joomgallery_comments` MODIFY `cmtip` VARCHAR(45) NOT NULL DEFAULT '';
