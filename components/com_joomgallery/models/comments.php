<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/comments.php $
// $Id: comments.php 4175 2013-04-05 11:13:27Z chraneco $
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
 * Comments Model
 *
 * Saves and removes comments.
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelComments extends JoomGalleryModel
{
  /**
   * The ID of the image the comment belongs to
   *
   * @var     int
   */
  protected $_id;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    $id = JRequest::getInt('id');
    $this->setId($id);
  }

  /**
   * Method to set the image ID
   *
   * @param   int     Image ID number
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new image ID if valid
    if(!$id)
    {
      JError::raiseError(500, JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));
    }
    $this->_id  = $id;
  }

  /**
   * Method to get the image ID
   *
   * @return  int     The image ID
   * @since   1.5.5
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Method to save a new comment
   *
   * @return  int     1 on success, 2 on success but approval necessary, boolean false otherwise
   * @since   1.5.5
   */
  public function save()
  {
    // Check for hacking attempt
    $authorised_viewlevels = implode(',', $this->_user->getAuthorisedViewLevels());

    $query = $this->_db->getQuery(true)
          ->select('c.cid')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
          ->where('a.published = 1')
          ->where('a.approved = 1')
          ->where('a.id = '.$this->_id)
          ->where('a.access IN ('.$authorised_viewlevels.')')
          ->where('c.access IN ('.$authorised_viewlevels.')');

    $this->_db->setQuery($query);
    $result = $this->_db->loadResult();
    if(   !$result
      ||  !$this->_config->get('jg_showcomment')
      || (!$this->_config->get('jg_anoncomment') && !$this->_user->get('id'))
      )
    {
      die('Hacking attempt, aborted!');
    }

    $categories = $this->_ambit->getCategoryStructure();
    if(!isset($categories[$result]))
    {
      die('Hacking attempt, aborted!');
    }

    // Comment text
    $filter = JFilterInput::getInstance();
    $text = trim($filter->clean(JRequest::getVar('cmttext', '', 'post')));
    if(!$text)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=detail&id='.$this->_id.'#joomcommentform', false),
                                  JText::_('COM_JOOMGALLERY_NO_COMMENT_ENTERED'), 'notice');
    }

    // Name of the one who comments
    if($this->_user->get('id'))
    {
      $name = $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username');
    }
    else
    {
      if($this->_config->get('jg_namedanoncomment'))
      {
        $name   = trim($filter->clean(JRequest::getVar('cmtname', '', 'post')));
        if(!$name)
        {
          $name = JText::_('COM_JOOMGALLERY_COMMON_GUEST');
        }
      }
      else
      {
        $name   = JText::_('COM_JOOMGALLERY_COMMON_GUEST');
      }
    }

    // Store the data in session
    $this->_mainframe->setUserState('joom.comments.name', $name);
    $this->_mainframe->setUserState('joom.comments.text', $text);

    // Captcha
    $valid = true;
    $plugins  = $this->_mainframe->triggerEvent('onJoomCheckCaptcha');
    foreach($plugins as $key => $result)
    {
      if(is_array($result) && isset($result['valid']) && !$result['valid'])
      {
        $valid = false;
        if(isset($result['error']) && $result['error'])
        {
          $msg = $result['error'];
        }
        else
        {
          $msg = JText::_('COM_JOOMGALLERY_DETAIL_MSG_COMMENT_SECURITY_CODE_WRONG');
        }
        break;
      }
    }

    if(!$valid)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=detail&id='.$this->_id.'#joomcommentform', false),
                                  $msg, 'notice');
    }

    // Check whether the comment has to be approved by administrators
    if(   (!$this->_config->get('jg_approvecom')      && $this->_user->get('id'))
      ||  (!$this->_config->get('jg_anonapprovecom')  && !$this->_user->get('id'))
      )
    {
      $approved = 1;

      // Load image data
      $image    = $this->getTable('joomgalleryimages');
      $image->load($this->_id);

      // Message about new comment to image owner
      // If comments have to be approved by administrators
      // this message will be sent as soon as the comment was approved
      if(     $this->_config->get('jg_msg_comment_toowner')
          &&  $image->owner
          &&  $image->owner != $this->_user->get('id')
        )
      {
        // Load image data
        $row = $this->getTable('joomgalleryimages');
        $row->load($this->_id);

        require_once(JPATH_COMPONENT.'/helpers/messenger.php');
        $messenger  = new JoomMessenger();
        $message    = array(
                            'from'      => $this->_user->get('id'),
                            'recipient' => $image->owner,
                            'subject'   => JText::_('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_TO_OWNER_SUBJECT'),
                            'body'      => JText::sprintf('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_TO_OWNER_BODY', $name, $image->imgtitle, $this->_id),
                            'type'      => $messenger->getType('comment')
                          );
      }
    }
    else
    {
      $approved = 0;

      // Message about new comment
      require_once(JPATH_COMPONENT.'/helpers/messenger.php');
      $messenger  = new JoomMessenger();

      $message    = array(
                            'from'      => $this->_user->get('id'),
                            'subject'   => JText::_('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_SUBJECT'),
                            'body'      => JText::sprintf('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_BODY', $name),
                            'mode'      => 'comment'
                          );
    }

    // Change \r\n or \n to <br />
    $text = nl2br(stripcslashes($text));
    $date = JFactory::getDate();
    $row  = $this->getTable('joomgallerycomments');

    $row->cmtpic    = $this->_id;
    $row->cmtip     = $_SERVER['REMOTE_ADDR'];
    $row->userid    = $this->_user->get('id');
    $row->cmtname   = $name;
    $row->cmttext   = $text;
    $row->cmtdate   = $date->toSQL();
    $row->published = 1;
    $row->approved  = $approved;

    // Trigger event 'onJoomBeforeComment'
    $plugins  = $this->_mainframe->triggerEvent('onJoomBeforeComment', array(&$row));
    if(in_array(false, $plugins, true))
    {
      return false;
    }

    if(!$row->check())
    {
      $this->setError($row->getError());

      return false;
    }

    if(!$row->store())
    {
      $this->setError(JText::_('COM_JOOMGALLERY_ERROR_SAVING_COMMENT'));

      return false;
    }

    if(isset($messenger))
    {
      $messenger->send($message);
    }

    $this->_mainframe->triggerEvent('onJoomAfterComment', array($row));

    // After successfully storing the comment remove the comment text from the session, but keep the name
    $this->_mainframe->setUserState('joom.comments.text', null);

    if($approved)
    {
      return 1;
    }
    else
    {
      return 2;
    }
  }

  /**
   * Method to delete a comment
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function remove()
  {
    if(!$this->_user->authorise('core.manage', _JOOM_OPTION))
    {
      JError::raiseError(500, JText::_('COM_JOOMGALLERY_COMMON_PERMISSION_DENIED'));
    }

    $cmtid = JRequest::getInt('cmtid');

    $query = $this->_db->getQuery(true)
          ->delete(_JOOM_TABLE_COMMENTS)
          ->where('cmtid = '.$cmtid)
          ->where('cmtpic  = '.$this->_id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError(JText::_('COM_JOOMGALLERY_ERROR_DELETING_COMMENT'));

      return false;
    }

    return true;
  }
}