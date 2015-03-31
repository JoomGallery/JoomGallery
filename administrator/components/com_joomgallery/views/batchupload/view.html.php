<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/batchupload/view.html.php $
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
 * HTML View class for the batch upload view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewBatchupload extends JoomGalleryView
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
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_UPLOAD_BATCH_UPLOAD_MANAGER'), 'upload');

    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    $this->form = JForm::getInstance(_JOOM_OPTION.'.batchupload', 'batchupload');
    $this->form->setFieldAttribute('access', 'default', (int) JFactory::getConfig()->get('access'));

    if($this->_config->get('jg_useorigfilename'))
    {
      $this->form->setFieldAttribute('imgtitle', 'required', 'false');
    }

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }
}