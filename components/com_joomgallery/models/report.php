<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/report.php $
// $Id: report.php 4175 2013-04-05 11:13:27Z chraneco $
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
 * Report Model
 *
 * Sends a report.
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryModelReport extends JoomGalleryModel
{
  /**
   * Method to send a report
   *
   * @param   string  $redirect_url Internal URL to the page on which the report was send
   * @return  boolean Redirect URL on success, false otherwise
   * @since   2.1
   */
  public function send($redirect_url = 'index.php')
  {
    $id = JRequest::getInt('id');

    if(!$id)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));

      return false;
    }

    // Do some security checks
    if(   !$this->_config->get('jg_report_images')
      ||  (!$this->_config->get('jg_report_unreg') && !$this->_user->get('id'))
      )
    {
      $msg = JText::_('JERROR_ALERTNOAUTHOR');
      if(!$this->_user->get('id'))
      {
        $msg .= JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED');
      }

      $this->setError($msg);

      return false;
    }

    if(!$this->_user->get('id'))
    {
      $fromname = $this->_mainframe->getUserStateFromRequest('report.image.name', 'name', '', 'post');
      $from     = $this->_mainframe->getUserStateFromRequest('report.image.email', 'email', '', 'post');
    }
    else
    {
      $fromname = $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username');
      $from     = $this->_user->get('id');
    }

    $report = $this->_mainframe->getUserStateFromRequest('report.image.report', 'report', '', 'post');

    if(!$report || !$fromname || !$from)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_FORM_NOT_FILLED'));

      return false;
    }

    // Captcha
    $valid  = true;
    $msg    = '';
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
          $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_SECURITY_CODE_WRONG');
        }
        break;
      }
    }

    if(!$valid)
    {
      $this->setError($msg);

      return false;
    }

    // Prepare links
    $image    = $this->_ambit->getImgObject($id);
    $link     = JRoute::_($redirect_url);
    $img_src  = JRoute::_($this->_ambit->getImg('img_url', $image));

    $current_uri  = JURI::getInstance(JURI::base());
    $current_host = $current_uri->toString(array('scheme', 'host', 'port'));

    // Ensure that the correct host and path is prepended
    $uri      = JFactory::getUri($link);
    $uri->setHost($current_host);
    $link     = $uri->toString();
    $uri      = JFactory::getUri($img_src);
    $uri->setHost($current_host);
    $img_src  = $uri->toString();

    $text = JText::sprintf( 'COM_JOOMGALLERY_REPORT_IMAGE_BODY',
                            $image->id,
                            $image->imgtitle,
                            $fromname,
                            $from,
                            $link,
                            $img_src,
                            $report
                          );

    $subject = JText::sprintf('COM_JOOMGALLERY_REPORT_IMAGE_SUBJECT', $this->_mainframe->getCfg('sitename'));

    // Create the message
    require_once JPATH_COMPONENT.'/helpers/messenger.php';
    $messenger = new JoomMessenger();

    $message = array( 'from'      => $from,
                      'fromname'  => $fromname,
                      'subject'   => $subject,
                      'body'      => $text,
                      'mode'      => 'report'
                    );

    // Message to image owner
    if($this->_config->get('jg_msg_report_toowner'))
    {
      $messenger->addRecipients($image->owner);
    }

    // Send the message
    if(!$messenger->send($message))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_REPORT_NOT_SENT'));

      return false;
    }

    // Delete data in session
    $this->_mainframe->setUserState('report.image', null);

    $this->_mainframe->triggerEvent('onJoomAfterReport', array($message));

    return true;
  }
}