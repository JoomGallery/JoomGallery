<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/ajaxupload/view.html.php $
// $Id: view.html.php 4398 2014-06-11 13:39:02Z erftralle $
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
 * HTML View class for the single upload view
 *
 * @package JoomGallery
 * @since
 */
class JoomGalleryViewAjaxupload extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   3.0
   */
  public function display($tpl = null)
  {
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_AJAX_UPLOAD'), 'upload');

    $this->_doc->addScriptDeclaration('    var jg_filenamewithjs = '.($this->_config->get('jg_filenamewithjs') ? 'true' : 'false').';');

    // Load Fine Uploader resources
    $this->_doc->addStyleSheet($this->_ambit->getScript('fineuploader/fineuploader.css'));
    $this->_doc->addScript($this->_ambit->getScript('fineuploader/js/fineuploader'.(JFactory::getConfig()->get('debug') ? '' : '.min').'.js'));

    $this->fileSizeLimit = 0;
    $this->chunkSize     = 0;
    $post_max_size = @ini_get('post_max_size');
    if(!empty($post_max_size))
    {
      $post_max_size   = JoomHelper::iniToBytes($post_max_size);
      $this->chunkSize = (int) min(500000, (int)(0.8 * $post_max_size));
    }
    $upload_max_filesize = @ini_get('upload_max_filesize');
    if(!empty($upload_max_filesize))
    {
      $upload_max_filesize = JoomHelper::iniToBytes($upload_max_filesize);
      $this->fileSizeLimit = $upload_max_filesize;
    }
    $this->editFilename = $this->_config->get('jg_useorigfilename');

    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    $this->form = JForm::getInstance(_JOOM_OPTION.'.ajaxupload', 'ajaxupload');
    $this->form->setFieldAttribute('access', 'default', (int) JFactory::getConfig()->get('access'));

    if($this->_config->get('jg_useorigfilename'))
    {
      $this->form->setFieldAttribute('imgtitle', 'required', 'false');
    }

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }
}