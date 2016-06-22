<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/helpers/helper.php $
// $Id: helper.php 4331 2013-09-08 08:27:42Z erftralle $
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
 * JoomGallery Global Helper
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomHelper
{
  /**
   * Returns a list of all categories for that a user has permission for a given action
   *
   * @param   string  $action The action to check for
   * @return  array   List of category objects for which the current can do the selected action to (empty array if none)
   * @since   2.0
   */
  public static function getAuthorisedCategories($action)
  {
    $user = JFactory::getUser();
    $cats = JoomAmbit::getInstance()->getCategoryStructure(true);
    $allowedCategories = array();
    foreach($cats as $category)
    {
      $action2 = false;
      if($action == 'joom.upload')
      {
        $action2 = 'joom.upload.inown';
      }
      if($action == 'core.create')
      {
        $action2 = 'joom.create.inown';
      }
      if($action == 'core.edit')
      {
        $action2 = 'core.edit.own';
      }

      if(     $user->authorise($action, _JOOM_OPTION.'.category.'.$category->cid)
          ||  (     $action2
                &&  $category->owner
                &&  $category->owner == $user->get('id')
                &&  $user->authorise($action2, _JOOM_OPTION.'.category.'.$category->cid)
              )
        )
      {
        $allowedCategories[] = $category;
      }
    }

    return $allowedCategories;
  }

  /**
   * Fix text for output in JavaScript Code
   *
   * @param   string  $text The text to fix
   * @return  string  The fixed text
   * @since   1.0.0
   */
  public static function fixForJS($text)
  {
    $text = str_replace("\"", "\&quot;", $text);
    $text = str_replace("'",  "\'", $text);
    $text = preg_replace('/[\n\t\r]*/', '', $text);

    return $text;
  }

  /**
   * Wrap text
   *
   * @param   string  $text The text to wrap
   * @param   int     $nr   Number of chars to wrap
   * @return  string  The wrapped text
   * @since   1.0.0
   */
  public static function processText($text, $nr = 40)
  {
    $mytext   = explode(' ', trim($text));
    $newtext  = array();
    $config   = JoomConfig::getInstance();

    foreach($mytext as $k => $txt)
    {
      if(strlen($txt) > $nr)
      {
        // Do not wrap BBcode [url] and [email]
        if(
              !$config->get('jg_bbcodesupport')
          ||  (   stripos($txt,'[url') === false && stripos($txt,'[/url]') === false
              &&  stripos($txt,'[email') === false && stripos($txt,'[/email]') === false
              )
          )
        {
          $txt  = wordwrap($txt, $nr, '- ', true);
        }
      }

      $newtext[]  = $txt;
    }

    return implode(' ', $newtext);
  }

  /**
   * Reads the category path from array
   * If not set read database and add to array
   *
   * @param   int     $catid  The ID of the category of which the catpath is requested
   * @return  string  The catpath of the category
   * @since   1.0.0
   */
  public static function getCatPath($catid)
  {
    static $catpath = array();

    if(!isset($catpath[$catid]))
    {
      $database = JFactory::getDBO();
      $query = $database->getQuery(true)
            ->select('catpath')
            ->from(_JOOM_TABLE_CATEGORIES)
            ->where('cid = '.$catid);
      $database->setQuery($query);

      if(!$path = $database->loadResult())
      {
        $catpath[$catid] = '/';
      }
      else
      {
        $catpath[$catid] = $path.'/';
      }
    }

    return $catpath[$catid];
  }

  /**
   * Check the upload time of image and determine if it is within a setted span
   * of time and so marked as NEW
   *
   * @param   int     $uptime   Upload time in seconds
   * @param   int     $daysnew  Span of time in days
   * @return  string  The HTML output of the new icon or empty string
   * @since   1.0.0
   */
  public static function checkNew($uptime, $daysnew)
  {
    $isnew = '';

    // Get the seconds from starting time of Unix Epoch (January 1 1970 00:00:00 GMT)
    // to now in seconds
    $thistime   = time();
    // Calculate the seconds according to days setted for new
    // See configuration manager
    $timefornew = 86400 * $daysnew;
    // If span of time since upload is lower than span of time setted in config
    if(($thistime - strtotime($uptime)) < $timefornew)
    {
      // Show the 'new' image
      $isnew = JHTML::_('joomgallery.icon', 'new.png', 'New');
    }

    return $isnew;
  }

  /**
   * Checks images of category and possibly sub-categories
   * and calls checkNew() to decide if NEW
   *
   * @param   string  $catids_values  IDs of categories ('x,y')
   * @return  string  HTML output of the new icon or empty string
   * @since   1.0.0
   */
  public static function checkNewCatg($cid)
  {
    $config = JoomConfig::getInstance();
    $db     = JFactory::getDBO();
    $user   = JFactory::getUser();
    $isnewcat = '';

    // Get all sub-categories including the current category
    $catids = JoomHelper::getAllSubCategories($cid, true);

    if(count($catids))
    {
      // Implode array to a comma separated string if more than one element in array
      $catid_values = implode(',', $catids);
      // Search in db the categories in $catids_values
      $query = $db->getQuery(true)
            ->select('MAX(imgdate)')
            ->from(_JOOM_TABLE_IMAGES.' AS a')
            ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
            ->where('a.catid IN ('.$catid_values.')');
      $db->setQuery($query);

      $maxdate = $db->loadResult();
      if($db->getErrorNum())
      {
        JError::raiseWarning(500, $db->getErrorMsg());
      }

      // If maxdate = NULL no image found
      // Otherwise check the date to 'new'
      if($maxdate)
      {
        $isnewcat = JoomHelper::checkNew($maxdate, $config->get('jg_catdaysnew'));
      }
    }

    // If no new found at all
    // Return empty string
    return $isnewcat;
  }

  /**
   * Construct page title
   *
   * @param   string  $text       The structure of the page title to use
   * @param   string  $catname    The name of the category which is currently displayed
   *                              or in which the currently displayed image is
   * @param   string  $imgtitle   The name of the image which is currently displayed
   * @param   string  $page_title The page title
   * @return  string  modified title
   * @since   1.0.0
   */
  public static function createPagetitle($text, $catname = '', $imgtitle = '', $page_title = '')
  {
    preg_match_all('/(\[\!.*?\!\])/i', $text, $results);
    define('COM_JOOMGALLERY_COMMON_CATEGORY', JText::_('COM_JOOMGALLERY_COMMON_CATEGORY'));
    define('COM_JOOMGALLERY_COMMON_IMAGE', JText::_('COM_JOOMGALLERY_COMMON_IMAGE'));
    for($i = 0; $i<count($results[0]); $i++)
    {
      $replace  = str_replace('[!', '', $results[0][$i]);
      $replace  = str_replace('!]', '', $replace);
      $replace  = trim($replace);
      $replace2 = str_replace('[!', '\[\!', $results[0][$i]);
      $replace2 = str_replace('!]', '\!\]', $replace2);
      $text     = preg_replace_callback('/('.$replace2.')/i', function($matches) use ($replace){
        return JText::_($replace);
      }, $text);
    }
    $text = str_replace('#cat', $catname, $text);
    $text = str_replace('#img', $imgtitle, $text);
    $text = str_replace('#page_title', $page_title, $text);

    $text = self::addSitenameToPagetitle($text);

    return $text;
  }

  /**
   * Add the sitename to the page title
   *
   * @param   string  $pagetitle  The page title until now
   * @return  string  Modified page title
   * @since   2.1.0
   */
  public static function addSitenameToPagetitle($pagetitle)
  {
    $app = JFactory::getApplication('site');

    if($app->getCfg('sitename_pagetitles'))
    {
      if($app->getCfg('sitename_pagetitles') == 1)
      {
        $pagetitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $pagetitle);
      }
      else
      {
        $pagetitle = JText::sprintf('JPAGETITLE', $pagetitle, $app->getCfg('sitename'), $pagetitle);
      }
    }

    return $pagetitle;
  }

  /**
   * Returns all categories and their sub-categories with published or no images
   *
   * @param   int     $cat          Category ID
   * @param   boolean $rootcat      True, if $cat shall also be returned as an
   *                                element of the array
   * @param   boolean $noimgcats    True if @return shall also include categories
   *                                with no images
   * @param   boolean $all          True if all categories shall be selected, defaults to false
   * @param   boolean $nohiddencats True, if hidden categories and it's subcategories should be
   *                                filtered out, defaults to true
   * @return  array   An array of found categories
   * @since   1.5.5
   */
  public static function getAllSubCategories($cat, $rootcat = false, $noimgcats = false, $all = false, $nohiddencats = true)
  {
    // Initialise variables
    $cat              = (int) $cat;
    $parentcats       = array();
    $parentcats[$cat] = true;
    $branchfound      = false;
    $allsubcats       = array();

    // Get category structure from ambit
    $ambit = JoomAmbit::getInstance();
    $cats  = $ambit->getCategoryStructure($all);

    $stopindex = count($cats);

    $keys = array_keys($cats);
    $startindex = array_search($cat, $keys);
    if($startindex === false)
    {
      return $allsubcats;
    }

    // Find all cats which are subcategories of cat
    $hidden = array();
    for($j = $startindex + 1; $j < $stopindex; $j++)
    {
      $i = $keys[$j];
      $parentcat = $cats[$i]->parent_id;
      if(isset($parentcats[$parentcat]))
      {
        $parentcats[$i] = true;
        $branchfound = true;

        // Don't include hidden sub-categories
        if($nohiddencats)
        {
          if($cats[$i]->hidden)
          {
            $hidden[$i] = true;
          }
          else
          {
            if(isset($hidden[$cats[$i]->parent_id]))
            {
              $hidden[$i] = true;
            }
          }
        }

        if(!isset($hidden[$i]))
        {
          if(!$noimgcats)
          {
            // Only categories with images
            if($cats[$i]->piccount > 0)
            {
              // Subcategory with images in array
              $allsubcats[] = $i;
            }
          }
          else
          {
            $allsubcats[] = $i;
          }
        }
      }
      else
      {
        if($branchfound)
        {
          // Branch has been processed completely
          break;
        }
      }
    }

    // Add rootcat
    if($rootcat)
    {
      if(!$noimgcats)
      {
        // Includes images
        if($cats[$cat]->piccount > 0)
        {
          $allsubcats[] = $cat;
        }
      }
      else
      {
        $allsubcats[] = $cat;
      }
    }

    return $allsubcats;
  }

  /**
   * Returns all parent categories of a specific category
   *
   * @param   int     $category The ID of the specific child category
   * @param   boolean $child    True, if category itself shall also be returned, defaults to false
   * @param   boolean $all      True if all categories shall be shown, defaults to false
   * @return  array   An array of parent category objects with cid,name,parent_id
   * @since   1.5.5
   */
  public static function getAllParentCategories($category, $child = false, $all = false)
  {
    // Get category structure from ambit
    $ambit      = JoomAmbit::getInstance();
    $cats       = $ambit->getCategoryStructure($all);
    $parents    = array();

    if($child)
    {
      $parents[$category]             = new stdClass();
      $parents[$category]->cid        = $cats[$category]->cid;
      $parents[$category]->name       = $cats[$category]->name;
      $parents[$category]->parent_id  = $cats[$category]->parent_id;
      $parents[$category]->published  = $cats[$category]->published;
    }

    $parentcat = $cats[$category]->parent_id;
    while($parentcat != 1)
    {
      $category = $parentcat;
      $parents[$category]             = new stdClass();
      $parents[$category]->cid        = $cats[$category]->cid;
      $parents[$category]->name       = $cats[$category]->name;
      $parents[$category]->parent_id  = $cats[$category]->parent_id;
      $parents[$category]->published  = $cats[$category]->published;
      $parentcat  = $cats[$category]->parent_id;
    }

    // Reverse the array to get the right order
    $parents = array_reverse($parents, true);

    return $parents;
  }

  /**
   * Returns all available smileys in an array
   *
   * @return  array   An array with the smileys
   * @since   1.5.0
   */
  public static function getSmileys()
  {
    $config = JoomConfig::getInstance();

    $path = JoomAmbit::getInstance()->get('icon_url').'smilies/'.$config->jg_smiliescolor.'/';

    $smileys                      = array();
    $smileys[':smile:']           = $path.'sm_smile.gif';
    $smileys[':cool:']            = $path.'sm_cool.gif';
    $smileys[':grin:']            = $path.'sm_biggrin.gif';
    $smileys[':wink:']            = $path.'sm_wink.gif';
    $smileys[':none:']            = $path.'sm_none.gif';
    $smileys[':mad:']             = $path.'sm_mad.gif';
    $smileys[':sad:']             = $path.'sm_sad.gif';
    $smileys[':dead:']            = $path.'sm_dead.gif';

    if($config->get('jg_anismilie'))
    {
      $smileys[':yes:']           = $path.'sm_yes.gif';
      $smileys[':lol:']           = $path.'sm_laugh.gif';
      $smileys[':smilewinkgrin:'] = $path.'sm_smilewinkgrin.gif';
      $smileys[':razz:']          = $path.'sm_bigrazz.gif';
      $smileys[':roll:']          = $path.'sm_rolleyes.gif';
      $smileys[':eek:']           = $path.'sm_bigeek.gif';
      $smileys[':no:']            = $path.'sm_no.gif';
      $smileys[':cry:']           = $path.'sm_cry.gif';
    }

    $dispatcher = JDispatcher::getInstance();
    $dispatcher->trigger('onJoomGetSmileys', array(&$smileys));

    return $smileys;
  }

  /**
   * At the moment just a wrapper function for JModuleHelper::getModules()
   *
   * @param   string  $pos  The position name
   * @return  array   An array of module objects
   * @since   1.5.0
   */
  public static function getModules($pos)
  {
    $view     = JRequest::getCmd('view');

    $position = 'jg_'.$pos;
    $modules  = & JModuleHelper::getModules($position);

    $views = array( ''            => 'gal',
                    'gallery'     => 'gal',
                    'category'    => 'cat',
                    'detail'      => 'dtl',
                    'toplist'     => 'tpl',
                    'search'      => 'sea',
                    'favourites'  => 'fav',
                    'userpanel'   => 'usp',
                    'upload'      => 'upl'
                  );
    if(isset($views[$view]))
    {
      $position = $position.'_'.$views[$view];
      $ind_mods = & JModuleHelper::getModules($position);
      $modules  = array_merge($modules, $ind_mods);
    }

    $ind_mods = & JModuleHelper::getModules($position.'_'.$view);
    $modules  = array_merge($modules, $ind_mods);

    return $modules;
  }

  /**
   * Renders modules provided by getModules()
   *
   * @param   string  $pos  The position name
   * @return  array   An array of rendered modules
   * @since   1.5.5
   */
  public static function getRenderedModules($pos)
  {
    static $renderer;

    $modules = JoomHelper::getModules($pos);

    if(count($modules))
    {
      if(!isset($renderer))
      {
        $document = JFactory::getDocument();
        $renderer = $document->loadRenderer('module');
      }

      $style  = -2;
      $params = array('style' => $style);

      foreach($modules as $key => $module)
      {
        $modules[$key]->rendered = $renderer->render($module, $params);
      }
    }

    return $modules;
  }

  /**
   * Sets all params for the output depending on the view and the config settings
   *
   * @param   $params The parameter object
   * @return  void
   * @since   1.5.5
   */
  public static function prepareParams(&$params)
  {
    $config = JoomConfig::getInstance();
    $user   = JFactory::getUser();
    $view   = JRequest::getCmd('view');
    $app    = JFactory::getApplication('site');

    // Page heading
    $menus  = $app->getMenu();
    $menu   = $menus->getActive();
    if($menu)
    {
      $params->def('page_heading', $params->get('page_title', $menu->title));
    }
    else
    {
      $params->def('page_heading', JText::_('COM_JOOMGALLERY_COMMON_GALLERY'));
      if($config->get('jg_showgalleryhead'))
      {
        $params->set('show_page_heading', 1);
      }
    }

    // Pathway
    if(!$params->get('disable_global_info') && ($view != 'gallery' || $config->get('jg_showgallerysubhead')))
    {
      // Pathway in the header
      if($config->get('jg_showpathway') == 1 || $config->get('jg_showpathway') == 3)
      {
        $params->set('show_header_pathway', 1);
      }
      // Pathway in the footer
      if($config->get('jg_showpathway') >= 2)
      {
        $params->set('show_footer_pathway', 1);
      }
    }

    // Search in the header
    if(!$params->get('disable_global_info') && ($config->get('jg_search') == 1 || $config->get('jg_search') == 3))
    {
      $params->set('show_header_search', 1);
    }
    //Search in the footer
    if(!$params->get('disable_global_info') && $config->get('jg_search') >= 2)
    {
      $params->set('show_footer_search', 1);
    }

    // Backlink in the header
    if(!$params->get('disable_global_info') && ($config->get('jg_showbacklink') == 1 || $config->get('jg_showbacklink') == 3))
    {
      $params->set('show_header_backlink', 1);
    }
    // Backlink in the footer
    if(!$params->get('disable_global_info') && $config->get('jg_showbacklink') >= 2)
    {
      $params->set('show_footer_backlink', 1);
    }

    // All images
    if(!$params->get('disable_global_info'))
    {
      // All Images in the header
      if($config->get('jg_showallpics') == 1 || $config->get('jg_showallpics') == 3)
      {
        $params->set('show_header_allpics', 1);
      }
      // All Images in the footer
      if($config->get('jg_showallpics') >= 2)
      {
        $params->set('show_footer_allpics', 1);
      }
    }

    // All hits
    if(!$params->get('disable_global_info'))
    {
      // All Hits in the header
      if($config->get('jg_showallhits') == 1 || $config->get('jg_showallhits') == 3)
      {
        $params->set('show_header_allhits', 1);
      }
      // All Hits in the footer
      if($config->get('jg_showallhits') >= 2)
      {
        $params->set('show_footer_allhits', 1);
      }
    }

    // Link to userpanel in the header
    if(!$params->get('disable_global_info') && $config->get('jg_userspace') == 1 && $config->get('jg_showuserpanel'))
    {
      if($user->get('id') || $config->get('jg_unregistered_permissions') || $config->get('jg_showuserpanel_unreg'))
      {
        if($user->get('id') || $config->get('jg_unregistered_permissions'))
        {
          $params->set('show_mygal', 1);
        }
        else
        {
          if($config->get('jg_showuserpanel_hint'))
          {
            $params->set('show_mygal_no_access', 1);
          }
        }
      }
    }

    // Link to favourites in the header
    if(!$params->get('disable_global_info') && $config->get('jg_favourites'))
    {
      if($view != 'favourites')
      {
        if(     $user->get('id')
           || (($config->get('jg_usefavouritesforpubliczip') == 1) && !$user->get('id'))
          )
        {
          if( ($config->get('jg_usefavouritesforzip') == 1)
             || (($config->get('jg_usefavouritesforpubliczip') == 1) && !$user->get('id'))
            )
          {
            $params->set('show_favourites', 1);
          }
          else
          {
            $tooltip_text = JText::_('COM_JOOMGALLERY_COMMON_FAVOURITES_DOWNLOAD_TIPTEXT', true);
            if($config->get('jg_zipdownload') && $view != 'createzip')
            {
              $tooltip_text .= ' '.JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADZIP_DOWNLOAD_ALLOWED_TIPTEXT', true);
            }
            $params->set('show_favourites', 2);
            $params->set('favourites_tooltip_text', $tooltip_text);
          }
        }
        else
        {
          if($config->get('jg_favouritesshownotauth') == 1)
          {
            if($config->get('jg_usefavouritesforzip') == 1)
            {
              $params->set('show_favourites', 3);
            }
            else
            {
              $params->set('show_favourites', 4);
            }
          }
        }
      }
      else
      {
        if(     $config->get('jg_zipdownload')
            || (!$user->get('id') && $config->get('jg_usefavouritesforpubliczip'))
          )
        {
          $params->set('show_favourites', 5);
        }
      }
    }

    // Toplist
    if( (     $config->get('jg_whereshowtoplist') == 0
          || ($config->get('jg_whereshowtoplist')  > 0 && $view == 'gallery')
          || ($config->get('jg_whereshowtoplist') == 2 && $view == 'category')
        )
      &&
        !$params->get('disable_global_info')
      )
    {
      // Toplist in the header
      if(    $config->get('jg_showtoplist') > 0
          && $config->get('jg_showtoplist') < 3
        )
      {
        $params->set('show_header_toplist', 1);
      }
      // Toplist in the footer
      if($config->get('jg_showtoplist') > 1)
      {
        $params->set('show_footer_toplist', 1);
      }
    }

    // RM/SM Legend in the footer
    if($config->get('jg_showrestrictedhint') == 1 && ($view == 'gallery' || $view == 'category'))
    {
      $params->set('show_rmsm_legend', 1);
    }

    // Separator in the footer
    if($view == 'detail' || $view == 'favourites' || $view == 'search' || $view == 'toplist')
    {
      $params->set('show_footer_separator', 1);
    }

    // Credits in the footer
    if($config->get('jg_suppresscredits'))
    {
      $params->set('show_credits', 1);
    }
  }

  /**
   * Counts images and hits in gallery or a category and their sub-categories
   *
   * @param   int     $cat      Category ID or 0 to return images/hits of gallery
   * @param   boolean $rootcat  True to count the images/hits also in category = $cid
   * @return  array   0 = Number of images in categories->subcategories....
   *                  1 = Number of hits in categories->subcategories....
   * @since   1.5.7
   */
  public static function getNumberOfImgHits($cat = 0, $rootcat = true)
  {
    // Initialise variables
    $cat        = (int) $cat;
    $imgHitsarr = array();
    $imgcount   = 0;
    $hitcount   = 0;

    // Get category structure from ambit
    $ambit = JoomAmbit::getInstance();
    $cats  = $ambit->getCategoryStructure();

    if($cat == 0)
    {
      // Count all images and hits in gallery
      foreach($cats as $category)
      {
        $imgcount += $category->piccount;
        $hitcount += $category->hitcount;
      }
    }
    else
    {
      $branchfound      = false;
      $parentcats       = array();
      $parentcats[$cat] = true;

      $keys = array_keys($cats);
      $startindex = array_search($cat, $keys);

      $stopindex = count($cats);

      if($rootcat && isset($cats[$cat]))
      {
        $imgcount += $cats[$cat]->piccount;
        $hitcount += $cats[$cat]->hitcount;
      }

      // Count all images in branch
      $hidden = array();
      for($j = $startindex + 1; $j < $stopindex; $j++)
      {
        $i = $keys[$j];
        $parentcat = $cats[$i]->parent_id;
        if(isset($parentcats[$parentcat]))
        {
          $parentcat = $i;
          $parentcats[$parentcat] = true;

          // Don't count images and hits of hidden categories
          if($cats[$i]->hidden)
          {
            $hidden[$i] = true;
          }
          else
          {
            if(isset($hidden[$cats[$i]->parent_id]))
            {
              $hidden[$i] = true;
            }
          }

          if(!isset($hidden[$i]))
          {
            $hitcount += $cats[$i]->hitcount;
            $imgcount += $cats[$i]->piccount;
          }

          $branchfound = true;
        }
        else
        {
          if($branchfound)
          {
            // Branch has been processed completely
            break;
          }
        }
      }

    }

    $imgHitsarr[0] = number_format($imgcount, 0, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));
    $imgHitsarr[1] = number_format($hitcount, 0, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));

    return $imgHitsarr;
  }

  /**
   * Returns the rating clause for an SQL - query dependent on the
   * rating calculation method selected.
   *
   * @param   string  $tablealias The table alias to use
   * @return  string  Rating clause
   * @since   1.5.6
   */
  public static function getSQLRatingClause($tablealias = '')
  {
    $db                   = JFactory::getDBO();
    $config               = JoomConfig::getInstance();
    static $avgimgvote    = 0.0;
    static $avgimgrating  = 0.0;
    static $avgdone       = false;

    $maxvoting            = $config->get('jg_maxvoting');
    $imgvotesum           = 'imgvotesum';
    $imgvotes             = 'imgvotes';
    if($tablealias != '')
    {
      $imgvotesum = $tablealias.'.'.$imgvotesum;
      $imgvotes   = $tablealias.'.'.$imgvotes;
    }

    // Standard rating clause
    $clause = 'ROUND(LEAST(IF(imgvotes > 0, '.$imgvotesum.'/'.$imgvotes.', 0.0), '.(float)$maxvoting.'), 2)';

    // Advanced (weigthed) rating clause (Bayes)
    if($config->get('jg_ratingcalctype') == 1)
    {
      if(!$avgdone)
      {
        // Needed values for weighted rating calculation
        $query = $db->getQuery(true)
              ->select('count(*) As imgcount')
              ->select('SUM(imgvotes) As sumimgvotes')
              ->select('SUM(imgvotesum/imgvotes) As sumimgratings')
              ->from(_JOOM_TABLE_IMAGES)
              ->where('imgvotes > 0');
        $db->setQuery($query);

        $row = $db->loadObject();
        if($row != null)
        {
          if($row->imgcount > 0)
          {
            $avgimgvote   = round($row->sumimgvotes / $row->imgcount, 2 );
            $avgimgrating = round($row->sumimgratings / $row->imgcount, 2);
            $avgdone      = true;
          }
        }
      }
      if($avgdone)
      {
        $clause = 'ROUND(LEAST(IF(imgvotes > 0, (('.$avgimgvote.'*'.$avgimgrating.') + '.$imgvotesum.') / ('.$avgimgvote.' + '.$imgvotes.'), 0.0), '.(float)$maxvoting.'), 2)';
      }
    }

    return $clause;
  }

  /**
   * Converts a given size with units e.g. read from php.ini to bytes.
   *
   * @param   string  $val  Value with units (e.g. 8M)
   * @return  int     Value in bytes
   * @since   3.0
   */

  public static function iniToBytes($val)
  {
    $val = trim($val);

    switch(strtolower(substr($val, -1)))
    {
      case 'm':
        $val = (int)substr($val, 0, -1) * 1048576;
        break;
      case 'k':
        $val = (int)substr($val, 0, -1) * 1024;
        break;
      case 'g':
        $val = (int)substr($val, 0, -1) * 1073741824;
        break;
      case 'b':
        switch(strtolower(substr($val, -2, 1)))
        {
          case 'm':
            $val = (int)substr($val, 0, -2) * 1048576;
            break;
          case 'k':
            $val = (int)substr($val, 0, -2) * 1024;
            break;
          case 'g':
            $val = (int)substr($val, 0, -2) * 1073741824;
            break;
          default:
            break;
        }
        break;
      default:
        break;
    }

    return $val;
  }
}