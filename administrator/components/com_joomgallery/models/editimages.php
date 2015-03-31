<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/editimages.php $
// $Id: editimages.php 4076 2013-02-12 10:35:29Z erftralle $
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

jimport('joomla.form.form');

/**
 * Edit multiple images model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelEditimages extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @var     array
   */
  protected $_images;

  /**
   * Returns the query for loading the image data
   *
   * @return  object    The query to be used to retrieve the image data from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    $cid = JRequest::getVar('cid', array(), '', 'array');

    $query = $this->_db->getQuery(true)
          ->select('a.*, c.cid AS category_id, c.name AS category_name, g.title AS groupname')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.id')
          ->leftJoin('#__viewlevels AS g ON g.id = a.access')
          ->where('a.id IN ('.implode(',', $cid).')');

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

  /**
   * Method to get the 'editimages' form
   *
   * @return  mixed   A JForm object on success, false on failure
   * @since 2.0
   */
  public function getForm()
  {
    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
    JForm::addRulePath(JPATH_COMPONENT.'/models/rules');

    $form = JForm::getInstance(_JOOM_OPTION.'.editimages', 'editimages');
    if(empty($form))
    {
      return false;
    }

    return $form;
  }
}