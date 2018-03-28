<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/cssedit/view.html.php $
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
 * HTML View class for the CSS edit view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewCssedit extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @access  public
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  function display($tpl = null)
  {
    jimport('joomla.filesystem.file');

    JToolBarHelper::apply('apply');
    JToolBarHelper::save('save');

    $path   = JPATH_ROOT.'/media/joomgallery/css/';
    $title  = JText::_('COM_JOOMGALLERY_CSSMAN_CSS_MANAGER');
    if(JFile::exists($path.'joom_local.css'))
    {
      $title .= ' ('.JText::_('COM_JOOMGALLERY_COMMON_TOOLBAR_EDIT').')';

      JToolBarHelper::deleteList(JText::_('COM_JOOMGALLERY_CSSMAN_CSS_CONFIRM_DELETE', true), 'remove', 'COM_JOOMGALLERY_CSSMAN_TOOLBAR_DELETE_CSS');

      $this->file = $path.'joom_local.css';

      if(!is_writable($this->file))
      {
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_CSSMAN_CSS_WARNING_PERMS'), 'notice');
      }

      $this->edit = true;
    }
    else
    {
      $title .= ' ('.JText::_('COM_JOOMGALLERY_COMMON_TOOLBAR_NEW').')';

      $this->file = $path.'joom_local.css.README';

      if(!is_writable($path))
      {
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_CSSMAN_CSS_WARNING_PERMS'), 'notice');
      }

      $this->edit = false;
    }

    JToolBarHelper::title($title, 'file');

    $this->content =  file_get_contents($this->file);
    if($this->content === false)
    {
      // Unable to read the file
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_CSSMAN_CSS_ERROR_READING') . $this->file, 'error');
    }
    else
    {
      $this->content = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8');
    }

    $this->file = $path.'joom_local.css';

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }
}