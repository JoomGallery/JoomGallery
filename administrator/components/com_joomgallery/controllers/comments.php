<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/comments.php $
// $Id: comments.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Comments Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerComments extends JoomGalleryController
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
    JRequest::setVar('view', 'comments');

    // Register tasks
    $this->registerTask('unpublish',  'publish');
    $this->registerTask('reject',     'approve');
  }

  /**
   * Publishes or unpublishes one or more comments
   *
   * @return  void
   * @since   1.5.5
   */
  public function publish()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = ($task == 'publish');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMAN_MSG_NO_COMMENTS_SELECTED'));
      $this->redirect();
    }

    $model = $this->getModel('comments');
    if($count = $model->publish($cid, $publish))
    {
      if($count != 1){
        $msg = JText::sprintf($publish ? 'COM_JOOMGALLERY_COMMAN_MSG_COMMENTS_PUBLISHED' : 'COM_JOOMGALLERY_COMMAN_MSG_COMMENTS_UNPUBLISHED', $count);
      } else {
        $msg = JText::_($publish ? 'COM_JOOMGALLERY_COMMAN_MSG_COMMENT_PUBLISHED' : 'COM_JOOMGALLERY_COMMAN_MSG_COMMENT_UNPUBLISHED');
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
   * @return  void
   * @since   1.5.5
   */
  public function approve()
  {
    // Initialize variables
    $cid      = JRequest::getVar('cid', array(), 'post', 'array');
    $task     = JRequest::getCmd('task');
    $publish  = ($task == 'approve');

    if(empty($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_COMMAN_MSG_NO_COMMENTS_SELECTED'));
      $this->redirect();
    }

    $model = $this->getModel('comments');
    if($count = $model->publish($cid, $publish, 'approve'))
    {
      if($count != 1){
        $msg = JText::sprintf($publish ? 'COM_JOOMGALLERY_COMMAN_MSG_COMMENTS_APPROVED' : 'COM_JOOMGALLERY_COMMAN_MSG_COMMENTS_REJECTED', $count);
      } else {
        $msg = JText::_($publish ? 'COM_JOOMGALLERY_COMMAN_MSG_COMMENT_APPROVED' : 'COM_JOOMGALLERY_COMMAN_MSG_COMMENT_REJECTED');
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
   * Removes one or more comments
   *
   * @return  void
   * @since   1.5.5
   */
  public function remove()
  {
    $model = $this->getModel('comments');
    $count = $model->delete();
    if($count === false){
      $msg = JText::_('COM_JOOMGALLERY_COMMAN_MSG_ERROR_DELETING_COMMENT');
    } else {
      if($count == 1){
        $msg = JText::_('COM_JOOMGALLERY_COMMAN_MSG_COMMENT_DELETED');
      } else {
        $msg = JText::sprintf('COM_JOOMGALLERY_COMMAN_MSG_COMMENTS_DELETED', $count);
      }
    }

    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
  }

  /**
   * Removes all comments in the gallery
   *
   * @return  void
   * @since   1.5.5
   */
  public function reset()
  {
    // Delete all comments
    $query = $this->_db->getQuery(true)
          ->delete()
          ->from(_JOOM_TABLE_COMMENTS);
    $this->_db->setQuery($query);

    if(!$this->_db->query())
    {
      // Redirect to maintenance manager because this task is usually launched there
      $this->setRedirect($this->_ambit->getRedirectUrl('maintenance&tab=comments'), $this->_db->getErrorMsg(), 'error');
      return;
    }

    // Redirect to maintenance manager because this task is usually launched there
    $this->setRedirect($this->_ambit->getRedirectUrl('maintenance&tab=comments'), JText::_('COM_JOOMGALLERY_MAIMAN_CM_MSG_ALL_COMMENTS_DELETED'));
  }

  /**
   * Synchronizes the comments with users registered and existing images.
   *
   * Comments of users that aren't registed any more will be marked as written by guests.
   *
   * @return  void
   * @since   1.5.5
   */
  public function synchronize()
  {
    // Synchronize users-comments-images
    $query = $this->_db->getQuery(true)
          ->delete('c USING '._JOOM_TABLE_COMMENTS.' AS c')
          ->leftJoin(_JOOM_TABLE_IMAGES.' AS i ON c.cmtpic  = i.id')
          ->where('i.id IS NULL');
    $this->_db->setQuery($query);

    if(!$this->_db->query())
    {
      // Redirect to maintenance manager because this task is usually launched there
      $this->setRedirect($this->_ambit->getRedirectUrl('maintenance&tab=comments'), $this->_db->getErrorMsg(), 'error');

      return;
    }

    $this->_db->getQuery(true)
          ->update(_JOOM_TABLE_COMMENTS.' AS c')
          ->leftJoin('#__users AS u ON  c.userid = u.id')
          ->set('c.userid = 0')
          ->where('u.id IS NULL');
    $this->_db->setQuery($query);

    if(!$this->_db->query())
    {
      // Redirect to maintenance manager because this task is usually launched there
      $this->setRedirect($this->_ambit->getRedirectUrl('maintenance&tab=comments'), $this->_db->getErrorMsg(), 'error');

      return;
    }

    // Redirect to maintenance manager because this task is usually launched there
    $this->setRedirect($this->_ambit->getRedirectUrl('maintenance&tab=comments'), JText::_('COM_JOOMGALLERY_MAIMAN_CM_MSG_COMMENTS_SYNCHRONIZED'));
  }
}