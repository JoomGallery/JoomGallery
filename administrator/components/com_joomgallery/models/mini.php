<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/mini.php $
// $Id: mini.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Mini Joom model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelMini extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @access  protected
   * @var     array
   */
  var $_images;

  /**
   * Images number
   *
   * @access  protected
   * @var     int
   */
  var $_total = null;

  /**
   * Retrieves the images data
   *
   * @access  public
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  function getImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_images))
    {
      $limitstart = JRequest::getInt('limitstart');
      $limit      = JRequest::getInt('limit');

      $query = $this->_buildQuery();

      $this->_images = $this->_getList($query, $limitstart, $limit);
    }

    return $this->_images;
  }

  /**
   * Method to get the total number of images
   *
   * @access  public
   * @return  int     The total number of images
   * @since   1.5.5
   */
  function getTotalImages()
  {
    // Let's load the categories if they doesn't already exist
    if (empty($this->_total))
    {
      $query = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }

  /**
   * Returns the query for loading the images
   *
   * @access  protected
   * @return  string    The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  function _buildQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('jg.id, jg.catid, jg.imgtitle, jg.imgthumbname')
          ->from(_JOOM_TABLE_IMAGES.' AS jg');

    // Join over the categories
    $query->select('jgc.name')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid');

    // Ensure that image may be seen later on
    if($this->_mainframe->getUserStateFromRequest('joom.mini.type', 'type', '', 'cmd') != 'category')
    {
      $query->where('jgc.published = 1');
    }

    $query->where('jg.published = 1')
          ->where('jg.approved  = 1')
          ->where('jg.access    IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('jgc.access   IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');

    if($this->_mainframe->getUserState('joom.mini.extended') != 0 && !$this->_mainframe->getUserState('joom.mini.showhidden'))
    {
      $query->where('jg.hidden      = 0')
            ->where('jgc.hidden     = 0')
            ->where('jgc.in_hidden  = 0');
    }

    // Filter by category
    $catid  = $this->_mainframe->getUserStateFromRequest('joom.mini.catid', 'catid', 0, 'int');
    if($catid || JRequest::getCmd('type') == 'category')
    {
      $query->where('jg.catid = '.$catid);
    }

    // Filter by search
    $search = $this->_mainframe->getUserStateFromRequest('joom.mini.search', 'search', '', 'string');
    if($search)
    {
      $search  = $this->_db->escape($search);
      $query->where('(LOWER(jg.imgtitle) LIKE \'%'.$search.'%\' OR LOWER(jg.imgtext) LIKE \'%'.$search.'%\')');
    }

    $query->order('jg.imgtitle ASC, jg.ordering ASC');

    return $query;
  }
}