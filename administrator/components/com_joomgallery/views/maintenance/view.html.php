<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/maintenance/view.html.php $
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
 * HTML View class for the maintenance manager view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewMaintenance extends JoomGalleryView
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
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_MAIMAN_MAINTENANCE_MANAGER'), 'wrench');

    $this->_doc->addStyleDeclaration('    .icon-32-refresh {
      background-image:url(templates/khepri/images/toolbar/icon-32-refresh.png);
    }');

    $lists = array();

    jimport('joomla.html.pane');
    $tabs = array('images'      => 0,
                  'categories'  => 1,
                  'orphans'     => 2,
                  'folders'     => 3,
                  'comments'    => 4,
                  'favourites'  => 5,
                  'nametags'    => 6,
                  'votes'       => 7,
                  'database'    => 8
                  );
    $tab  = $this->_mainframe->getUserStateFromRequest('joom.maintenance.tab', 'tab', 'images', 'cmd');
    if(!$tab || !isset($tabs[$tab]))
    {
      $tab = 'images';
    }

    $checked = $this->_mainframe->getUserState('joom.maintenance.checked');

    $state  = $this->get('State');

    switch($tab)
    {
      case 'categories':
        // Select list of the batch jobs for the categories
        $b_options              = array();
        $b_options[]            = JHTML::_('select.option', '',                   JText::_('COM_JOOMGALLERY_MAIMAN_SELECT_JOB'));
        $b_options[]            = JHTML::_('select.option', 'setuser',            JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_SET_NEW_USER'));
        $b_options[]            = JHTML::_('select.option', 'addorphanedfolders', JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ADD_ORPHANED_FOLDERS'));
        $b_options[]            = JHTML::_('select.option', 'create',             JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_CREATE_FOLDERS'));
        $b_options[]            = JHTML::_('select.option', 'removecategory',     JText::_('COM_JOOMGALLERY_MAIMAN_CT_OPTION_REMOVE_CATEGORIES'));
        $lists['cat_jobs']      = JHTML::_( 'select.genericlist', $b_options, 'job',
                                            'class="inputbox" size="1" onchange="joom_selectbatchjob(this.value);"',
                                            'value', 'text');

        $f_options              = array();
        $f_options[]            = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_OPTION_ALL_CATEGORIES'));
        $f_options[]            = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_THUMB_FOLDER_ONLY'));
        $f_options[]            = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_IMG_FOLDER_ONLY'));
        $f_options[]            = JHTML::_('select.option', 3, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_ORIG_FOLDER_ONLY'));
        $f_options[]            = JHTML::_('select.option', 4, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_USER_ONLY'));
        $f_options[]            = JHTML::_('select.option', 5, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_PARENT_CATEGORY_ONLY'));
        $lists['cat_filter']    = JHTML::_( 'select.genericlist', $f_options, 'filter_type',
                                            'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                            'value', 'text', $state->get('filter.type'));

        if(!is_null($checked))
        {
          // Get data from the model
          $items  = $this->get('Categories');
        }
        break;
      case 'orphans':
        // Select list of the batch jobs for the orphans
        $b_options              = array();
        $b_options[]            = JHTML::_('select.option', '',                 JText::_('COM_JOOMGALLERY_MAIMAN_SELECT_JOB'));
        $b_options[]            = JHTML::_('select.option', 'addorphan',        JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ADD_ORPHANS'));
        $b_options[]            = JHTML::_('select.option', 'applysuggestions', JText::_('COM_JOOMGALLERY_MAIMAN_APPLY_SUGGESTIONS'));
        $b_options[]            = JHTML::_('select.option', 'deleteorphan',     JText::_('COM_JOOMGALLERY_MAIMAN_REMOVE_ORPHANS'));
        $lists['orphan_jobs']   = JHTML::_( 'select.genericlist', $b_options, 'job',
                                            'class="inputbox" size="1"',
                                            'value', 'text');

        $p_options                = array();
        $p_options[]              = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ALL_FILES'));
        $p_options[]              = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_PROPOSAL_AVAILABLE'));
        $p_options[]              = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_NO_PROPOSAL_AVAILABLE'));
        $lists['orphan_proposal'] = JHTML::_( 'select.genericlist', $p_options, 'filter_proposal',
                                              'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                              'value', 'text', $state->get('filter.proposal'));

        $f_options                = array();
        $f_options[]              = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ALL_FILES'));
        $f_options[]              = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_THUMB_ONLY'));
        $f_options[]              = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_IMG_ONLY'));
        $f_options[]              = JHTML::_('select.option', 3, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_ORIG_ONLY'));
        $f_options[]              = JHTML::_('select.option', 4, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_UNKNOWN_ONLY'));
        $lists['orphan_filter']   = JHTML::_( 'select.genericlist', $f_options, 'filter_type',
                                              'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                              'value', 'text', $state->get('filter.type'));

        if(!is_null($checked))
        {
          // Get data from the model
          $items  = $this->get('Orphans');
        }
        break;
      case 'folders':
        // Select list of the batch jobs for the orphans
        $b_options              = array();
        $b_options[]            = JHTML::_('select.option', '',                         JText::_('COM_JOOMGALLERY_MAIMAN_SELECT_JOB'));
        $b_options[]            = JHTML::_('select.option', 'addorphanedfolder',        JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ADD_ORPHANED_FOLDERS'));
        $b_options[]            = JHTML::_('select.option', 'applyfoldersuggestions',   JText::_('COM_JOOMGALLERY_MAIMAN_APPLY_SUGGESTIONS'));
        $b_options[]            = JHTML::_('select.option', 'deleteorphanedfolder',     JText::_('COM_JOOMGALLERY_MAIMAN_OF_OPTION_REMOVE_ORPHANED_FOLDERS'));
        $lists['folder_jobs']   = JHTML::_( 'select.genericlist', $b_options, 'job',
                                            'class="inputbox" size="1"',
                                            'value', 'text');

        $p_options                = array();
        $p_options[]              = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_MAIMAN_OF_OPTION_ALL_FOLDERS'));
        $p_options[]              = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OF_OPTION_PROPOSAL_AVAILABLE_FOLDERS'));
        $p_options[]              = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OF_OPTION_NO_PROPOSAL_AVAILABLE_FOLDERS'));
        $lists['folder_proposal'] = JHTML::_( 'select.genericlist', $p_options, 'filter_proposal',
                                              'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                              'value', 'text', $state->get('filter.proposal'));

        $f_options                = array();
        $f_options[]              = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ALL_FILES'));
        $f_options[]              = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_THUMB_ONLY'));
        $f_options[]              = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_IMG_ONLY'));
        $f_options[]              = JHTML::_('select.option', 3, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_ORIG_ONLY'));
        $f_options[]              = JHTML::_('select.option', 4, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_TYPE_UNKNOWN_ONLY'));
        $lists['folder_filter']   = JHTML::_( 'select.genericlist', $f_options, 'filter_type',
                                              'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                              'value', 'text', $state->get('filter.type'));

        if(!is_null($checked))
        {
          // Get data from the model
          $items = $this->get('OrphanedFolders');
        }
        break;
      default:
        // Select list of the batch jobs for the images
        $b_options              = array();
        $b_options[]            = JHTML::_('select.option', '',           JText::_('COM_JOOMGALLERY_MAIMAN_SELECT_JOB'));
        $b_options[]            = JHTML::_('select.option', 'setuser',    JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_SET_NEW_USER'));
        $b_options[]            = JHTML::_('select.option', 'addorphans', JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_ADD_ORPHANS'));
        $b_options[]            = JHTML::_('select.option', 'recreate',   JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_RECREATE'));
        $b_options[]            = JHTML::_('select.option', 'remove',     JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_REMOVE_IMAGES'));
        $lists['img_jobs']    = JHTML::_( 'select.genericlist', $b_options, 'job',
                                            'class="inputbox" size="1" onchange="joom_selectbatchjob(this.value);"',
                                            'value', 'text');

        $f_options              = array();
        $f_options[]            = JHTML::_('select.option', 0, JText::_('COM_JOOMGALLERY_COMMON_OPTION_ALL_IMAGES'));
        $f_options[]            = JHTML::_('select.option', 1, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_THUMB_ONLY'));
        $f_options[]            = JHTML::_('select.option', 2, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_IMG_ONLY'));
        $f_options[]            = JHTML::_('select.option', 3, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_ORIG_ONLY'));
        $f_options[]            = JHTML::_('select.option', 4, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_USER_ONLY'));
        $f_options[]            = JHTML::_('select.option', 5, JText::_('COM_JOOMGALLERY_MAIMAN_OPTION_MISSING_CATEGORY_ONLY'));
        $lists['img_filter']    = JHTML::_( 'select.genericlist', $f_options, 'filter_type',
                                            'class="inputbox" size="1" onchange="document.adminForm.submit();"',
                                            'value', 'text', $state->get('filter.type'));

        if(!is_null($checked))
        {
          // Get data from the model
          $items = $this->get('Images');
        }
        break;
    }

    if(!is_null($checked))
    {
      $this->items = $items;
      $this->pagination = $this->get('Pagination');

      if($state->get('filter.inuse') && !$this->get('Total'))
      {
        $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_ITEMS_FOUND_MATCHING_YOUR_QUERY'));
      }
    }

    $information = $this->get('Information');
    $warning  = '<img src="'.$this->_ambit->getIcon('error.png').'" border="0" alt="Warning" height="11" width="11" />';
    foreach($information as $key => $found)
    {
      if($found)
      {
        $information[$key] = '&nbsp;'.$warning;
      }
      else
      {
        $information[$key] = '';
      }
    }

    $this->assignRef('current_tab',   $tab);
    $this->assignRef('startOffset',   $tabs[$tab]);
    $this->assignRef('checked',       $checked);
    $this->assignRef('information',   $information);
    $this->assignRef('lists',         $lists);
    $this->assignRef('state',         $state);

    $this->_doc->addScript($this->_ambit->getScript('maintenance.js'));

    // Language
    JText::script('COM_JOOMGALLERY_MAIMAN_ALERT_RESET_VOTES_CONFIRM');
    JText::script('COM_JOOMGALLERY_MAIMAN_CM_ALERT_RESET_COMMENTS_CONFIRM');
    JText::script('COM_JOOMGALLERY_MAIMAN_FV_ALERT_RESET_FAVOURITES_CONFIRM');
    JText::script('COM_JOOMGALLERY_MAIMAN_NT_ALERT_RESET_NAMETAGS_CONFIRM');

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }

  function cross($title = 'COM_JOOMGALLERY_MAIMAN_MISSING')
  {
    return '<i class="hasTooltip icon-unpublish" title="'.JText::_($title).'"></i>';
  }

  function tick($title = 'COM_JOOMGALLERY_MAIMAN_AVAILABLE')
  {
    return '<i class="hasTooltip icon-publish" title="'.JText::_($title).'"></i>';
  }

  function correct($task, $id, $title = 'Apply', $js = false, $extra = null)
  {
    if($js)
    {
      $link = $js;
    }
    else
    {
      $link = 'index.php?option='._JOOM_OPTION.'&amp;controller=maintenance&amp;task='.$task.'&amp;cid='.$id.$extra;
    }

    return '<span class="hasTooltip" title="'.$title.'"><a href="'.$link.'">
              <img src="'.$this->_ambit->getIcon('joom_maintenance.png').'" border="0" alt="'.$title.'" /></a></span>';
  }

  function warning($title, $text)
  {
    return '<span class="hasTooltip" title="'.$title.'::'.$text.'"><img src="'.$this->_ambit->getIcon('error.png').'" alt="Warning" /></span>';
  }
}