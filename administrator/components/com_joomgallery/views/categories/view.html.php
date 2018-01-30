<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/categories/view.html.php $
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
 * HTML View class for the categories list view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewCategories extends JoomGalleryView
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
    // Get data from the model
    $this->items         = $this->get('Categories');
    $this->state         = $this->get('State');
    $this->pagination    = $this->get('Pagination');
    $this->filterForm    = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');

    $this->addToolbar();

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }

  /**
   * Add the toolbar and toolbar title.
   *
   * @access  protected
   * @return  void
   *
   * @since 2.0
   */
  protected function addToolbar()
  {
    // Get the results for each action
    $canDo = JoomHelper::getActions();

    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_CATMAN_CATEGORY_MANAGER'), 'folder');

    if($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.create') || count(JoomHelper::getAuthorisedCategories('core.create')))
    {
      JToolbarHelper::addNew('new');
    }

    if(($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.edit') || count(JoomHelper::getAuthorisedCategories('core.edit'))) && $this->pagination->total)
    {
      JToolbarHelper::editList('edit');
      JHtml::_('bootstrap.modal', 'collapseModal');
      $title = JText::_('JTOOLBAR_BATCH');
      $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
                  <i class=\"icon-checkbox-partial\" title=\"$title\"></i>
                  $title</button>";
      JToolBar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
      JToolbarHelper::divider();
    }

    if(($this->_config->get('jg_disableunrequiredchecks') || count(JoomHelper::getAuthorisedCategories('core.edit.state'))) && $this->pagination->total)
    {
      JToolbarHelper::publishList('publish', 'COM_JOOMGALLERY_COMMON_PUBLISH');
      JToolbarHelper::unpublishList('unpublish', 'COM_JOOMGALLERY_COMMON_UNPUBLISH');
      JToolbarHelper::divider();
    }

    if(($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.delete') || count(JoomHelper::getAuthorisedCategories('core.delete'))) && $this->pagination->total)
    {
      JToolbarHelper::deleteList('','remove');
      JToolbarHelper::divider();
    }
  }
}