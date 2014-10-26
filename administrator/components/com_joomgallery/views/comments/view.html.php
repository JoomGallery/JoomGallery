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
    $state      = $this->get('State');
    $items      = $this->get('Comments');
    $pagination = $this->get('Pagination');

    $this->assignRef('items',       $items);
    $this->assignRef('pagination',  $pagination);
    $this->assignRef('state',       $state);

    if($state->get('filter.inuse') && !$this->get('Total'))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMAN_MSG_NO_COMMENTS_FOUND_MATCHING_YOUR_QUERY'));
    }

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

    // Add filter by state
    $options = array(JHtml::_('select.option', 0, JText::_('JOPTION_SELECT_PUBLISHED')),
                     JHtml::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMAN_OPTION_PUBLISHED_ONLY')),
                     JHtml::_('select.option', 2, JText::_('COM_JOOMGALLERY_COMMAN_OPTION_NOT_PUBLISHED_ONLY')),
                     JHtml::_('select.option', 3, JText::_('COM_JOOMGALLERY_COMMAN_OPTION_APPROVED_ONLY')),
                     JHtml::_('select.option', 4, JText::_('COM_JOOMGALLERY_COMMAN_OPTION_NOT_APPROVED_ONLY')));

    JHtmlSidebar::addFilter(
      JText::_('JOPTION_SELECT_PUBLISHED'),
      'filter_state',
      JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.state'), true),
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
      'user' => JText::_('COM_JOOMGALLERY_COMMON_AUTHOR'),
      'c.cmttext' => JText::_('COM_JOOMGALLERY_COMMAN_TEXT'),
      'c.published' => JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED'),
      'c.approved' => JText::_('COM_JOOMGALLERY_COMMON_APPROVED'),
      'i.imgtitle' => JText::_('COM_JOOMGALLERY_COMMON_IMAGE'),
      'c.cmtip' => JText::_('COM_JOOMGALLERY_COMMAN_IP'),
      'c.cmtdate' => JText::_('COM_JOOMGALLERY_COMMON_DATE'),
      'c.cmtid' => JText::_('COM_JOOMGALLERY_COMMON_ID')
    );
  }
}