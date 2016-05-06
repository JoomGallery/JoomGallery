<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/edit.php $
// $Id: edit.php 2015-04-10 $
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
 * JoomGallery Edit Image model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelEdit extends JoomGalleryModel
{
  /**
   * Image ID
   *
   * @var     int
   */
  protected $_id;

  /**
   * Image data object
   *
   * @var     object
   */
  protected $_image;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    // Additional security check for unregistered users
    if(!$this->_user->get('id') && !$this->_config->get('jg_unregistered_permissions'))
    {
      throw new Exception(JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED'));
    }
  }

  /**
   * Method to set the image identifier
   *
   * @param   int     $id The image ID
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new image ID if valid and wipe data
    if(!$id)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=userpanel', false), JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'), 'notice');
    }

    $this->_id    = $id;
    $this->_image = null;
  }

  /**
   * Method to get the image data
   *
   * @return  object  Image data object
   * @since   1.5.5
   */
  public function getImage()
  {
    if($this->_loadImage())
    {
      // Check whether we are allowed to edit the image
      $asset = _JOOM_OPTION.'.image.'.$this->_id;
      if(!$this->_user->authorise('core.edit', $asset) && (!$this->_user->authorise('core.edit.own', $asset) || !$this->_image->owner || $this->_image->owner != $this->_user->get('id')))
      {
        $this->_mainframe->redirect(JRoute::_('index.php?option=com_joomgallery&view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_EDIT_IMAGE'), 'notice');
      }

      return $this->_image;
    }

    return false;
  }

  /**
   * Method to check whether the image can have its state edited by the current user
   *
   * @param   array   Holds the data of the image
   * @return  boolean True if the current user is allowed to change the state of the image, false otherwise
   * @since   2.0
   */
  protected function canEditState($data)
  {
    // Check for existing image
    if(isset($data['id']) && $data['id'])
    {
      return $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.(int) $data['id']);
    }
    else
    {
      // Maybe it is a new image, so check against the category
      if(isset($data['catid']) && $data['catid'])
      {
        return $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.category.'.(int) $data['catid']);
      }
      else
      {
        // Default to component settings if neither image nor category known
        return $this->_user->authorise('core.edit.state', _JOOM_OPTION);
      }
    }
  }

  /**
   * Method to get the form for the image
   *
   * @param   array   Holds the data of the image (if available)
   * @return  mixed   A JForm object on success, false on failure
   * @since   2.0
   */
  public function getForm($data = array(), $formName = 'edit')
  {
    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields');
    JForm::addRulePath(JPATH_COMPONENT_ADMINISTRATOR.'/models/rules');

    $form = JForm::getInstance(_JOOM_OPTION.'.'.$formName, $formName);
    if(empty($form))
    {
      return false;
    }

    if(empty($data))
    {
      $data = (array) $this->getImage();
    }

    // Allow plugins to preprocess the form
    JPluginHelper::importPlugin('joomgallery');
    $this->preprocessForm($form, $data);

    if(!$this->canEditState($data))
    {
      // Disable fields for display
      $form->setFieldAttribute('published', 'disabled', 'true');

      // Unset the data of fields which we aren't allowed to change
      $form->setFieldAttribute('published', 'filter', 'unset');
    }
    
    if(!$this->_config->get('jg_edit_metadata'))
    {
      $form->setFieldAttribute('metakey', 'disabled', 'true');
      $form->setFieldAttribute('metakey', 'filter', 'unset');
      $form->setFieldAttribute('metadesc', 'disabled', 'true');
      $form->setFieldAttribute('metadesc', 'filter', 'unset');
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
   * Method to load the image data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadImage()
  {
    if(empty($this->_image))
    {
      $row = $this->getTable('joomgalleryimages');

      if(!$row->load($this->_id))
      {
        $row->imgtitle      = $this->_mainframe->getUserStateFromRequest('joom.image.imgtitle',       'imgtitle');
        $row->imgtext       = $this->_mainframe->getUserStateFromRequest('joom.image.imgtext',        'imgtext');
        $row->imgauthor     = $this->_mainframe->getUserStateFromRequest('joom.image.imgauthor',      'imgauthor');
        $row->owner         = $this->_mainframe->getUserStateFromRequest('joom.image.owner',          'owner');
        $row->published     = $this->_mainframe->getUserStateFromRequest('joom.image.published',      'published', 1, 'int');
        $row->imgfilename   = $this->_mainframe->getUserStateFromRequest('joom.image.imgfilename',    'imgfilename');
        $row->imgthumbname  = $this->_mainframe->getUserStateFromRequest('joom.image.imgthumbname',   'imgthumbname');
        $row->catid         = $this->_mainframe->getUserStateFromRequest('joom.image.catid',          'catid', 0, 'int');
        $row->thumb_url     = null;
        //Source category for original and detail picture
        #$row->detail_catid  = $this->_mainframe->getUserStateFromRequest('joom.image.detail_catid',   'detail_catid', 0, 'int');
        //Source category for thumbnail
        #$row->thumb_catid   = $this->_mainframe->getUserStateFromRequest('joom.image.thumb_catid',    'thumb_catid', 0, 'int');
        #$row->copy_original = $this->_mainframe->getUserStateFromRequest('joom.image.copy_original',  'copy_original', 0, 'int');
      }
      else
      {
        $row->thumb_url = $this->_ambit->getImg('thumb_url', $row);
      }

      JPluginHelper::importPlugin('joomgallery');
      $this->_mainframe->triggerEvent('onContentPrepareData', array(_JOOM_OPTION.'.image', $row));

      $this->_image = $row;
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
   * Method to store an edited image
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function store($data = null)
  {
    $row = $this->getTable('joomgalleryimages');

    if(is_null($data))
    {
      $data = JRequest::get('post', 2);
    }

    // Check for validation errors
    $form = $this->getForm($data);
    $data = $this->_validate($form, $data);
    if($data === false)
    {
      return false;
    }

    // Check whether it is an existing image
    if(!$id = intval($data['id']))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'), 'error');
    }

    // Load image data from the database
    $row->load($id);

    // Check whether we are allowed to edit it
    $asset = _JOOM_OPTION.'.image.'.$id;
    if(!$this->_user->authorise('core.edit', $asset) && (!$this->_user->authorise('core.edit.own', $asset) || !$row->owner || $row->owner != $this->_user->get('id')))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?option=com_joomgallery&view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_EDIT_IMAGE'), 'notice');
    }

    // Read old category ID
    $catid_old  = $row->catid;

    // Bind the form fields to the images table
    if(!$row->bind($data))
    {
      $this->setError($row->getError());

      return false;
    }

    // Load category information for permission checks
    $query = $this->_db->getQuery(true)
          ->select('cid, owner')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('cid = '.$row->catid);
    $this->_db->setQuery($query);
    $category = $this->_db->loadObject();

    $move = false;
    if(isset($catid_old) && $catid_old != $row->catid)
    {
      $move = true;

      // Check whether the new category is a valid one
      if(!$category)
      {
        // If that's not the case store the image in the old category and leave a message
        $move = false;
        $row->catid = $catid_old;

        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_VALID_CATEGORY_SELECTED'), 'notice');
      }
      else
      {
        // Access check for the selected new category
        if(   !$this->_user->authorise('joom.upload', _JOOM_OPTION.'.category.'.$row->catid)
            &&
              (     !$this->_user->authorise('joom.upload.inown', _JOOM_OPTION.'.category.'.$row->catid)
                ||  !$category->owner
                ||  $category->owner != $this->_user->get('id')
              )
          )
        {
          $move = false;
          $row->catid = $catid_old;

          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_STORE_IMAGE_IN_CATEGORY'), 'notice');
        }
      }
    }

    if($move && !$this->moveImage($row, $row->catid, $catid_old))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_MOVE_IMAGE'), 'notice');

      return false;
    }
    else
    {
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

    // Successfully stored image (and moved)
    $row->reorder('catid = '.$row->catid);
    if(isset($catid_old) && $catid_old != $row->catid)
    {
      $row->reorder('catid = '.$catid_old);
    }

    $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.image', &$row, false));

    return $row->id;
  }

  /**
   * Method to store an edited image from the quick edit form
   *
   * @param   int   ID of the image to edit
   * @param   array Associative array of image data to store
   * @return  boolean True on success
   * @since   3.3
   */
  public function quickEdit($id, $data)
  {
    $row = $this->getTable('joomgalleryimages');

    // Check for validation errors
    $form = $this->getForm($data, 'quickedit');
    $data = $this->_validate($form, $data);
    if($data === false)
    {
      throw new RuntimeException($this->getError());
    }

    // Check whether it is an existing image
    $id = (int) $id;
    if(!$id)
    {
      throw new RuntimeException(JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));
    }

    // Load image data from the database
    $row->load($id);

    // Check whether we are allowed to edit it
    $asset = _JOOM_OPTION.'.image.'.$id;
    if(!$this->_user->authorise('core.edit', $asset) && (!$this->_user->authorise('core.edit.own', $asset) || !$row->owner || $row->owner != $this->_user->get('id')))
    {
      throw new RuntimeException(JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_EDIT_IMAGE'));
    }

    // Bind the form fields to the images table
    if(!$row->bind($data))
    {
      throw new RuntimeException($row->getError());
    }

    // Make sure the record is valid
    if(!$row->check())
    {
      throw new RuntimeException($row->getError());
    }

    // Store the entry to the database
    if(!$row->store())
    {
      throw new RuntimeException($row->getError());
    }

    // Successfully stored image
    $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.image.quick', &$row, false));

    return true;
  }

  /**
   * Method to delete an image
   *
   * @return  boolean  True on success, false otherwise
   * @since   1.5.5
   */
  public function delete()
  {
    jimport('joomla.filesystem.file');

    $row = $this->getTable('joomgalleryimages');

    $row->load($this->_id);

    // Check whether we are allowed to delete this image
    if(!$this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$row->id))
    {
      throw new RuntimeException(JText::_('COM_JOOMGALLERY_IMAGE_MSG_DELETE_NOT_PERMITTED'));
    }

    // Database query to check if there are other images which this
    // thumbnail is assigned to and how many of them exist
    $query = $this->_db->getQuery(true);
    $query->select('COUNT(id)')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('imgthumbname = \''.$row->imgthumbname.'\'')
          ->where('id          != '.$row->id)
          ->where('catid        = '.$row->catid);
    $this->_db->setQuery($query);
    $thumb_count = $this->_db->loadResult();

    // Database query to check if there are other images which this
    // detail image is assigned to and how many of them exist
    $query->clear('where');
    $query->where('imgfilename = \''.$row->imgfilename.'\'')
          ->where('id         != '.$row->id)
          ->where('catid       = '.$row->catid);
    $this->_db->setQuery($query);
    $img_count = $this->_db->loadResult();

    // Delete the thumbnail if there are no other images
    // in same category assigned to it
    if(!$thumb_count)
    {
      $thumb = $this->_ambit->getImg('thumb_path', $row);
      if(!JFile::delete($thumb))
      {
        // If thumbnail is not deletable set an error message
        JLog::add(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_THUMB', $thumb), JLog::WARNING, 'jerror');
      }
    }

    // Delete the detail if there are no other detail and
    // original images from same category assigned to it
    if(!$img_count)
    {
      $img = $this->_ambit->getImg('img_path', $row);
      if(!JFile::delete($img))
      {
        // If detail is not deletable set an error message
        JLog::add(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_IMAGE', $img), JLog::WARNING, 'jerror');
      }
      // Original exists?
      $orig = $this->_ambit->getImg('orig_path', $row);
      if(JFile::exists($orig))
      {
        // Delete it
        if(!JFile::delete($orig))
        {
          // If original is not deletable set an error message
          JLog::add(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_ORIG', $orig), JLog::WARNING, 'jerror');
        }
      }
    }

    // Delete the corresponding database entries of the comments
    $query->clear();
    $query->delete(_JOOM_TABLE_COMMENTS)
          ->where('cmtpic = '.$this->_id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      JLog::add(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_COMMENTS', $this->_id), JLog::WARNING, 'jerror');
    }

    // Delete the corresponding database entries of the name tags
    $query->clear();
    $query->delete(_JOOM_TABLE_NAMESHIELDS)
          ->where('npicid = '.$this->_id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      JLog::add(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_NAMETAGS', $this->_id), JLog::WARNING, 'jerror');
    }

    // Delete the corresponding database entries of the Votes
    $query->clear();
    $query->delete(_JOOM_TABLE_VOTES)
          ->where('picid = '.$this->_id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      JLog::add(JText::_('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_VOTES'), JLog::WARNING, 'jerror');
    }

    // Delete the database entry of the image
    if(!$row->delete())
    {
      throw new RuntimeException(JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_DELETE_IMAGE_DATA', $this->_id));
    }

    // Image successfully deleted
    $row->reorder('catid = '.$row->catid);

    JPluginHelper::importPlugin('joomgallery');
    $this->_mainframe->triggerEvent('onContentAfterDelete', array(_JOOM_OPTION.'.image', $row));

    return true;
  }

  /**
   * Moves image into another category
   * (The given image data is only stored in the database if old and new category are different from each other)
   *
   * @param   object  $item       Holds the data of the image to move, if it's not an object we will try to retrieve the data from the database
   * @param   int     $catid_new  The ID of the category to which the image should be moved
   * @param   int     $catid_old  The ID of the old category of the image
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function moveImage(&$item, $catid_new, $catid_old = 0)
  {
    jimport('joomla.filesystem.file');

    // If we just have the image ID
    if(!is_object($item))
    {
      $id   = intval($item);
      $item = $this->getTable('joomgalleryimages');
      $item->load($id);
      $catid_old = $item->catid;
    }

    // If the image is already in the correct category return true
    if($catid_new == $catid_old)
    {
      return true;
    }

    $catpath_old  = JoomHelper::getCatPath($catid_old);
    $catpath_new  = JoomHelper::getCatPath($catid_new);

    // Database query to check if there are other images which this
    // thumbnail is assigned to and how many of them exist
    $query = $this->_db->getQuery(true);
    $query->select('COUNT(id)')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('imgthumbname  = \''.$item->imgthumbname.'\'')
          ->where('id           != '.$item->id)
          ->where('catid         = '.$catid_old);
    $this->_db->setQuery($query);
    $thumb_count = $this->_db->loadResult();

    // Check if thumbnail already exists in source directory and
    // if it doesn't already exist in destination directory.
    // If that's the case the file will not be copied.
    $thumb_created  = false;
    $thumb_source   = $this->_ambit->get('thumb_path').$catpath_old.$item->imgthumbname;
    $thumb_dest     = $this->_ambit->get('thumb_path').$catpath_new.$item->imgthumbname;
    if(JFile::exists($thumb_dest))
    {
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_EDITIMAGE_MSG_DEST_THUMB_ALREADY_EXISTS'));

      if($thumb_count && JFile::exists($thumb_source))
      {
        JFile::delete($thumb_source);
      }
    }
    else
    {
      if(!JFile::exists($thumb_source))
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_SOURCE_THUMB_NOT_EXISTS', $thumb_source));

        return false;
      }
      else
      {
        // If there is no image remaining in source directory
        // which uses the file
        if(!$thumb_count)
        {
          // Move the thumbnail
          $result = JFile::move($thumb_source, $thumb_dest);
        }
        else
        {
          // Otherwise just copy the thumbnail in order that it remains in the source directory
          $result = JFile::copy($thumb_source, $thumb_dest);
        }
        // If not succesful raise an error message and abort
        if(!$result)
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_MOVE_THUMB', JPath::clean($thumb_dest)));

          return false;
        }

        // Set control variable according to the successful move/copy procedure
        $thumb_created = true;
      }
    }

    // Database query to check if there are other images which this
    // file is assigned to and how many of them exist
    $query->clear('where');
    $query->where('imgfilename  = \''.$item->imgfilename.'\'')
          ->where('id          != '.$item->id)
          ->where('catid        = '.$catid_old);
    $this->_db->setQuery($query);
    $img_count    = $this->_db->loadResult();

    // Same procedure with the detail image
    // In case of error roll previous copy/move procedure back
    $img_created  = false;
    $img_source   = $this->_ambit->get('img_path').$catpath_old.$item->imgfilename;
    $img_dest     = $this->_ambit->get('img_path').$catpath_new.$item->imgfilename;
    if(JFile::exists($img_dest))
    {
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_EDITIMAGE_MSG_DEST_IMG_ALREADY_EXISTS'));

      if($img_count && JFile::exists($img_source))
      {
        JFile::delete($img_source);
      }
    }
    else
    {
      if(!JFile::exists($img_source))
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_SOURCE_IMG_NOT_EXISTS', $img_source));

        return false;
      }
      else
      {
        if(!$img_count)
        {
          $result = JFile::move($img_source, $img_dest);
        }
        else
        {
          $result = JFile::copy($img_source, $img_dest);
        }
        if(!$result)
        {
          if($thumb_created)
          {
            if(!$thumb_count)
            {
              JFile::move($thumb_dest, $thumb_source);
            }
            else
            {
              JFile::delete($thumb_dest);
            }
          }

          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_MOVE_IMG', JPath::clean($img_dest)));

          return false;
        }

        // Set control variable according to the successful move/copy procedure
        $img_created = true;
      }
    }

    // Go on with original image
    $orig_source  = $this->_ambit->get('orig_path').$catpath_old.$item->imgfilename;
    $orig_dest    = $this->_ambit->get('orig_path').$catpath_new.$item->imgfilename;
    if(JFile::exists($orig_dest))
    {
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_EDITIMAGE_MSG_DEST_ORIG_ALREADY_EXISTS'));

      if($img_count && JFile::exists($orig_source))
      {
        JFile::delete($orig_source);
      }
    }
    else
    {
      if(JFile::exists($orig_source))
      {
        if(!$img_count)
        {
          $result = JFile::move($orig_source, $orig_dest);
        }
        else
        {
          $result = JFile::copy($orig_source, $orig_dest);
        }
        if(!$result)
        {
          if($thumb_created)
          {
            if(!$thumb_count)
            {
              JFile::move($thumb_dest, $thumb_source);
            }
            else
            {
              JFile::delete($thumb_dest);
            }
          }
          if($img_created)
          {
            if(!$img_count)
            {
              JFile::move($img_dest, $img_source);
            }
            else
            {
              JFile::delete($img_dest);
            }
          }

          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_EDITIMAGE_MSG_COULD_NOT_MOVE_ORIG', JPath::clean($orig_dest)));

          return false;
        }
      }
    }

    // If all folder operations for the image were successful
    // modify the database entry
    $item->catid    = $catid_new;
    $item->ordering = $item->getNextOrder('catid = '.$catid_new);

    // Make sure the record is valid
    if(!$item->check())
    {
      JError::raiseWarning($item->getError());

      return false;
    }

    // Store the entry to the database
    if(!$item->store())
    {
      JError::raiseWarning($item->getError());

      return false;
    }

    return true;
  }

  /**
   * Method to publish resp. unpublish an image
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.7
   */
  public function publish()
  {
    $row = $this->getTable('joomgalleryimages');

    $row->load($this->_id);

    // Check whether we are allowed to edit the state of this image
    if(!$this->_user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$row->id))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_IMAGE_MSG_EDITSTATE_NOT_PERMITTED'));

      return false;
    }

    // Remember old state for check at the end whether the change was successful
    $published = $row->published;

    $row->published = 1 - $row->published;

    if(!$row->check())
    {
      $this->setError($row->getError());
      return false;
    }

    if(!$row->store())
    {
      $this->setError($row->getError());
    }

    // If publishing or unpublishung wasn't successful, return false
    if($row->published == $published)
    {
      return false;
    }

    return true;
  }
}
