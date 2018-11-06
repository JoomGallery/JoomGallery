ALTER TABLE `#__joomgallery_countstop` MODIFY `csip` VARCHAR(45) NOT NULL DEFAULT '';

ALTER TABLE `#__joomgallery_catg` ADD `allow_download` int(1) NOT NULL default -1 AFTER `exclude_search`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_comment` int(1) NOT NULL default -1 AFTER `allow_download`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_rating` int(1) NOT NULL default -1 AFTER `allow_comment`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_watermark` int(1) NOT NULL default -1 AFTER `allow_rating`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_watermark_download` int(1) NOT NULL default -1 AFTER `allow_watermark`;

UPDATE `#__joomgallery_catg` SET `allow_download` = -1;
UPDATE `#__joomgallery_catg` SET `allow_comment` = -1;
UPDATE `#__joomgallery_catg` SET `allow_rating` = -1;
UPDATE `#__joomgallery_catg` SET `allow_watermark` = -1;
UPDATE `#__joomgallery_catg` SET `allow_watermark_download` = -1;