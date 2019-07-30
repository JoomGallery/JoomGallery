ALTER TABLE `#__joomgallery_countstop` MODIFY `csip` VARCHAR(45) NOT NULL DEFAULT '';

ALTER TABLE `#__joomgallery_config` ADD `jg_storecommentip` INT(1) NOT NULL AFTER `jg_approvecom`;
UPDATE `#__joomgallery_config` SET `jg_storecommentip` = 1;

ALTER TABLE `#__joomgallery_comments` MODIFY `cmtip` VARCHAR(45) NOT NULL DEFAULT '';

ALTER TABLE `#__joomgallery_config` ADD `jg_storenametagip` INT(1) NOT NULL AFTER `jg_show_nameshields_unreg`;
UPDATE `#__joomgallery_config` SET `jg_storenametagip` = 1;

ALTER TABLE `#__joomgallery_nameshields` MODIFY `nuserip` VARCHAR(45) NOT NULL DEFAULT '';

ALTER TABLE `#__joomgallery_votes` MODIFY `userip` VARCHAR(45) NOT NULL DEFAULT '';
