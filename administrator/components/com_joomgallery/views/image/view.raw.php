<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/image/view.raw.php $
// $Id: view.raw.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Raw View class for the image view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewImage extends JoomGalleryView
{
  /**
   * Raw view display method, outputs one image
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  public function display($tpl = null)
  {
    jimport('joomla.filesystem.file');

    $type     = JRequest::getWord('type', 'thumb');
    $image    = $this->get('Data');
    $img      = $this->_ambit->getImg($type.'_path', $image);

    if(!JFile::exists($img))
    {
      $this->_mainframe->redirect(JRoute::_('index.php', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_IMAGE_NOT_EXIST'), 'error');
    }

    $info = getimagesize($img);
    switch($info[2])
    {
      case 1:
        $mime = 'image/gif';
       break;
      case 2:
        $mime = 'image/jpeg';
        break;
      case 3:
        $mime = 'image/png';
        break;
      default:
        JError::raiseError(404, JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $info[2]));
        break;
    }

    // Set mime encoding
    $this->_doc->setMimeEncoding($mime);

    // Set header to specify the file name
    $disposition = 'inline';
    JResponse::setHeader('Content-disposition', $disposition.'; filename='.basename($img));

    echo JFile::read($img);
  }
}