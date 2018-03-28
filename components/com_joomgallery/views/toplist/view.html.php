<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/toplist/view.html.php $
// $Id: view.html.php 4082 2013-02-12 14:46:02Z chraneco $
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
 * HTML View class for the toplist view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewToplist extends JoomGalleryView
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
    $this->params = $this->_mainframe->getParams();

    // Header and footer
    JoomHelper::prepareParams($this->params);

    $this->backtarget = JRoute::_('index.php?view=gallery'); //see above
    $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');

    // Get number of images and hits in gallery
    $numbers            = JoomHelper::getNumberOfImgHits();
    $this->numberofpics = $numbers[0];
    $this->numberofhits = $numbers[1];

    // Load modules at position 'top'
    $this->modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($this->modules['top']))
    {
      $this->params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $this->modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($this->modules['btm']))
    {
      $this->params->set('show_btm_modules', 1);
    }

    $this->type = $this->_mainframe->input->getCmd('type');
    switch($this->type)
    {
      case 'lastcommented':
        $this->rows    = $this->get('LastCommented');
        $this->title   = JText::sprintf('COM_JOOMGALLERY_TOPLIST_LAST_COMMENTED_IMAGE', $this->_config->get('jg_toplist'));
        $this->pathway = $this->title;
        break;
      case 'lastadded':
        $this->rows    = $this->get('LastAdded');
        $this->title   = JText::sprintf('COM_JOOMGALLERY_TOPLIST_LAST_ADDED_IMAGE', $this->_config->get('jg_toplist'));
        $this->pathway = $this->title;
        break;
      case 'toprated':
        $this->rows    = $this->get('TopRated');
        $this->title   = JText::sprintf('COM_JOOMGALLERY_TOPLIST_BEST_RATED_IMAGE', $this->_config->get('jg_toplist'));
        $this->pathway = $this->title;
        break;
      default:
        $this->rows    = $this->get('MostViewed');
        $this->title   = JText::sprintf('COM_JOOMGALLERY_TOPLIST_MOST_VIEWED_IMAGE', $this->_config->get('jg_toplist'));
        $this->pathway = $this->title;
        break;
    }

    // Check whether this is the active menu item. This is a
    // special case in addition to code in constructor of parent class
    // because here we have to check the toplist type, too.
    if($this->type == 'lastcommented' || $this->type == 'lastadded' || $this->type == 'toprated')
    {
      $active = $this->_mainframe->getMenu()->getActive();
      if(!$active || strpos($active->link, '&type='.$this->type) === false)
      {
        // Get the default layout from the configuration
        if($layout = $this->_config->get('jg_alternative_layout'))
        {
          $this->setLayout($layout);
        }
      }
    }

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      $breadcrumbs->addItem($this->title);
    }

    // Check whether the (comments) data rows where delivered by a plugin
    if(isset($this->rows[0]->delivered_by_plugin) && $this->rows[0]->delivered_by_plugin)
    {
      $this->params->set('delivered_by_plugin', 1);
    }

    foreach($this->rows as $key => $row)
    {
      $this->rows[$key]->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_detailpic_open'), $row);

      $cropx    = null;
      $cropy    = null;
      $croppos  = null;
      if($this->_config->get('jg_dyncrop'))
      {
        $cropx    = $this->_config->get('jg_dyncropwidth');
        $cropy    = $this->_config->get('jg_dyncropheight');
        $croppos  = $this->_config->get('jg_dyncropposition');
      }
      $this->rows[$key]->thumb_src = $this->_ambit->getImg('thumb_url', $row, null, 0, true, $cropx, $cropy, $croppos);

      // Set the title attribute in a tag with title and/or description of image
      // if a box is activated
      if(!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 1)
      {
        $this->rows[$key]->atagtitle = JHTML::_('joomgallery.getTitleforATag', $row);
      }
      else
      {
        // Set the imgtitle by default
        $this->rows[$key]->atagtitle = 'title="'.$row->imgtitle.'"';
      }

      if($this->_config->get('jg_showauthor'))
      {
        if($row->imgauthor)
        {
          $this->rows[$key]->authorowner = $row->imgauthor;
        }
        else
        {
          if($this->_config->get('jg_showowner'))
          {
            $this->rows[$key]->authorowner = JHTML::_('joomgallery.displayname', $row->owner);
          }
          else
          {
            $this->rows[$key]->authorowner = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
          }
        }
      }

      if(!$this->params->get('delivered_by_plugin'))
      {
        if($this->type == 'lastcommented' && $this->_config->get('jg_showthiscomment'))
        {
          if($row->userid)
          {
            $this->rows[$key]->cmtname = JHTML::_('joomgallery.displayname', $row->userid, false);
          }

          $cmttext = $row->cmttext;
          $cmttext = JoomHelper::processText($cmttext);
          if($this->_config->get('jg_bbcodesupport'))
          {
            $cmttext = JHTML::_('joomgallery.bbdecode', $cmttext);
          }
          if($this->_config->get('jg_smiliesupport'))
          {
            $smileys = JoomHelper::getSmileys();
            foreach($smileys as $i => $sm)
            {
              $cmttext = str_replace($i, '<img src="'.$sm.'" border="0" alt="'.$i.'" title="'.$i.'" />', $cmttext);
            }
          }
          $cmttext = stripslashes($cmttext);

          $this->rows[$key]->processed_cmttext = $cmttext;
        }
      }

      // Show editor links for that image
      $this->rows[$key]->show_edit_icon   = false;
      $this->rows[$key]->show_delete_icon = false;
      if(   $this->_config->get('jg_showtoplisteditorlinks') == 1
         && $this->_config->get('jg_userspace') == 1
        )
      {
        if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$this->rows[$key]->id)
            ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$this->rows[$key]->id)
                &&  $this->rows[$key]->owner
                &&  $this->rows[$key]->owner == $this->_user->get('id')
                )
            )
        )
        {
          $this->rows[$key]->show_edit_icon = true;
        }

        if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$this->rows[$key]->id))
        {
          $this->rows[$key]->show_delete_icon = true;
        }
      }
    }

    // Download Icon
    if($this->_config->get('jg_download') && $this->_config->get('jg_showtoplistdownload'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_download_unreg'))
      {
        $this->params->set('show_download_icon', 1);
      }
      else
      {
        if($this->_config->get('jg_download_hint'))
        {
          $this->params->set('show_download_icon', -1);
        }
      }
    }

    // Favourites icon
    if(!$this->params->get('disable_global_info') && $this->_config->get('jg_favourites') && $this->_config->get('jg_showtoplistfavourite'))
    {
      if(   $this->_user->get('id')
         || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
        )
      {
        if(    $this->_config->get('jg_usefavouritesforzip')
           || ($this->_config->get('jg_usefavouritesforpubliczip') && !$this->_user->get('id'))
          )
        {
          $this->params->set('show_favourites_icon', 2);
        }
        else
        {
          $this->params->set('show_favourites_icon', 1);
        }
      }
      else
      {
        if(($this->_config->get('jg_favouritesshownotauth') == 1))
        {
          if($this->_config->get('jg_usefavouritesforzip'))
          {
            $this->params->set('show_favourites_icon', -2);
          }
          else
          {
            $this->params->set('show_favourites_icon', -1);
          }
        }
      }
    }

    // Report icon
    if($this->_config->get('jg_report_images') && $this->_config->get('jg_toplist_report_images'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_report_unreg'))
      {
        $this->params->set('show_report_icon', 1);

        JHTML::_('behavior.modal');
      }
      else
      {
        if($this->_config->get('jg_report_hint'))
        {
          $this->params->set('show_report_icon', -1);
        }
      }
    }

    // Set redirect url used in editor links to redirect back to favourites view after edit/delete
    $this->redirect = '&redirect='.base64_encode(JFactory::getURI()->toString());

    parent::display($tpl);
  }
}