<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/send2friend.php $
// $Id: send2friend.php 4222 2013-04-22 12:19:11Z chraneco $
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
 * Send2Friend Model
 *
 * Sends an image link to another persion.
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryModelSend2Friend extends JoomGalleryModel
{
  /**
   * Method to send an image link to another persion
   *
   * @return  boolean Redirect URL on success, false otherwise
   * @since   2.1
   */
  public function send()
  {
    $id = JRequest::getInt('id');

    if(!$this->_user->get('id'))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED'));

      return false;
    }

    if(!$id)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));

      return false;
    }

    require_once JPATH_COMPONENT.'/helpers/messenger.php';

    $send2friendname  = JRequest::getVar('send2friendname', '', 'post');
    $send2friendemail = JRequest::getVar('send2friendemail', '', 'post');

    // Prepare link
    $link = JRoute::_('index.php?view=detail&id='.$id);

    $current_uri  = JURI::getInstance(JURI::base());
    $current_host = $current_uri->toString(array('scheme', 'host', 'port'));

    // Ensure that the correct host and path is prepended
    $uri  = JFactory::getUri($link);
    $uri->setHost($current_host);
    $link = $uri->toString();

    $text = JText::sprintf( 'COM_JOOMGALLERY_MESSAGE_IMAGE_FROM_FRIEND_BODY',
                            $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username'),
                            $this->_user->get('email'),
                            $link
                          );

    $subject = $this->_mainframe->getCfg('sitename').' - '.JText::_('COM_JOOMGALLERY_MESSAGE_IMAGE_FROM_FRIEND_SUBJECT');

    $message = array( 'from'      => $this->_user->get('email'),
                      'fromname'  => $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username'),
                      'recipient' => $send2friendemail,
                      'subject'   => $subject,
                      'body'      => $text,
                      'mode'      => 'send2friend'
                    );

    $messenger = new JoomMessenger();

    if(!$messenger->send($message))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_DETAIL_SENDTOFRIEND_MSG_MAIL_NOT_SENT'));

      return false;
    }

    $this->_mainframe->triggerEvent('onJoomAfterSend2Friend', array($message));

    return true;
  }
}