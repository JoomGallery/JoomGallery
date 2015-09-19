<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/comments/view.html.php $
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
 * HTML View class for the comments list view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewComments extends JoomGalleryView
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
    // Get data from the model
    $this->items         = $this->get('Comments');
    $this->state         = $this->get('State');
    $this->pagination    = $this->get('Pagination');
    $this->filterForm    = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');

    $this->addToolbar();

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   *
   * @since 2.0
   */
  public function addToolbar()
  {
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_COMMAN_COMMENTS_MANAGER'), 'comments-2');
    JToolbarHelper::publishList('publish', 'COM_JOOMGALLERY_COMMAN_TOOLBAR_PUBLISH_COMMENT');
    JToolbarHelper::unpublishList('unpublish', 'COM_JOOMGALLERY_COMMAN_TOOLBAR_UNPUBLISH_COMMENT');
    JToolbarHelper::custom('approve', 'upload.png', 'upload_f2.png', 'COM_JOOMGALLERY_COMMAN_TOOLBAR_APPROVE_COMMENT');
    JToolbarHelper::divider();
    JToolbarHelper::deleteList('', 'remove', 'COM_JOOMGALLERY_COMMAN_TOOLBAR_REMOVE_COMMENT');
    JToolbarHelper::divider();
  }
}