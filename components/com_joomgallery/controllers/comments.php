<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/comments.php $
// $Id: comments.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Comments Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerComments extends JControllerLegacy
{
  /**
   * Saves a comment
   *
   * @return  void
   * @since   1.5.5
   */
  public function comment()
  {
    $model = $this->getModel('comments');

    if(!$return = $model->save())
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), $model->getError(), 'error');
    }
    else
    {
      if($return == 1)
      {
        $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), JText::_('COM_JOOMGALLERY_DETAIL_MSG_COMMENT_SAVED'));
      }
      else
      {
        $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), JText::_('COM_JOOMGALLERY_DETAIL_MSG_COMMENT_SAVED_BUT_NEEDS_ARROVAL'));
      }
    }
  }

  /**
   * Deletes a specific comment
   *
   * @return  void
   * @since   1.5.5
   */
  public function remove()
  {
    $model = $this->getModel('comments');

    if(!$model->remove())
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), JText::_('COM_JOOMGALLERY_DETAIL_MSG_COMMENT_DELETED'));
    }
  }
}