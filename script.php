<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/script.php $
// $Id: script.php 4408 2014-07-12 08:24:56Z erftralle $
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
 * Install method
 * is called by the installer of Joomla!
 *
 * @access  protected
 * @return  void
 * @since   2.0
 */
class Com_JoomGalleryInstallerScript
{
  /**
   * Version string of the current version
   *
   * @var string
   */
  private $version = '3.3.3';

  /**
   * Preflight method
   *
   * Is called afore installation and update processes
   *
   * @param   $type   string  'install', 'discover_install', or 'update'
   * @return  boolean False if installation or update shall be prevented, true otherwise
   * @since   2.1
   */
  public function preflight($type = 'install')
  {
    if(version_compare(JVERSION, '4.0', 'ge') || version_compare(JVERSION, '3.0', 'lt'))
    {
      JError::raiseWarning(500, 'JoomGallery 3.x is only compatible to Joomla! 3.x');

      return false;
    }

    return true;
  }

  /**
   * Install method
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function install()
  {
    $app = JFactory::getApplication();
    jimport('joomla.filesystem.file');

    // Create image directories
    require_once JPATH_ADMINISTRATOR.'/components/com_joomgallery/helpers/file.php';
    $thumbpath  = JPATH_ROOT.'/images/joomgallery/thumbnails';
    $imgpath    = JPATH_ROOT.'/images/joomgallery/details';
    $origpath   = JPATH_ROOT.'/images/joomgallery/originals';
    $result     = array();
    $result[]   = JFolder::create($thumbpath);
    $result[]   = JoomFile::copyIndexHtml($thumbpath);
    $result[]   = JFolder::create($imgpath);
    $result[]   = JoomFile::copyIndexHtml($imgpath);
    $result[]   = JFolder::create($origpath);
    $result[]   = JoomFile::copyIndexHtml($origpath);
    $result[]   = JoomFile::copyIndexHtml(JPATH_ROOT.'/images/joomgallery');

    if(in_array(false, $result))
    {
      $app->enqueueMessage(JText::_('Unable to create image directories!'), 'error');

      return false;
    }

    // Create news feed module
    $subdomain = '';
    $language = JFactory::getLanguage();
    if(strpos($language->getTag(), 'de-') === false)
    {
      $subdomain = 'en.';
    }

    $row = JTable::getInstance('module');
    $row->title     = 'JoomGallery News';
    $row->ordering  = 1;
    $row->position  = 'joom_cpanel';
    $row->published = 1;
    $row->module    = 'mod_feed';
    $row->access    = $app->getCfg('access');
    $row->showtitle = 1;
    $row->params    = 'cache=1
    cache_time=15
    moduleclass_sfx=
    rssurl=http://www.'.$subdomain.'joomgallery.net/feed/rss.html
    rssrtl=0
    rsstitle=1
    rssdesc=0
    rssimage=1
    rssitems=3
    rssitemdesc=1
    word_count=200';
    $row->client_id = 1;
    $row->language  = '*';
    if(!$row->store())
    {
      $app->enqueueMessage(JText::_('Unable to insert feed module data!'), 'error');
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->insert('#__modules_menu');
    $query->set('moduleid = '.$row->id);
    $query->set('menuid = 0');
    $db->setQuery($query);
    if(!$db->query())
    {
      $app->enqueueMessage(JText::_('Unable to assign feed module!'), 'error');
    }

    // joom_settings.css
    $temp = JPATH_ROOT.'/media/joomgallery/css/joom_settings.temp.css';
    $dest = JPATH_ROOT.'/media/joomgallery/css/joom_settings.css';

    if(!JFile::move($temp, $dest))
    {
      $app->enqueueMessage(JText::_('Unable to copy joom_settings.css!'), 'error');

      return false;
    }
?>
    <div class="hero-unit">
      <img src="../media/joomgallery/images/joom_logo.png" alt="JoomGallery Logo" />
      <div class="alert alert-success">
        <h3>JoomGallery <?php echo $this->version; ?> was installed successfully.</h3>
      </div>
      <p>You may now start using JoomGallery or download specific language files afore:</p>
      <p>
        <a title="Start" class="btn" onclick="location.href='index.php?option=com_joomgallery'; return false;" href="#">Start now!</a>
        <a title="Languages" class="btn btn-primary" onclick="location.href='index.php?option=com_joomgallery&controller=help'; return false;" href="#">Languages</a>
      </p>
    </div>
  <?php
  }

  /**
   * Update method
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function update()
  {
    jimport('joomla.filesystem.file'); ?>
    <div class="hero-unit">
      <img src="../media/joomgallery/images/joom_logo.png" alt="JoomGallery Logo" />
      <div class="alert alert-info">
        <h3>Update JoomGallery to version: <?php echo $this->version; ?></h3>
      </div>
    </div>
    <?php

    $error = false;

    // Delete temporary joom_settings.temp.css
    if(JFile::exists(JPATH_ROOT.'/media/joomgallery/css/joom_settings.temp.css'))
    {
      if(!JFile::delete(JPATH_ROOT.'/media/joomgallery/css/joom_settings.temp.css'))
      {
        JError::raiseWarning(500, JText::_('Unable to delete temporary joom_settings.temp.css!'));

        $error = true;
      }
    }

    //******************* Delete folders/files ************************************
    echo '<div class="alert alert-info">';
    echo '<h3>File system</h3>';

    $delete_folders = array();

    // MooRainbow assets
    $delete_folders[] = JPATH_ROOT.'/media/joomgallery/js/moorainbow';
    // Old vote view
    $delete_folders[] = JPATH_ROOT.'/components/com_joomgallery/views/vote';

    echo '<p>';
    echo 'Looking for orphaned files and folders from the old installation ';

    // Unzipped folder of latest auto update with cURL
    $temp_dir = false;
    $database = JFactory::getDbo();
    $query = $database->getQuery(true)
          ->select('jg_pathtemp')
          ->from('#__joomgallery_config');
    $database->setQuery($query);
    $temp_dir = $database->loadResult();
    if($temp_dir)
    {
      //$delete_folders[] = JPATH_SITE.'/'.$temp_dir.'update';

      for($i = 0; $i <= 100; $i++)
      {
        $update_folder = JPATH_SITE.'/'.$temp_dir.'update'.$i;
        if(JFolder::exists($update_folder))
        {
          $delete_folders[] = $update_folder;
        }
      }
    }

    $deleted = false;

    $jg_delete_error = false;
    foreach($delete_folders as $delete_folder)
    {
      if(JFolder::exists($delete_folder))
      {
        echo 'delete folder: '.$delete_folder.' : ';
        $result = JFolder::delete($delete_folder);
        if($result == true)
        {
          $deleted  = true;
          echo '<span class="label label-success">ok</span>';
        }
        else
        {
          $jg_delete_error = true;
          echo '<span class="label label-important">not ok</span>';
        }
        echo '<br />';
      }
    }

    // Files
    $delete_files = array();

    // Cache file of the newsfeed for the update checker
    $delete_files[] = JPATH_ADMINISTRATOR.'/cache/'.md5('http://www.joomgallery.net/components/com_newversion/rss/extensions2.rss').'.spc';
    $delete_files[] = JPATH_ADMINISTRATOR.'/cache/'.md5('http://www.en.joomgallery.net/components/com_newversion/rss/extensions2.rss').'.spc';
    $delete_files[] = JPATH_ADMINISTRATOR.'/cache/'.md5('http://www.joomgallery.net/components/com_newversion/rss/extensions3.rss').'.spc';
    $delete_files[] = JPATH_ADMINISTRATOR.'/cache/'.md5('http://www.en.joomgallery.net/components/com_newversion/rss/extensions3.rss').'.spc';

    // Zip file of latest auto update with cURL
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/temp/update.zip';
    // Old category form field
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/models/fields/category.php';
    // JHtml file that is not used anymore
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/helpers/html/joompopup.php';
    // JFormFields that aren't used anymore
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/models/fields/cbowner.php';
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/models/fields/owner.php';
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/models/fields/color.php';
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/models/fields/thumbnail.php';
    // Template files that aren't used anymore
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/category/tmpl/default_catpagination.php';
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/category/tmpl/default_imgpagination.php';
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/gallery/tmpl/default_pagination.php';
    // Old changelog.php
    $delete_files[] = JPATH_ROOT.'/administrator/components/com_joomgallery/changelog.php';
    // Old ordering form field
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/models/fields/ordering.php';
    // Old view file of MiniJoom
    $delete_files[] = JPATH_ADMINISTRATOR.'/components/com_joomgallery/views/mini/view.html.php';
    // Unnecessary layout XML files in views which cannot be linked from a menu
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/downloadzip/tmpl/default.xml';
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/edit/tmpl/default.xml';
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/editcategory/tmpl/default.xml';
    // Old CSS file of MiniJoom
    $delete_files[] = JPATH_ROOT.'/media/joomgallery/css/mini.css';
    // Old JavaScript files
    $delete_files[] = JPATH_ROOT.'/media/joomgallery/js/miniupload.js';
    $delete_files[] = JPATH_ROOT.'/media/joomgallery/js/thickbox3/js/jquery-latest.pack.js';
    // Old motion gallery
    $delete_files[] = JPATH_ROOT.'/media/joomgallery/js/motiongallery.js';
    // Old raw view for Cooliris 
    $delete_files[] = JPATH_ROOT.'/components/com_joomgallery/views/category/view.raw.php';
    // Override function for setting permissions via AJAX
    $delete_files[] = JPATH_ROOT.'/media/joomgallery/js/permissions.js';

    foreach($delete_files as $delete_file)
    {
      if(JFile::exists($delete_file))
      {
        echo 'delete file: '.$delete_file.' : ';
        $result = JFile::delete($delete_file);
        if($result == true)
        {
          $deleted  = true;
          echo '<span class="label label-success">ok</span>';
        }
        else
        {
          $jg_delete_error = true;
          echo '<span class="label label-important">not ok</span>';
        }
        echo '<br />';
      }
    }
   //******************* END delete folders/files ************************************

    if($deleted)
    {
      if($jg_delete_error)
      {
        echo '<span class="label label-important">problems in deletion of files/folders</span>';
        $error = true;
      }
      else
      {
        echo '<span class="label label-success">files/folders sucessfully deleted</span>';
      }
    }
    else
    {
      echo '<span class="label label-success">nothing to delete</span>';
    }

    echo '</p>';
    echo '</div>';

    //******************* Write joom_settings.css ************************************
    /*echo '<div class="alert alert-info">';
    echo '<h3>CSS</h3>';
    echo '<p>';
    echo 'Update configuration dependent CSS settings: ';

    require_once JPATH_ADMINISTRATOR.'/components/com_joomgallery/includes/defines.php';
    JLoader::register('JoomConfig', JPATH_ADMINISTRATOR.'/components/com_joomgallery/helpers/config.php');
    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_joomgallery/tables');

    $config = JoomConfig::getInstance('admin');
    if(!$config->save())
    {
      $error = true;
      echo '<span class="label label-important">not ok</span>';
    }
    else
    {
      echo '<span class="label label-success">ok</span>';
    }

    echo '</p>';
    echo '</div>';*/
    //******************* End write joom_settings.css ************************************

