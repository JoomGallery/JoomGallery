<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/usercategories/view.html.php $
// $Id: view.html.php 4391 2014-06-08 12:50:10Z erftralle $
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
 * HTML View class for the user categories list view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewUsercategories extends JoomGalleryView
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
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'), 'index.php?view=userpanel');
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES'));
    }

    // Header and footer
    JoomHelper::prepareParams($params);

    $this->pathway = null;
    if($this->_config->get('jg_showpathway'))
    {
      $this->pathway  = '<a href="'.JRoute::_('index.php?view=userpanel').'">'.JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL').'</a>';
      $this->pathway .= ' &raquo; '.JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES');
    }

    $this->backtarget = JRoute::_('index.php?view=userpanel');
    $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_USER_PANEL');

    // Get number of images and hits in gallery
    $numbers             = JoomHelper::getNumberOfImgHits();
    $this->numberofpics  = $numbers[0];
    $this->numberofhits  = $numbers[1];

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

    // Show upload quota
    if($this->_config->get('jg_newpicnote') && $this->_user->get('id'))
    {
      $params->set('show_categories_notice', 1);
    }

    // Get data from the model
    $this->total      = $this->get('Total');
    $this->state      = $this->get('State');

    if($this->state->get('list.start') >= $this->total)
    {
      // This may happen for instance when a category has been deleted on a page with just one entry
      $limitstart = ($this->total > 0 && $this->total > $this->state->get('list.limit')) ? (floor(($this->total - 1) / $this->state->get('list.limit')) * $this->state->get('list.limit')) : 0;
      $this->state->set('list.start', $limitstart);
    }
    $this->slimitstart = ($this->state->get('list.start') > 0 ? '&limitstart='.$this->state->get('list.start') : '');

    // Get data from the model
    $this->categoryNumber = $this->get('CategoryNumber');
    $this->items          = $this->get('Categories');
    $this->pagination     = $this->get('Pagination');

    // Enqueue a message in case no categories were found
    if(!$this->total)
    {
      if($this->state->get('filter.inuse'))
      {
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERPANEL_MSG_NO_CATEGORIES_FOUND_MATCHING_YOUR_QUERY'));
      }
      else
      {
        // Guests cannot own any categories
        if(!$this->_user->get('id'))
        {
          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERCATEGORIES_GUESTS_CANNOT_OWN_CATEGORIES'));
        }
        else
        {
          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_USERCATEGORIES_YOU_NOT_HAVE_CATEGORY'));
        }
      }
    }

    // Show the button to create a new category only for users
    // with create permissions and who have not reached the limits
    if( (     $this->_user->authorise('core.create', _JOOM_OPTION)
            ||  $this->_config->get('jg_disableunrequiredchecks')
            ||  count(JoomHelper::getAuthorisedCategories('core.create'))
        )
            &&
            ($this->_config->get('jg_maxusercat') - $this->categoryNumber) > 0
      )
    {
      $params->set('show_category_button', 1);
    }

    // Preprocess the list of items to find ordering divisions.
    foreach ($this->items as &$item)
    {
      $this->ordering[$item->parent_id][] = $item->cid;
    }

    $this->lists = array();

    // Filter by state
    $options = array( JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_SELECT_STATE')),
            JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_COMMON_OPTION_PUBLISHED_ONLY')),
            JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_COMMON_OPTION_NOT_PUBLISHED_ONLY'))
    );

    $this->lists['filter_state'] = JHTML::_( 'select.genericlist', $options, 'filter_state',
                                             'class="inputbox" size="1" onchange="form.submit();"',
                                             'value', 'text', $this->state->get('filter.state')
                                           );

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
                  'c.lft'        => JText::_('COM_JOOMGALLERY_COMMON_ORDERING'),
                  'c.name'       => JText::_('COM_JOOMGALLERY_COMMON_CATEGORY'),
                  'images'       => JText::_('COM_JOOMGALLERY_USERCATEGORIES_IMAGES'),
                  'c.parent_id'  => JText::_('COM_JOOMGALLERY_COMMON_PARENT_CATEGORY')
                );
  }
}