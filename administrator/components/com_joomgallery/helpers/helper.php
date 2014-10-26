<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/helper.php $
// $Id: helper.php 4330 2013-09-08 08:19:39Z erftralle $
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
 * JoomGallery Global Helper for the Backend
 *
 * @static
 * @package JoomGallery
 * @since 1.5.5
 */
class JoomHelper
{
  /**
   * Add managers to the sub-menu
   *
   * @return  void
   * @since  2.0
   */
  public static function addSubmenu()
  {
    $current_controller = JRequest::getCmd('controller', 'control');

    $controllers = array( 'control'     => JText::_('COM_JOOMGALLERY_CONTROL_PANEL'),
                          'categories'  => JText::_('COM_JOOMGALLERY_CATEGORY_MANAGER'),
                          'images'      => JText::_('COM_JOOMGALLERY_IMAGE_MANAGER'),
                          'comments'    => JText::_('COM_JOOMGALLERY_COMMENTS_MANAGER'),
                          'upload'      => JText::_('COM_JOOMGALLERY_IMAGE_UPLOAD'),
                          'ajaxupload'  => JText::_('COM_JOOMGALLERY_AJAX_UPLOAD'),
                          'batchupload' => JText::_('COM_JOOMGALLERY_BATCH_UPLOAD'),
                          'ftpupload'   => JText::_('COM_JOOMGALLERY_FTP_UPLOAD'),
                          'jupload'     => JText::_('COM_JOOMGALLERY_JAVA_UPLOAD'),
                          'config'      => JText::_('COM_JOOMGALLERY_CONFIGURATION_MANAGER'),
                          'cssedit'     => JText::_('COM_JOOMGALLERY_CUSTOMIZE_CSS'),
                          'migration'   => JText::_('COM_JOOMGALLERY_MIGRATION_MANAGER'),
                          'maintenance' => JText::_('COM_JOOMGALLERY_MAINTENANCE_MANAGER'),
                          'help'        => JText::_('COM_JOOMGALLERY_HELP')
                        );

    $canDo = self::getActions();

    if(!JoomConfig::getInstance()->get('jg_disableunrequiredchecks') && !$canDo->get('joom.upload') && !count(JoomHelper::getAuthorisedCategories('joom.upload')))
    {
      unset($controllers['upload']);
      unset($controllers['batchupload']);
      unset($controllers['ftpupload']);
      unset($controllers['jupload']);
      unset($controllers['ajaxupload']);
    }

    if(!$canDo->get('core.admin'))
    {
      unset($controllers['config']);
      unset($controllers['cssedit']);
      unset($controllers['maintenance']);
    }

    foreach($controllers as $controller => $title)
    {
      JHtmlSidebar::addEntry( $title,
                              'index.php?option='._JOOM_OPTION.'&controller='.$controller,
                              $controller == $current_controller
                            );
    }
  }

  /**
   * Returns a list of the actions that can be performed
   *
   * @param   string  $type The type of the content to check
   * @param   int     $id   The ID of the content (category or image)
   * @return  JObject An object holding the results of the check
   * @since   2.0
   */
  public static function getActions($type = 'component', $id = 0)
  {
    static $cache = array();

    // Create a unique key for the this pair of parameters
    $key = $type.':'.$id;

    if(isset($cache[$key]))
    {
      return $cache[$key];
    }

    $user   = JFactory::getUser();
    $result = new JObject();

    $actions = array('core.admin', 'core.manage', 'joom.upload', 'joom.upload.inown', 'core.create', 'joom.create.inown', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete');

    switch($type)
    {
      case 'category':
        $assetName = _JOOM_OPTION.'.category.'.$id;
        break;
      case 'image':
        $assetName = _JOOM_OPTION.'.image.'.$id;
        break;
      default:
        $assetName = _JOOM_OPTION;
        break;
    }

    foreach($actions as $action)
    {
      $result->set($action, $user->authorise($action, $assetName));
    }

    // Store the result for better performance
    $cache[$key] = $result;

    return $result;
  }

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
   * Returns all parent categories of a specific category
   *
   * @param   int     $category The ID of the specific child category
   * @param   boolean $child    True, if category itself shall also be returned, defaults to false
   * @return  array   An array of parent category objects with cid,name,parent_id
   * @since   1.5.5
   */
  public static function getAllParentCategories($category, $child = false)
  {
    // Get category structure from ambit
    $ambit      = JoomAmbit::getInstance();
    $cats       = $ambit->getCategoryStructure();
    $parents    = array();

    if(!$category)
    {
      return $parents;
    }

    if($child)
    {
      $parents[$category]            = new stdClass();
      $parents[$category]->cid       = $cats[$category]->cid;
      $parents[$category]->name      = $cats[$category]->name;
      $parents[$category]->parent_id = $cats[$category]->parent_id;
      $parents[$category]->published = $cats[$category]->published;
    }

    $parentcat = $cats[$category]->parent_id;
    while($parentcat != 1)
    {
      $category   = $parentcat;
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
   * Returns all categories and their sub-categories with published or no images
   *
   * @param   int     $cat          Category ID
   * @param   boolean $rootcat      True, if $cat shall also be returned as an
   *                                element of the array
   * @param   boolean $noimgcats    True if @return shall also include categories
   *                                with no images
   * @param   boolean $all          True if all categories shall be selected, defaults to true
   * @param   boolean $nohiddencats True, if sub-categories of hidden categories should be
   *                                filtered out, defaults to false
   * @return  array   An array of found categories
   * @since   1.5.5
   */
  public static function getAllSubCategories($cat, $rootcat = false, $noimgcats = false, $all = true, $nohiddencats = false)
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
   * Wrap text
   *
   * @param   string  $text Text to wrap
   * @param   int     $nr   Number of chars to wrap
   * @return  string  Wrapped text
   * @since   1.0.0
   */
  public static function processText($text, $nr = 40)
  {
    $mytext   = explode(' ', trim($text));
    $newtext  = array();
    foreach($mytext as $k => $txt)
    {
      if(strlen($txt) > $nr)
      {
        $txt  = wordwrap($txt, $nr, '- ', 1);
      }
      $newtext[]  = $txt;
    }

    return implode(' ', $newtext);
  }

  /**
   * Reads the category path from array.
   * If not set read db and add to array.
   *
   * @param   int     $catid  The ID of the category
   * @return  string  The category path
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
            ->where('cid= '.$catid);

      $database->setQuery($query);
      if(!$path = $database->loadResult())
      {
        $catpath[$catid] = '';
      }
      else
      {
        $catpath[$catid] = $path.'/';
      }
    }

    return $catpath[$catid];
  }

  /**
   * Returns the rating clause for an SQL - query dependent on the
   * rating calculation method selected.
   *
   * @param   string  $tablealias   Table alias
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
   * Returns the rating of an image
   *
   * @param   string  $imgid   Image id to get the rating for
   * @return  float   Rating
   * @since   1.5.6
   */
  public static function getRating($imgid)
  {
    $db     = JFactory::getDBO();
    $rating = 0.0;

    $query = $db->getQuery(true)
          ->select(JoomHelper::getSQLRatingClause().' AS rating')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('id = '.$imgid);

    $db->setQuery($query);
    if(($result = $db->loadResult()) != null)
    {
      $rating = $result;
    }

    return $rating;
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