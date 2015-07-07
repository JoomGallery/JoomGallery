<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/userpanel/view.html.php $
// $Id: view.html.php 4224 2013-04-22 15:46:14Z erftralle $
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
 * HTML View class for the user panel view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewUserpanel extends JoomGalleryView
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
    if(!$this->_config->get('jg_userspace'))
    {
      $msg = JText::_('JERROR_ALERTNOAUTHOR');

      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), $msg, 'notice');
    }

    // Additional security check for unregistered users
    if(!$this->_user->get('id') && !$this->_config->get('jg_unregistered_permissions'))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED'), 'notice');
    }

    $params = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'));
    }

    // Header and footer
    JoomHelper::prepareParams($params);

    $this->pathway = JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL');

    $this->backtarget = JRoute::_('index.php?view=gallery');
    $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');

    // Get number of images and hits in gallery
    $numbers  = JoomHelper::getNumberOfImgHits();
    $this->numberofpics = $numbers[0];
    $this->numberofhits = $numbers[1];

    // Load modules at position 'top'
    $this->modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($this->modules['top']))
    {
      $params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $this->modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($this->modules['btm']))
    {
      $params->set('show_btm_modules', 1);
    }

    // Display button 'Upload' only if there is at least
    // one category into which the user is allowed to upload
    if($this->_config->get('jg_disableunrequiredchecks') || count(JoomHelper::getAuthorisedCategories('joom.upload')))
    {
      $params->set('show_upload_button', 1);
    }
    else
    {
      $params->set('show_upload_button', 0);
    }

    // Display button 'Categories' if the current user is allowed
    // to create categories or if there are categories owned by him
    if(   $this->_user->authorise('core.create', _JOOM_OPTION)
      ||  $this->_config->get('jg_disableunrequiredchecks')
      ||  count(JoomHelper::getAuthorisedCategories('core.create'))
      ||  count($this->get('Categories'))
      )
    {
      $params->set('show_categories_button', 1);
    }
    else
    {
      $params->set('show_categories_button', 0);
    }

    // Get data from the model
    $this->total = $this->get('Total');
    $this->state = $this->get('State');

    if($this->state->get('list.start') >= $this->total)
    {
        // This may happen for instance when an image has been deleted on a page with just one entry
        $limitstart = ($this->total > 0 && $this->total > $this->state->get('list.limit')) ? (floor(($this->total - 1) / $this->state->get('list.limit')) * $this->state->get('list.limit')) : 0;
        $this->state->set('list.start', $limitstart);

    }

    $this->slimitstart = ($this->state->get('list.start') > 0 ? '&limitstart='.$this->state->get('list.start') : '');

    $this->items      = $this->get('Images');
    $this->pagination = $this->get('Pagination');

    if(!$this->total)
    {
      if($this->state->get('filter.inuse'))
      {
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERPANEL_MSG_NO_IMAGES_FOUND_MATCHING_YOUR_QUERY'));
      }
      else
      {
        // Guests cannot own any images
        if(!$this->_user->get('id'))
        {
          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERPANEL_GUESTS_CANNOT_OWN_IMAGES'));
        }
        else
        {
          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERPANEL_YOU_DO_NOT_HAVE_IMAGE'));
        }
      }
    }

    $this->lists = array();

    // Filter by type
    $options = array( JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_SELECT_STATE')),
                      JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMON_OPTION_APPROVED_ONLY')),
                      JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_APPROVED_ONLY')),
                      JHTML::_('select.option', 3, JText::_('COM_JOOMGALLERY_COMMON_OPTION_PUBLISHED_ONLY')),
                      JHTML::_('select.option', 4, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_PUBLISHED_ONLY'))
                    );

    $this->lists['filter_state'] = JHTML::_('select.genericlist', $options, 'filter_state',
                                            'class="inputbox" size="1" onchange="form.submit();"',
                                            'value', 'text', $this->state->get('filter.state'));

    foreach($this->items as $key => &$item)
    {
      // Set the title attribute in a tag with title and/or description of image
      // if a box is activated
      if(!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 1)
      {
        $item->atagtitle = JHTML::_('joomgallery.getTitleforATag', $item);
      }
      else
      {
        // Set the imgtitle by default
        $item->atagtitle = 'title="'.$item->imgtitle.'"';
      }

      // Show editor links for that image
      $this->items[$key]->show_edit_icon   = false;
      $this->items[$key]->show_delete_icon = false;
      if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$this->items[$key]->id)
          ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$this->items[$key]->id)
              &&  $this->items[$key]->owner
              &&  $this->items[$key]->owner == $this->_user->get('id')
              )
          )
      )
      {
        $this->items[$key]->show_edit_icon = true;
      }

      if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$this->items[$key]->id))
      {
        $this->items[$key]->show_delete_icon = true;
      }
    }

    // Quick editing
    JText::script('COM_JOOMGALLERY_USERPANEL_DATACHANGED_SUCCESS');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE');
    $this->_doc->addStyleDeclaration( '.toggle-editor, .jg-editor-wrapper > p.label{display:none;}');
		JHtml::_('jquery.framework');
    $this->_doc->addScript($this->_ambit->getScript("userpanel.js"));
    $this->editor = JFactory::getEditor(JFactory::getConfig()->get('editor'));

    $this->params = $params;

    parent::display($tpl);
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
      'ordering' => JText::_('COM_JOOMGALLERY_COMMON_ORDERING'),
      'imgdate' => JText::_('COM_JOOMGALLERY_COMMON_DATE'),
      'imgtitle' => JText::_('COM_JOOMGALLERY_COMMON_TITLE'),
      'hits' => JText::_('COM_JOOMGALLERY_COMMON_HITS'),
      'downloads' => JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADS'),
      'catid' => JText::_('COM_JOOMGALLERY_COMMON_CATEGORY')
    );
  }
}