    if($error)
    {
      echo '<div class="alert alert-error">
              <h3>Problem with the update to JoomGallery version '.$this->version.'<br />Please read the update infos above</h3>
            </div>';
      JFactory::getApplication()->enqueueMessage(JText::_('Problem with the update to JoomGallery version '.$this->version.'. Please read the update infos below'), 'error');
    }
    else
    { ?>
    <div class="hero-unit">
      <img src="../media/joomgallery/images/joom_logo.png" alt="JoomGallery Logo" />
      <div class="alert alert-success">
        <h3>JoomGallery was updated to version <?php echo $this->version; ?> successfully.</h3>
        <button class="btn btn-small btn-info" data-toggle="modal" data-target="#jg-changelog-popup"><i class="icon-list"></i> Changelog</button>
      </div>
      <p>You may now start using JoomGallery or download specific language files afore:</p>
      <p>
        <a title="Start" class="btn" onclick="location.href='index.php?option=com_joomgallery'; return false;" href="#">Go on!</a>
        <a title="Languages" class="btn btn-primary" onclick="location.href='index.php?option=com_joomgallery&controller=help'; return false;" href="#">Languages</a>
      </p>
    </div>
    <?php JHtml::_('bootstrap.modal', 'jg-changelog-popup'); ?>
    <div class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="PopupChangelogModalLabel" aria-hidden="true" id="jg-changelog-popup">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="PopupChangelogModalLabel">Changelog</h3>
      </div>
      <div id="jg-changelog-popup-container">
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></button>
      </div>
    </div>
    <script type="text/javascript">
      jQuery('#jg-changelog-popup').modal({backdrop: true, keyboard: true, show: false});
      jQuery('#jg-changelog-popup').on('show', function ()
      {
        document.getElementById('jg-changelog-popup-container').innerHTML = '<div class="modal-body"><iframe class="iframe" frameborder="0" src="<?php echo JRoute::_('index.php?option=com_joomgallery&controller=changelog&tmpl=component'); ?>" height="400px" width="100%"></iframe></div>';
      });
    </script>
<?php
    }

    return !$error;
  }

  /**
   * Uninstall method
   *
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function uninstall()
  {
    $path = JPATH_ROOT.'/images/joomgallery';
    if(JFolder::exists($path))
    {
      JFolder::delete($path);
    }

    echo '<div class="alert alert-info">JoomGallery was uninstalled successfully!<br />
          Please remember to remove your images folders manually
          if you didn\'t use JoomGallery\'s default directories.</div>';

    return true;
  }
}
