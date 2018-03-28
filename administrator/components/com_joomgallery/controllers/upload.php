<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/upload.php $
// $Id: upload.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Single Upload Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerUpload extends JoomGalleryController
{
  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    // Set view
    $this->input->set('view', 'upload');
  }

  /**
   * Displays the default upload form
   *
   * @param   boolean  $cachable   If true, the view output will be cached
   * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
   * @return  void
   * @since   2.0
   */
  public function display($cachable = false, $urlparams = false)
  {
    // Access check
    if(!$this->_config->get('jg_disableunrequiredchecks') && !count(JoomHelper::getAuthorisedCategories('joom.upload')))
    {
      $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl('categories'), false), JText::_('No categories found into which you are allowed to upload'), 'notice');

      return;
    }

    parent::display($cachable, $urlparams);
  }

  /**
   * Uploads the selected images
   *
   * @return  void
   * @since   1.5.5
   */
  public function upload()
  {
    require_once JPATH_COMPONENT.'/helpers/upload.php';
    $uploader = new JoomUpload();
    if($uploader->upload($this->input->getCmd('type', 'single')))
    {
      $msg  = JText::_('COM_JOOMGALLERY_UPLOAD_MSG_SUCCESSFULL');
      $url  = $this->_ambit->getRedirectUrl();

      // Set custom redirect if we are asked for that
      if($redirect = $this->input->getBase64('redirect', ''))
      {
        $url_decoded  = base64_decode($redirect);
        if(JURI::isInternal($url))
        {
          $url = $url_decoded;
        }
      }

      $this->setRedirect(JRoute::_($url, false), $msg);
    }
    else
    {
      if($error = $uploader->getError())
      {
        $this->setRedirect($this->_ambit->getRedirectUrl(), $error, 'error');
      }
    }
  }
}