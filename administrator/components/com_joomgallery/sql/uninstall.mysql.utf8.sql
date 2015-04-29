DELETE FROM `#__joomgallery`;
DROP TABLE `#__joomgallery`;

DELETE FROM `#__joomgallery_category_details`;
DROP TABLE `#__joomgallery_category_details`;

DELETE FROM `#__joomgallery_catg`;
DROP TABLE `#__joomgallery_catg`;

DELETE FROM `#__joomgallery_comments`;
DROP TABLE `#__joomgallery_comments`;

DELETE FROM `#__joomgallery_config`;
DROP TABLE `#__joomgallery_config`;

DELETE FROM `#__joomgallery_countstop`;
DROP TABLE `#__joomgallery_countstop`;

DELETE FROM `#__joomgallery_image_details`;
DROP TABLE `#__joomgallery_image_details`;

DELETE FROM `#__joomgallery_nameshields`;
DROP TABLE `#__joomgallery_nameshields`;

DELETE FROM `#__joomgallery_users`;
DROP TABLE `#__joomgallery_users`;

DELETE FROM `#__joomgallery_votes`;
DROP TABLE `#__joomgallery_votes`;

DELETE FROM `#__joomgallery_maintenance`;
DROP TABLE `#__joomgallery_maintenance`;

DELETE FROM `#__joomgallery_orphans`;
DROP TABLE `#__joomgallery_orphans`;

DELETE FROM `#__modules_menu` WHERE `moduleid` IN (SELECT id FROM `#__modules` WHERE `position` = 'joom_cpanel');
DELETE FROM `#__modules` WHERE `position` = 'joom_cpanel';