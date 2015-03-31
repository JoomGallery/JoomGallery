<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/category.php $
// $Id: category.php 4395 2014-06-10 14:03:32Z erftralle $
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
 * Category model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelCategory extends JoomGalleryModel
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

    $array = JRequest::getVar('cid',  0, '', 'array');
    $this->setId((int)$array[0]);
  }

  /**
   * Method to set the category identifier
   *
   * @param   int   $id The Category ID
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set id and wipe data
    $this->_id    = $id;
    $this->_data  = null;
  }

  /**
   * Method to get the data of the current category
   *
   * @return  object  An object with the category data
   * @since   1.5.5
   */
  public function getData()
  {
    if(empty($this->_data))
    {
      $row = $this->getTable('joomgallerycategories');
      $row->load($this->_id);

      $this->_data = $row;

      $this->_data->thumbnail_available = false;
      if($this->_data->thumbnail)
      {
        $query = $this->_db->getQuery(true)
              ->select('id')
              ->from(_JOOM_TABLE_IMAGES)
              ->where('id = '.$this->_data->thumbnail)
              ->where('published  = 1')
              ->where('approved   = 1');
        $this->_db->setQuery($query);
        if($this->_db->loadResult())
        {
          $this->_data->thumbnail_available = true;
        }
      }
    }

    $this->_mainframe->triggerEvent('onContentPrepareData', array(_JOOM_OPTION.'.category', $this->_data));

    return $this->_data;
  }

  /**
   * Method to check whether the category can have its state edited by the current user
   *
   * @param   array   Holds the data of the category
   * @return  boolean True if the current user is allowed to change the state of the category, false otherwise
   * @since   2.0
   */
  protected function canEditState($data)
  {
    // Check for existing category
    if(isset($data['cid']) && $data['cid'])
    {
      return $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.category.'.(int) $data['cid']);
    }
    else
    {
      // Maybe it is a new category, so check against the parent category
      if(isset($data['parent_id']) && $data['parent_id'])
      {
        return $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.category.'.(int) $data['parent_id']);
      }
      else
      {
        // Default to component settings if neither category nor parent category known
        return $this->_user->authorise('core.edit.state', _JOOM_OPTION);
      }
    }
  }

  /**
   * Method to get the form for the category
   *
   * @param   array Holds the data of the category (if available)
   * @return  mixed A JForm object on success, false on failure
   * @since 2.0
   */
  public function getForm($data = array())
  {
    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');

    $form = JForm::getInstance(_JOOM_OPTION.'.category', 'category');
    if(empty($form))
    {
      return false;
    }

    // Allow plugins to preprocess the form
    $this->preprocessForm($form, $data);

    if(!$this->canEditState($data))
    {
      // Disable fields for display
      $form->setFieldAttribute('ordering', 'disabled', 'true');
      $form->setFieldAttribute('published', 'disabled', 'true');
      $form->setFieldAttribute('hidden', 'disabled', 'true');

      // Unset the data of fields which we aren't allowed to change
      $form->setFieldAttribute('ordering', 'filter', 'unset');
      $form->setFieldAttribute('published', 'filter', 'unset');
      $form->setFieldAttribute('hidden', 'filter', 'unset');
    }

    return $form;
  }

  /**
   * Method to allow plugins to preprocess the form
   *
   * @param   JForm   $form   A JForm object.
   * @param   mixed   $data   The data expected for the form.
   * @param   string  $group  The name of the plugin group to import (defaults to "content").
   * @return  void
   * @since   2.1
   */
  protected function preprocessForm(JForm $form, $data, $group = 'content')
  {
    // Import the appropriate plugin group
    JPluginHelper::importPlugin($group);

    // Get the dispatcher
    $dispatcher = JDispatcher::getInstance();

    // Trigger the form preparation event
    $results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

    // Check for errors encountered while preparing the form
    if(count($results) && in_array(false, $results, true))
    {
      // Get the last error
      $error = $dispatcher->getError();

      if(!($error instanceof Exception))
      {
        throw new Exception($error);
      }
    }
  }

  /**
   * Method to store a category
   *
   * @return  int   The ID of the category, boolean false if an error occured
   * @since   1.5.5
   */
  public function store()
  {
    $row = $this->getTable('joomgallerycategories');

    // Get all necessary data from the post
    $data     = JRequest::get('post', 2);
    $params   = isset($data['params']) ? $data['params'] : array();

    // Creating a main category means creating
    // a category in ROOT category
    if($data['parent_id'] == 0)
    {
      $data['parent_id'] = 1;
    }

    // Check for validation errors
    $form = $this->getForm($data);
    $data = $this->_validate($form, $data);
    if($data === false)
    {
      return false;
    }

    // Check whether it is an existing category
    if($cid = intval($data['cid']))
    {
      $isNew = false;

      // Load category from the database
      $row->load($cid);

      // Check whether we are allowed to edit it
      $asset = _JOOM_OPTION.'.category.'.$cid;
      if(!$this->_user->authorise('core.edit', $asset) && (!$this->_user->authorise('core.edit.own', $asset) || !$row->owner || $row->owner != $this->_user->get('id')))
      {
        $this->_mainframe->redirect(JRoute::_('index.php?option=com_joomgallery&view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_EDIT_CATEGORY'), 'notice');
      }

      // Read old category name
      $catname_old  = $row->name;
      // Read old parent assignment
      $parent_old   = $row->parent_id;
    }
    else
    {
      $isNew = true;

      // Alter the name and the alias //for save as copy
      //if(JRequest::getCmd('task') == 'save2copy')
      //{
        list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['name']);
        $data['name']  = $title;
        $data['alias']  = $alias;
      //}
    }

    if(isset($data['password']) && $data['password'])
    {
      $salt = JUserHelper::genRandomPassword(32);
      $crypt = JUserHelper::getCryptedPassword($data['password'], $salt);
      $data['password'] = $crypt.':'.$salt;
    }

    // Bind the form fields to the category table
    if(!$row->bind($data))
    {
      $this->setError($row->getError());

      return false;
    }

    // Additional parameters, if set
    if(count($params))
    {
      // Build parameter INI string
      $txt = array();
      foreach($params as $k => $v)
      {
      $txt[] = $k.'='.$v;
      }
      $row->params = implode("\n", $txt);
    }

    // Bind the rules
    if(isset($data['rules']))
    {
      $rules = new JAccessRules($data['rules']);
      $row->setRules($rules);
    }

    // If it's a new category or the category will be moved
    // do an access check for the selected parent category
    $valid_parent = true;
    $row->parent_id = intval($row->parent_id);
    if($isNew || $parent_old != $row->parent_id)
    {
      if($row->parent_id > 1)
      {
        // Get data of the parent category
        $query = $this->_db->getQuery(true)
              ->select('cid, owner')
              ->from(_JOOM_TABLE_CATEGORIES)
              ->where('cid = '.$row->parent_id);
        $this->_db->setQuery($query);
        $parent_category = $this->_db->loadObject();

        if(     !$parent_category
            ||  (     !$this->_user->authorise('core.create', _JOOM_OPTION.'.category.'.$row->parent_id)
                  &&  (     !$this->_user->authorise('joom.create.inown', _JOOM_OPTION.'.category.'.$row->parent_id)
                        ||  !$parent_category->owner
                        ||  $parent_category->owner != $this->_user->get('id')
                      )
                )
          )
        {
          $valid_parent = false;
        }
      }
      else
      {
        if(!$this->_user->authorise('core.create', _JOOM_OPTION))
        {
          $valid_parent = false;
        }
      }
    }

    if($isNew)
    {
      // Check whether the user is allowed to store the category into the specified parent category or as a main category
      if(!$valid_parent)
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_NOT_ALLOWED_STORE_CATEGORY_IN_PARENT'));

        return false;
      }

      // Determine location in category tree
      if(!isset($data['ordering']) || !$data['ordering'] || $data['ordering'] == 'first-child')
      {
        $row->setLocation($data['parent_id'], 'first-child');
      }
      else
      {
        if($data['ordering'] == 'last-child')
        {
          $row->setLocation($data['parent_id'], 'last-child');
        }
        else
        {
          $row->setLocation($data['ordering'], 'after');
        }
      }

      // Make sure the record is valid
      if(!$row->check())
      {
        $this->setError($row->getError());
        return false;
      }

      JFilterOutput::objectHTMLSafe($row->name);

      // Check if special characters of catname can be replaced for a valid catpath
      // if resulting string is invalid set an error
      $catpath = JoomFile::fixFilename($row->name);
      if(JoomFile::checkValidFilename($row->name, $catpath) == false)
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_INVALID_FOLDERNAME'));

        return false;
      }

      // Store the entry to the database in order to get the new ID
      if(!$row->store())
      {
        $this->setError($row->getError());

        return false;
      }

      if($row->parent_id > 1)
      {
        $parent_catpath = JoomHelper::getCatPath($row->parent_id);
        $catpath   = $parent_catpath . $catpath;
      }

      // Add the category id to catpath
      $catpath .= '_'.$row->cid;

      if(!$this->_createFolders($catpath))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_CREATING_FOLDERS'));

        // Delete the just stored database entry
        $row->delete();

        return false;
      }
      else
      {
        $row->catpath = $catpath;

        // Make sure the record is valid
        if(!$row->check())
        {
          $this->setError($row->getError());
          return false;
        }

        // Store the entry to the database
        if(!$row->store())
        {
          $this->setError($row->getError());

          return false;
        }
      }

      $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.category', &$row, true));

      // New category successfully created
      return $row->cid;
    }

    // Move the category folder, if parent assignment or category name changed
    if($parent_old != $row->parent_id || $catname_old != $row->name)
    {
      // Check whether the user is allowed to move the category into the specified parent category
      if(!$valid_parent)
      {
        // If not store the category in the old parent category and leave a message.
        $row->parent_id = $parent_old;

        /*if(!$row->store())
        {
            JError::raiseError(100, $row->getError());
            return false;
        }*/
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_STORE_IMAGE_IN_CATEGORY'), 'notice');
      }
      else
      {
        if($parent_old != $row->parent_id)
        {
          if(isset($data['ordering']) && $data['ordering'] != $data['cid'])
          {
            // Determine location in category tree
            if(!$data['ordering'] || $data['ordering'] == 'first-child')
            {
              $row->setLocation($data['parent_id'], 'first-child');
            }
            else
            {
              if($data['ordering'] == 'last-child')
              {
                $row->setLocation($data['parent_id'], 'last-child');
              }
              else
              {
                $row->setLocation($data['ordering'], 'after');
              }
            }
          }
          else
          {
            $row->setLocation($data['parent_id'], 'first-child');
          }
        }
      }

      // Save old path
      $catpath_old    = $row->catpath;

      JFilterOutput::objectHTMLSafe($row->name);

      // Check if special characters of catname can be replaced for a valid catpath
      // if resulting string is invalid set an error
      $catpath = JoomFile::fixFilename($row->name);
      if(JoomFile::checkValidFilename($catpath_old, $catpath) == false)
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_INVALID_FOLDERNAME'));
        return false;
      }

      // Add the category id to catpath
      $catpath .= '_' . $row->cid;

      if($row->parent_id > 1)
      {
        $parent_catpath = JoomHelper::getCatPath($row->parent_id);
        $catpath   = $parent_catpath . $catpath;
      }

      // Move folders, only if the catpath has changed
      if($catpath_old != $catpath && !$this->_moveFolders($catpath_old, $catpath))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_MOVING_FOLDERS'));
        return false;
      }
      // Update catpath in the database
      $row->catpath = $catpath;

      // Modify catpath of all sub-categories in the database
      $this->updateNewCatpath($row->cid, $catpath_old, $catpath);
    }
    else
    {
      // Check whether ordering has changed
      if(isset($data['ordering']) && $data['ordering'] != $row->cid)
      {
        // Determine location in category tree
        if($data['ordering'] == 'first-child' || $data['ordering'] == 'last-child')
        {
          $row->setLocation($data['parent_id'], $data['ordering']);
        }
        else
        {
          // Check whether the new reference category is a
          // valid child category of the current parent category
          $this->_db->setQuery($this->_db->getQuery(true)
                ->select('cid')
                ->from(_JOOM_TABLE_CATEGORIES)
                ->where('parent_id = '.$row->parent_id)
                ->where('cid = '.$data['ordering']));
          if($this->_db->loadResult())
          {
            $row->setLocation($data['ordering'], 'after');
          }
        }
      }
    }

    // Make sure the record is valid
    if(!$row->check())
    {
      $this->setError($row->getError());
      return false;
    }

    // Store the entry to the database
    if(!$row->store())
    {
      $this->setError($row->getError());

      return false;
    }

    $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.category', &$row, true));

    return $row->cid;
  }

  /**
   * Method to perform batch operations on a category or a set of categories
   *
   * @param   array   $commands  An array of commands to perform
   * @param   array   $pks       An array of category IDs
   * @return  boolean True on success, false otherwis
   * @since   3.0
   */
  public function batch($commands, $pks)
  {
    // Sanitize user IDs
    $pks = array_unique($pks);
    JArrayHelper::toInteger($pks);

    // Remove any values of zero
    if(array_search(0, $pks, true))
    {
      unset($pks[array_search(0, $pks, true)]);
    }

    if(empty($pks))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));

      return false;
    }

    $done = false;

    if($cmd = JArrayHelper::getValue($commands, 'move_copy'))
    {
      if($cmd == 'c')
      {
        $result = $this->batchCopy($commands['category_id'], $pks);
        if(is_array($result))
        {
          $pks = $result;
        }
        else
        {
          return false;
        }
      }
      else
      {
        if($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks))
        {
          return false;
        }
      }

      $done = true;
    }

    if(!empty($commands['assetgroup_id']))
    {
      if(!$this->batchAccess($commands['assetgroup_id'], $pks))
      {
        return false;
      }

      $done = true;
    }

    if(!empty($commands['language_id']))
    {
      if(!$this->batchLanguage($commands['language_id'], $pks))
      {
        return false;
      }

      $done = true;
    }

    if(!$done)
    {
      $this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

      return false;
    }

    return true;
  }

  /**
   * Batch access level changes for a group of categories
   *
   * @param   int      $value     The new value matching an Asset Group ID
   * @param   array    $pks       An array of row IDs
   * @return  boolean  True on success, false otherwise
   * @since   3.0
   */
  protected function batchAccess($value, $pks)
  {
    $table = $this->getTable('joomgallerycategories');

    foreach($pks as $pk)
    {
      $table->reset();
      $table->load($pk);

      if(      $this->_user->authorise('core.edit', _JOOM_OPTION.'.category.'.$pk)
          || (
                $table->owner == $this->_user->get('id')
            &&  $table->owner != 0
            &&  $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.category.'.$pk)
              )
        )
      {
        $table->load($pk);
        $table->access = (int) $value;

        if(!$table->store())
        {
          $this->setError($table->getError());

          return false;
        }
      }
      else
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

        return false;
      }
    }

    return true;
  }

  /**
   * Batch copy items to a new category or current
   *
   * @param   int   $value  The new parent category
   * @param   array $pks    An array of category IDs
   * @return  mixed An array of new IDs on success, boolean false on failure
   * @since   3.0
   */
  protected function batchCopy($value, $pks)
  {
    $categoryId = (int) $value;

    $table = $this->getTable('joomgallerycategories');
    $i = 0;

    // Check that the parent category exists
    if($categoryId)
    {
      if(!$parent_category = $table->load($categoryId))
      {
        if($error = $table->getError())
        {
          $this->setError($error);

          return false;
        }
        else
        {
          $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

          return false;
        }
      }
    }

    if(!$categoryId)
    {
      // Check that the user has create permissions in root
      if(!$this->_user->authorise('core.create', _JOOM_OPTION))
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

        return false;
      }

      $categoryId = 1;
    }
    else
    {
      // Check that the user has create permissions
      if(!$this->_user->authorise('core.create', _JOOM_OPTION.'.category.'.$categoryId)
          &&  (     !$this->_user->authorise('joom.create.inown', _JOOM_OPTION.'.category.'.$categoryId)
                ||  !$parent_category->owner
                ||  $parent_category->owner != $this->_user->get('id')
              )
        )
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

        return false;
      }
    }

    foreach($pks as $pk)
    {
      $table->reset();

      // Check that the row actually exists
      if(!$table->load($pk))
      {
        if($error = $table->getError())
        {
          $this->setError($error);

          return false;
        }
        else
        {
          // Not fatal error
          $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));

          continue;
        }
      }

      // Reset the ID and the alias (resetting catpath is
      // necessary for that, too) because we are making a copy
      $table->cid = 0;
      $table->alias = '';
      $table->catpath = '';

      // New parent category ID
      $table->parent_id = $categoryId;

      // Set ordering
      $table->setLocation($categoryId, 'last-child');

      // Check the row
      if(!$table->check())
      {
        $this->setError($table->getError());

        return false;
      }

      JFilterOutput::objectHTMLSafe($table->name);

      // Check if special characters of catname can be replaced for a valid catpath
      // if resulting string is invalid set an error
      $catpath = JoomFile::fixFilename($table->name);
      if(JoomFile::checkValidFilename($table->name, $catpath) == false)
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_INVALID_FOLDERNAME'));

        return false;
      }

      // Store the entry to the database in order to get the new ID
      if(!$table->store())
      {
        $this->setError($table->getError());

        return false;
      }

      if($table->parent_id > 1)
      {
        $parent_catpath = JoomHelper::getCatPath($table->parent_id);
        $catpath   = $parent_catpath . $catpath;
      }

      // Add the category id to catpath
      $catpath .= '_'.$table->cid;

      if(!$this->_createFolders($catpath))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_CREATING_FOLDERS'));

        // Delete the just stored database entry
        $table->delete();

        return false;
      }

      $table->catpath = $catpath;

      // Make sure the record is valid
      if(!$table->check())
      {
        $this->setError($table->getError());

        return false;
      }

      // Alter the title and alias (for preventing duplicates)
      $data = $this->generateNewTitle($categoryId, $table->alias, $table->name);
      $table->name = $data['0'];
      $table->alias = $data['1'];

      // Store the entry to the database
      if(!$table->store())
      {
        $this-setError($table->getError());

        return false;
      }

      $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.category', &$table, true));

      // Get the new category ID
      $newId = $table->cid;

      // Add the new ID to the array
      $newIds[$i]  = $newId;
      $i++;
    }

    return $newIds;
  }

  /**
   * Batch move categories to a new category
   *
   * @param   int      $value The new parent category ID
   * @param   array    $pks   An array of category IDs
   * @return  boolean  True on success, false otherwise
   * @since   3.0
   */
  protected function batchMove($value, $pks)
  {
    $categoryId = (int) $value;

    $table = $this->getTable('joomgallerycategories');

    // Check that the parent category exists
    if($categoryId)
    {
      if(!$parent_category = $table->load($categoryId))
      {
        if($error = $table->getError())
        {
          $this->setError($error);

          return false;
        }
        else
        {
          $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));

          return false;
        }
      }
    }

    if(!$categoryId)
    {
      // Check that the user has create permissions in root
      if(!$this->_user->authorise('core.create', _JOOM_OPTION))
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

        return false;
      }

      $categoryId = 1;
    }
    else
    {
      // Check that the user has create permissions
      if(!$this->_user->authorise('core.create', _JOOM_OPTION.'.category.'.$categoryId)
          &&  (     !$this->_user->authorise('joom.create.inown', _JOOM_OPTION.'.category.'.$categoryId)
                ||  !$parent_category->owner
                ||  $parent_category->owner != $this->_user->get('id')
              )
        )
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));

        return false;
      }
    }

    foreach($pks as $pk)
    {
      // Check that the category actually exists
      if(!$table->load($pk))
      {
        if($error = $table->getError())
        {
          $this->setError($error);

          return false;
        }
        else
        {
          // Not fatal error
          $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));

          continue;
        }
      }

      if(!$this->_user->authorise('core.edit', _JOOM_OPTION.'.category.'.$pk)
          &&  (     !$this->_user->authorise('joom.edit.own', _JOOM_OPTION.'.category.'.$pk)
                ||  !$table->owner
                ||  $table->owner != $this->_user->get('id')
              )
        )
      {
        $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

        return false;
      }

      // New parent category ID
      $table->parent_id = $categoryId;

      // Set ordering
      $table->setLocation($categoryId, 'last-child');

      // Save old path
      $catpath_old    = $table->catpath;

      JFilterOutput::objectHTMLSafe($table->name);

      // Check if special characters of catname can be replaced for a valid catpath
      // if resulting string is invalid set an error
      $catpath = JoomFile::fixFilename($table->name);
      if(JoomFile::checkValidFilename($catpath_old, $catpath) == false)
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_INVALID_FOLDERNAME'));

        return false;
      }

      // Add the category ID to catpath
      $catpath .= '_' . $table->cid;

      if($table->parent_id > 1)
      {
        $parent_catpath = JoomHelper::getCatPath($table->parent_id);
        $catpath = $parent_catpath . $catpath;
      }

      // Move folders, only if the catpath has changed
      if($catpath_old != $catpath && !$this->_moveFolders($catpath_old, $catpath))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_MOVING_FOLDERS'));

        return false;
      }

      // Update catpath in the database
      $table->catpath = $catpath;

      // Modify catpath of all sub-categories in the database
      $this->updateNewCatpath($table->cid, $catpath_old, $catpath);

      // Make sure the record is valid
      if(!$table->check())
      {
        $this->setError($table->getError());

        return false;
      }

      // Store the entry to the database
      if(!$table->store())
      {
        $this-setError($table->getError());

        return false;
      }
    }

    return true;
  }

  /**
   * Retrieves the data for creating the orderings drop down list
   *
   * @param   int   $parent Parent category which has to be included into the list independent of it's access state
   * @return  array An array of JHTML select options with the ordering numbers
   *                and the category names
   * @since   1.5.5
   */
  public function getOrderings($parent = null)
  {
    if(empty($this->_orderings))
    {
      $categories = JoomHelper::getAuthorisedCategories('core.create');

      $allowed_categories = '';

      // If the user is allowed to create main categories add '1' as an allowed parent category
      if($this->_user->authorise('core.create', _JOOM_OPTION))
      {
        $allowed_categories .= '1,';
      }

      foreach($categories as $category)
      {
        $allowed_categories .= $category->cid.',';
      }

      if(!is_null($parent))
      {
        $allowed_categories .= $parent;
      }
      else
      {
        $allowed_categories = trim($allowed_categories, ',');
      }

      if(!$allowed_categories)
      {
        return array();
      }

      $query = $this->_db->getQuery(true);
      $query->select('cid')
            ->select('parent_id')
            ->select('name')
            ->from(_JOOM_TABLE_CATEGORIES)
            ->where('parent_id IN ('.$allowed_categories.')')
            ->where('lft > 0')
            ->order('lft');
      $this->_db->setQuery($query);
      if(!$this->_orderings = $this->_db->loadObjectList())
      {
        $this->setError($this->_db->getError());

        return array();
      }
    }

    return $this->_orderings;
  }

  /**
   * Update of category path in the database for sub-categories
   * if a parent category has been moved or the name has changed.
   *
   * Recursive call to each level of depth.
   *
   * @param   string  $catids_values  ID(s) of the categories to update (comma separated)
   * @param   string  $oldpath        Former relative category path
   * @param   string  $newpath        New relative category path
   * @return  void
   * @since   1.0.0
   */
  public function updateNewCatpath($catids_values, &$oldpath, &$newpath)
  {
    // Query for sub-categories with parent in $catids_values
    $query = $this->_db->getQuery(true)
          ->select('cid')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('parent_id IN ('.$catids_values.')');
    $this->_db->setQuery($query);

    $subcatids = $this->_db->loadColumn();

    if($this->_db->getErrorNum())
    {
      JError::raiseWarning(500, $this->_db->getErrorMsg());
    }

    // Nothing found, return
    if(!count($subcatids))
    {
      return;
    }

    $row = JTable::getInstance('joomgallerycategories', 'Table');
    foreach($subcatids as $subcatid)
    {
      $row->load($subcatid);
      $catpath = $row->catpath;

      // Replace former category path with new one
      $catpath = str_replace($oldpath.'/', $newpath.'/', $catpath);

      // Then save it
      $row->catpath = $catpath;
      if(!$row->store())
      {
        JError::raiseError(500, $row->getError());
      }
    }

    // Split the array in comma separated string
    $catids_values = implode (',', $subcatids);

    // Call again with sub-categories as parent
    $this->updateNewCatpath($catids_values, $oldpath, $newpath);
  }

  /**
   * Creates the folders for a category
   *
   * @param   string  The category path for the category
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _createFolders($catpath)
  {
    $catpath = JPath::clean($catpath);

    // Create the folder of the category for the original images
    if(!JFolder::create($this->_ambit->get('orig_path').$catpath))
    {
      // If not successfull
      return false;
    }
    else
    {
      // Copy an index.html file into the new folder
      JoomFile::copyIndexHtml($this->_ambit->get('orig_path').$catpath);

      // Create the folder of the category for the detail images
      if(!JFolder::create($this->_ambit->get('img_path').$catpath))
      {
        // If not successful
        JFolder::delete($this->_ambit->get('orig_path').$catpath);
        return false;
      }
      else
      {
        // Copy an index.html file into the new folder
        JoomFile::copyIndexHtml($this->_ambit->get('img_path').$catpath);

        // Create the folder of the category for the thumbnails
        if(!JFolder::create($this->_ambit->get('thumb_path').$catpath))
        {
          // If not successful
          JFolder::delete($this->_ambit->get('orig_path').$catpath);
          JFolder::delete($this->_ambit->get('img_path').$catpath);
          return false;
        }
        else
        {
          // Copy an index.html file into the new folder
          JoomFile::copyIndexHtml($this->_ambit->get('thumb_path').$catpath);
        }
      }
    }

    return true;
  }

  /**
   * Moves folders of an existing category
   *
   * @param   string  $src  The source category path
   * @param   string  $dest The destination category path
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _moveFolders($src, $dest)
  {
    $orig_src   = JPath::clean($this->_ambit->get('orig_path').$src);
    $orig_dest  = JPath::clean($this->_ambit->get('orig_path').$dest);
    $img_src    = JPath::clean($this->_ambit->get('img_path').$src);
    $img_dest   = JPath::clean($this->_ambit->get('img_path').$dest);
    $thumb_src  = JPath::clean($this->_ambit->get('thumb_path').$src);
    $thumb_dest = JPath::clean($this->_ambit->get('thumb_path').$dest);

    // Move the folder of the category for the original images
    $return = JFolder::move($orig_src, $orig_dest);
    if($return !== true)
    {
      // If not successfull
      JError::raiseWarning(100, $return);
      return false;
    }
    else
    {
      // Move the folder of the category for the detail images
      $return = JFolder::move($img_src, $img_dest);
      if($return !== true)
      {
        // If not successful
        JFolder::move($orig_dest, $orig_src);
        JError::raiseWarning(100, $return);
        return false;
      }
      else
      {
        // Move the folder of the category for the thumbnails
        $return = JFolder::move($thumb_src, $thumb_dest);
        if($return !== true)
        {
          // If not successful
          JFolder::move($orig_dest, $orig_src);
          JFolder::move($img_dest, $img_src);
          JError::raiseWarning(100, $return);
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Method to validate the form data
   *
   * @param   object  $form   The form to validate against
   * @param   array   $data   The data to validate
   * @return  mixed   Array of filtered data if valid, false otherwise
   * @since   2.0
   */
  protected function _validate($form, $data)
  {
    // Filter and validate the form data
    $data   = $form->filter($data);
    $return = $form->validate($data);

    // Check for an error
    if(JError::isError($return))
    {
      $this->setError($return->getMessage());
      return false;
    }

    // Check the validation results
    if ($return === false)
    {
      // Get the validation messages from the form
      foreach($form->getErrors() as $message)
      {
        $this->setError(JText::_($message));
      }
      return false;
    }

    return $data;
  }

  /**
   * Method to change the name and the alias if there exist already the same
   *
   * @param   int     The parent category ID
   * @param   string  The alias of the category
   * @param   string  The name of the category
   * @return  array   Holds the new name and alias
   * @since   2.0
   */
  function generateNewTitle(&$parent_id, &$alias, &$title)
  {
    // Alter the name and the alias
    $table = $this->getTable('joomgallerycategories');
    while($table->load(array('alias' => $alias, 'parent_id' => $parent_id)))
    {
      $m = null;
      if(preg_match('#-(\d+)$#', $alias, $m))
      {
        $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
      }
      else
      {
        $alias .= '-2';
      }
      if(preg_match('#\((\d+)\)$#', $title, $m))
      {
        $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
      }
      else
      {
        $title .= ' (2)';
      }
    }

    return array($title, $alias);
  }
}