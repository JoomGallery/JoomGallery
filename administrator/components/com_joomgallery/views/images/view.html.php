<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/images/view.html.php $
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
 * HTML View class for the images list view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewImages extends JoomGalleryView
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
    $items      = $this->get('Images');
    $state      = $this->get('State');
    $pagination = $this->get('Pagination');

    $this->assignRef('items',       $items);
    $this->assignRef('state',       $state);
    $this->assignRef('pagination',  $pagination);

    if($state->get('filter.inuse') && !$this->get('Total'))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_IMGMAN_MSG_NO_IMAGES_FOUND_MATCHING_YOUR_QUERY'));
    }

    $this->addToolbar();
    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }

  protected function addToolbar()
  {
    // Get the results for each action
    $canDo = JoomHelper::getActions();

    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_MANAGER'), 'images');

    if(($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('joom.upload') || count(JoomHelper::getAuthorisedCategories('joom.upload'))) && $this->pagination->total)
    {
      JToolbarHelper::addNew('new');
    }

    if(($canDo->get('core.edit') || $canDo->get('core.edit.own')) && $this->pagination->total)
    {
      JToolbarHelper::editList();
      JToolbarHelper::custom('edit', 'checkbox-partial', 'checkbox-partial', 'JTOOLBAR_BATCH');
      JToolbarHelper::custom('showmove', 'move.png', 'move.png', 'COM_JOOMGALLERY_COMMON_TOOLBAR_MOVE');
      JToolbarHelper::custom('recreate', 'refresh.png', 'refresh.png', 'COM_JOOMGALLERY_COMMON_TOOLBAR_RECREATE');
      JToolbarHelper::divider();
    }

    if($canDo->get('core.edit.state') && $this->pagination->total)
    {
      JToolbarHelper::publishList('publish', JText::_('COM_JOOMGALLERY_COMMON_PUBLISH'));
      JToolbarHelper::unpublishList('unpublish', JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH'));
      JToolbarHelper::custom('approve', 'upload.png', 'upload_f2.png', 'COM_JOOMGALLERY_IMGMAN_TOOLBAR_APPROVE');
      JToolbarHelper::divider();
    }

    //if($canDo->get('core.delete'))
    //{
      JToolbarHelper::deleteList('', 'remove');
    //}

    // Add filter by state
    $options  = array(JHtml::_('select.option', 0, JText::_('JOPTION_SELECT_PUBLISHED')),
                      JHtml::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMON_OPTION_PUBLISHED_ONLY')),
                      JHtml::_('select.option', 2, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_PUBLISHED_ONLY')),
                      JHtml::_('select.option', 3, JText::_('COM_JOOMGALLERY_COMMON_OPTION_APPROVED_ONLY')),
                      JHtml::_('select.option', 4, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_APPROVED_ONLY')),
                      JHtml::_('select.option', 5, JText::_('COM_JOOMGALLERY_COMMON_OPTION_REJECTED_ONLY')));

    JHtmlSidebar::addFilter(
      JText::_('JOPTION_SELECT_PUBLISHED'),
      'filter_state',
      JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.state'), false),
      true
    );

    // Add filter by category
    if(!$this->_config->get('jg_ajaxcategoryselection'))
    {
      // TODO must find a better way instead of removing the tags, what about switching to AJAX boxes -> doesn't work
      $options = preg_replace('~^<select[^>]*+>\s*~', '', trim(JHtml::_('joomselect.categorylist', $this->state->get('filter.category'), 'filter_category', 'class="inputbox" onchange="document.id(\'adminForm\').submit()"', null, '- ', 'filter')));
      $options = preg_replace('~\s*</select>$~', '', $options);
      JHtmlSidebar::addFilter(
        JText::_('COM_JOOMGALLERY_COMMON_ALL'),
        'filter_category',
        $options,
        true
      );
    }

    // Add filter by access
    JHtmlSidebar::addFilter(
      JText::_('JOPTION_SELECT_ACCESS'),
      'filter_access',
      JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
    );

    // Add filter by owner
    $html = JHtml::_('joomselect.userspopup', 'filter_owner', $this->state->get('filter.owner'), 'document.id(\'adminForm\').submit();');
    if(strpos($html, '<select') !== false)
    {
      // TODO must find a better way instead of removing the tags
      $options = preg_replace('~^<select[^>]*+>\s*~', '', trim($html));
      $options = preg_replace('~\s*</select>$~', '', $options);
      JHtmlSidebar::addFilter(
        JText::_('COM_JOOMGALLERY_COMMON_OPTION_SELECT_OWNER'),
        'filter_owner',
        $options,
        true
      );
    }

    // Add filter by type
    $options  = array(JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_OPTION_SELECT_TYPE')),
                                JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMON_OPTION_USER_UPLOADED_ONLY')),
                                JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_COMMON_OPTION_ADMIN_UPLOADED_ONLY')));
    JHtmlSidebar::addFilter(
      JText::_('COM_JOOMGALLERY_COMMON_OPTION_SELECT_TYPE'),
      'filter_type',
      JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.type'), false),
      true
    );
  }

  /**
   * Returns an array of fields the table can be sorted by
   *
   * @return  array  Array containing the field name to sort by as the key and display text as value
   *
   * @since   3.0
   */
  protected function getSortFields()
  {
    return array(
      'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
      'a.imgtitle' => JText::_('COM_JOOMGALLERY_COMMON_TITLE'),
      'a.published' => JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED'),
      'a.approved' => JText::_('COM_JOOMGALLERY_COMMON_APPROVED'),
      'category_name' => JText::_('COM_JOOMGALLERY_COMMON_CATEGORY'),
      'access_level' => JText::_('COM_JOOMGALLERY_COMMON_ACCESS'),
      'a.owner' => JText::_('COM_JOOMGALLERY_COMMON_OWNER'),
      'a.imgauthor' => JText::_('COM_JOOMGALLERY_COMMON_AUTHOR'),
      'a.imgdate' => JText::_('COM_JOOMGALLERY_COMMON_DATE'),
      'a.hits' => JText::_('COM_JOOMGALLERY_IMGMAN_HITS'),
      'a.downloads' => JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADS'),
      'a.id' => JText::_('COM_JOOMGALLERY_COMMON_ID')
    );
  }
}