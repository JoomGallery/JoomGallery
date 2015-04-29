<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/extensions.php $
// $Id: extensions.php 4093 2013-02-13 22:06:53Z chraneco $
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
 * JoomGallery Extensions Class
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomExtensions
{
  /**
   * Returns all downloadable extensions developed by JoomGallery::ProjectTeam
   * with some additional information like the current version number or a
   * short description of the extension
   *
   * @return  array Two-dimensional array with extension information
   * @since   1.5.0
   */
  public static function getAvailableExtensions()
  {
    static $extensions;

    if(isset($extensions))
    {
      return $extensions;
    }

    // Check whether the german or the english RSS file should be loaded
    $subdomain = '';
    $language = JFactory::getLanguage();
    if(strpos($language->getTag(), 'de-') === false)
    {
      $subdomain = 'en.';
    }

    $site   = 'http://www.'.$subdomain.'joomgallery.net';
    $site2  = 'http://'.$subdomain.'joomgallery.net';
    $rssurl = $site.'/components/com_newversion/rss/extensions3.rss';

    // Get RSS parsed object
    $rssDoc = false;
		try
		{
			$feed = new JFeedFactory;
			$rssDoc = $feed->getFeed($rssurl);
		}
		catch (InvalidArgumentException $e)
		{
		}
		catch (RunTimeException $e)
		{
		}

    $extensions = array();
    if($rssDoc != false)
    {
      for($i = 0; isset($rssDoc[$i]); $i++)
      {
        $item = $rssDoc[$i];
        $name = $item->title;

        // The data type is delivered as the name of the first category
        $categories = $item->categories;
        $type = key($categories);
        switch($type)
        {
          case 'general':
            $description  = $item->content;
            $link         = $item->uri;
            if(!is_null($description) && $description != '')
            {
              $extensions[$name]['description']   = $description;
            }
            if(!is_null($link) && $link != $site && $link != $site2)
            {
              $extensions[$name]['downloadlink']  = $link;
            }
            break;
          case 'version':
            $version  = $item->content;
            $link     = $item->uri;
            if(!is_null($version) && $version != '')
            {
              $extensions[$name]['version']       = $version;
            }
            if(!is_null($link) && $link != $site && $link != $site2)
            {
              $extensions[$name]['releaselink']   = $link;
            }
            break;
          case 'autoupdate':
            $xml  = $item->content;
            $link = $item->uri;
            if(!is_null($xml) && $xml != '')
            {
              $extensions[$name]['xml']           = $xml;
            }
            if(!is_null($link) && $link != $site && $link != $site2)
            {
              $extensions[$name]['updatelink']    = $link;
            }
            break;
          default:
            break;
        }
      }

      // Sort the extensions in alphabetical order
      ksort($extensions);
    }

    return $extensions;
  }

  /**
   * Returns all installed JoomGallery extensions and JoomGallery itself
   * with additional information provided by getAvailableExtensions
   *
   * @param   array $extensions Extensions provided by getAvailableExtensions
   * @return  array Two-dimensional array with extension information
   * @since   1.5.0
   */
  public static function getInstalledExtensions($extensions = null)
  {
    static $installed_extensions;

    if(isset($installed_extensions))
    {
      return $installed_extensions;
    }

    if(is_null($extensions))
    {
      $extensions = JoomExtensions::getAvailableExtensions();
    }

    $installed_extensions = array();
    foreach($extensions as $name => $extension)
    {
      if(!isset($extension['xml']))
      {
        continue;
      }
      $xml_file = JPath::clean(JPATH_ROOT.'/'.$extension['xml']);
      if(file_exists($xml_file))
      {
        $installed_extensions[$name] = $extension;

        $xml = simplexml_load_file($xml_file);

        $installed_version = (string) $xml->version;
        $installed_extensions[$name]['installed_version'] = $installed_version;
      }
    }

    return $installed_extensions;
  }

  /**
   * Compares all installed extension versions with the current ones
   * and returns all dated JoomGallery extensions and JoomGallery itself
   * with additional information provided by getAvailableExtensions
   *
   * @param   array $extensions Installed extensions provided by getInstalledExtensions
   * @return  array Two-dimensional array with extension information
   * @since   1.5.0
   */
  public static function checkUpdate($extensions = null)
  {
    static $dated_extensions;

    if(isset($dated_extensions))
    {
      return $dated_extensions;
    }

    if(is_null($extensions))
    {
      $extensions = JoomExtensions::getInstalledExtensions();
    }

    $dated_extensions = array();
    foreach($extensions as $name => $extension)
    {
      if(version_compare($extension['version'], $extension['installed_version'], '>'))
      {
        $dated_extensions[$name] = $extension;
      }
    }

    return $dated_extensions;
  }

  /**
   * Returns the currently installed version of JoomGallery
   *
   * @return  string  Version
   * @since   1.5.0
   */
  public static function getGalleryVersion()
  {
    static $version;

    if(!isset($version))
    {
      $config = JoomConfig::getInstance();

      $version_from_xml = true;

      // Do not read RSS file if update check is disabled
      if($config->get('jg_checkupdate'))
      {
        $version_from_xml = false;

        $mainframe = JFactory::getApplication('administrator');
        if(!$version = $mainframe->getUserState('joom.version.string'))
        {
          $extensions = JoomExtensions::getInstalledExtensions();

          if(isset($extensions['JoomGallery']['installed_version']))
          {
            $version = $extensions['JoomGallery']['installed_version'];
          }
          else
          {
            $version_from_xml = true;
          }

          $mainframe->setUserState('joom.version.string', $version);
        }
      }

      if($version_from_xml)
      {
        $xml_file = JPATH_ADMINISTRATOR.'/components/com_joomgallery/joomgallery.xml';
        if(file_exists($xml_file))
        {
          $xml = simplexml_load_file($xml_file);

          if(!isset($xml->version))
          {
            $version = 'not found';
          }
          else
          {
            $version = (string) $xml->version;
          }
        }
      }
    }

    return $version;
  }

  /**
   * Fetches an update zip file from JoomGallery server and extracts it
   *
   * @param   string  $url  The URL to the zip to fetch and extract
   * @return  void
   * @since   1.5.0
   */
  public static function autoUpdate($url)
  {
    $mainframe = JFactory::getApplication();

    if(!$url || !extension_loaded('curl'))
    {
      $mainframe->redirect('index.php?option='._JOOM_OPTION, JText::_('COM_JOOMGALLERY_ADMENU_MSG_ERROR_FETCHING_ZIP'), 'error');
    }

    $ambit = JoomAmbit::getInstance();

    // Create curl resource
    $ch = curl_init(strtolower($url));

    // Some settings for curl
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: JoomGallery v3'));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Create the zip file
    jimport('joomla.filesystem.file');
    $config = JoomConfig::getInstance();
    $output = curl_exec($ch);
    JFile::write($ambit->get('temp_path').'update.zip', $output);

    // Close curl resource to free up system resources
    curl_close($ch);

    // Delete files and folders from previous updates
    $folder = $ambit->get('temp_path').'update';
    if(JFolder::exists($folder))
    {
      JFolder::delete($folder);
    }

    // Extract the zip file
    jimport('joomla.filesystem.archive');
    if(!JArchive::extract($ambit->get('temp_path').'update.zip', $folder))
    {
      $mainframe->redirect('index.php?option='._JOOM_OPTION, JText::_('Error extracting the zip'), 'error');
    }

    // Copy an index.html into the created folder if there isn't already one
    if(!JFile::exists($folder.'/index.html'))
    {
      $src  = JPATH_ROOT.'/media/joomgallery/index.html';
      $dest = $folder.'/index.html';

      JFile::copy($src, $dest);
    }

    $mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_ADMENU_REDIRECT_NOTE'), 'notice');

    // Let's redirect to do the rest
?>
    <form action="index.php" method="post" name="JoomUpdateForm">
      <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
      <input type="hidden" name="controller" value="control" />
      <input type="hidden" name="task" value="doinstallation" />
      <?php echo JHtml::_('form.token'); ?>
    </form>
    <script type="text/javascript">
      document.JoomUpdateForm.submit();
    </script>
<?php
  }
}
