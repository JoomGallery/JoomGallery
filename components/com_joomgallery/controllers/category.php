<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/category.php $
// $Id: category.php 4405 2014-07-02 07:13:31Z chraneco $
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
 * JoomGallery Category Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerCategory extends JControllerLegacy
{
  /**
   * Saves a category
   *
   * @return  void
   * @since   1.5.5
   */
  public function save()
  {
    $model = $this->getModel('editcategory');

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if(JRequest::getVar('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.JRequest::getInt('limitstart', 0);
    }

    // Set default redirect URL
    $redirect = 'index.php?view=usercategories'.$slimitstart;

    // Check whether a redirect is requested
    if($url = JRequest::getVar('redirect', '', '', 'base64'))
    {
      $url = base64_decode($url);
      if(JURI::isInternal($url))
      {
        $redirect = $url;
      }
    }

    if($id = $model->store())
    {
      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_CATEGORY_SAVED');
      $this->setRedirect(JRoute::_($redirect, false), $msg);
    }
    else
    {
      $msg  = $model->getError();
      $this->setRedirect(JRoute::_($redirect, false), $msg, 'error');
    }
  }

  /**
   * Deletes a category
   *
   * @return  void
   * @since   1.5.5
   */
  public function delete()
  {
    $model = $this->getModel('editcategory');

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if(JRequest::getVar('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.JRequest::getInt('limitstart', 0);
    }

    try
    {
      $model->delete();

      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_SUCCESS_DELETING_CATEGORY');
      $this->setRedirect(JRoute::_('index.php?view=usercategories'.$slimitstart, false), $msg);
    }
    catch(RuntimeException $e)
    {
      $this->setRedirect(JRoute::_('index.php?view=usercategories'.$slimitstart, false), $e->getMessage(), 'error');
    }
  }

  /**
   * Publishes resp. unpublishes a category
   *
   * @return  void
   * @since   1.5.7
   */
  public function publish()
  {
    $model = $this->getModel('editcategory');

    // Get limitstart from request to set the correct limitstart (page) for redirect url
    $slimitstart = '';
    if(JRequest::getVar('limitstart', null) != null)
    {
      $slimitstart = '&limitstart='.JRequest::getInt('limitstart', 0);
    }

    if($model->publish())
    {
      $msg  = JText::_('COM_JOOMGALLERY_COMMON_MSG_SUCCESS_CHANGE_PUBLISH_STATE');
      $this->setRedirect(JRoute::_('index.php?view=usercategories'.$slimitstart, false), $msg);
    }
    else
    {
      $msg  = $model->getError();
      $this->setRedirect(JRoute::_('index.php?view=usercategories'.$slimitstart, false), $msg, 'error');
    }
  }

  /**
   * Unlocks a password protected category
   *
   * @return  void
   * @since   3.1
   */
  public function unlock()
  {
    $input = JFactory::getApplication()->input;
    $model = $this->getModel('category');

    $catid = $input->getInt('catid');

    try
    {
      $model->unlock($catid, $input->getString('password'));
    }
    catch(Exception $e)
    {
      $this->setMessage($e->getMessage(), 'error');
    }

    $this->setRedirect(JRoute::_('index.php?view=category&catid='.$catid, false));
  }
}