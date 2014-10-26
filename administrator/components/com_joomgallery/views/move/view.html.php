<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/move/view.html.php $
// $Id: view.html.php 4361 2014-02-24 18:03:18Z erftralle $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * HTML View class for the move view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewMove extends JoomGalleryView
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
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_MANAGER').' :: '.JText::_('COM_JOOMGALLERY_IMGMAN_MOVE_IMAGE'), 'images');
    JToolbarHelper::save('move');
    JToolbarHelper::cancel('cancel');
    JToolbarHelper::spacer();

    $catid = $this->_mainframe->getUserStateFromRequest('joom.move.catid', 'catid', 0, 'int');
    $items = $this->get('Images');
    $lists = array();
    $lists['cats'] = JHTML::_('joomselect.categorylist', $catid, 'catid', 'class="inputbox" size="1" ', null, '- ', null, 'joom.upload');

    $this->assignRef('items', $items);
    $this->assignRef('lists', $lists);

    parent::display($tpl);
  }
}