ALTER TABLE `#__joomgallery_config` ADD `jg_storenametagip` INT(1) NOT NULL AFTER `jg_show_nameshields_unreg`;
UPDATE `#__joomgallery_config` SET `jg_storenametagip` = 1;

ALTER TABLE `#__joomgallery_nameshields` MODIFY `nuserip` VARCHAR(45) NOT NULL DEFAULT '';
