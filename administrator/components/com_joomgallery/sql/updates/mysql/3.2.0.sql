ALTER TABLE `#__joomgallery` CHANGE `imgfilename` `imgfilename` VARCHAR(255) NOT NULL;
ALTER TABLE `#__joomgallery` CHANGE `imgthumbname` `imgthumbname` VARCHAR(255) NOT NULL;

ALTER TABLE `#__joomgallery_catg` CHANGE `alias` `alias` VARCHAR(2048) NOT NULL;
ALTER TABLE `#__joomgallery_catg` CHANGE `catpath` `catpath` VARCHAR(2048) NOT NULL;
