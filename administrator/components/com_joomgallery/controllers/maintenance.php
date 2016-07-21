<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/maintenance.php $
// $Id: maintenance.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Maintenance Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerMaintenance extends JoomGalleryController
{
  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    // Access check
    if(!JFactory::getUser()->authorise('core.admin', _JOOM_OPTION))
    {
      $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl(''), false), 'You are not allowed to configure this component', 'notice');
      $this->redirect();
    }

    // Set view
    JRequest::setVar('view', 'maintenance');

    $this->registerTask('parsefolders',             'check');
    $this->registerTask('checkimages',              'check');
    $this->registerTask('checkcategories',          'check');
    $this->registerTask('createfilesuggestions',    'check');
    $this->registerTask('createfoldersuggestions',  'check');
  }

  /**
   * Removes one or more images even if some files aren't found
   *
   * @return  void
   * @since   1.5.5
   */
  public function remove()
  {
    $model = $this->getModel('maintenance');
    if(!$count = $model->delete())
    {
      $msg  = $model->getError();
      $type = 'error';
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_IMAGE_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_IMAGES_DELETED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Removes one or more categories even if some files or
   * folders aren't found or if there are still sub-categories
   *
   * @return  void
   * @since   1.5.5
   */
  public function removeCategory()
  {
    $model = $this->getModel('maintenance');
    if(!$count = $model->deletecategory())
    {
      $msg  = $model->getError();
      $type = 'error';
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_CATEGORY_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_CATEGORIES_DELETED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=categories', $msg, $type);
  }

  /**
   * Removes one or more categories even though there
   * are still images or sub-categories in them.
   *
   * @return  void
   * @since   1.5.5
   */
  public function deleteCompletely()
  {
    $mainframe  = JFactory::getApplication('administrator');
    $categories = $mainframe->getUserState('joom.maintenance.delete.categories');
    $images     = $mainframe->getUserState('joom.maintenance.delete.images');

    if(!$categories || !is_array($categories) || !count($categories))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=categories', JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'), 'notice');
      return;
    }

    require_once JPATH_COMPONENT.'/helpers/refresher.php';

    $refresher = new JoomRefresher();

    $img_count = $mainframe->getUserState('joom.maintenance.delete.img_count');
    if(is_null($img_count))
    {
      $img_count = 0;
    }
    $cat_count = $mainframe->getUserState('joom.maintenance.delete.cat_count');
    if(is_null($cat_count))
    {
      $cat_count = 0;
    }

    $model  = $this->getModel('maintenance');
    if($images && is_array($images) && count($images))
    {
      $row    = $model->getTable('joomgalleryimages');
      foreach($images as $key => $image)
      {
        // Check whether image still exists.
        // It may have been deleted before if categories were selected
        // to delete as well as their sub-categories.
        if($row->load($image))
        {
          JRequest::setVar('cid', array($image));
          if($model->delete(false))
          {
            $img_count++;
          }

          unset($images[$key]);
        }

        if(!$refresher->check())
        {
          $mainframe->setUserState('joom.maintenance.delete.images', $images);
          $mainframe->setUserState('joom.maintenance.delete.img_count', $img_count);
          $refresher->refresh();
        }
      }
    }

    $row  = $model->getTable('joomgallerycategories');
    for($i = count($categories) - 1; $i >= 0; $i--)
    {
      foreach($categories[$i] as $key => $category)
      {
        // Check whether category still exists.
        // It may have been deleted before if categories were selected
        // to delete as well as their sub-categories.
        if($row->load($category))
        {
          JRequest::setVar('cid', array($category));
          if($model->deletecategory(false, false))
          {
            $cat_count++;
          }

          unset($categories[$i][$key]);
        }

        if(!$refresher->check() && count($categories[0]))
        {
          $mainframe->setUserState('joom.maintenance.delete.categories', $categories);
          $mainframe->setUserState('joom.maintenance.delete.img_count', $img_count);
          $mainframe->setUserState('joom.maintenance.delete.cat_count', $cat_count);
          $refresher->refresh();
        }
      }
    }

    if($img_count)
    {
      if($img_count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_IMAGE_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_IMAGES_DELETED', $img_count);
      }
      $mainframe->enqueueMessage($msg);
    }

    if($cat_count)
    {
      if($cat_count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_CATEGORY_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_CATEGORIES_DELETED', $cat_count);
      }
      $mainframe->enqueueMessage($msg);
    }

    // Reset all user states of this task
    $mainframe->setUserState('joom.maintenance.delete', null);

    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=categories');
  }

  /**
   * Resets aliases of all images and categories
   *
   * @return  void
   * @since   1.5.5
   */
  public function setAlias()
  {
    $model  = $this->getModel('maintenance');
    $count  = $model->resetAliases();
    if(!$count[0])
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_CATEGORIES_ALIAS_RESET');
      if($count[0])
      {
        $msg .= '</li><li>'.JText::_('COM_JOOMGALLERY_MAIMAN_MSG_IMAGES_ALIAS_RESET');
      }
    }

    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Sets a new user as the owner of the selected images
   *
   * @return  void
   * @since   1.5.5
   */
  public function setUser()
  {
    $model  = $this->getModel('maintenance');
    $count  = $model->setUser();
    if(!$count)
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_IMAGE_USER_SET');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_IMAGES_USER_SET', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Sets a new user as the owner of the selected categories
   *
   * @return  void
   * @since   1.5.5
   */
  public function setCategoryUser()
  {
    $model  = $this->getModel('maintenance');
    $count  = $model->setCategoryUser();
    if(!$count)
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_CT_MSG_CATEGORY_USER_SET');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_CT_MSG_CATEGORIES_USER_SET', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=categories', $msg, $type);
  }

  /**
   * Moves orphaned files to their suggested correct folder
   *
   * @return  void
   * @since   1.5.5
   */
  public function addOrphan()
  {
    $tab    = JRequest::getCmd('tab');

    if($tab == 'orphans')
    {
      $tab  = '&tab=orphans';
    }
    else
    {
      $tab  = '';
    }

    $model  = $this->getModel('maintenance');
    if(!$count = $model->addOrphan())
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_ORPHAN_SUCCESSFULLY_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ORPHANS_SUCCESSFULLY_ADDED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().$tab, $msg, $type);
  }

  /**
   * Filters selected images and moves all orphaned files to their suggested correct folder
   *
   * @return  void
   * @since   1.5.5
   */
  public function addOrphans()
  {
    $tab    = JRequest::getCmd('tab');

    if($tab == 'orphans')
    {
      $tab  = '&tab=orphans';
    }
    else
    {
      $tab  = '';
    }

    $model  = $this->getModel('maintenance');
    if(!$count = $model->addOrphans())
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_ORPHAN_SUCCESSFULLY_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ORPHANS_SUCCESSFULLY_ADDED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().$tab, $msg, $type);
  }

  /**
   * Moves orphaned files to their suggested correct folder
   *
   * @return  void
   * @since   1.5.5
   */
  public function addOrphanedFolder()
  {
    $tab    = JRequest::getCmd('tab');

    if($tab == 'folders')
    {
      $tab  = '&tab=folders';
    }
    else
    {
      $tab  = '&tab=categories';
    }

    $model  = $this->getModel('maintenance');
    if(!$count = $model->addOrphanedFolder())
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDER_SUCCESSFULLY_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_SUCCESSFULLY_ADDED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().$tab, $msg, $type);
  }

  /**
   * Filters selected images and moves all orphaned files to their suggested correct folder
   *
   * @return  void
   * @since   1.5.5
   */
  public function addOrphanedFolders()
  {
    $tab    = JRequest::getCmd('tab');

    if($tab == 'folders')
    {
      $tab  = '&tab=folders';
    }
    else
    {
      $tab = '&tab=categories';
    }

    $model  = $this->getModel('maintenance');
    if(!$count = $model->addOrphanedFolders())
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDER_SUCCESSFULLY_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_SUCCESSFULLY_ADDED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().$tab, $msg, $type);
  }

  /**
   * Recreates thumbnails and detail images
   *
   * @return  void
   * @since   1.5.5
   */
  public function recreate()
  {
    // Load the necessary image IDs
    $cids = JRequest::getVar('cid', array(), '', 'array');

    if(!count($cids))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'), 'error');
      return;
    }

    JArrayHelper::toInteger($cids);

    $query = $this->_db->getQuery(true)
          ->select('refid')
          ->from($this->_db->qn(_JOOM_TABLE_MAINTENANCE))
          ->where('id IN ('.implode(',', $cids).')');
    $this->_db->setQuery($query);
    if(!$cids = $this->_db->loadColumn())
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), $this->_db->getErrorMsg(), 'error');

      return;
    }

    JRequest::setVar('cid', $cids);

    // Recreate the images
    $model  = $this->getModel('images');
    $count  = $model->recreate();
    if(!$count[0] && !$count[1])
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      $msg  = '';
      /*if($count[0])
      {
        if($count[0] == 1)
        {
          $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_THUMBNAIL_RECREATED');
        }
        else
        {
          $msg  = JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_THUMBNAILS_RECREATED', $count[0]);
        }
      }
      if($count[1])
      {
        if($count[1] == 1)
        {
          $msg  .= '</li><li>'.JText::_('COM_JOOMGALLERY_COMMON_MSG_IMAGE_RECREATED');
        }
        else
        {
          $msg  .= '</li><li>'.JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_IMAGES_RECREATED', $count[1]);
        }
      }*/

      foreach($count[2] as $key => $types)
      {
        $set = '';
        if(in_array('thumb', $types))
        {
          $set = 'thumb = '.$this->_db->q($this->_ambit->getImg('thumb_url', $key)).', thumborphan = 0';
        }
        if(in_array('img', $types))
        {
          if($set)
          {
            $set .= ', ';
          }
          $set .= 'img = '.$this->_db->q($this->_ambit->getImg('img_url', $key)).', imgorphan = 0';
        }

        if($set)
        {
          $query = $this->_db->getQuery(true)
                ->update($this->_db->qn(_JOOM_TABLE_MAINTENANCE))
                ->set($set)
                ->where('refid = '.$key)
                ->where('type = 0');
          $this->_db->setQuery($query);
          if(!$this->_db->query())
          {
            JError::raiseWarning(500, $this->_db->getErrorMsg());
          }
        }
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Removes one or more orphaned files
   *
   * @return  void
   * @since   1.5.5
   */
  public function deleteOrphan()
  {
    $model = $this->getModel('maintenance');
    if(!$count = $model->deleteOrphan())
    {
      $msg  = $model->getError();
      $type = 'error';
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_FILE_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_FILES_DELETED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=orphans', $msg, $type);
  }

  /**
   * Removes one or more orphaned files
   *
   * @return  void
   * @since   1.5.5
   */
  public function deleteOrphanedFolder()
  {
    $model = $this->getModel('maintenance');
    if(!$count = $model->deleteOrphanedFolder())
    {
      $msg  = $model->getError();
      $type = 'error';
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDER_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_DELETED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=folders', $msg, $type);
  }

  /**
   * Executes all suggested tasks on orphaned files.
   *
   * Files for which corrupt images were found will be moved.
   * All other files will be deleted.
   *
   * @return  void
   * @since   1.5.5
   */
  public function applySuggestions()
  {
    $model  = $this->getModel('maintenance');
    $count  = $model->applySuggestions();
    if(!$count)
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      if($count[0] == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_MSG_ONE_ORPHAN_SUCCESSFULLY_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ORPHANS_SUCCESSFULLY_ADDED', $count[0]);
      }
      if($count[1] !== false)
      {
        if($count[1] == 1)
        {
          $msg  .= '</li><li>'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_ONE_FILE_DELETED');
        }
        else
        {
          $msg  .= '</li><li>'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_FILES_DELETED', $count[1]);
        }
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=orphans', $msg, $type);
  }

  /**
   * Executes all suggested tasks on orphaned folders.
   *
   * Folders for which corrupt categories were found will be moved.
   * All other folders will be deleted.
   *
   * @return  void
   * @since   1.5.5
   */
  public function applyFolderSuggestions()
  {
    $model  = $this->getModel('maintenance');
    $count  = $model->applyFolderSuggestions();
    if(!$count)
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      if($count[0] == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_ONE_FOLDER_ADDED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_SUCCESSFULLY_ADDED', $count[0]);
      }
      if($count[1] !== false)
      {
        if($count[1] == 1)
        {
          $msg  .= '</li><li>'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_ONE_FOLDER_DELETED');
        }
        else
        {
          $msg  .= '</li><li>'.JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_DELETED', $count[1]);
        }
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=folders', $msg, $type);
  }

  /**
   * Creates new folders for categories.
   *
   * @return  void
   * @since   1.5.5
   */
  public function create()
  {
    $model = $this->getModel('maintenance');
    if(!$count = $model->create())
    {
      $msg  = $model->getError();
      $type = 'error';
    }
    else
    {
      $type = 'message';
      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDER_CREATED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_MAIMAN_OF_MSG_FOLDERS_CREATED', $count);
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=categories', $msg, $type);
  }

  /**
   * Searches for corrupt images and orphand files in the filesystem of JoomGallery
   *
   * @return  void
   * @since   1.5.5
   */
  public function check()
  {
    $model = $this->getModel('maintenancecheck');

    $task   = JRequest::getCmd('task');

    require_once JPATH_COMPONENT.'/helpers/refresher.php';

    // The model will redirect, too.
    $model->$task();
  }

  /**
   * Optimizes all database tables of JoomGallery
   *
   * @return  void
   * @since   1.5.5
   */
  public function optimize()
  {
    $model = $this->getModel('maintenance');

    if(!$model->optimize())
    {
      $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=database', JText::_('COM_JOOMGALLERY_MAIMAN_DB_MSG_OPTIMIZATION_NOT_SUCCESSFUL'), 'error');
    }
    else
    {
      $this->setRedirect($this->_ambit->getRedirectUrl().'&tab=database', JText::_('COM_JOOMGALLERY_MAIMAN_DB_MSG_OPTIMIZATION_SUCCESSFUL'));
    }
  }
}