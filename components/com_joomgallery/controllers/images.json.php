<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/images.json.php $
// $Id: images.json.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Images JSON Controller
 *
 * @package JoomGallery
 * @since   3.0
 */
class JoomGalleryControllerImages extends JControllerLegacy
{
  /**
   * Method to save the submitted ordering values for records via AJAX.
   *
   * @return  void
   * @since   3.0
   */
  public function saveOrder()
  {
    require_once JPATH_ADMINISTRATOR.'/components/com_languages/helpers/jsonresponse.php';

    JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

    $conditions = array();

    $cid   = JRequest::getVar('cid', array(), 'post', 'array');
    $order = JRequest::getVar('order', array(), 'post', 'array');
    $user  = JFactory::getUser();

    $row = JTable::getInstance('joomgalleryimages', 'Table');

    // Update the ordering for items in the cid array
    for($i = 0; $i < count($cid); $i ++)
    {
      if(!$user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$cid[$i]))
      {
        continue;
      }

      $row->load((int)$cid[$i]);
      if($row->ordering != $order[$i])
      {
        $row->ordering = $order[$i];
        if(!$row->store())
        {
          break;
        }

        // Remember the categories for reordering
        $condition = 'catid = '.(int) $row->catid;
        $found = false;
        foreach($conditions as $cond)
        {
          if($cond == $condition)
          {
            $found = true;
            break;
          }
        }

        if(!$found)
        {
          $conditions[] = $condition;
        }
      }
    }

    // Execute reorder for each category
    foreach($conditions as $cond)
    {
      $row->reorder($cond);
    }

    echo new JJsonResponse();
  }
}