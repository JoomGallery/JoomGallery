<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/tables/joomgallerymaintenance.php $
// $Id: joomgallerymaintenance.php 4076 2013-02-12 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * JoomGallery maintenance table class
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class TableJoomgalleryMaintenance extends JTable
{
  /** @var int Primary key */
  var $id           = null;
  /** @var int */
  var $refid        = null;
  /** @var int */
  var $catid        = null;
  /** @var int */
  var $owner        = null;
  /** @var string */
  var $title        = null;
  /** @var string */
  var $thumb        = null;
  /** @var string */
  var $img          = null;
  /** @var string */
  var $orig         = null;
  /** @var string */
  var $thumborphan  = null;
  /** @var string */
  var $imgorphan    = null;
  /** @var string */
  var $origorphan   = null;
  /** @var int */
  var $type         = null;
  
  function __construct($db)
  {
    parent::__construct(_JOOM_TABLE_MAINTENANCE, 'id', $db);
  }
}