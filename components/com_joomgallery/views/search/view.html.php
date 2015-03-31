<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/search/view.html.php $
// $Id: view.html.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * HTML View class for the search result view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewSearch extends JoomGalleryView
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
    $params           = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_SEARCH_RESULTS'));
    }

    // Header and footer
    JoomHelper::prepareParams($params);

    $pathway = JText::_('COM_JOOMGALLERY_SEARCH_RESULTS');

    $backtarget = JRoute::_('index.php?view=gallery'); //see above
    $backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');

    // Get number of images and hits in gallery
    $numbers  = JoomHelper::getNumberOfImgHits();

    // Load modules at position 'top'
    $modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($modules['top']))
    {
      $params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($modules['btm']))
    {
      $params->set('show_btm_modules', 1);
    }

    $sstring  = trim(JRequest::getVar('sstring'));
    $rows     = array();
    if(!empty($sstring))
    {
      $rows     = $this->get('SearchResults');
    }

    foreach($rows as $key => $row)
    {
      $rows[$key]->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_detailpic_open'), $row);

      $cropx    = null;
      $cropy    = null;
      $croppos  = null;
      if($this->_config->get('jg_dyncrop'))
      {
        $cropx    = $this->_config->get('jg_dyncropwidth');
        $cropy    = $this->_config->get('jg_dyncropheight');
        $croppos  = $this->_config->get('jg_dyncropposition');
      }
      $row->thumb_src = $this->_ambit->getImg('thumb_url', $row, null, 0, true, $cropx, $cropy, $croppos);

      // Set the title attribute in a tag with title and/or description of image
      // if a box is activated
      if(!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 1)
      {
        $rows[$key]->atagtitle = JHTML::_('joomgallery.getTitleforATag', $row);
      }
      else
      {
        // Set the imgtitle by default
        $rows[$key]->atagtitle = 'title="'.$row->imgtitle.'"';
      }

      if($this->_config->get('jg_showauthor'))
      {
        if($row->imgauthor)
        {
          $rows[$key]->authorowner = $row->imgauthor;
        }
        else
        {
          if($this->_config->get('jg_showowner'))
          {
            $rows[$key]->authorowner = JHTML::_('joomgallery.displayname', $row->owner);
          }
          else
          {
            $rows[$key]->authorowner = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
          }
        }
      }

      // Show editor links for that image
      $rows[$key]->show_edit_icon   = false;
      $rows[$key]->show_delete_icon = false;
      if(   $this->_config->get('jg_showsearcheditorlinks') == 1
         && $this->_config->get('jg_userspace') == 1
        )
      {
        if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$rows[$key]->id)
            ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$rows[$key]->id)
                &&  $rows[$key]->owner
                &&  $rows[$key]->owner == $this->_user->get('id')
                )
            )
        )
        {
          $rows[$key]->show_edit_icon = true;
        }

        if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$rows[$key]->id))
        {
          $rows[$key]->show_delete_icon = true;
        }
      }
    }

    // Download icon
    if($this->_config->get('jg_download') && $this->_config->get('jg_showsearchdownload'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_download_unreg'))
      {
        $params->set('show_download_icon', 1);
      }
      else
      {
        if($this->_config->get('jg_download_hint'))
        {
          $params->set('show_download_icon', -1);
        }
      }
    }

    // Favourites icon
    if(!$params->get('disable_global_info') && $this->_config->get('jg_favourites') && $this->_config->get('jg_showsearchfavourite'))
    {
      if(   $this->_user->get('id')
         || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
        )
      {
        if(    $this->_config->get('jg_usefavouritesforzip')
           || ($this->_config->get('jg_usefavouritesforpubliczip') && !$this->_user->get('id'))
          )
        {
          $params->set('show_favourites_icon', 2);
        }
        else
        {
          $params->set('show_favourites_icon', 1);
        }
      }
      else
      {
        if(($this->_config->get('jg_favouritesshownotauth') == 1))
        {
          if($this->_config->get('jg_usefavouritesforzip'))
          {
            $params->set('show_favourites_icon', -2);
          }
          else
          {
            $params->set('show_favourites_icon', -1);
          }
        }
      }
    }

    // Report icon
    if($this->_config->get('jg_report_images') && $this->_config->get('jg_search_report_images'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_report_unreg'))
      {
        $params->set('show_report_icon', 1);

        JHTML::_('behavior.modal');
      }
      else
      {
        if($this->_config->get('jg_report_hint'))
        {
          $params->set('show_report_icon', -1);
        }
      }
    }

    $uri = JFactory::getURI();
    $uri->setVar('sstring', $sstring);
    $redirect = '&redirect='.base64_encode($uri->toString());

    $this->assignRef('params',          $params);
    $this->assignRef('rows',            $rows);
    $this->assignRef('sstring',         $sstring);
    $this->assignRef('pathway',         $pathway);
    $this->assignRef('modules',         $modules);
    $this->assignRef('backtarget',      $backtarget);
    $this->assignRef('backtext',        $backtext);
    $this->assignRef('numberofpics',    $numbers[0]);
    $this->assignRef('numberofhits',    $numbers[1]);
    $this->assignRef('redirect',        $redirect);

    parent::display($tpl);
  }
}