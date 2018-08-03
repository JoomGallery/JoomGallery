ALTER TABLE `#__joomgallery_countstop` MODIFY `csip` VARCHAR(45) NOT NULL DEFAULT '';

ALTER TABLE `#__joomgallery_catg` ADD `allow_download` int(1) NOT NULL AFTER `exclude_search`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_comment` int(1) NOT NULL AFTER `allow_download`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_rating` int(1) NOT NULL AFTER `allow_comment`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_watermark` int(1) NOT NULL AFTER `allow_rating`;
ALTER TABLE `#__joomgallery_catg` ADD `allow_watermark_download` int(1) NOT NULL AFTER `allow_watermark`;

UPDATE `#__joomgallery_catg` SET `allow_download` = 0;
UPDATE `#__joomgallery_catg` SET `allow_comment` = 0;
UPDATE `#__joomgallery_catg` SET `allow_rating` = 0;
UPDATE `#__joomgallery_catg` SET `allow_watermark` = 0;
UPDATE `#__joomgallery_catg` SET `allow_watermark_download` = 0;