<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/image/view.raw.php $
// $Id: view.raw.php 4224 2013-04-22 15:46:14Z erftralle $
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
    $download = JRequest::getCmd('download');

    $crop_image = false;
    $cropwidth  = JRequest::getInt('width');
    $cropheight = JRequest::getInt('height');
    if($cropwidth && $cropheight)
    {
      $crop_image = true;
    }

    $model = $this->getModel();

    if(!$image = $model->getImage(JRequest::getInt('id')))
    {
      return $this->displayError($model->getError());
    }

    $img  = $this->_ambit->getImg($type.'_path', $image);

    $include_watermark = false;

    // Check access rights
    // If the thumbnail is required, we won't have to do more checks than the
    // general access level check in the model.
    // Additionally the hit counter gets only increased if we are not
    // displaying a thumbnail.
    if($type != 'thumb')
    {
      // Downloading
      if($download)
      {
        // Is the download allowed for the user group of the current user?
        if(   !$this->_config->get('jg_download')
          ||  (!$this->_config->get('jg_download_unreg') && !$this->_user->get('id'))
          )
        {
          $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_ACCESS'), 'error');
        }

        // Is the download of the requested image type allowed?
        if(!$this->_config->get('jg_downloadfile') && $type == 'orig')
        {
          $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_ACCESS'), 'notice');
        }
        if($this->_config->get('jg_downloadfile') == 1 && !JFile::exists($img))
        {
          $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_ORIGINAL_NOT_AVAILABLE'), 'notice');
        }
        if($this->_config->get('jg_downloadfile') == 2 && $type == 'orig')
        {
          if(!JFile::exists($img))
          {
            // Offer detail image for download if original images isn't available
            $type = 'img';
            $img  = $this->_ambit->getImg($type.'_path', $image);
          }
        }

        // Include watermark when downloading image?
        if($this->_config->get('jg_downloadwithwatermark'))
        {
          $include_watermark = true;
        }

        // Trigger event 'onJoomBeforeDownload'
        $plugins = $this->_mainframe->triggerEvent('onJoomBeforeDownload', array(&$image, &$img, &$type, &$include_watermark));
        if(in_array(false, $plugins, true))
        {
          $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false));
        }

        // Message about new download
        if(!$this->_user->get('username'))
        {
          $username = JText::_('COM_JOOMGALLERY_COMMON_GUEST');
        }
        else
        {
          $username = $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username');
        }

        require_once JPATH_COMPONENT.'/helpers/messenger.php';
        $messenger    = new JoomMessenger();
        $message      = array(
                              'subject'   => JText::_('COM_JOOMGALLERY_MESSAGE_NEW_DOWNLOAD_SUBJECT'),
                              'body'      => JText::sprintf('COM_JOOMGALLERY_MESSAGE_NEW_DOWNLOAD_BODY',
                                             $image->imgtitle, $image->imgfilename, $username),
                              'mode'      => 'download'
                              );
        $messenger->send($message);

        // Increase download counter
        $model->download();
      }
      // Displaying, not downloading
      else
      {
        if(!$this->_config->get('jg_showdetailpage') && !$this->_user->get('id'))
        {
          return $this->displayError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_ACCESS'));
        }

        // Include watermark when displaying image in the detail view?
        if($this->_config->get('jg_watermark'))
        {
          $include_watermark = true;
        }

        // Link to original image in detail view or category view
        if(   ($type == 'orig')
            &&
              (
                  (         (is_numeric($this->_config->get('jg_detailpic_open')) && $this->_config->get('jg_detailpic_open') == 0)
                    &&
                      (     (!$this->_config->get('jg_bigpic') && $this->_user->get('id'))
                        ||  (!$this->_config->get('jg_bigpic_unreg') && !$this->_user->get('id'))
                      )
                  )
                ||
                  (     (!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 0)
                    &&  !$this->_config->get('jg_lightboxbigpic')
                  )
              )
          )
        {
          return $this->displayError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_ACCESS'));
        }
      }

      // Increase hit counter
      $model->hit();
    }

    if(!JFile::exists($img))
    {
      return $this->displayError(JText::_('COM_JOOMGALLERY_COMMON_MSG_IMAGE_NOT_EXIST'));
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
        return $this->displayError(JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $info[2]));
    }

    // Set mime encoding
    $this->_doc->setMimeEncoding($mime);

    // Set header to specify the file name
    $disposition = 'inline';
    if($download)
    {
      // Allow downloading
      $disposition = 'attachment';
    }
    JResponse::setHeader('Content-disposition', $disposition.'; filename='.basename($img));

    // Inlude watermark and crop
    if(($include_watermark || $crop_image) && !$model->isGif($img))
    {
      $img_resource = null;
      if($crop_image)
      {
        $croppos  = JRequest::getInt('pos');
        $offsetx  = JRequest::getInt('x');
        $offsety  = JRequest::getInt('y');
        $img_resource = $model->cropImage($img, $cropwidth, $cropheight, $croppos, $offsetx, $offsety);
      }

      if($include_watermark)
      {
        if(!$img_resource = $model->includeWatermark($img, $img_resource, $cropwidth, $cropheight))
        {
          return $this->displayError($model->getError());
        }
      }

      if(!$img_resource)
      {
        echo JFile::read($img);
      }
      else
      {
        switch($mime)
        {
          case 'image/gif':
            imagegif($img_resource);
            break;
          case 'image/png':
            imagepng($img_resource);
            break;
          case 'image/jpeg':
            $quali = JRequest::getInt('quali', 95);
            imagejpeg($img_resource, null, $quali);
            break;
          default:
            return $this->displayError(JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $mime));
        }

        imagedestroy($img_resource);
      }
    }
    else
    {
      echo JFile::read($img);
    }
  }

  /**
   * Creates an empty image and inserts a text string for displaying error messages in 'img' tags
   *
   * @param   string  $msg  The message to display
   * @return  void
   * @since   3.0
   */
  protected function displayError($msg)
  {
    $this->_doc->setMimeEncoding('image/jpeg');

    $type   = JRequest::getWord('type', 'thumb');
    $width  = JRequest::getInt('width');
    $height = JRequest::getInt('height');

    if(!$width || !$height)
    {
      switch($type)
      {
        case 'thumb':
          $width  = $this->_config->get('jg_thumbwidth');
          $height = $this->_config->get('jg_thumbheight');
          break;
        case 'img':
          $width  = $this->_config->get('jg_maxwidth');
          $height = $this->_config->get('jg_maxwidth');
          break;
        default:
          $width  = 500;
          $height = 500;
          break;
      }
    }

    $img = imagecreatetruecolor($width, $height);
    $text_color = imagecolorallocate($img, 255, 0, 0);
    imagestring($img, 5, 5, 5,  $msg, $text_color);
    imagejpeg($img);
    imagedestroy($img);
  }
}