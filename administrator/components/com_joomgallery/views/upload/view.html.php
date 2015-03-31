<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/upload/view.html.php $
// $Id: view.html.php 4361 2014-02-24 18:03:18Z erftralle $
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
class JoomGalleryViewUpload extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  public function display($tpl = null)
  {
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_UPLOAD_IMAGE_UPLOAD_MANAGER'), 'upload');

    $this->_doc->addScriptDeclaration('    var jg_filenamewithjs = '.($this->_config->get('jg_filenamewithjs') ? 'true' : 'false').';');
    $this->_doc->addScript($this->_ambit->getScript('upload.js'));
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_CATEGORY');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_ONE_IMAGE');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_WRONG_EXTENSION');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_WRONG_FILENAME');
    JText::script('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_ONE');
    JText::script('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_TWO');

    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    $this->form = JForm::getInstance(_JOOM_OPTION.'.upload', 'upload');
    $this->form->setFieldAttribute('access', 'default', (int) JFactory::getConfig()->get('access'));

    if($this->_config->get('jg_useorigfilename'))
    {
      $this->form->setFieldAttribute('imgtitle', 'required', 'false');
    }

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }
}
