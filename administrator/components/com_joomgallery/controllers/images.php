<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/images.php $
// $Id: images.php 4405 2014-07-02 07:13:31Z chraneco $
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
 * JoomGallery Images Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerImages extends JoomGalleryController
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

    // Set view
    JRequest::setVar('view', JRequest::getCmd('view', 'images'));

    // Register tasks
    $this->registerTask('new',              'edit');
    $this->registerTask('apply',            'save');
    $this->registerTask('save2new',         'save');
    $this->registerTask('save2copy',        'save');
    $this->registerTask('unpublish',        'publish');
    $this->registerTask('unfeature',        'feature');
    $this->registerTask('reject',           'approve');
    $this->registerTask('accesspublic',     'access');
    $this->registerTask('accessregistered', 'access');
    $this->registerTask('accessspecial',    'access');
    $this->registerTask('orderup',          'order');
    $this->registerTask('orderdown',        'order');
  }

  /**
   * Publishes or unpublishes one or more images
   *
   * @return  void
   * @since   1.5.5
   */
  public function publish()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = (int)($task == 'publish');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      $this->redirect();
    }

    $unchanged_images = 0;
    foreach($cid as $key => $id)
    {
      // Prune images for which we aren't allowed to change the state
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$id))
      {
        unset($cid[$key]);
        $unchanged_images++;
      }
    }

    if($unchanged_images)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_images));
    }

    $model = $this->getModel('images');
    if($count = $model->publish($cid, $publish))
    {
      if($count != 1)
      {
        $msg = JText::sprintf($publish ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_PUBLISHED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_UNPUBLISHED', $count);
      }
      else
      {
        $msg = JText::_($publish ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_PUBLISHED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_UNPUBLISHED');
      }
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
    }
    else
    {
      $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_ERROR_PUBLISHING_UNPUBLISHING');
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'error');
    }
  }

  /**
   * Features or unfeatures one or more images
   *
   * @return  void
   * @since   3.3
   */
  public function feature()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $feature  = (int)($task == 'feature');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      $this->redirect();
    }

    $unchanged_images = 0;
    foreach($cid as $key => $id)
    {
      // Prune images for which we aren't allowed to change the state
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$id))
      {
        unset($cid[$key]);
        $unchanged_images++;
      }
    }

    if($unchanged_images)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_images));
    }

    $model = $this->getModel('images');
    if($count = $model->publish($cid, $feature, 'feature'))
    {
      if($count != 1)
      {
        $msg = JText::sprintf($feature ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_FEATURED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_UNFEATURED', $count);
      }
      else
      {
        $msg = JText::_($feature ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_FEATURED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_UNFEATURED');
      }
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
    }
    else
    {
      $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_ERROR_FEATURING_UNFEATURING');
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'error');
    }
  }

  /**
   * Approves or rejects one or more images
   *
   * @return  void
   * @since   1.5.5
   */
  public function approve()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = -1;
    if($task == 'approve')
    {
      $publish = 1;
    }

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      $this->redirect();
    }

    $unchanged_images = 0;
    foreach($cid as $key => $id)
    {
      // Prune images for which we aren't allowed to change the state
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$id))
      {
        unset($cid[$key]);
        $unchanged_images++;
      }
    }

    if($unchanged_images)
    {
      $this->_mainframe->enqueueMessage(JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_images), 'notice');
    }

    $model = $this->getModel('images');
    if($count = $model->publish($cid, $publish, 'approve'))
    {
      if($count != 1)
      {
        $msg = JText::sprintf($publish == 1 ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_APPROVED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_REJECTED', $count);
      }
      else
      {
        $msg = JText::_($publish == 1 ? 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_APPROVED' : 'COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_REJECTED');

        // Send message about rejection if a message was specified
        if($task == 'reject' && $message = JRequest::getString('message'))
        {
          $model->sendRejectionMessage($cid[0], $message);
        }
      }

      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
    }
    else
    {
      $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_ERROR_APPROVING_REJECTING');
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'error');
    }
  }

  /**
   * Removes one or more images
   *
   * @return  void
   * @since   1.5.5
   */
  public function remove()
  {
    $model = $this->getModel('images');

    $cid  = JRequest::getVar('cid', array(), 'post', 'array');
    $unaffected_images = 0;
    foreach($cid as $key => $id)
    {
      // Prune images which we aren't allowed to delete
      if(!JFactory::getUser()->authorise('core.delete', _JOOM_OPTION.'.image.'.$id))
      {
        unset($cid[$key]);
        $unaffected_images++;
      }
    }

    JRequest::setVar('cid', $cid);

    if($unaffected_images)
    {
      JLog::add(JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_DELETE_NOT_PERMITTED', $unaffected_images), JLog::WARNING, 'jerror');
    }

    if(!count($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl());

      return;
    }

    try
    {
      $count = $model->delete($cid);

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_DELETED', $count);
      }

      // Some messages are enqueued by the model
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
    }
    catch(RuntimeException $e)
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), $e->getMessage(), 'error');
    }
  }

  /**
   * Displays the edit form for one or multiple images
   *
   * @return  void
   * @since   1.5.5
   */
  public function edit()
  {
    $cid = JRequest::getVar('cid', array(), '', 'array');
    if(count($cid) <= 1)
    {
      if(count($cid))
      {
        $exploded = explode(',', $cid[0]);
        if(count($exploded) > 1)
        {
          JRequest::setVar('cid',   $exploded);
          JRequest::setVar('view',  'editimages');
        }
        else
        {
          JRequest::setVar('view',  'image');
        }
      }
      else
      {
        JRequest::setVar('view',  'image');
      }
    }
    else
    {
      JRequest::setVar('view',  'editimages');
    }

    JRequest::setVar('layout',  'form');
    JRequest::setVar('hidemainmenu', 1);

    parent::display();
  }

  /**
   * Saves one or more images
   *
   * @return  void
   * @since   1.5.5
   */
  public function save()
  {
    $model = $this->getModel('image');

    $data = JRequest::get('post', 2);

    // Editing more than one image?
    if(isset($data['cids']))
    {
      // Selected images
      $cids_string  = $data['cids'];
      $cids         = explode(',', $cids_string);

      // We need selected fields
      if(!isset($data['change']))
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(null, implode(',', $cids)), JText::_('COM_JOOMGALLERY_IMGMAN_MSG_NO_BOXES_FOR_EDITING_CHECKED'), 'notice');
        return;
      }

      // Selected fields to edit
      $change = $data['change'];

      // Get start counter for consecutive numbering of image title
      $imgtitlestartcounter = (int) $data['imgtitlestartcounter'];
      $imgname_separator    = JText::_('COM_JOOMGALLERY_IMGMAN_IMAGENAME_SEPARATOR');

      $changeable_fields  = array('imgtitle', 'catid', 'access', 'imgtext', 'owner', 'imgauthor', 'clearvotes',
                                  'cleardownloads', 'clearhits', 'published', 'approved', 'hidden', 'ordering', 'metakey', 'metadesc');
      $state_fields       = array('published', 'approved', 'hidden', 'ordering');

      // Delete all unselected and unchangeable fields
      foreach($data as $key => $value)
      {
        if(!in_array($key, $change) || !in_array($key, $changeable_fields))
        {
          unset($data[$key]);
        }
      }

      // Save each image
      $return   = array();
      $count    = 0;
      $imgtitle = isset($data['imgtitle']) ? $data['imgtitle'] : '';
      foreach($cids as $cid)
      {
        // Add consecutive number in image title
        if($imgtitlestartcounter > 0 && !empty($imgtitle))
        {
          $data['imgtitle'] = sprintf('%s'.$imgname_separator.'%d', $imgtitle, $imgtitlestartcounter++);
        }

        $cloned_data = $data;

        // Delete state fields if editing the state is not allowed
        if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$cid))
        {
          foreach($state_fields as $field)
          {
            if(isset($cloned_data[$field]))
            {
              unset($cloned_data[$field]);
            }
          }
        }

        // Check whether images shall be moved or copied
        if(isset($cloned_data['catid']) && JRequest::getCmd('movecopy') == 'copy')
        {
          $orig_img = (array) $this->_ambit->getImgObject($cid);

          $orig_img['thumb_catid'] = $orig_img['catid'];
          $orig_img['detail_catid'] = $orig_img['catid'];

          $orig_img['alias'] = '';

          $orig_img['copy_original'] = false;
          if(is_file($this->_ambit->getImg('orig_path', (object) $orig_img)))
          {
            $orig_img['copy_original'] = true;
          }

          foreach($cloned_data as $key => $value)
          {
            $orig_img[$key] = $value;
          }

          // Set image ID back so that the model creates new images
          $orig_img['id'] = 0;
          $orig_img['cid'] = 0;

          $cloned_data = $orig_img;
        }
        else
        {
          $cloned_data['cid']  = $cid;
        }

        if($model->store($cloned_data))
        {
          $count++;
        }
        else
        {
          JError::raiseWarning(100, $model->getError());
        }
      }

      if($count)
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(null, implode(',', $cids)), JText::plural('COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_SAVED', $count));
      }
      else
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(), JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_ERROR_SAVING_IMAGES'), 'error');
      }

      return;
    }

    if(JRequest::getCmd('task') == 'save2copy')
    {
      // Reset the ID and then treat the request as for apply.
      // This way a new image will be created and after that
      // it will be displayed right away
      JRequest::setVar('cid', 0);
      JRequest::setVar('task', 'apply');
    }

    // Editing only one image
    if($cid = $model->store())
    {
      if(JRequest::getCmd('task') == 'save2new')
      {
        // Reset the ID after storing so that we
        // will be redirected to an empty form
        JRequest::setVar('task', 'apply');
        $cid = 0;
      }

      $msg  = JText::_('COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_SAVED');
      $this->setRedirect($this->_ambit->getRedirectUrl(null, $cid), $msg);
    }
    else
    {
      $msg  = $model->getError();
      $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'error');
    }
  }

  /**
   * Moves the order of an image
   *
   * @return  void
   * @since   1.5.5
   */
  public function order()
  {
    $cid = JRequest::getVar('cid', array(), 'post', 'array');

    // Direction
    $dir  = 1;
    $task = JRequest::getCmd('task');
    if($task == 'orderup')
    {
      $dir = -1;
    }

    if(isset($cid[0]))
    {
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$cid[0]))
      {
        $msg = JText::_('COM_JOOMGALLERY_IMGMAN_ERROR_EDITSTATE_NOT_PERMITTED');
        $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'notice');

        return;
      }

      $row = JTable::getInstance('joomgalleryimages', 'Table');
      $row->load((int)$cid[0]);
      $row->move($dir, 'catid = '.$row->catid);
      $row->reorder('catid = '.$row->catid);
    }

    $this->setRedirect($this->_ambit->getRedirectUrl());
  }

  /**
   * Saves the order of the images
   *
   * @return  void
   * @since   1.5.5
   */
  public function saveOrder()
  {
    $cid    = JRequest::getVar('cid', array(), 'post', 'array');
    $order  = JRequest::getVar('order', array (0), 'post', 'array');
    $user   = JFactory::getUser();

    // Create and load the images table object
    $row = JTable::getInstance('joomgalleryimages', 'Table');

    // Update the ordering for items in the cid array
    $unchanged_images = 0;
    for($i = 0; $i < count($cid); $i ++)
    {
      if(!$user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$cid))
      {
        $unchanged_images++;
        continue;
      }

      $row->load((int)$cid[$i]);
      if($row->ordering != $order[$i])
      {
        $row->ordering = $order[$i];
        if(!$row->store())
        {
          JError::raiseError(500, $this->_db->getErrorMsg());
          return false;
        }
      }
    }

    if($unchanged_images)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_images));
    }

    $row->reorderAll();

    $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_NEW_ORDERING_SAVED');
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
  }

  /**
   * Displays the move form
   *
   * @return  void
   * @since   1.5.5
   */
  public function showmove()
  {
    JRequest::setVar('view',    'move');
    JRequest::setVar('hidemainmenu', 1);

    parent::display();
  }

  /**
   * Moves images to another category
   *
   * @return  void
   * @since   1.5.5
   */
  public function move()
  {
    $cid    = JRequest::getVar('cid', array(), 'post', 'array');
    $catid  = JRequest::getInt('catid');

    if(!count($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'), 'notice');
      $this->redirect();
    }
    if(!$catid)
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_IMGMAN_MSG_NO_CATEGORY_SELECTED'), 'notice');
      $this->redirect();
    }

    $user = JFactory::getUser();

    $count = 0;
    $unaffected_images = 0;
    $model = $this->getModel('image');
    foreach($cid as $id)
    {
      if(!$user->authorise('joom.upload', _JOOM_OPTION.'.category.'.$catid))
      {
        $unaffected_images++;
        continue;
      }

      if($model->moveImage($id, $catid))
      {
        $count++;
      }
    }

    if($unaffected_images)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_MOVE_NOT_PERMITTED', $unaffected_images));
    }

    if($count)
    {
      if($count == 1)
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_MOVED'));
      }
      else
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(), JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_IMAGES_MOVED', $count));
      }
    }
    else
    {
      $this->setRedirect($this->_ambit->getRedirectUrl());
    }
  }

  /**
   * Recreates thumbnails and detail images
   *
   * @return  void
   * @since   1.5.5
   */
  public function recreate()
  {
    $model  = $this->getModel('images');
    $count  = $model->recreate();
    if(!$count[0])
    {
      $type = 'error';
      $msg  = $model->getError();
    }
    else
    {
      $type = 'message';
      if($count[0] == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_THUMBNAIL_RECREATED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_THUMBNAILS_RECREATED', $count[0]);
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
      }
    }

    // Some messages are enqueued by the model
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Resets hits of an image
   *
   * @return  void
   * @since   1.5.5
   */
  public function resetHits()
  {
    $id = JRequest::getInt('cid');

    if(!JFactory::getUser()->authorise('core.edit', _JOOM_OPTION.'.image.'.$id))
    {
      $msg = JText::_('COM_JOOMGALLERY_IMGMAN_ERROR_EDIT_NOT_PERMITTED');
      $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg, 'notice');

      return;
    }

    // Instantiate and load an image table
    $row = JTable::getInstance('joomgalleryimages', 'Table');
    $row->load($id);
    $row->hits = 0;
    $row->store();

    JRequest::setVar('task', 'apply');
    $msg = JText::_('COM_JOOMGALLERY_IMGMAN_MSG_HITS_RESETED');
    $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg);
  }

  /**
   * Resets downloads of an image
   *
   * @return  void
   * @since   3.1
   */
  public function resetDownloads()
  {
    $id = JRequest::getInt('cid');

    if(!JFactory::getUser()->authorise('core.edit', _JOOM_OPTION.'.image.'.$id))
    {
      $msg = JText::_('COM_JOOMGALLERY_IMGMAN_ERROR_EDIT_NOT_PERMITTED');
      $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg, 'notice');

      return;
    }

    // Instantiate and load an image table
    $row = JTable::getInstance('joomgalleryimages', 'Table');
    $row->load($id);
    $row->downloads = 0;
    $row->store();

    JRequest::setVar('task', 'apply');
    $msg = JText::_('COM_JOOMGALLERY_IMGMAN_MSG_DOWNLOADS_RESETED');
    $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg);
  }

  /**
   * Resets votes of an image
   *
   * @return  void
   * @since   1.5.5
   */
  public function resetVotes()
  {
    $id = JRequest::getInt('cid');

    if(!JFactory::getUser()->authorise('core.edit', _JOOM_OPTION.'.image.'.$id))
    {
      $msg = JText::_('COM_JOOMGALLERY_IMGMAN_ERROR_EDIT_NOT_PERMITTED');
      $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg, 'notice');

      return;
    }

    // Instantiate and load an image table
    $row = JTable::getInstance('joomgalleryimages', 'Table');
    $row->load($id);

    // Delete votes for image
    $row->imgvotes = 0;
    $row->imgvotesum = 0;
    $query = $this->_db->getQuery(true)
          ->delete(_JOOM_TABLE_VOTES)
          ->where('picid = '.$row->id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      JFactory::getApplication()->enqueueMessagge($this->_db->getErrorMsg(), 'error');

      return false;
    }
    $row->store();

    JRequest::setVar('task', 'apply');
    $msg = JText::_('COM_JOOMGALLERY_IMGMAN_MSG_VOTES_RESETED');
    $this->setRedirect($this->_ambit->getRedirectUrl(null, $id), $msg);
  }

  /**
   * Cancel creating, editing or moving images
   *
   * @return  void
   * @since   1.5.5
   */
  public function cancel()
  {
    $this->setRedirect($this->_ambit->getRedirectUrl());
  }
}