ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgtitle` int(4) NOT NULL AFTER `jg_filenamereplace`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgtext` int(4) NOT NULL AFTER `jg_replaceimgtitle`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgauthor` int(4) NOT NULL AFTER `jg_replaceimgtext`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceimgdate` int(4) NOT NULL AFTER `jg_replaceimgauthor`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replacemetakey` int(4) NOT NULL AFTER `jg_replaceimgdate`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replacemetadesc` int(4) NOT NULL AFTER `jg_replacemetakey`;
ALTER TABLE `#__joomgallery_config` ADD `jg_replaceshowwarning` int(1) NOT NULL AFTER `jg_replacemetadesc`;
