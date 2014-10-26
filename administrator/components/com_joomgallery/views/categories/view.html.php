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
    $items      = $this->get('Categories');
    $state      = $this->get('State');
    $pagination = $this->get('Pagination');

    $this->assignRef('items',       $items);
    $this->assignRef('state',       $state);
    $this->assignRef('pagination',  $pagination);

    if($state->get('filter.inuse') && !$this->get('Total'))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_CATMAN_MSG_NO_CATEGORIES_FOUND_MATCHING_YOUR_QUERY'));
    }

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
      JToolbarHelper::publishList('publish', JText::_('COM_JOOMGALLERY_COMMON_PUBLISH'));
      JToolbarHelper::unpublishList('unpublish', JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH'));
      JToolbarHelper::divider();
    }

    if(($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.delete') || count(JoomHelper::getAuthorisedCategories('core.delete'))) && $this->pagination->total)
    {
      JToolbarHelper::deleteList('','remove');
      JToolbarHelper::divider();
    }

    $options = array( JHtml::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMON_OPTION_PUBLISHED_ONLY')),
                      JHtml::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_PUBLISHED_ONLY')));
    JHtmlSidebar::addFilter(
      JText::_('JOPTION_SELECT_PUBLISHED'),
      'filter_published',
      JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.published'), true)
    );

    JHtmlSidebar::addFilter(
      JText::_('JOPTION_SELECT_ACCESS'),
      'filter_access',
      JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
    );

    $options = array( JHtml::_('select.option', 1, JText::_('COM_JOOMGALLERY_CATMAN_OPTION_USERCATEGORIES_ONLY')),
                      JHtml::_('select.option', 2, JText::_('COM_JOOMGALLERY_CATMAN_OPTION_BACKENDCATEGORIES_ONLY')));
    JHtmlSidebar::addFilter(
      JText::_('COM_JOOMGALLERY_COMMON_OPTION_SELECT_TYPE'),
      'filter_type',
      JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.type'), true)
    );
  }

  /**
   * Returns an array of fields the table can be sorted by
   *
   * @return  array Array containing the field name to sort by as the key and display text as value
   * @since   3.0
   */
  protected function getSortFields()
  {
    return array(
      'c.lft' => JText::_('JGRID_HEADING_ORDERING'),
      'c.published' => JText::_('JSTATUS'),
      'c.name' => JText::_('JGLOBAL_TITLE'),
      'access_level' => JText::_('JGRID_HEADING_ACCESS'),
      'c.cid' => JText::_('JGRID_HEADING_ID'),
      'c.alias' => 'Alias',
      'c.parent_id' => 'Parent',
      'c.owner' => 'Owner',
      //'c.level' => 'Level'
    );
  }
}