<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/ajaxupload.raw.php $
// $Id: ajaxupload.raw.php 4398 2014-06-11 13:39:02Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013 JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * JoomGallery AJAX Upload JSON Controller
 *
 * @package JoomGallery
 * @since   3.0
 */
class JoomGalleryControllerAjaxupload extends JoomGalleryController
{
  /**
   * Uploads the selected images
   *
   * @return  void
   * @since   3.0
   */
  public function upload()
  {
    $result = array('error' => false);

    require_once JPATH_COMPONENT.'/helpers/upload.php';

    $uploader = new JoomUpload();

    if($image = $uploader->upload(JRequest::getCmd('type', 'ajax')))
    {
      $result['success'] = true;
      if(is_object($image))
      {
        $result['id'] = $image->id;
        $result['imgtitle'] = $image->imgtitle;
        $result['thumbnailUrl'] = $this->_ambit->getImg('thumb_url', $image);
      }
    }
    else
    {
      if($error = $uploader->getError())
      {
        $result['error'] = $error;
      }
      else
      {
        $result['error'] = JText::_('COM_JOOMGALLERY_UPLOAD_ERROR_FILE_NOT_UPLOADED');;
      }
    }

    if($debug_output = $uploader->getDebugOutput())
    {
      $result['debug_output'] = $debug_output;
    }

    $doc = JFactory::getDocument();
    $doc->setMimeEncoding('text/plain');

    echo json_encode($result);
  }
}