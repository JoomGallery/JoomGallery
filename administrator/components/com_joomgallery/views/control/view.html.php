<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/control/view.html.php $
// $Id: view.html.php 4373 2014-03-05 09:07:31Z erftralle $
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
 * HTML View class for the control panel view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewControl extends JoomGalleryView
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
    $this->params = JComponentHelper::getParams('com_joomgallery');

    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_ADMENU_ADMINMENU') , 'joomgallery');

    $this->canDo = JoomHelper::getActions();
    if($this->canDo->get('core.admin'))
    {
      JToolBarHelper::preferences('com_joomgallery');
      JToolBarHelper::spacer();
    }

    // Get data from the model
    $model                      = $this->getModel();
    $this->state                = $model->getState();
    $this->items                = $model->getData();
    $this->popularImages        = $model->getImages('a.hits desc', true, 5, 'a.hits > 0');
    $this->notApprovedImages    = $model->getImages('a.imgdate desc', false, 5);
    $this->notApprovedComments  = $model->getComments('c.cmtdate desc', false, 5);
    $this->topDownloads         = $model->getImages('a.downloads desc', true, 5, 'a.downloads > 0');

    $lang = JFactory::getLanguage();

    $this->modules =& JModuleHelper::getModules('joom_cpanel');

    if($this->_config->get('jg_checkupdate'))
    {
      $available_extensions = JoomExtensions::getAvailableExtensions();
      $this->params->set('url_fopen_allowed', @ini_get('allow_url_fopen'));
      $this->params->set('curl_loaded', extension_loaded('curl'));

      // If there weren't any available extensions found
      // loading the RSS feed wasn't successful
      if(count($available_extensions))
      {
        $installed_extensions = JoomExtensions::getInstalledExtensions();
        $this->assignRef('available_extensions',  $available_extensions);
        $this->assignRef('installed_extensions',  $installed_extensions);
        $this->params->set('show_available_extensions', 1);

        $dated_extensions = JoomExtensions::checkUpdate();
        if(count($dated_extensions))
        {
          $this->assignRef('dated_extensions', $dated_extensions);
          $this->params->set('dated_extensions', 1);
        }
        else
        {
          $this->params->set('dated_extensions', 0);
          $this->params->set('show_update_info_text', 1);
        }
      }
    }
    else
    {
      $this->params->set('dated_extensions', 0);
    }

    parent::display($tpl);
  }
}