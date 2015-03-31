<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/favourites.php $
// $Id: favourites.php 4318 2013-08-18 07:58:35Z erftralle $
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
 * Favourites model
 *
 * @package JoomGallery
 * @since   2.0
 */
class JoomGalleryModelFavourites extends JoomGalleryModel
{
  /**
   * Resets all favourites in the gallery and deletes all created zips of favourites
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function reset()
  {
    // Delete all users in the database table
    $query = $this->_db->getQuery(true)
          ->delete(_JOOM_TABLE_USERS);
    $this->_db->setQuery($query);

    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    // Delete all created zips in component directory
    if(!$this->deleteArchives())
    {
      return false;
    }

    return true;
  }

  /**
   * Synchronizes the entries in the users table of JoomGallery
   * with users registered and deletes all zips of favourites.
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function synchronize()
  {
    // Synchronize users-users
    $subquery = $this->_db->getQuery(true)
              ->select('id')
              ->from('#__users AS ju')
              ->where('ju.id = uuserid');
    $query = $this->_db->getQuery(true)
          ->delete(_JOOM_TABLE_USERS)
          ->where('NOT EXISTS ('.$subquery.')');
    $this->_db->setQuery($query);

    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    // Delete all created zips in component directory
    if(!$this->deleteArchives())
    {
      return false;
    }

    return true;
  }

  /**
   * Deletes all created zips of favourites
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  protected function deleteArchives()
  {
    jimport('joomla.filesystem.file');

    $archives = JFolder::files(JPATH_COMPONENT_SITE, '.zip', false, true);
    if(!JFile::delete($archives))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_MAIMAN_FV_ERROR_DELETING_ARCHIVES'));

      return false;
    }

    return true;
  }
}