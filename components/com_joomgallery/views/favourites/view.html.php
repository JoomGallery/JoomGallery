<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/favourites/view.html.php $
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
 * HTML View class for the favourites view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewFavourites extends JoomGalleryView
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
    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      $breadcrumbs->addItem($this->output('MY'));
    }

    $this->params = $this->_mainframe->getParams();

    // Header and footer
    JoomHelper::prepareParams($this->params);

    $this->pathway = $this->output('MY');

    $this->backtarget = JRoute::_('index.php?view=gallery'); //see above
    $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');

    // Get number of images and hits in gallery
    $numbers = JoomHelper::getNumberOfImgHits();
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

    $this->state = $this->get('State');
    $this->rows = $this->get('Favourites');

    foreach($this->rows as $key => $row)
    {
      $row->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_detailpic_open'), $row);

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
          $row->authorowner = $row->imgauthor;
        }
        else
        {
          if($this->_config->get('jg_showowner'))
          {
            $row->authorowner = JHTML::_('joomgallery.displayname', $row->imgowner);
          }
          else
          {
            $row->authorowner = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
          }
        }
      }

      // Show editor links for that image
      $row->show_edit_icon   = false;
      $row->show_delete_icon = false;
      if(   $this->_config->get('jg_showfavouriteseditorlinks') == 1
         && $this->_config->get('jg_userspace') == 1
        )
      {
        if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$row->id)
            ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$row->id)
                &&  $row->imgowner
                &&  $row->imgowner == $this->_user->get('id')
                )
            )
        )
        {
          $row->show_edit_icon = true;
        }

        if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$row->id))
        {
          $row->show_delete_icon = true;
        }
      }
    }

    // Download Icon
    if($this->_config->get('jg_download') && $this->_config->get('jg_showfavouritesdownload'))
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

    // Set redirect url used in editor links to redirect back to favourites view after edit/delete
    $this->redirect = '&redirect='.base64_encode(JFactory::getURI()->toString());

    $layout = $this->_mainframe->input->getCmd('layout');
    if(!$layout && $this->get('Layout'))
    {
      $this->setLayout('list');
    }

    parent::display($tpl);
  }

  /**
   * Returns a language string depending on the used mode for the zip download
   *
   * @access  public
   * @param   string  The main part of the language constant to use
   * @return  string  The translated string of the selected and completed language constant
   * @since   1.0.0
   */
  function output($msg)
  {
    if($this->_user->get('id') && $this->_config->get('jg_usefavouritesforzip') != 1)
    {
      $prefix = 'COM_JOOMGALLERY_FAVOURITES_';
    }
    else
    {
      $prefix = 'COM_JOOMGALLERY_DOWNLOADZIP_';
    }

    return JText::_($prefix.$msg);
  }
}