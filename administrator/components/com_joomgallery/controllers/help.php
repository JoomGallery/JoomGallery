<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/help.php $
// $Id: help.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Help and Information Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerHelp extends JoomGalleryController
{
  /**
   * Constructor
   *
   * @access  protected
   * @return  void
   * @since   1.5.5
   */
  function __construct()
  {
    parent::__construct();

    // Set view
    JRequest::setVar('view', 'help');
  }

  /**
   * Installs a new language package for JoomGallery
   *
   * The cURL library needs to be installed on the server for this.
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function install()
  {
    $language = JRequest::getCmd('language', 0, 'get');

    if(!$this->_config->get('jg_checkupdate') || !$language || !extension_loaded('curl'))
    {
      $link = base64_decode(JRequest::getCmd('downloadlink'));
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::sprintf('COM_JOOMGALLERY_ADMENU_MSG_ERROR_FETCHING_LANGUAGE_ZIP', $link), 'error');
    }
    else
    {
      $extensions = JoomExtensions::getAvailableExtensions();
      $url        = $extensions['JoomGallery']['updatelink'];

      $url        = substr($url, 0, strrpos($url, '/') + 1);
      $url        = str_replace('component/', '', $url);
      $url       .= 'languages/'.$language.'.com_joomgallery.zip';

      JoomExtensions::autoUpdate($url);
    }
  }
}