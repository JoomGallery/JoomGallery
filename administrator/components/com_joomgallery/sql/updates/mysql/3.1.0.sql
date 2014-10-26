ALTER TABLE `#__joomgallery_config` ADD `jg_uploadicongallery` int(1) NOT NULL AFTER `jg_showdescriptioningalleryview`;
ALTER TABLE `#__joomgallery_config` ADD `jg_uploadiconcategory` int(1) NOT NULL AFTER `jg_category_rss_icon`;
ALTER TABLE `#__joomgallery_config` ADD `jg_uploadiconsubcat` int(1) NOT NULL AFTER `jg_showtotalsubcathits`;

ALTER TABLE `#__joomgallery` ADD `downloads` int(11) NOT NULL AFTER `hits`;
ALTER TABLE `#__joomgallery_config` ADD `jg_showdetaildownloads` int(1) NOT NULL AFTER `jg_showdetailhits`;
ALTER TABLE `#__joomgallery_config` ADD `jg_showdownloads` int(1) NOT NULL AFTER `jg_showhits`;

ALTER TABLE `#__joomgallery_config` ADD `jg_msg_global_from` int(1) NOT NULL AFTER `jg_msg_report_toowner`;

ALTER TABLE `#__joomgallery_config` DROP `jg_wrongvaluecolor`;

ALTER TABLE `#__joomgallery_config` CHANGE `jg_dyncropbgcol` `jg_dyncropbgcol` VARCHAR(12) NOT NULL;
ALTER TABLE `#__joomgallery_config` CHANGE `jg_openjs_background` `jg_openjs_background` VARCHAR(12) NOT NULL;
ALTER TABLE `#__joomgallery_config` CHANGE `jg_dhtml_border` `jg_dhtml_border` VARCHAR(12) NOT NULL;

ALTER TABLE `#__joomgallery_catg` ADD `password` varchar(100) NOT NULL AFTER `in_hidden`;

ALTER TABLE `#__joomgallery_config` ADD `jg_msg_rejectimg_type` int(1) NOT NULL AFTER `jg_msg_report_toowner`;
UPDATE `#__joomgallery_config` SET `jg_msg_rejectimg_type` = 1;

ALTER TABLE `#__joomgallery_catg` ADD `exclude_toplists` int(1) NOT NULL;
ALTER TABLE `#__joomgallery_catg` ADD `exclude_search` int(1) NOT NULL;

ALTER TABLE `#__joomgallery_config` ADD `jg_maxuserimage_timespan` int(9) NOT NULL AFTER `jg_maxuserimage`;