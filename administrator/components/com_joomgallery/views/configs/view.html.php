<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/configs/view.html.php $
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
 * HTML View class for the configs list view
 *
 * @package JoomGallery
 * @since   2.0
 */
class JoomGalleryViewConfigs extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @access  public
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   2.0
   */
  function display($tpl = null)
  {
    // Get data from the model
    $this->items      = $this->get('Configs');
    $this->allitems   = $this->get('AllConfigs');
    $this->state      = $this->get('State');
    $this->pagination = $this->get('Pagination');
    $this->usergroups = $this->get('Usergroups');

    $this->addToolbar();
    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }

  /**
   * Add the toolbar and toolbar title.
   *
   * @return  void
   * @since  2.0
   */
  protected function addToolbar()
  {
    require_once JPATH_COMPONENT.'/includes/popup.php';

    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_CONFIGS_CONFIGURATION_MANAGER'), 'equalizer');

    $toolbar = JToolbar::getInstance('toolbar');
    $toolbar->appendButton('Popup', 'new', 'JTOOLBAR_NEW', 'index.php?option='._JOOM_OPTION.'&amp;controller=config&amp;layout=new&amp;tmpl=component', 400, 350, 0, 0, '', 'COM_JOOMGALLERY_CONFIGS_NEW_HEADING', 'jg-new-popup', 'new');

    JToolbarHelper::editList('edit');

    JToolbarHelper::deleteList('','remove');
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
      'c.ordering' => JText::_('JGRID_HEADING_ORDERING'),
      'g.title' => JText::_('COM_JOOMGALLERY_CONFIGS_TITLE'),
      'g.lft' => JText::_('COM_JOOMGALLERY_CONFIGS_GROUP'),
      'c.id' => JText::_('JGRID_HEADING_ID')
    );
  }
}