<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/image.php $
// $Id: image.php 4362 2014-02-24 19:09:23Z erftralle $
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
 * Image model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelImage extends JoomGalleryModel
{
  /**
   * Image ID
   *
   * @var int
   */
  protected $_id;

  /**
   * Image data object
   *
   * @var object
   */
  protected $_data;

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
   * Method to set the image identifier
   *
   * @param   int   $id The image ID
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set ID and wipe data
    $this->_id    = $id;
    $this->_data  = null;
  }

  /**
   * Retrieves the image data
   *
   * @return  object  Image data object
   * @since   1.5.5
   */
  public function getData()
  {
    $row = $this->getTable('joomgalleryimages');
    $row->load($this->_id);

    if(!$this->_id)
    {
      $row->imgtitle      = $this->_mainframe->getUserStateFromRequest('joom.image.imgtitle',       'imgtitle');
      $row->imgtext       = $this->_mainframe->getUserStateFromRequest('joom.image.imgtext',        'imgtext');
      $row->imgauthor     = $this->_mainframe->getUserStateFromRequest('joom.image.imgauthor',      'imgauthor');
      $row->owner         = $this->_mainframe->getUserStateFromRequest('joom.image.owner',          'owner');
      $row->metadesc      = $this->_mainframe->getUserStateFromRequest('joom.image.metadesc',       'metadesc');
      $row->metakey       = $this->_mainframe->getUserStateFromRequest('joom.image.metakey',        'metakey');
      $row->published     = $this->_mainframe->getUserStateFromRequest('joom.image.published',      'published', 1, 'int');
      $row->imgfilename   = $this->_mainframe->getUserStateFromRequest('joom.image.imgfilename',    'imgfilename');
      $row->imgthumbname  = $this->_mainframe->getUserStateFromRequest('joom.image.imgthumbname',   'imgthumbname');
      $row->catid         = $this->_mainframe->getUserStateFromRequest('joom.image.catid',          'catid', 0, 'int');
      $row->access        = $this->_mainframe->getUserStateFromRequest('joom.image.access',         'access', 1, 'int');
      // Source category for original and detail picture
      $row->detail_catid  = $this->_mainframe->getUserStateFromRequest('joom.image.detail_catid',   'detail_catid', 0, 'int');
      if(!$row->detail_catid)
      {
        $row->imgfilename = '';
      }
      // Source category for thumbnail
      $row->thumb_catid   = $this->_mainframe->getUserStateFromRequest('joom.image.thumb_catid',    'thumb_catid', 0, 'int');
      if(!$row->thumb_catid)
      {
        $row->imgthumbname = '';
      }
      $row->copy_original = $this->_mainframe->getUserStateFromRequest('joom.image.copy_original',  'copy_original', 0, 'int');
    }

    $this->_mainframe->triggerEvent('onContentPrepareData', array(_JOOM_OPTION.'.image', $row));

    $this->_data = $row;

    return $this->_data;
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
   * @param   array Holds the data of the image (if available)
   * @return  mixed A JForm object on success, false on failure
   * @since   2.0
   */
  public function getForm($data = array())
  {
    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
    JForm::addRulePath(JPATH_COMPONENT.'/models/rules');

    $form = JForm::getInstance(_JOOM_OPTION.'.image', 'image');
    if(empty($form))
    {
      return false;
    }

    // Allow plugins to preprocess the form
    $this->preprocessForm($form, $data);

    if(!$this->canEditState($data))
    {
      // Disable fields for display
      $form->setFieldAttribute('published', 'disabled', 'true');
      $form->setFieldAttribute('approved', 'disabled', 'true');
      $form->setFieldAttribute('hidden', 'disabled', 'true');

      // Unset the data of fields which we aren't allowed to change
      $form->setFieldAttribute('ordering', 'filter', 'unset');
      $form->setFieldAttribute('published', 'filter', 'unset');
      $form->setFieldAttribute('approved', 'filter', 'unset');
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
   * Method to store an image
   *
   * @param   array $data   The data of the image to store, if null we will use the data of the current request
   * @param   array $files  Image files to upload, if null we will use the data of the current request
   * @param   array $params Additional parameters of the image, if null we will use the data of the current request
   * @return  int   The image ID on success, boolean false otherwise
   * @since   1.5.5
   */
  public function store($data = null, $files = null, $params = null)
  {
    $row = $this->getTable('joomgalleryimages');

    $validate = true;
    if(is_null($data))
    {
      $data = JRequest::get('post', 2);
    }
    else
    {
      // No validation in case of e.g. 'editimages' view
      $validate = false;
    }
    if(is_null($params))
    {
      $params = JRequest::getVar('params', array(), 'post', 'array');
    }

    // Check for validation errors
    if($validate)
    {
      $form = $this->getForm($data);
      $data = $this->_validate($form, $data);
      if($data === false)
      {
        return false;
      }
    }
    else
    {
      // Sanitize image description here because JForm didn't take care of it above
      if(isset($data['imgtext']))
      {
        $data['imgtext'] = JComponentHelper::filterText($data['imgtext']);
      }
    }

    // Check whether it is a new image
    if($id = intval($data['cid']))
    {
      $isNew = false;

      // Read image from database
      $row->load($id);

      // Check whether we are allowed to edit it
      $asset = _JOOM_OPTION.'.image.'.$id;
      if(!$this->_user->authorise('core.edit', $asset) && (!$this->_user->authorise('core.edit.own', $asset) || !$row->owner || $row->owner != $this->_user->get('id')))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_EDIT_IMAGE'));

        return false;
      }

      // Read old category ID
      $catid_old  = $row->catid;
    }
    else
    {
      $isNew = true;
    }

    // Bind the form fields to the image table
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

    // Load category information for permission checks
    $query = $this->_db->getQuery(true)
          ->select('cid, owner')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('cid = '.$row->catid);
    $this->_db->setQuery($query);
    $category = $this->_db->loadObject();

    if($isNew)
    {
      // Check whether we are allowed to create the image in the selected category
      $asset = _JOOM_OPTION.'.category.'.$row->catid;
      if(   !$this->_user->authorise('joom.upload', $asset)
        &&  (     !$this->_user->authorise('joom.upload.inown', $asset)
              ||  !$category->owner
              ||  $category->owner != $this->_user->get('id')
            )
        )
      {
        $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_TO_CREATE_IMAGE'));

        return false;
      }

      // Approve image
      $row->approved = 1;

      // Set date of image
      $date = JFactory::getDate();
      $row->imgdate = $date->toSQL();

      // Make sure the record is valid
      if(!$row->check())
      {
        $this->setError($row->getError());

        return false;
      }

      // Category path for destination category
      $catpath        = JoomHelper::getCatPath($row->catid);
      // Source path for original and detail image
      $detail_catpath = JoomHelper::getCatPath($data['detail_catid']);
      // Source path for thumbnail
      $thumb_catpath  = JoomHelper::getCatPath($data['thumb_catid']);

      // Make sure the record is valid
      if(!$row->check())
      {
        $this->setError($row->getError());

        return false;
      }

      // Copy the image files, the row will be stored, too
      if(!$this->_newImage($row, $catpath, $detail_catpath, $thumb_catpath, $data['copy_original']))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_IMGMAN_MSG_ERROR_CREATING_NEW_IMAGES'));

        return false;
      }

      // Successfully stored new image
      $row->reorder('catid = '.$row->catid);

      $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.image', &$row, true));

      return $row->id;
    }

    // Get new image files
    if(is_null($files))
    {
      $files = JRequest::getVar('files', '', 'files');
    }

    // Clear votes if 'clearvotes' is checked
    if(isset($data['clearvotes']) && $data['clearvotes'])
    {
      $row->imgvotes    = 0;
      $row->imgvotesum  = 0;
      // Delete votes for image
      $query = $this->_db->getQuery(true)
            ->delete()
            ->from(_JOOM_TABLE_VOTES)
            ->where('picid = '.$row->id);

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($row->getError());

        return false;
      }
    }

    // Clear hits if 'clearhits' is checked
    if(isset($data['clearhits']) && $data['clearhits'])
    {
      $row->hits = 0;
    }

    // Clear downloads if 'cleardownloads' is checked
    if(isset($data['cleardownloads']) && $data['cleardownloads'])
    {
      $row->downloads = 0;
    }

    // Upload and handle new image files
    $types = array('thumb', 'img', 'orig');
    foreach($types as $type)
    {
      if(isset($files['tmp_name']) && isset($files['tmp_name'][$type]) && $files['tmp_name'][$type])
      {
        jimport('joomla.filesystem.file');

        // Possibly the file name has to be changed because of another image format
        $temp_filename = $files['name'][$type];
        $columnname = 'imgfilename';
        if($type == 'thumb')
        {
          $columnname = 'imgthumbname';
        }
        $filename = $row->$columnname;
        $new_ext = JFile::getExt($temp_filename);
        $old_ext = JFile::getExt($filename);
        if($new_ext != $old_ext)
        {
          $row->$columnname = substr_replace($row->$columnname, '.'.$new_ext, - (strlen($old_ext) + 1));
        }

        // Upload the file
        $file = $this->_ambit->getImg($type.'_path', $row);
        //JFile::delete($file);
        if(!JFile::upload($files['tmp_name'][$type], $file))
        {
          JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_UPLOAD_ERROR_UPLOADING', $this->_ambit->getImg($type.'_path', $row)));

          // Revert database entry
          $row->$columnname = $filename;
        }
        // Resize image
        $debugoutput = '';
        switch($type)
        {
          case 'thumb':
            $return = JoomFile::resizeImage($debugoutput,
                                            $file,
                                            $file,
                                            $this->_config->get('jg_useforresizedirection'),
                                            $this->_config->get('jg_thumbwidth'),
                                            $this->_config->get('jg_thumbheight'),
                                            $this->_config->get('jg_thumbcreation'),
                                            $this->_config->get('jg_thumbquality')
                                            );
            break;
          case 'img':
            $return = JoomFile::resizeImage($debugoutput,
                                            $file,
                                            $file,
                                            false,
                                            $this->_config->get('jg_maxwidth'),
                                            false,
                                            $this->_config->get('jg_thumbcreation'),
                                            $this->_config->get('jg_picturequality'),
                                            true
                                            );
            break;
          default:
            break;
        }
      }
    }

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

    // Move the image if necessary (the data is stored in function moveImage because
    // we have ensured that the old and new category ID are different from each other)
    if($move && !$this->moveImage($row, $row->catid, $catid_old))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COULD_NOT_MOVE_IMAGE'), 'notice');

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
    if(isset($catid_old) AND $catid_old != $row->catid)
    {
      $row->reorder('catid = '.$catid_old);
    }

    $this->_mainframe->triggerEvent('onContentAfterSave', array(_JOOM_OPTION.'.image'.(!$validate ? '.batch' : ''), &$row, false));

    return $row->id;
  }

  /**
   * Returns the ID of the image which belongs to the given file name
   *
   * @param   string  $file   The file name to look for
   * @param   int     $catid  Optional category ID for setting an additional limit
   * @param   thumb   $thumb  True if the given file name is the name of a thumbnail, false otherwise
   * @return  int     The ID of the image
   * @since   2.0
   */
  public function getIdByFilename($file, $catid = 0, $thumb = false)
  {
    $query = $this->_db->getQuery(true)
          ->select('id')
          ->from(_JOOM_TABLE_IMAGES);

    if($catid)
    {
      $query->where('catid = '.$catid);
    }

    if($thumb)
    {
      $query->where('imgfilename = '.$this->_db->quote($file));
    }
    else
    {
      $query->where('imgthumbname = '.$this->_db->quote($file));
    }

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Method to copy/move the files for a new image.
   * This method also creates new file names for the images and stores the row.
   *
   * @param   object  $row            Holds the data of the new image.
   * @param   string  $catpath        The catpath of the new image
   * @param   string  $detail_catpath The catpath of the detail image to copy
   * @param   string  $thumb_catpath  The catpath of the thumbnail to copy
   * @param   int     $copy_original  Indicates whether the original image should be copied, too
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _newImage($row, $catpath, $detail_catpath, $thumb_catpath, $copy_original)
  {
    jimport('joomla.filesystem.file');

    // Create new file names
    $date       = date('Ymd');
    $filename   = JoomFile::fixFilename($row->imgtitle);
    $img_tag    = JFile::getExt($row->imgfilename);
    $thumb_tag  = JFile::getExt($row->imgthumbname);

    $src_imgfilename  = $row->imgfilename;
    $src_imgthumbname = $row->imgthumbname;

    do
    {
      mt_srand();
      $randomnumber = mt_rand(1000000000, 2099999999);

      // New filename
      $newfilename = $filename.'_'.$date.'_'.$randomnumber;
    }
    while(    JFile::exists($this->_ambit->getImg('orig_path', $newfilename.'.'.$img_tag, null, $row->catid))
           || JFile::exists($this->_ambit->getImg('img_path', $newfilename.'.'.$img_tag, null, $row->catid))
           || JFile::exists($this->_ambit->getImg('thumb_path', $newfilename.'.'.$thumb_tag, null, $row->catid))
         );

    $row->imgfilename   = $newfilename.'.'.$img_tag;
    $row->imgthumbname  = $newfilename.'.'.$thumb_tag;

    // If the destination thumbnail directory doesn't exist
    if(!JFolder::exists($this->_ambit->get('thumb_path').$catpath))
    {
      // Raise an error message and abort
      $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_FOLDER_NOT_EXISTENT', $this->_ambit->get('thumb_path').$catpath), 'error');

      return false;
    }

    // Try to copy the thumbnail from source to destination
    $result = JFile::copy(JPath::clean($this->_ambit->get('thumb_path').$thumb_catpath.$src_imgthumbname),
                          JPath::clean($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname));

    if(!$result)
    {
      // Raise an error message and abort
      $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_ERROR_COPYING_THUMB', $this->_ambit->get('thumb_path').$catpath.$row->imgthumbname), 'error');

      return false;
    }

    // Same procedure like thumbnail for copying the detail image
    // In case of error delete the copied thumbnail from destination
    if(!JFolder::exists($this->_ambit->get('img_path').$catpath))
    {
      JFile::delete($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname);

      $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_FOLDER_NOT_EXISTENT', $this->_ambit->get('img_path').$catpath), 'error');

      return false;
    }

    $result = JFile::copy(JPath::clean($this->_ambit->get('img_path').$detail_catpath.$src_imgfilename),
                          JPath::clean($this->_ambit->get('img_path').$catpath.$row->imgfilename));
    if(!$result)
    {
      JFile::delete($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname);

      $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_ERROR_COPYING_IMAGE', $this->_ambit->get('img_path').$catpath.$row->imgfilename), 'error');

      return false;
    }

    // If setted to create an original image do the following action,
    // otherwise do not copy the image
    $orig_created = false;
    if($copy_original)
    {
      if(JFile::exists($this->_ambit->get('orig_path').$detail_catpath.$src_imgfilename))
      {
        // Use the path to original images from now on
        $imagepath = $this->_ambit->get('orig_path').$detail_catpath;
      }
      else
      {
        // Image doesn't exist
        // Use the path to detail images from now and use detail image as original image
        $imagepath = $this->_ambit->get('img_path').$detail_catpath;
      }

      if(!JFolder::exists($this->_ambit->get('orig_path').$catpath))
      {
        // Directory doesn't exist, so delete the thumbnail and the detail image
        JFile::delete($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname);
        JFile::delete($this->_ambit->get('img_path').$catpath.$row->imgfilename);

        // Raise an error message and abort
        $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_FOLDER_NOT_EXISTENT', $this->_ambit->get('orig_path').$catpath), 'error');

        return false;
      }

      // Destination directory exists, so try to copy the image from source to destination
      $result = JFile::copy(JPath::clean($imagepath.$src_imgfilename),
                            JPath::clean($this->_ambit->get('orig_path').$catpath.$row->imgfilename));

      if(!$result)
      {
        // Delete thumbnail and detail image
        JFile::delete($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname);
        JFile::delete($this->_ambit->get('img_path').$catpath.$row->imgfilename);

        $this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_ERROR_COPYING_ORIGINAL%s', $this->_ambit->get('orig_path').$catpath.$row->imgfilename), 'error');

        return false;
      }

      $orig_created = true;
    }

    // Store the record
    // If not succesful raise an error messages and abort
    if(!$row->store())
    {
      // Delete the thumbnail, detail image and original image
      JFile::delete($this->_ambit->get('thumb_path').$catpath.$row->imgthumbname);
      JFile::delete($this->_ambit->get('img_path').$catpath.$row->imgfilename);

      if($orig_created)
      {
        JFile::delete($this->_ambit->get('orig_path').$catpath.$row->imgfilename);
      }

      $this->_mainframe->enqueueMessage($row->getError(), 'error');

      return false;
    }

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
  public function moveImage($item, $catid_new, $catid_old = 0)
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

    // If the image is already in the correct category return false, no message will be set
    if($catid_new == $catid_old)
    {
      return false;
    }

    $catpath_old  = JoomHelper::getCatPath($catid_old);
    $catpath_new  = JoomHelper::getCatPath($catid_new);

    // Database query to check if there are other images which this
    // thumbnail is assigned to and how many of them exist
    $query = $this->_db->getQuery(true)
           ->select('COUNT(id)')
           ->from(_JOOM_TABLE_IMAGES)
           ->where("imgthumbname  = '".$item->imgthumbname."'")
           ->where('id != '.$item->id)
           ->where('catid = '.$catid_old);

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
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_COMMON_DEST_THUMB_ALREADY_EXISTS'));

      if($thumb_count && JFile::exists($thumb_source))
      {
        JFile::delete($thumb_source);
      }
    }
    else
    {
      if(!JFile::exists($thumb_source))
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_COMMON_SOURCE_THUMB_NOT_EXISTS', $thumb_source));

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
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_COULD_NOT_MOVE_THUMB', JPath::clean($thumb_dest)));

          return false;
        }

        // Set control variable according to the successful move/copy procedure
        $thumb_created = true;
      }
    }

    // Database query to check if there are other images which this
    // file is assigned to and how many of them exist
    $query->clear('where')
          ->where("imgfilename = '".$item->imgfilename."'")
          ->where('id != '.$item->id)
          ->where('catid = '.$catid_old);

    $this->_db->setQuery($query);
    $img_count    = $this->_db->loadResult();

    // Same procedure with the detail image
    // In case of error roll previous copy/move procedure back
    $img_created  = false;
    $img_source   = $this->_ambit->get('img_path').$catpath_old.$item->imgfilename;
    $img_dest     = $this->_ambit->get('img_path').$catpath_new.$item->imgfilename;
    if(JFile::exists($img_dest))
    {
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_COMMON_DEST_IMG_ALREADY_EXISTS'));

      if($img_count && JFile::exists($img_source))
      {
        JFile::delete($img_source);
      }
    }
    else
    {
      if(!JFile::exists($img_source))
      {
        JError::raiseWarning(500, JText::sprintf('COM_JOOMGALLERY_COMMON_SOURCE_IMG_NOT_EXISTS', $img_source));

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

          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_COULD_NOT_MOVE_IMG', JPath::clean($img_dest)));

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
      JError::raiseNotice(0, JText::_('COM_JOOMGALLERY_COMMON_DEST_ORIG_ALREADY_EXISTS'));

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

          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_COULD_NOT_MOVE_ORIGINAL', JPath::clean($orig_dest)));

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
   * Method to validate the form data
   *
   * @param   object  $form   The form to validate against
   * @param   array   $data   The data to validate
   * @return  mixed   Array of filtered data if valid, false otherwise
   * @since   2.0
   */
  protected function _validate($form, $data)
  {
    if(isset($data['cid']) && (int) $data['cid'] == 0 )
    {
      // Add some validation and required attributes for the case of
      // adding a new image
      $form->setFieldAttribute('detail_catid', 'required', true);
      $form->setFieldAttribute('detail_catid', 'validate', 'joompositivenumeric');
      $form->setFieldAttribute('thumb_catid',  'required', true);
      $form->setFieldAttribute('thumb_catid',  'validate', 'joompositivenumeric');
      $form->setFieldAttribute('imgfilename',  'required', true);
      $form->setFieldAttribute('imgthumbname', 'required', true);
    }

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
}