<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/nametags.php $
// $Id: nametags.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Name Tags Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerNametags extends JControllerLegacy
{
  /**
   * Saves a name tag
   *
   * @return  void
   * @since   2.1
   */
  public function save()
  {
    $model = $this->getModel('nametags');

    if(!$model->save())
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), JText::_('COM_JOOMGALLERY_DETAIL_NAMETAGS_MSG_SAVED'));
    }
  }

  /**
   * Deletes a specific name tag
   *
   * @return  void
   * @since   2.1
   */
  public function remove()
  {
    $model = $this->getModel('nametags');

    if(!$model->remove())
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=detail&id='.$model->getId(), false), JText::_('COM_JOOMGALLERY_DETAIL_NAMETAGS_MSG_DELETED'));
    }
  }
}