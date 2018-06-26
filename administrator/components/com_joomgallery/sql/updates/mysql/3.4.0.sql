ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgtitle` text NOT NULL AFTER `jg_filenamereplace`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgtext` text NOT NULL AFTER `jg_replaceimgtitle`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgauthor` text NOT NULL AFTER `jg_replaceimgtext`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgdate` text NOT NULL AFTER `jg_replaceimgauthor`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replacemetakey` text NOT NULL AFTER `jg_replaceimgdate`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replacemetadesc` text NOT NULL AFTER `jg_replacemetakey`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceshowwarning` int(1) NOT NULL AFTER `jg_replacemetadesc`;