<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/image.php $
// $Id: image.php 4405 2014-07-02 07:13:31Z chraneco $
/****************************************************************************************\
**   JoomGallery 3                                                                   **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * JoomGallery Image Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerImage extends JControllerLegacy
{
  /**
   * Saves an image after editing
   *
   * @return  void
   * @since   1.5.5
   */
  public function save()
  {
    $model = $this->getModel('edit');

    $array = $this->input->get('id', array(0), 'array');

    $model->setId((int)$array[0]);

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if($this->input->get('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.$this->input->getInt('limitstart', 0);
    }

    // Set standard redirect URL
    $redirect = 'index.php?view=userpanel'.$slimitstart;
    // Is there any redirect requested?
    $url = $this->input->getBase64('redirect', null);
    if($url !== null)
    {
      $url = base64_decode($url);
      if(JURI::isInternal($url))
      {
        $redirect = $url;
      }
    }

    // Editing only one image
    if($id = $model->store())
    {
      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_IMAGE_SAVED');
      $this->setRedirect(JRoute::_($redirect, false), $msg);
    }
    else
    {
      $msg  = $model->getError();
      $this->setRedirect(JRoute::_($redirect, false), $msg, 'error');
    }
  }

  /**
   * Deletes an image
   *
   * @return  void
   * @since   1.5.5
   */
  public function delete()
  {
    $model = $this->getModel('edit');

    $array = $this->input->get('id', array(0), 'array');

    $model->setId((int)$array[0]);

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if($this->input->get('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.$this->input->get('limitstart', 0);
    }

    // Set standard redirect URL
    $redirect = 'index.php?view=userpanel'.$slimitstart;
    // Is there any redirect requested?
    $url = $this->input->getBase64('redirect', null);
    if($url !== null)
    {
      $url = base64_decode($url);
      if(JURI::isInternal($url))
      {
        $redirect = $url;
      }
    }

    try
    {
      $model->delete();

      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_IMAGE_AND_COMMENTS_DELETED');
      $this->setRedirect(JRoute::_($redirect, false), $msg);
    }
    catch(RuntimeException $e)
    {
      $this->setRedirect(JRoute::_($redirect, false), $e->getMessage(), 'error');
    }
  }

  /**
   * Publishes resp. unpublishes an image
   *
   * @return  void
   * @since   1.5.7
   */
  public function publish()
  {
    $model = $this->getModel('edit');

    $array = $this->input->get('id', array(0), 'array');

    $model->setId((int)$array[0]);

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if($this->input->get('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.$this->input->getInt('limitstart', 0);
    }

    if($model->publish())
    {
      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_SUCCESS_CHANGE_PUBLISH_STATE');
      $this->setRedirect(JRoute::_('index.php?view=userpanel'.$slimitstart, false), $msg);
    }
    else
    {
      $msg  = $model->getError();
      $this->setRedirect(JRoute::_('index.php?view=userpanel'.$slimitstart, false), $msg, 'error');
    }
  }
}