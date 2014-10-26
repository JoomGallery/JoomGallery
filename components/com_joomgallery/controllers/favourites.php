<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/favourites.php $
// $Id: favourites.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery favourites Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerFavourites extends JControllerLegacy
{
  /**
   * Adds an image to the list of the favourites
   *
   * @return  void
   * @since   1.5.5
   */
  public function addImage()
  {
    $model = $this->getModel('favourites');

    // Determine correct redirect URL
    if($catid = JRequest::getInt('catid'))
    {
      // Request was initiated from category view
      $url = JRoute::_('index.php?view=category&catid='.$catid, false);
    }
    elseif(($toplist = JRequest::getVar('toplist')) !== null)
    {
      // Request was initiated from toplist view
      if(empty($toplist))
      {
        // Toplist view 'Most viewed'
        $url = JRoute::_('index.php?view=toplist', false);
      }
      else
      {
        // Any other toplist view
        $url = JRoute::_('index.php?view=toplist&type='.$toplist, false);
      }
    }
    elseif(($sstring = JRequest::getVar('sstring')) !== null)
    {
     // Request was initiated from search view
      $url = JRoute::_('index.php?view=search&sstring='.$sstring, false);
    }
    else
    {
      // Request was initiated from detail view or any other view
      $url = JRoute::_('index.php?view=detail&id='.$model->getId(), false);
    }

    // Add the image to the list of favourite images
    if(!$model->addImage())
    {
      $this->setRedirect($url, $model->getError(), 'error');
    }
    else
    {
      // Message is set by the model
      $this->setRedirect($url);
    }
  }

  /**
   * Adds all images of a category (but none of the sub-categories) to the list of the favourites
   *
   * @return  void
   * @since   1.5.5
   */
  public function addImages()
  {
    $catid  = JRequest::getInt('catid');
    $model  = $this->getModel('favourites');

    // Determine correct redirect URL
    if(JRequest::getCmd('return') == 'gallery')
    {
      // Request was initiated from gallery view
      $url = JRoute::_('index.php?view=gallery', false);
    }
    else
    {
      if($return = JRequest::getInt('return'))
      {
        // Request was initiated from parent category view
        $url = JRoute::_('index.php?view=category&catid='.(int) $return, false);
      }
      else
      {
        // Request was initiated from category view
        $url = JRoute::_('index.php?view=category&catid='.$catid, false);
      }
    }

    // Add the image to the list of favourite images
    if(!$model->addImages($catid))
    {
      $this->setRedirect($url, $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect($url, $model->output('SUCCESSFULLY_ADDED_MORE'));
    }
  }

  /**
   * Removes an image from the list of the favourites
   *
   * @return  void
   * @since   1.5.5
   */
  public function removeImage()
  {
    $model = $this->getModel('favourites');

    if(!$model->removeImage())
    {
      $this->setRedirect(JRoute::_('index.php?view=favourites', false), $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=favourites', false), $model->output('SUCCESSFULLY_REMOVED'));
    }
  }

  /**
   * Clears the list of the favourites
   *
   * @return  void
   * @since   1.5.5
   */
  public function removeAll()
  {
    $model = $this->getModel('favourites');

    if(!$model->removeAll())
    {
      $this->setRedirect(JRoute::_('index.php?view=favourites', false), $model->getError(), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=favourites', false), $model->output('ALL_REMOVED'));
    }
  }

  /**
   * Switches the layout of the favourites view
   *
   * @return  void
   * @since   1.5.5
   */
  public function switchLayout()
  {
    $model = $this->getModel('favourites');

    $model->switchLayout();

    $this->setRedirect(JRoute::_('index.php?view=favourites', false));
  }

  /**
   * Creates a zip archive of the favourites
   *
   * @return  void
   * @since   1.5.5
   */
  public function createZip()
  {
    $model = $this->getModel('favourites');

    if(!$model->createZip())
    {
      $this->setRedirect(JRoute::_('index.php?view=favourites', false), JText::sprintf('COM_JOOMGALLERY_ERROR_CREATING_ZIP', $model->getError()), 'error');
    }
    else
    {
      $this->setRedirect(JRoute::_('index.php?view=downloadzip', false));
    }
  }
}