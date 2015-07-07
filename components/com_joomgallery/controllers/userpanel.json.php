<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/userpanel.json.php $
// $Id: userpanel.json.php 4077 2015-05-19 10:46:13Z chraneco $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2015  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/controller.php';

/**
 * JoomGallery JSON Userpanel Controller
 *
 * @package JoomGallery
 * @since   3.3
 */
class JoomGalleryControllerUserpanel extends JoomGalleryController
{
  /**
   * Method for saving Ajax data.
   *
   * @return  string  Ajax answer
   * @since   3.3
   */
  public function quickEdit()
  {
    try
    {
      $data = JFactory::getApplication()->input->get('images', null, 'array');
      if(!$data || !is_array($data) || !count($data))
      {
        throw new RuntimeException(JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));
      }

      $model = $this->getModel('edit');

      foreach($data as $id => $imageData)
      {
        $model->quickEdit($id, $imageData);
      }

      echo new JResponseJson();
    }
    catch(Exception $e)
    {
      echo new JResponseJson($e);
    }
  }
}