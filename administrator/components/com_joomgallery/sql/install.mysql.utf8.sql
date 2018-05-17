CREATE TABLE IF NOT EXISTS `#__joomgallery` (
  `id` int(11) NOT NULL auto_increment,
  `asset_id` int(10) NOT NULL default 0,
  `catid` int(11) NOT NULL default 0,
  `imgtitle` text NOT NULL,
  `alias` varchar(255) NOT NULL default '',
  `imgauthor` varchar(50) default NULL,
  `imgtext` text NOT NULL,
  `imgdate` datetime NOT NULL,
  `hits` int(11) NOT NULL default '0',
  `downloads` int(11) NOT NULL default '0',
  `imgvotes` int(11) NOT NULL default '0',
  `imgvotesum` int(11) NOT NULL default '0',
  `access` tinyint(3) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `hidden` tinyint(1) NOT NULL default '0',
  `featured` tinyint(1) NOT NULL default '0',
  `imgfilename` varchar(255) NOT NULL default '',
  `imgthumbname` varchar(255) NOT NULL default '',
  `checked_out` int(11) NOT NULL default '0',
  `owner` int(11) UNSIGNED NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '0',
  `useruploaded` tinyint(1) NOT NULL default '0',
  `ordering` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  PRIMARY KEY (`id`),
  INDEX idx_catid (`catid`),
  INDEX idx_owner (`owner`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_category_details` (
  `id` int(11) NOT NULL,
  `details_key` varchar(255) NOT NULL,
  `details_value` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`,`details_key`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_catg` (
  `cid` int(11) NOT NULL auto_increment,
  `asset_id` int(10) NOT NULL default 0,
  `name` varchar(255) NOT NULL default '',
  `alias` varchar(2048) NOT NULL default '',
  `parent_id` int(11) NOT NULL default 0,
  `lft` int(11) NOT NULL default 0,
  `rgt` int(11) NOT NULL default 0,
  `level` int(1) UNSIGNED NOT NULL default 0,
  `description` text,
  `access` tinyint(3) unsigned NOT NULL default 0,
  `published` tinyint(1) NOT NULL default 0,
  `hidden` tinyint(1) NOT NULL default 0,
  `in_hidden` tinyint(1) NOT NULL default 0,
  `password` varchar(100) NOT NULL default '',
  `owner` int(11) default 0,
  `thumbnail` int(11),
  `img_position` int(10) default 0,
  `catpath` varchar(2048) NOT NULL default '',
  `params` text NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `exclude_toplists` int(1) NOT NULL,
  `exclude_search` int(1) NOT NULL,
  PRIMARY KEY (`cid`),
  INDEX idx_parent_id (`parent_id`)
) DEFAULT CHARSET=utf8;

INSERT INTO `#__joomgallery_catg` VALUES ('1', '0', 'ROOT', 'root', '0', '0', '0', '0', NULL, '1', '1', '0', '0', '', '0', NULL, '0', '', '', '', '', 0, 0);

CREATE TABLE IF NOT EXISTS `#__joomgallery_comments` (
  `cmtid` int(11) NOT NULL auto_increment,
  `cmtpic` int(11) NOT NULL default '0',
  `cmtip` varchar(15) NOT NULL default '',
  `userid` int(11) UNSIGNED NOT NULL default '0',
  `cmtname` varchar(50) NOT NULL default '',
  `cmttext` text NOT NULL,
  `cmtdate` datetime NOT NULL,
  `published` tinyint(1) NOT NULL default '0',
  `approved` tinyint(1) NOT NULL default '1',
  PRIMARY KEY (`cmtid`),
  INDEX idx_cmtpic (`cmtpic`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_config` (
  `id` int(1) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `jg_pathimages` varchar(100) NOT NULL,
  `jg_pathoriginalimages` varchar(100) NOT NULL,
  `jg_paththumbs` varchar(100) NOT NULL,
  `jg_pathftpupload` varchar(100) NOT NULL,
  `jg_pathtemp` varchar(100) NOT NULL,
  `jg_wmpath` varchar(100) NOT NULL,
  `jg_wmfile` varchar(50) NOT NULL,
  `jg_use_real_paths` int(1) NOT NULL,
  `jg_checkupdate` int(1) NOT NULL,
  `jg_filenamewithjs` int(1) NOT NULL,
  `jg_filenamereplace` text NOT NULL,
  `jg_replaceimgtitle` text NOT NULL,
  `jg_replaceimgtext` text NOT NULL,
  `jg_replaceimgauthor` text NOT NULL,
  `jg_replaceimgdate` text NOT NULL,
  `jg_replacemetakey` text NOT NULL,
  `jg_replacemetadesc` text NOT NULL,
  `jg_thumbcreation` varchar(5) NOT NULL,
  `jg_fastgd2thumbcreation` int(1) NOT NULL,
  `jg_impath` varchar(50) NOT NULL,
  `jg_resizetomaxwidth` int(1) NOT NULL,
  `jg_maxwidth` int(5) NOT NULL,
  `jg_picturequality` int(3) NOT NULL,
  `jg_useforresizedirection` int(1) NOT NULL,
  `jg_cropposition` int(1) NOT NULL,
  `jg_thumbwidth` int(5) NOT NULL,
  `jg_thumbheight` int(5) NOT NULL,
  `jg_thumbquality` int(3) NOT NULL,
  `jg_uploadorder` int(1) NOT NULL,
  `jg_useorigfilename` int(1) NOT NULL,
  `jg_filenamenumber` int(1) NOT NULL,
  `jg_delete_original` int(1) NOT NULL,
  `jg_msg_upload_type` int(1) NOT NULL,
  `jg_msg_upload_recipients` text NOT NULL,
  `jg_msg_download_type` int(1) NOT NULL,
  `jg_msg_download_recipients` text NOT NULL,
  `jg_msg_zipdownload` int(1) NOT NULL,
  `jg_msg_comment_type` int(1) NOT NULL,
  `jg_msg_comment_recipients` text NOT NULL,
  `jg_msg_comment_toowner` int(1) NOT NULL,
  `jg_msg_nametag_type` int(1) NOT NULL,
  `jg_msg_nametag_recipients` text NOT NULL,
  `jg_msg_nametag_totaggeduser` int(1) NOT NULL,
  `jg_msg_nametag_toowner` int(1) NOT NULL,
  `jg_msg_report_type` int(1) NOT NULL,
  `jg_msg_report_recipients` text NOT NULL,
  `jg_msg_report_toowner` int(1) NOT NULL,
  `jg_msg_rejectimg_type` int(1) NOT NULL,
  `jg_msg_global_from` int(1) NOT NULL,
  `jg_realname` int(1) NOT NULL,
  `jg_contentpluginsenabled` int(1) NOT NULL,
  `jg_itemid` varchar(10) NOT NULL,
  `jg_ajaxcategoryselection` int(1) NOT NULL,
  `jg_disableunrequiredchecks` int(1) NOT NULL,
  `jg_use_listbox_max_user_count` int(1) NOT NULL,
  `jg_userspace` int(1) NOT NULL,
  `jg_useruploaddefaultcat` int(1) NOT NULL,
  `jg_approve` int(1) NOT NULL,
  `jg_unregistered_permissions` int(1) NOT NULL,
  `jg_maxusercat` int(5) NOT NULL,
  `jg_maxuserimage` int(9) NOT NULL,
  `jg_maxuserimage_timespan` int(9) NOT NULL,
  `jg_maxfilesize` int(9) NOT NULL,
  `jg_usercatacc` int(1) NOT NULL,
  `jg_usercatthumbalign` int(1) NOT NULL,
  `jg_useruploadsingle` int(1) NOT NULL,
  `jg_maxuploadfields` int(3) NOT NULL,
  `jg_useruploadajax` int(1) NOT NULL,
  `jg_useruploadbatch` int(1) NOT NULL,
  `jg_useruploadjava` int(1) NOT NULL,
  `jg_useruseorigfilename` int(1) NOT NULL,
  `jg_useruploadnumber` int(1) NOT NULL,
  `jg_special_gif_upload` int(1) NOT NULL,
  `jg_delete_original_user` int(1) NOT NULL,
  `jg_newpiccopyright` int(1) NOT NULL,
  `jg_newpicnote` int(1) NOT NULL,
  `jg_redirect_after_upload` int(1) NOT NULL,
  `jg_edit_metadata` int(1) NOT NULL,
  `jg_download` int(1) NOT NULL,
  `jg_download_unreg` int(1) NOT NULL,
  `jg_download_hint` int(1) NOT NULL,
  `jg_downloadfile` int(1) NOT NULL,
  `jg_downloadwithwatermark` int(1) NOT NULL,
  `jg_showrating` int(1) NOT NULL,
  `jg_maxvoting` int(1) NOT NULL,
  `jg_ratingcalctype` int(1) NOT NULL,
  `jg_ratingdisplaytype` int(1) NOT NULL,
  `jg_ajaxrating` int(1) NOT NULL,
  `jg_votingonlyonce` int(1) NOT NULL,
  `jg_votingonlyreg` int(1) NOT NULL,
  `jg_showcomment` int(1) NOT NULL,
  `jg_anoncomment` int(1) NOT NULL,
  `jg_namedanoncomment` int(1) NOT NULL,
  `jg_anonapprovecom` int(1) NOT NULL,
  `jg_approvecom` int(1) NOT NULL,
  `jg_bbcodesupport` int(1) NOT NULL,
  `jg_smiliesupport` int(1) NOT NULL,
  `jg_anismilie` int(1) NOT NULL,
  `jg_smiliescolor` varchar(10) NOT NULL,
  `jg_report_images` int(1) NOT NULL,
  `jg_report_unreg` int(1) NOT NULL,
  `jg_report_hint` int(1) NOT NULL,
  `jg_alternative_layout` varchar(255) NOT NULL,
  `jg_anchors` int(1) NOT NULL,
  `jg_tooltips` int(1) NOT NULL,
  `jg_dyncrop` int(1) NOT NULL,
  `jg_dyncropposition` int(1) NOT NULL,
  `jg_dyncropwidth` int(5) NOT NULL,
  `jg_dyncropheight` int(5) NOT NULL,
  `jg_dyncropbgcol` varchar(12) NOT NULL,
  `jg_hideemptycats` int(1) NOT NULL,
  `jg_skipcatview` int(1) NOT NULL,
  `jg_imgalign` int(3) NOT NULL,
  `jg_showrestrictedcats` int(1) NOT NULL,
  `jg_showrestrictedhint` int(1) NOT NULL,
  `jg_firstorder` varchar(20) NOT NULL,
  `jg_secondorder` varchar(20) NOT NULL,
  `jg_thirdorder` varchar(20) NOT NULL,
  `jg_pagetitle_cat` text NOT NULL,
  `jg_pagetitle_detail` text NOT NULL,
  `jg_showgalleryhead` int(1) NOT NULL,
  `jg_showpathway` int(1) NOT NULL,
  `jg_completebreadcrumbs` int(1) NOT NULL,
  `jg_search` int(1) NOT NULL,
  `jg_searchcols` int(1) NOT NULL,
  `jg_searchthumbalign` int(1) NOT NULL,  
  `jg_searchtextalign` int(1) NOT NULL,
  `jg_showsearchdownload` int(1) NOT NULL,
  `jg_showsearchfavourite` int(1) NOT NULL,
  `jg_search_report_images` int(1) NOT NULL,
  `jg_showsearcheditorlinks` int(1) NOT NULL,
  `jg_showallpics` int(1) NOT NULL,
  `jg_showallhits` int(1) NOT NULL,
  `jg_showbacklink` int(1) NOT NULL,
  `jg_suppresscredits` int(1) NOT NULL,
  `jg_showuserpanel` int(1) NOT NULL,
  `jg_showuserpanel_hint` int(1) NOT NULL,
  `jg_showuserpanel_unreg` int(1) NOT NULL,
  `jg_showallpicstoadmin` int(1) NOT NULL,
  `jg_showminithumbs` int(1) NOT NULL,
  `jg_openjs_padding` int(3) NOT NULL,
  `jg_openjs_background` varchar(12) NOT NULL,
  `jg_dhtml_border` varchar(12) NOT NULL,
  `jg_show_title_in_popup` int(1) NOT NULL,
  `jg_show_description_in_popup` int(1) NOT NULL,
  `jg_lightbox_speed` int(3) NOT NULL,
  `jg_lightbox_slide_all` int(1) NOT NULL,
  `jg_resize_js_image` int(1) NOT NULL,
  `jg_disable_rightclick_original` int(1) NOT NULL,
  `jg_showgallerysubhead` int(1) NOT NULL,
  `jg_showallcathead` int(1) NOT NULL,
  `jg_colcat` int(1) NOT NULL,
  `jg_catperpage` int(1) NOT NULL,
  `jg_ordercatbyalpha` int(1) NOT NULL,
  `jg_showgallerypagenav` int(1) NOT NULL,
  `jg_showcatcount` int(1) NOT NULL,
  `jg_showcatthumb` int(1) NOT NULL,
  `jg_showrandomcatthumb` int(1) NOT NULL,
  `jg_ctalign` int(1) NOT NULL,
  `jg_showtotalcatimages` int(1) NOT NULL,
  `jg_showtotalcathits` int(1) NOT NULL,
  `jg_showcatasnew` int(1) NOT NULL,
  `jg_catdaysnew` int(3) NOT NULL,
  `jg_showdescriptioningalleryview` int(1) NOT NULL,
  `jg_uploadicongallery` int(1) NOT NULL,
  `jg_showsubsingalleryview` int(1) NOT NULL,
  `jg_category_rss` int(9) NOT NULL,
  `jg_category_rss_icon` varchar(10) NOT NULL,
  `jg_uploadiconcategory` int(1) NOT NULL,
  `jg_showcathead` int(1) NOT NULL,
  `jg_usercatorder` int(1) NOT NULL,
  `jg_usercatorderlist` varchar(50) NOT NULL,
  `jg_showcatdescriptionincat` int(1) NOT NULL,
  `jg_showpagenav` int(1) NOT NULL,
  `jg_showpiccount` int(1) NOT NULL,
  `jg_perpage` int(3) NOT NULL,
  `jg_catthumbalign` int(1) NOT NULL,
  `jg_colnumb` int(3) NOT NULL,
  `jg_detailpic_open` varchar(50) NOT NULL,
  `jg_lightboxbigpic` int(1) NOT NULL,
  `jg_showtitle` int(1) NOT NULL,
  `jg_showpicasnew` int(1) NOT NULL,
  `jg_daysnew` int(3) NOT NULL,
  `jg_showhits` int(1) NOT NULL,
  `jg_showdownloads` int(1) NOT NULL,
  `jg_showauthor` int(1) NOT NULL,
  `jg_showowner` int(1) NOT NULL,
  `jg_showcatcom` int(1) NOT NULL,
  `jg_showcatrate` int(1) NOT NULL,
  `jg_showcatdescription` int(1) NOT NULL,
  `jg_showcategorydownload` int(1) NOT NULL,
  `jg_showcategoryfavourite` int(1) NOT NULL,
  `jg_category_report_images` int(1) NOT NULL,
  `jg_showcategoryeditorlinks` int(1) NOT NULL,
  `jg_showsubcathead` int(1) NOT NULL,
  `jg_showsubcatcount` int(1) NOT NULL,
  `jg_colsubcat` int(3) NOT NULL,
  `jg_subperpage` int(3) NOT NULL,
  `jg_showpagenavsubs` INT(1) NOT NULL,
  `jg_subcatthumbalign` int(1) NOT NULL,
  `jg_showsubthumbs` int(1) NOT NULL,
  `jg_showrandomsubthumb` int(1) NOT NULL,
  `jg_showdescriptionincategoryview` int(1) NOT NULL,
  `jg_ordersubcatbyalpha` int(1) NOT NULL,
  `jg_showtotalsubcatimages` int(1) NOT NULL,
  `jg_showtotalsubcathits` int(1) NOT NULL,
  `jg_uploadiconsubcat` int(1) NOT NULL,
  `jg_showdetailpage` int(1) NOT NULL,
  `jg_disabledetailpage` int(1) NOT NULL,
  `jg_showdetailnumberofpics` int(1) NOT NULL,
  `jg_cursor_navigation` int(1) NOT NULL,
  `jg_disable_rightclick_detail` int(1) NOT NULL,
  `jg_detail_report_images` int(1) NOT NULL,
  `jg_showdetaileditorlinks` int(1) NOT NULL,
  `jg_showdetailtitle` int(1) NOT NULL,
  `jg_showdetail` int(1) NOT NULL,
  `jg_showdetailaccordion` int(1) NOT NULL,
  `jg_accordionduration` int(3) NOT NULL,
  `jg_accordiondisplay` int(3) NOT NULL,
  `jg_accordionopacity` int(1) NOT NULL,
  `jg_accordionalwayshide` int(1) NOT NULL,
  `jg_accordioninitialeffect` int(1) NOT NULL,
  `jg_showdetaildescription` int(1) NOT NULL,
  `jg_showdetaildatum` int(1) NOT NULL,
  `jg_showdetailhits` int(1) NOT NULL,
  `jg_showdetaildownloads` int(1) NOT NULL,
  `jg_showdetailrating` int(1) NOT NULL,
  `jg_showdetailfilesize` int(1) NOT NULL,
  `jg_showdetailauthor` int(1) NOT NULL,
  `jg_showoriginalfilesize` int(1) NOT NULL,
  `jg_showdetaildownload` int(1) NOT NULL,
  `jg_watermark` int(1) NOT NULL,
  `jg_watermarkpos` int(1) NOT NULL,
  `jg_watermarkzoom` int(1) NOT NULL,
  `jg_watermarksize` int(1) NOT NULL,
  `jg_bigpic` int(1) NOT NULL,
  `jg_bigpic_unreg` int(1) NOT NULL,
  `jg_bigpic_open` varchar(50) NOT NULL,
  `jg_bbcodelink` int(1) NOT NULL,
  `jg_showcommentsunreg` int(1) NOT NULL,
  `jg_showcommentsarea` int(1) NOT NULL,
  `jg_send2friend` int(1) NOT NULL,
  `jg_minis` int(1) NOT NULL,
  `jg_motionminis` int(1) NOT NULL,
  `jg_motionminiWidth` int(3) NOT NULL,
  `jg_motionminiHeight` int(3) NOT NULL,
  `jg_motionminiLimit` int(2) NOT NULL,
  `jg_miniWidth` int(3) NOT NULL,
  `jg_miniHeight` int(3) NOT NULL,
  `jg_minisprop` int(1) NOT NULL,
  `jg_nameshields` int(1) NOT NULL,
  `jg_nameshields_others` int(1) NOT NULL,
  `jg_nameshields_unreg` int(1) NOT NULL,
  `jg_show_nameshields_unreg` int(1) NOT NULL,
  `jg_nameshields_height` int(3) NOT NULL,
  `jg_nameshields_width` int(3) NOT NULL,
  `jg_slideshow` int(1) NOT NULL,
  `jg_slideshow_timer` int(3) NOT NULL,
  `jg_slideshow_transition` int(1) NOT NULL,
  `jg_slideshow_transtime` int(3) NOT NULL,
  `jg_slideshow_maxdimauto` int(1) NOT NULL,
  `jg_slideshow_width` int(3) NOT NULL,
  `jg_slideshow_heigth` int(3) NOT NULL,
  `jg_slideshow_infopane` int(1) NOT NULL,
  `jg_slideshow_carousel` int(1) NOT NULL,
  `jg_slideshow_arrows` int(1) NOT NULL,
  `jg_slideshow_repeat` int(1) NOT NULL,
  `jg_showexifdata` int(1) NOT NULL,
  `jg_showgeotagging` int(1) NOT NULL,
  `jg_geotaggingkey` text NOT NULL,
  `jg_subifdtags` text NOT NULL,
  `jg_ifdotags` text NOT NULL,
  `jg_gpstags` text NOT NULL,
  `jg_showiptcdata` int(1) NOT NULL,
  `jg_iptctags` text NOT NULL,
  `jg_showtoplist` int(1) NOT NULL,
  `jg_toplist` int(3) NOT NULL,
  `jg_topthumbalign` int(1) NOT NULL,
  `jg_toptextalign` int(1) NOT NULL,
  `jg_toplistcols` int(3) NOT NULL,
  `jg_whereshowtoplist` int(1) NOT NULL,
  `jg_showrate` int(1) NOT NULL,
  `jg_showlatest` int(1) NOT NULL,
  `jg_showcom` int(1) NOT NULL,
  `jg_showthiscomment` int(1) NOT NULL,
  `jg_showmostviewed` int(1) NOT NULL,
  `jg_showtoplistdownload` int(1) NOT NULL,
  `jg_showtoplistfavourite` int(1) NOT NULL,
  `jg_toplist_report_images` int(1) NOT NULL,
  `jg_showtoplisteditorlinks` int(1) NOT NULL,
  `jg_favourites` int(1) NOT NULL,
  `jg_favouritesshownotauth` int(1) NOT NULL,
  `jg_maxfavourites` int(5) NOT NULL,
  `jg_zipdownload` int(1) NOT NULL,
  `jg_usefavouritesforpubliczip` int(1) NOT NULL,
  `jg_usefavouritesforzip` int(1) NOT NULL,
  `jg_allimagesofcategory` int(1) NOT NULL,
  `jg_showfavouritesdownload` int(1) NOT NULL,
  `jg_showfavouriteseditorlinks` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_countstop` (
  `cspicid` int(11) NOT NULL default 0,
  `csip` varchar(20) NOT NULL,
  `cssessionid` varchar(200),
  `cstime` DATETIME,
  INDEX idx_cspicid (`cspicid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_image_details` (
  `id` int(11) NOT NULL,
  `details_key` varchar(255) NOT NULL,
  `details_value` text NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`,`details_key`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_maintenance` (
  `id` int(11) NOT NULL auto_increment,
  `refid` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `title` text NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `orig` varchar(255) NOT NULL,
  `thumborphan` int(11) NOT NULL,
  `imgorphan` int(11) NOT NULL,
  `origorphan` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_nameshields` (
  `nid` int(11) NOT NULL auto_increment,
  `npicid` int(11) NOT NULL default '0',
  `nuserid` int(11) UNSIGNED NOT NULL default '0',
  `nxvalue` int(11) NOT NULL default '0',
  `nyvalue` int(11) NOT NULL default '0',
  `by` int(11) NOT NULL default '0',
  `nuserip` varchar(15) NOT NULL default '0',
  `ndate` datetime NOT NULL,
  `nzindex` int(11) NOT NULL default '0',
  PRIMARY KEY  (`nid`),
  INDEX idx_picid (`npicid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_orphans` (
  `id` int(11) NOT NULL auto_increment,
  `fullpath` varchar(255) NOT NULL,
  `type` varchar(7) NOT NULL,
  `refid` int(11) NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fullpath` (`fullpath`(255))
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_users` (
  `uid` int(11) NOT NULL auto_increment,
  `uuserid` int(11) NOT NULL default '0',
  `piclist` text default NULL,
  `layout` int(1) NOT NULL,
  `time` datetime NOT NULL,
  `zipname` varchar(70) NOT NULL,
  PRIMARY KEY (`uid`),
  INDEX idx_uid (`uuserid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__joomgallery_votes` (
  `voteid` int(11) NOT NULL auto_increment,
  `picid` int(11) NOT NULL default '0',
  `userid` int(11) UNSIGNED NOT NULL default '0',
  `userip` varchar(15) NOT NULL default '0',
  `datevoted` datetime NOT NULL,
  `vote` int(11) NOT NULL default '0',
  PRIMARY KEY (`voteid`),
  INDEX idx_picid (`picid`)
) DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__joomgallery_config`
  VALUES (
  /* id */       1,
  /* group_id */ 1,
  /* ordering */ 1,

  /* ### General settings->path and directories ####*/
  /*jg_pathimages*/         'images/joomgallery/details/',
  /*jg_pathoriginalimages*/ 'images/joomgallery/originals/',
  /*jg_paththumbs*/         'images/joomgallery/thumbnails/',
  /*jg_pathftpupload*/      'administrator/components/com_joomgallery/temp/ftp_upload/',
  /*jg_pathtemp*/           'administrator/components/com_joomgallery/temp/',
  /*jg_wmpath*/             'media/joomgallery/images/',
  /*jg_wmfile*/             'watermark.png',
  /*jg_use_real_paths*/     0,
  /*jg_checkupdate*/        1,

  /* ### General settings->Replacements ####*/
  /*jg_filenamewithjs*/     1,
  /*jg_filenamereplace*/    'Š|S, Œ|O, Ž|Z, š|s, œ|oe, ž|z, Ÿ|Y, ¥|Y, µ|u, À|A, Á|A, Â|A, Ã|A, Ä|AE, Å|A, Æ|A, Ç|C, È|E, É|E, Ê|E, Ë|E, Ì|I, Í|I, Î|I, Ï|I, Ð|D, Ñ|N, Ò|O, Ó|O, Ô|O, Õ|O, Ö|OE, Ø|O, Ù|U, Ú|U, Û|U, Ü|UE, Ý|Y, à|a, á|a, â|a, ã|a, ä|ae, å|a, æ|a, ç|c, è|e, é|e, ê|e, ë|e, ì|i, í|i, î|i, ï|i, ð|o, ñ|n, ò|o, ó|o, ô|o, õ|o, ö|oe, ø|o, ù|u, ú|u, û|u, ü|ue, ý|y, ÿ|y, ß|ss, ă|a, ş|s, ţ|t, ț|t, Ț|T, Ș|S, ș|s, Ş|S',

  /* ### General settings->Image Processing ####*/
  /*jg_thumbcreation*/          'gd2',
  /*jg_fastgd2thumbcreation*/   1,
  /*jg_impath*/                 '',
  /*jg_resizetomaxwidth*/       1,
  /*jg_maxwidth*/               400,
  /*jg_picturequality*/         100,
  /*jg_useforresizedirection*/  0,
  /*jg_cropposition*/           2,
  /*jg_thumbwidth*/             133,
  /*jg_thumbheight*/            100,
  /*jg_thumbquality*/           100,

  /* ### General settings->Backend Upload ####*/
  /*jg_uploadorder*/        2,
  /*jg_useorigfilename*/    0,
  /*jg_filenamenumber*/     1,
  /*jg_delete_original*/    0,

  /* ### General settings->Messages ####*/
  /*jg_msg_upload_type*/          2,
  /*jg_msg_upload_recipients*/    '-1',
  /*jg_msg_download_type*/        2,
  /*jg_msg_download_recipients*/  '-1',
  /*jg_msg_zipdownload*/          0,
  /*jg_msg_comment_type*/         2,
  /*jg_msg_comment_recipients*/   '-1',
  /*jg_msg_comment_toowner*/      0,
  /*jg_msg_nametag_type*/         2,
  /*jg_msg_nametag_recipients*/   '-1',
  /*jg_msg_nametag_totaggeduser*/ 1,
  /*jg_msg_nametag_toowner*/      0,
  /*jg_msg_report_type*/          2,
  /*jg_msg_report_recipients*/    '-1',
  /*jg_msg_report_toowner*/       0,
  /*jg_msg_rejectimg_type*/       1,
  /*jg_msg_global_from*/          0,

  /* ### Frontend Settings->General Settings (partly, see more below)####*/
  /*jg_realname*/                 0,
  /*jg_contentpluginsenabled*/    1,
  /*jg_itemid*/                   '',

  /* ### General settings->Performance settings ####*/
  /*jg_ajaxcategoryselection*/      0,
  /*jg_disableunrequiredchecks*/    0,
  /*jg_use_listbox_max_user_count*/ 25,

  /* ### User Access rights->User upload ####*/
  /*jg_userspace*/                1,
  /*jg_useruploaddefaultcat*/     0,
  /*jg_approve*/                  0,
  /*jg_unregistered_permissions*/ 0,
  /*jg_maxusercat*/               10,
  /*jg_maxuserimage*/             500,
  /*jg_maxuserimage_timespan*/    0,
  /*jg_maxfilesize*/              2000000,
  /*jg_usercatacc*/               1,
  /*jg_usercatthumbalign*/        1,
  /*jg_useruploadsingle*/         1,
  /*jg_maxuploadfields*/          3,
  /*jg_useruploadajax*/           1,
  /*jg_useruploadbatch*/          1,
  /*jg_useruploadjava*/           1,
  /*jg_useruseorigfilename*/      0,
  /*jg_useruploadnumber*/         1,
  /*jg_special_gif_upload*/       1,
  /*jg_delete_original_user*/     2,
  /*jg_newpiccopyright*/          1,
  /*jg_newpicnote*/               1,
  /*jg_redirect_after_upload*/    1,
  /*jg_edit_metadata*/            0,

  /* ### User Access rights->Download ####*/
  /*jg_download*/               1,
  /*jg_download_unreg*/         1,
  /*jg_download_hint*/          1,
  /*jg_downloadfile*/           2,
  /*jg_downloadwithwatermark*/  1,

  /* ### User Access rights->Rating ####*/
  /*jg_showrating*/         1,
  /*jg_maxvoting*/          5,
  /*jg_ratingcalctype*/     0,
  /*jg_ratingdisplaytype*/  0,
  /*jg_ajaxrating*/         0,
  /*jg_votingonlyonce*/     1,
  /*jg_votingonlyreg*/      0,

  /* ### User Access rights->Comments ####*/
  /*jg_showcomment*/      1,
  /*jg_anoncomment*/      1,
  /*jg_namedanoncomment*/ 1,
  /*jg_anonapprovecom*/   1,
  /*jg_approvecom*/       0,
  /*jg_bbcodesupport*/    1,
  /*jg_smiliesupport*/    1,
  /*jg_anismilie*/        0,
  /*jg_smiliescolor*/     'grey',

  /* ### User Access rights->Reports ####*/
  /*jg_report_images*/  1,
  /*jg_report_unreg*/   1,
  /*jg_report_hint*/    1,

  /* ### Frontend Settings->General Settings (partly, see more above) ####*/
  /*jg_alternative_layout*/ '',
  /*jg_anchors*/            1,
  /*jg_tooltips*/           1,
  /*jg_dyncrop*/            0,
  /*jg_dyncropposition*/    2,
  /*jg_dyncropwidth*/       100,
  /*jg_dyncropheight*/      100,
  /*jg_dyncropbgcol*/       '#ffffff',
  /*jg_hideemptycats*/      0,
  /*jg_skipcatview*/        0,
  /*jg_imgalign*/           0,
  /*jg_showrestrictedcats*/ 1,
  /*jg_showrestrictedhint*/ 1,

  /* ### Frontend Settings->Picture Ordering ####*/
  /*jg_firstorder*/   'ordering ASC',
  /*jg_secondorder*/  'imgdate DESC',
  /*jg_thirdorder*/   'imgtitle DESC',

  /* ### Frontend Settings->Page Title ####*/
  /*jg_pagetitle_cat*/    '#page_title - [! COM_JOOMGALLERY_COMMON_CATEGORY!]: #cat',
  /*jg_pagetitle_detail*/ '#page_title - [! COM_JOOMGALLERY_COMMON_CATEGORY!]: #cat - [! COM_JOOMGALLERY_COMMON_IMAGE!]:  #img',

  /* ### Frontend Settings->Header and Footer ####*/
  /*jg_showgalleryhead*/      1,
  /*jg_showpathway*/          1,
  /*jg_completebreadcrumbs*/  1,

  /* --> ### Search ####*/
  /*jg_search*/                     1,
  /*jg_searchcols*/                 1,
  /*jg_searchthumbalign*/           1,
  /*jg_searchtextalign*/            1,
  /*jg_showsearchdownload*/         0,
  /*jg_showsearchfavourite*/        0,
  /*jg_search_report_images*/       0,
  /*jg_showsearcheditorlinks*/      0,

  /*jg_showallpics*/          3,
  /*jg_showallhits*/          1,
  /*jg_showbacklink*/         3,
  /*jg_suppresscredits*/      1,

  /* ### Frontend Settings->User Panel ####*/
  /*jg_showuserpanel*/        1,
  /*jg_showuserpanel_hint*/   1,
  /*jg_showuserpanel_unreg*/  0,
  /*jg_showallpicstoadmin*/   1,
  /*jg_showminithumbs*/       1,

  /* ### Frontend Settings->Popup Functions ####*/
  /*jg_openjs_padding*/               10,
  /*jg_openjs_background*/            '#ffffff',
  /*jg_dhtml_border*/                 '#808080',
  /*jg_show_title_in_popup*/          1,
  /*jg_show_description_in_popup*/    1,
  /*jg_lightbox_speed*/               5,
  /*jg_lightbox_slide_all*/           1,
  /*jg_resize_js_image*/              1,
  /*jg_disable_rightclick_original*/  1,

  /* ### Gallery View->General Settings ####*/
  /*jg_showgallerysubhead*/           1,
  /*jg_showallcathead*/               1,
  /*jg_colcat*/                       3,
  /*jg_catperpage*/                   9,
  /*jg_ordercatbyalpha*/              0,
  /*jg_showgallerypagenav*/           1,
  /*jg_showcatcount*/                 1,
  /*jg_showcatthumb*/                 1,
  /*jg_showrandomcatthumb*/           3,
  /*jg_ctalign*/                      1,
  /*jg_showtotalcatimages*/           1,
  /*jg_showtotalcathits*/             1,
  /*jg_showcatasnew*/                 1,
  /*jg_catdaysnew*/                   7,
  /*jg_showdescriptioningalleryview*/ 1,
  /*jg_uploadicongallery*/            0,
  /*jg_showsubsingalleryview*/        0,

  /* ### Category View->General Settings ####*/
  /*jg_category_rss*/             10,
  /*jg_category_rss_icon*/        'rss',
  /*jg_uploadiconcategory*/       0,
  /*jg_showcathead*/              1,
  /*jg_usercatorder*/             1,
  /*jg_usercatorderlist*/         'date,title',
  /*jg_showcatdescriptionincat*/  1,
  /*jg_showpagenav*/              2,
  /*jg_showpiccount*/             1,
  /*jg_perpage*/                  8,
  /*jg_catthumbalign*/            1,
  /*jg_colnumb*/                  2,
  /*jg_detailpic_open*/           '0',
  /*jg_lightboxbigpic*/           1,
  /*jg_showtitle*/                1,
  /*jg_showpicasnew*/             1,
  /*jg_daysnew*/                  10,
  /*jg_showhits*/                 1,
  /*jg_showdownloads*/            1,
  /*jg_showauthor*/               1,
  /*jg_showowner*/                1,
  /*jg_showcatcom*/               1,
  /*jg_showcatrate*/              1,
  /*jg_showcatdescription*/       1,
  /*jg_showcategorydownload*/     0,
  /*jg_showcategoryfavourite*/    0,
  /*jg_category_report_images*/   0,
  /*jg_showcategoryeditorlinks*/  0,

  /* ### Category View->Sub-Categories ####*/
  /*jg_showsubcathead*/                 1,
  /*jg_showsubcatcount*/                1,
  /*jg_colsubcat*/                      2,
  /*jg_subperpage*/                     8,
  /*jg_showpagenavsubs*/                1,
  /*jg_subcatthumbalign*/               3,
  /*jg_showsubthumbs*/                  2,
  /*jg_showrandomsubthumb*/             3,
  /*jg_showdescriptionincategoryview*/  1,
  /*jg_ordersubcatbyalpha*/             0,
  /*jg_showtotalsubcatimages*/          1,
  /*jg_showtotalsubcathits*/            1,
  /*jg_uploadiconsubcat*/               0,

  /* ### Detail View->General Settings ####*/
  /*jg_showdetailpage*/             1,
  /*jg_disabledetailpage*/          1,
  /*jg_showdetailnumberofpics*/     1,
  /*jg_cursor_navigation*/          1,
  /*jg_disable_rightclick_detail*/  0,
  /*jg_detail_report_images*/       1,
  /*jg_showdetaileditorlinks*/      1,
  /*jg_showdetailtitle*/            1,
  /*jg_showdetail*/                 1,
  /*jg_showdetailaccordion*/        0,
  /*jg_accordionduration*/        300,
  /*jg_accordiondisplay*/           1,
  /*jg_accordionopacity*/           0,
  /*jg_accordionalwayshide*/        0,
  /*jg_accordioninitialeffect*/     1,
  /*jg_showdetaildescription*/      1,
  /*jg_showdetaildatum*/            1,
  /*jg_showdetailhits*/             1,
  /*jg_showdetaildownloads*/        1,
  /*jg_showdetailrating*/           1,
  /*jg_showdetailfilesize*/         1,
  /*jg_showdetailauthor*/           1,
  /*jg_showoriginalfilesize*/       1,
  /*jg_showdetaildownload*/         1,
  /*jg_watermark*/                  0,
  /*jg_watermarkpos*/               9,
  /*jg_watermarkzoom*/              0,
  /*jg_watermarksize*/              15,
  /*jg_bigpic*/                     1,
  /*jg_bigpic_unreg*/               1,
  /*jg_bigpic_open*/                '6',
  /*jg_bbcodelink*/                 3,
  /*jg_showcommentsunreg*/          1,
  /*jg_showcommentsarea*/           2,
  /*jg_send2friend*/                1,

  /* ### Detail View->Motiongallery ####*/
  /*jg_minis*/            1,
  /*jg_motionminis*/      2,
  /*jg_motionminiWidth*/  400,
  /*jg_motionminiHeight*/ 50,
  /*jg_motionminiLimit*/  0,
  /*jg_miniWidth*/        28,
  /*jg_miniHeight*/       28,
  /*jg_minisprop*/        2,

  /* ### Detail View->Nametags ####*/
  /*jg_nameshields*/            0,
  /*jg_nameshields_others*/     1,
  /*jg_nameshields_unreg*/      1,
  /*jg_show_nameshields_unreg*/ 0,
  /*jg_nameshields_height*/     10,
  /*jg_nameshields_width*/      6,

  /* ### Detail View->Slideshow Settings ####*/
  /*jg_slideshow*/              1,
  /*jg_slideshow_timer*/        6000,
  /*jg_slideshow_transition*/   0,
  /*jg_slideshow_transtime*/    2000,
  /*jg_slideshow_maxdimauto*/   0,
  /*jg_slideshow_width*/        640,
  /*jg_slideshow_heigth*/       480,
  /*jg_slideshow_infopane*/     0,
  /*jg_slideshow_carousel*/     0,
  /*jg_slideshow_arrows*/       0,
  /*jg_slideshow_repeat*/       0,

  /* ### Detail View->Exif data ####*/
  /*jg_showexifdata*/   0,
  /*jg_showgeotagging*/ 0,
  /*jg_geotaggingkey*/  '',  
  /*jg_subifdtags*/     '',
  /*jg_ifdotags*/       '',
  /*jg_gpstags*/        '',

  /* ### Detail View->IPTC data ####*/
  /*jg_showiptcdata*/   0,
  /*jg_iptctags*/       '',

  /* ### Toplists ####*/
  /*jg_showtoplist*/                2,
  /*jg_toplist*/                    12,
  /*jg_topthumbalign*/              1,
  /*jg_toptextalign*/               1,
  /*jg_toplistcols*/                1,
  /*jg_whereshowtoplist*/           0,
  /*jg_showrate*/                   1,
  /*jg_showlatest*/                 1,
  /*jg_showcom*/                    1,
  /*jg_showthiscomment*/            1,
  /*jg_showmostviewed*/             1,
  /*jg_showtoplistdownload*/        0,
  /*jg_showtoplistfavourite*/       0,
  /*jg_toplist_report_images*/      0,
  /*jg_showtoplisteditorlinks*/     0,

  /* ### Favorites ####*/
  /*jg_favourites*/                 1,
  /*jg_favouritesshownotauth*/      0,
  /*jg_maxfavourites*/              0,
  /*jg_zipdownload*/                1,
  /*jg_usefavouritesforpubliczip*/  0,
  /*jg_usefavouritesforzip*/        0,
  /*jg_allimagesofcategory*/        0,
  /*jg_showfavouritesdownload*/     1,
  /*jg_showfavouriteseditorlinks*/  1
  );
