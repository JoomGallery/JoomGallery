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
    $this->items          = $this->get('Images');
    $this->state          = $this->get('State');
    $this->pagination     = $this->get('Pagination');
    $this->filterForm     = $this->get('FilterForm');
    $this->activeFilters  = $this->get('ActiveFilters');

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
      JToolbarHelper::publishList('publish', 'COM_JOOMGALLERY_COMMON_PUBLISH');
      JToolbarHelper::unpublishList('unpublish', 'COM_JOOMGALLERY_COMMON_UNPUBLISH');
      JToolbarHelper::custom('approve', 'upload.png', 'upload_f2.png', 'COM_JOOMGALLERY_IMGMAN_TOOLBAR_APPROVE');
      JToolbarHelper::divider();
    }

    //if($canDo->get('core.delete'))
    //{
      JToolbarHelper::deleteList('', 'remove');
    //}

  }

  /**
   * Returns feature/unfeature buttons for the images list view
   *
   * @access  public
   * @param   integer       $value     The featured state.
   * @param   integer       $i         The row index
   * @param   string|array  $prefix    An optional task prefix or an array of options
   * @param   boolean       $enabled   An optional setting for access control on the action.
   * @param   string        $checkbox  An optional prefix for checkboxes.
   * @return  string  The HTML markup
   * @since   3.3
   */
  public function featured($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb')
  {
    if (is_array($prefix))
    {
      $options = $prefix;
      $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
      $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
      $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
    }

    $states = array(
      0 => array('feature', '', 'JGLOBAL_TOGGLE_FEATURED', 'COM_JOOMGALLERY_COMMON_UNFEATURED', true, 'unfeatured', 'unfeatured'),
      1 => array('unfeature', '', 'JGLOBAL_TOGGLE_FEATURED', 'COM_JOOMGALLERY_COMMON_FEATURED', true, 'featured', 'featured'),
    );

    return JHtml::_('jgrid.state', $states, $value, $i, $prefix, $enabled, true, $checkbox);
  }
}