<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/downloadzip/view.html.php $
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
 * HTML View class for the download view for zips
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewDownloadzip extends JoomGalleryView
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
    $this->params = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_DOWNLOADZIP_DOWNLOAD'));
    }

    // Header and footer
    JoomHelper::prepareParams($this->params);

    $this->pathway = JText::_('COM_JOOMGALLERY_DOWNLOADZIP_DOWNLOAD');

    $this->backtarget = JRoute::_('index.php?view=favourites'); //see above
    $this->backtext   = JText::_('COM_JOOMGALLERY_DOWNLOADZIP_BACK_TO_FAVOURITES');

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

    $this->zipname = $this->_mainframe->getUserState('joom.favourites.zipname');

    if(!$this->zipname || !file_exists(JPath::clean(JPATH_ROOT.'/'.$this->zipname)))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false), JText::_('COM_JOOMGALLERY_DOWNLOADZIP_ZIPFILE_NOT_FOUND'), 'error');
    }

    $zipsize = filesize($this->zipname);
    if($zipsize < 1000000)
    {
      $zipsize        = round($zipsize, -3) / 1000;
      $zipsize_string = JText::sprintf('COM_JOOMGALLERY_FAVOURITES_ZIP_SIZEKB',
                                         $zipsize
                                      );
    }
    else
    {
      $zipsize        = round($zipsize, -6) / 1000000;
      $zipsize_string = JText::sprintf('COM_JOOMGALLERY_FAVOURITES_ZIP_SIZEMB',
                                        $zipsize
                                      );
    }

    $this->zipsize = &$zipsize_string;

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