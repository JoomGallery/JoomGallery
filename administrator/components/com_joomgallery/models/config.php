<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/config.php $
// $Id: config.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Configuration model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelConfig extends JoomGalleryModel
{
  /**
   * Attempts to determine if gd is configured, and if so,
   * what version is installed
   *
   * @return  string  The result of request
   * @since   1.0.0
   */
  public function getGDVersion()
  {
    if(!extension_loaded('gd'))
    {
      return;
    }

    $phpver = substr(phpversion(), 0, 3);
    // gd_info came in at 4.3
    if($phpver < 4.3)
    {
      return -1;
    }

    if(function_exists('gd_info'))
    {
      $ver_info = gd_info();
      preg_match('/\d/', $ver_info['GD Version'], $match);
      $gd_ver = $match[0];
      return $match[0];
    }
    else
    {
      return;
    }
  }

  /**
   * Checks if exec is disabled in php.ini
   *
   * @return  boolean true if exec exists in array of disabled fuctions
   * @since   1.0.0
   */
  public function getDisabledExec()
  {
    $disable_functions = explode(',', ini_get('disable_functions'));
    foreach($disable_functions as $disable_function)
    {
      if(trim($disable_function) == 'exec')
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Attempts to determine if ImageMagick is configured, and if so,
   * what version is installed
   *
   * @return  string  The result of request
   * @since   1.0.0
   */
  public function getIMVersion()
  {
    $config = JoomConfig::getInstance();
    $status = null;
    $output = array();

    if(!empty($config->jg_impath))
    {
      $execstring = $config->get('jg_impath').'convert -version';
    }
    else
    {
      $execstring = 'convert -version';
    }

    @exec($execstring, $output, $status);

    if(count($output) == 0)
    {
      return 0;
    }
    else
    {
      return $output[0];
    }
  }

  /**
   * Returns the title of the current config row
   *
   * @return  string  The title of the current config row
   * @since   2.0
   */
  public function getConfigTitle()
  {
    $query = $this->_db->getQuery(true)
          ->select('g.title')
          ->from(_JOOM_TABLE_CONFIG.' AS c')
          ->from('#__usergroups AS g')
          ->where('c.group_id = g.id')
          ->where('c.id = '.JRequest::getInt('id'));
    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }
}