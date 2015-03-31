<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/categories.php $
// $Id: categories.php 4405 2014-07-02 07:13:31Z chraneco $
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
 * JoomGallery Categories Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerCategories extends JoomGalleryController
{
  /**
   * Constructor
   *
   * @access  protected
   * @return  void
   * @since   1.5.5
   */
  function __construct()
  {
    parent::__construct();

    // Set view
    JRequest::setVar('view', 'categories');

    // Register tasks
    $this->registerTask('new',              'edit');
    $this->registerTask('apply',            'save');
    $this->registerTask('save2new',         'save');
    $this->registerTask('save2copy',        'save');
    $this->registerTask('unpublish',        'publish');
    #$this->registerTask('reject',          'approve');
    $this->registerTask('accesspublic',     'access');
    $this->registerTask('accessregistered', 'access');
    $this->registerTask('accessspecial',    'access');
    $this->registerTask('orderup',          'order');
    $this->registerTask('orderdown',        'order');
  }

  /**
   * Publishes or unpublishes one or more categories
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function publish()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = (int)($task == 'publish');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
      $this->redirect();
    }

    $unchanged_categories = 0;
    foreach($cid as $key => $id)
    {
      // Prune categories for which we aren't allowed to change the state
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.category.'.$id))
      {
        unset($cid[$key]);
        $unchanged_categories++;
      }
    }

    if($unchanged_categories)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_CATMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_categories));
    }

    $model = $this->getModel('categories');
    if($count = $model->publish($cid, $publish))
    {
      if($count != 1)
      {
        $msg = JText::sprintf($publish ? 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_PUBLISHED' : 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_UNPUBLISHED', $count);
      }
      else
      {
        $msg = JText::_($publish ? 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_PUBLISHED' : 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_UNPUBLISHED');
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
   * Approves or rejects one or more comments
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function approve()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = ($task == 'approve');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
      $this->redirect();
    }

    $unchanged_categories = 0;
    foreach($cid as $key => $id)
    {
      // Prune categories for which we aren't allowed to change the state
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.category.'.$id))
      {
        unset($cid[$key]);
        $unchanged_categories++;
      }
    }

    if($unchanged_categories)
    {
      JError::raiseNotice(403, JText::plural('COM_JOOMGALLERY_CATMAN_ERROR_EDITSTATE_NOT_PERMITTED', $unchanged_categories));
    }

    $model = $this->getModel('categories');
    if($count = $model->publish($cid, $publish, 'approve'))
    {
      if($count != 1)
      {
        $msg = JText::sprintf($publish ? 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_APPROVED' : 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_REJECTED', $count);
      }
      else
      {
        $msg = JText::_($publish ? 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_APPROVED' : 'COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_REJECTED');
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
   * Removes one or more categories
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function remove()
  {
    $ids = JRequest::getVar('cid', array(), '', 'array');

    $model = $this->getModel('categories');
    try
    {
      $count = $model->delete($ids);

      if($count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_DELETED', $count);
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
   * Removes one or more categories even though there
   * are still images or sub-categories in them.
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function deletecompletely()
  {
    $mainframe  = JFactory::getApplication('administrator');
    $categories = $mainframe->getUserState('joom.categories.delete.categories');
    $images     = $mainframe->getUserState('joom.categories.delete.images');

    if(!$categories || !is_array($categories) || !count($categories))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'), 'notice');

      return;
    }

    require_once JPATH_COMPONENT.'/helpers/refresher.php';

    $refresher = new JoomRefresher(array('msg' => true));

    $img_count = $mainframe->getUserState('joom.categories.delete.img_count');
    if(is_null($img_count))
    {
      $img_count = 0;
    }
    $cat_count = $mainframe->getUserState('joom.categories.delete.cat_count');
    if(is_null($cat_count))
    {
      $cat_count = 0;
    }

    $error = false;

    if($images && is_array($images) && count($images))
    {
      $model  = $this->getModel('images');
      $row    = $model->getTable('joomgalleryimages');
      foreach($images as $key => $image)
      {
        // Check whether image still exists.
        // It may have been deleted before if categories were selected
        // to delete as well as their sub-categories.
        if($row->load($image))
        {
          try
          {
            $model->delete(array($image));
          }
          catch(RuntimeException $e)
          {
            JLog::add($e->getMessage(), JLog::ERROR, 'jerror');
            $error = true;
            break;
          }

          $img_count++;
        }

        unset($images[$key]);

        if(!$refresher->check())
        {
          $mainframe->setUserState('joom.categories.delete.images', $images);
          $mainframe->setUserState('joom.categories.delete.img_count', $img_count);
          $refresher->refresh();
        }
      }
    }

    $model  = $this->getModel('categories');
    $row    = $model->getTable('joomgallerycategories');

    $categories = $model->getOrderedCategories($categories, 'lft', 'DESC');

    if(!$error)
    {
      foreach($categories as $key => $category)
      {
        // Check whether category still exists.
        // It may have been deleted before if categories were selected
        // to delete as well as their sub-categories.
        if($row->load($category))
        {
          try
          {
            $model->delete(array($category));
          }
          catch(RuntimeException $e)
          {
            JLog::add($e->getMessage(), JLog::ERROR, 'jerror');
            break;
          }

          $cat_count++;
        }

        unset($categories[$key]);

        if(!$refresher->check() && count($categories))
        {
          $mainframe->setUserState('joom.categories.delete.images', $images);
          $mainframe->setUserState('joom.categories.delete.categories', $categories);
          $mainframe->setUserState('joom.categories.delete.img_count', $img_count);
          $mainframe->setUserState('joom.categories.delete.cat_count', $cat_count);
          $refresher->refresh();
        }
      }
    }

    if($img_count)
    {
      if($img_count == 1)
      {
        $msg  = JText::_('COM_JOOMGALLERY_CATMAN_MSG_IMAGE_DELETED');
      }
      else
      {
        $msg  = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_IMAGES_DELETED', $img_count);
      }
      $mainframe->enqueueMessage($msg);
    }

    if($cat_count == 1)
    {
      $msg  = JText::_('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_DELETED');
    }
    else
    {
      $msg  = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORIES_DELETED', $cat_count);
    }

    // Reset all user states of this task
    $mainframe->setUserState('joom.categories.delete', null);

    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
  }

  /**
   * Displays the edit form of a category
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function edit()
  {
    JRequest::setVar('view',    'category');
    JRequest::setVar('layout',  'form');
    JRequest::setVar('hidemainmenu', 1);

    parent::display();
  }

  /**
   * Saves a category
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function save()
  {
    $model = $this->getModel('category');

    // Check whether a redirect is requested
    $redirect = false;
    if($url = JRequest::getVar('redirect', '', '', 'base64'));
    {
      $url = base64_decode($url);
      if(JURI::isInternal($url))
      {
        $redirect = $url;
      }
    }

    if(JRequest::getCmd('task') == 'save2copy')
    {
      // Reset the ID and then treat the request as for apply.
      // This way a new category will be created and after that
      // it will be displayed right away
      JRequest::setVar('cid', 0);
      JRequest::setVar('task', 'apply');
    }

    if($cid = $model->store())
    {
      if(!$redirect)
      {
        if(JRequest::getCmd('task') == 'save2new')
        {
          // Reset the ID after storing so that we
          // will be redirected to an empty form
          $cid = 0;
          JRequest::setVar('task', 'apply');
        }
        $redirect = $this->_ambit->getRedirectUrl(null, $cid);
      }

      $msg  = JText::_('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_SAVED');
      $this->setRedirect($redirect, $msg);
    }
    else
    {
      if(!$redirect)
      {
        $redirect = $this->_ambit->getRedirectUrl();
      }

      $msg  = $model->getError();
      $this->setRedirect($redirect, $msg, 'error');
    }
  }

  /**
   * Moves the order of a category
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function order()
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
      if(!JFactory::getUser()->authorise('core.edit.state', _JOOM_OPTION.'.category.'.$cid[0]))
      {
        $msg = JText::_('COM_JOOMGALLERY_CATMAN_ERROR_EDITSTATE_NOT_PERMITTED');
        $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, 'notice');

        return;
      }

      $row = JTable::getInstance('joomgallerycategories', 'Table');
      $row->load((int)$cid[0]);
      $row->move($dir);
    }

    $this->setRedirect($this->_ambit->getRedirectUrl());
  }

  /**
   * Saves the order of the categories
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function saveOrder()
  {
    JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

    // Get the arrays from the request
    $pks            = JRequest::getVar('cid',  null,  'post',  'array');
    $order          = JRequest::getVar('order',  null, 'post', 'array');
    $originalOrder  = explode(',', JRequest::getString('original_order_values'));

    // Make sure something has changed
    if($order !== $originalOrder)
    {
      // Create and load the categories table object
      $table = JTable::getInstance('joomgallerycategories', 'Table');

      if($table->saveorder($pks, $order))
      {
        $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_NEW_ORDERING_SAVED');
        $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
      }
      else
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(), $table->getError(), 'error');
      }
    }
    else
    {
      // Nothing to reorder
      $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl(), false));
    }
  }

  /**
   * Method to run batch operations
   *
   * @return  void
   * @since   3.0
   */
  public function batch()
  {
    JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
  
    $vars = $this->input->post->get('batch', array(), 'array');
    $cid  = $this->input->post->get('cid', array(), 'array');

    $model = $this->getModel('category');

    // Attempt to run the batch operation
    if($model->batch($vars, $cid))
    {
      $this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_BATCH'));
    }
    else
    {
      $this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_FAILED', $model->getError()), 'error');
    }

    $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl(), false));
  }

  /**
   * Cancel editing or creating a category
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function cancel()
  {
    $this->setRedirect($this->_ambit->getRedirectUrl());
  }
}