<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/move.php $
// $Id: move.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Move images model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelMove extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @var     array
   */
  protected $_images;

  /**
   * Categories data array
   *
   * @var     array
   */
  protected $_categories;

  /**
   * Returns the query for loading all selected images
   *
   * @return  object    The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    $cids = JRequest::getVar('cid', array(0), 'post', 'array');

    $query = $this->_db->getQuery(true)
          ->select('*')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('id IN ('.implode(',', $cids).')')
          ->order('imgtitle, id');

    return $query;
  }

  /**
   * Retrieves the data of the selected images
   *
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  public function getImages()
  {
    // Lets load the data if it doesn't already exist
    if(empty($this->_images))
    {
      $query = $this->_buildQuery();
      $this->_images = $this->_getList($query);
    }

    return $this->_images;
  }
}