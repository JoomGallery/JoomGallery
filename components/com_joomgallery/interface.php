<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/interface.php $
// $Id: interface.php 4408 2014-07-12 08:24:56Z erftralle $
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
 * The JoomGallery interface class provides an interface / API
 * to other Joomla extensions to use functions of the Gallery,
 * e.g. to display thumbnails in a Plugin or Module.
 *
 * You just need to include this file, create an interface object
 * and set some options if you want to adjust the output, before
 * using one of the functions.
 * If you display any HTML output, you should once call getPageHeader()
 * first
 *
 * @package JoomGallery
 * @since   1.0.0
 */
class JoomInterface
{
  /**
   * Holds the interface configuration
   *
   * @var array
   */
  protected $_config = array();

  /**
   * JApplication object
   *
   * @var object
   */
  protected $_mainframe;

  /**
   * JDatabase object
   *
   * @var object
   */
  protected $_db;

  /**
   * JoomAmbit object
   *
   * @var object
   */
  protected $_ambit;

  /**
   * Holds the JoomGallery configuration
   *
   * @var object
   */
  protected $_jg_config  = null;

  /**
   * Holds the stored interface configuration
   *
   * @var array
   */
  protected $_storedConfig = array();

  /**
   * JUser object
   *
   * @var object
   */
  protected $_user;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    // Load JoomGallery defines
    require_once JPATH_ADMINISTRATOR.'/components/com_joomgallery/includes/defines.php';
    // Register some classes
    JLoader::register('JoomConfig', JPATH_ADMINISTRATOR.'/components/'._JOOM_OPTION.'/helpers/config.php');
    JLoader::register('JoomHelper', JPATH_ROOT.'/components/'._JOOM_OPTION.'/helpers/helper.php');
    JLoader::register('JoomAmbit',  JPATH_ROOT.'/components/'._JOOM_OPTION.'/helpers/ambit.php');
    // Add include path for JoomGallery tables
    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/'._JOOM_OPTION.'/tables');
    // Add include path for JoomGallery HTML functions
    JHtml::addIncludePath(JPATH_ADMINISTRATOR.'/components/'._JOOM_OPTION.'/helpers/html');

    $this->_mainframe = JFactory::getApplication();
    $this->_db        = JFactory::getDBO();
    $this->_ambit     = JoomAmbit::getInstance();
    $this->_jg_config = JoomConfig::getInstance();
    $this->_user      = JFactory::getUser();

    // Include language for display
    $language = JFactory::getLanguage();
    $language->load(_JOOM_OPTION);

    // Load JoomGallery plugins
    JPluginHelper::importPlugin('joomgallery');

    // Set some default values for options given in global JG config (may be overridden)
    $this->_config['showhits']        = $this->_jg_config->get('jg_showhits');
    $this->_config['showdownloads']   = $this->_jg_config->get('jg_showdownloads');
    $this->_config['showpicasnew']    = $this->_jg_config->get('jg_showpicasnew');
    $this->_config['showtitle']       = $this->_jg_config->get('jg_showtitle');
    $this->_config['showauthor']      = $this->_jg_config->get('jg_showauthor');
    $this->_config['showrate']        = $this->_jg_config->get('jg_showcatrate');
    $this->_config['shownumcomments'] = $this->_jg_config->get('jg_showcatcom');
    $this->_config['showdescription'] = $this->_jg_config->get('jg_showcatdescription');

    $this->_config['openimage']       = $this->_jg_config->get('jg_detailpic_open');

    // Further defaults (not given by JG config)
    // - Category path links to category
    $this->_config['showcatlink']     = 1;
    // - Comma-separated list of categories to filter from (empty: all categories, default)
    $this->_config['categoryfilter']  = '';
    // - Display last comment (see Module JoomImages) not implemented yet!
    $this->_config['showlastcomment'] = 0;
    // - Make use of hidden images and images in hidden categories
    $this->_config['showhidden']      = 0;

    // Store the config for being able to reset it later on (if useful)
    $this->storeConfig();
  }

  /**
   * Passes a whole array of config items, existing (default)
   * values are overwritten if a new item with the same key
   * is passed.
   *
   * @param   array   $config An array of settings
   * @return  void
   * @since   1.0.0
   */
  public function setConfig($config)
  {
    foreach($config as $key => $value)
    {
      $config[$key] = $this->_db->escape($value);
    }
    // Merge new array into existing one, overwriting if needed:
    $this->_config = array_merge($this->_config, $config);
  }

  /**
   * Sets a single option in the interface settings
   * If the key already exists, the setting will be overridden.
   *
   * @param   string  $key    The key of the new setting
   * @param   string  $value  The value of the new setting
   * @return  void
   * @since   1.0.0
   */
  public function addConfig($key, $value = '')
  {
    $this->_config[$key] = $this->_db->escape($value);
  }

  /**
   * Requests string (e.g. modification of a SQL query or true/false)
   * associated with config option $key.
   * If the according value has not been set with addConfig
   * before, a default is returned. Config options are not used
   * directly for security.
   *
   * @param   string  $key The key of the requested setting
   * @return  string  The requested setting, boolean false, if the key was not found
   * @since   1.0.0
   */
  public function getConfig($key)
  {
    if(array_key_exists($key, $this->_config))
    {
      // Access filtered to special keys (DB query strings)
      if($key == 'hidebackend')
      {
        if(    $this->_config['hidebackend'] == 'true'
            || $this->_config['hidebackend'] === true
            || $this->_config['hidebackend'] == 1
          )
        {
          return 'jg.owner != 0';
        }
        else
        {
          return '';
        }
      }
      elseif($key == 'categoryfilter')
      {
        $catids = trim($this->_db->escape($this->_config['categoryfilter']));
        if($catids != '')
        {
          return 'jg.catid IN ('.$catids.')';
        }
        else
        {
          return '';
        }
      }
      else
      {
        // Regular keys
        return $this->_config[$key];
      }
    }
    else
    {
      return false;
    }
  }

  /**
   * Stores the config in order to be able to reset it later on
   *
   * @return  void
   * @since   1.5.0
   */
  public function storeConfig()
  {
    $this->_storedConfig = $this->_config;
  }

  /**
   * Resets the config in order to get the settings as they were
   * at the point of time when 'storeConfig' was called lastly.
   *
   * @return  void
   * @since   1.5.0
   */
  public function resetConfig()
  {
    $this->_config = $this->_storedConfig;
  }

  /**
   * Returns config value associated with config option $key
   * of the global configuration of JoomGallery.
   *
   * @param   string  $key The key of the requested setting
   * @return  string  The requested setting, boolean false, if the key was not found
   * @since   1.0.0
   */
  public function getJConfig($key)
  {
    return $this->_jg_config->get($key);
  }

  /**
   * Returns the JoomGallery ambit object
   *
   * @return  object  JoomAmbit object
   * @since   1.5.5
   */
  public function getAmbit()
  {
    return $this->_ambit;
  }

  /**
   * Returns version string of installed JoomGallery
   *
   * @return  string  The version string
   * @since   1.5.0
   */
  public function getGalleryVersion()
  {
    return '3.3';
  }

  /**
   * Returns an Itemid associated with the gallery.
   *
   * At first check out, if an Itemid was set via the interface,
   * if not, take the Itemid provided by JoomAmbit.
   *
   * @param   boolean     $string True, if a string like '&Itemid=X' should be returned.
   * @return  int/string  The Itemid for use in URLs ('&Itemid=X' or as integer)
   * @since   1.0.0
   */
  public function getJoomId($string = true)
  {
    if(isset($this->_config['Itemid']) && $this->_config['Itemid'])
    {
      $Itemid = intval($this->_config['Itemid']);

      return ($string && $Itemid) ? '&Itemid='.$Itemid : $Itemid;
    }
    else
    {
      return $this->_ambit->getItemid($string);
    }
  }

  /**
   * Corrects a link with the right 'option' and 'Itemid' vars of JoomGallery
   *
   * @param   string  $url    The link to complete
   * @param   boolean $xhtml  True, if all '&' appearances shall be replaced with '&amp;', defaults to true
   * @return  string  The corrected link
   * @since   1.5.5
   */
  public function route($url, $xhtml = true)
  {
    // Get the router
    $router = $this->_mainframe->getRouter();
    // Get current values of vars 'option' and 'Itemid'
    $option = $router->getVar('option');
    $Itemid = $router->getVar('Itemid');
    // Set vars 'option' and 'Itemid'
    $router->setVar('option', _JOOM_OPTION);
    $router->setVar('Itemid', $this->getJoomId(false));

    $url = JRoute::_($url, $xhtml);
    $routervars = $router->getVars();
    if(is_null($option))
    {
      // Delete the var from array
      unset($routervars['option']);
    }
    else
    {
      $routervars['option'] = $option;
    }
    if(is_null($Itemid))
    {
      unset($routervars['Itemid']);
    }
    else
    {
      $routervars['Itemid'] = $Itemid;
    }
    $router->setVars($routervars, false);

    return $url;
  }

  /**
   * Simple forwarding of JHTML::_('joomgallery.openimage'):
   * Returns the link to the thumb, detail or original image.
   *
   * @param   int/object  $img  The image ID or the image object to use
   * @param   string      $type The image type ('thumb', 'img', 'orig')
   * @return  string      Link to the image
   * @since   1.5.5
   */
  public function getImageLink($img, $type = false)
  {
    // Get the router
    $router = $this->_mainframe->getRouter();
    // Get current values of vars 'option' and 'Itemid'
    $option = $router->getVar('option');
    $Itemid = $router->getVar('Itemid');

    // Set vars 'option' and 'Itemid'
    $router->setVar('option', _JOOM_OPTION);
    $router->setVar('Itemid', $this->getJoomId(false));

    $link = JHTML::_('joomgallery.openimage', $this->_config['openimage'], $img, $type, $this->getConfig('group'));
    if($title = JHtml::_('joomgallery.getTitleforATag', $img, false))
    {
      $link .= '" title="'.$title;
    }

    // Reset vars 'option' and 'Itemid'
    // if the preserved values are null delete the var formerly setted
    // from array of vars
    $routervars = $router->getVars();
    if(is_null($option))
    {
      // Delete the var from array
      unset($routervars['option']);
    }
    else
    {
      $routervars['option'] = $option;
    }
    if(is_null($Itemid))
    {
      unset($routervars['Itemid']);
    }
    else
    {
      $routervars['Itemid'] = $Itemid;
    }
    $router->setVars($routervars, false);

    return $link;
  }

  /**
   * Adds all elements needed to display JoomGallery images
   * properly like CSS. The necessary Javascript is included in
   * the JoomGallery JHTML function openImage().
   *
   * @return  void
   * @since   1.5.5
   */
  public function getPageHeader()
  {
    $document = JFactory::getDocument();

    // Add the CSS file generated from backend settings
    $document->addStyleSheet($this->_ambit->getStyleSheet('joom_settings.css'));

    // Add the main CSS file
    $document->addStyleSheet($this->_ambit->getStyleSheet('joomgallery.css'));

    // Add the RTL CSS file if an RTL language is used
    if(JFactory::getLanguage()->isRTL())
    {
      $document->addStyleSheet($this->_ambit->getStyleSheet('joomgallery_rtl.css'));
    }

    // Add individual CSS file if it exists
    if(file_exists(JPATH_ROOT.'/media/joomgallery/css/joom_local.css'))
    {
      $document->addStyleSheet($this->_ambit->getStyleSheet('joom_local.css'));
    }
  }

  /**
   * Creates HTML for linked thumbnail of one image,
   * with display options and style just like in JG
   *
   * @param   object  $obj    DB-row coming from this interface, e.g. getPicsByCategory
   * @param   boolean $linked If true, we will link the thumbnail, defaults to true
   * @param   string  $class  Optional, addional css class name which is assigned to the img tag
   * @param   string  $div    Optional css class name which is assigned to a div around the img tag
   * @param   string  $extra  Optional, adddional HTML code, which is placed in the img tag
   * @param   string  $type   Optional, image type the link shall open (thumb, img, orig)
   * @return  string  HTML displaying thumbnail (linked, like configured in JG if $linked = true)
   * @since   1.0.0
   */
  public function displayThumb($obj, $linked = true, $class = null, $div = null, $extra = null, $type = false)
  {
    $output = '';
    if($obj->id != '')
    {
      // Get the router
      $router = $this->_mainframe->getRouter();
      // Get current values of vars 'option' and 'Itemid'
      $option = $router->getVar('option');
      $Itemid = $router->getVar('Itemid');
      // Set vars 'option' and 'Itemid'
      $router->setVar('option', _JOOM_OPTION);
      $router->setVar('Itemid', $this->getJoomId(false));

      if($div)
      {
        $output .= '<div class="'.$div.'">';
      }

      if($linked)
      {
        // Check for link to category
        if(isset($this->_config['catlink']) && $this->_config['catlink'] == 1)
        {
          $link = JRoute::_('index.php?&view=category&catid='.$obj->catid);
        }
        else
        {
          $link = JHTML::_('joomgallery.openimage', $this->_config['openimage'], $obj, $type, $this->getConfig('group'));
          if($title = JHtml::_('joomgallery.getTitleforATag', $obj, false))
          {
            $link .= '" title="'.$title;
          }
        }

        $output .= '  <a href="'.$link.'" class="jg_catelem_photo">';
      }
      if($class)
      {
        $class = ' '.$class;
      }
      if($extra)
      {
        $extra = ' '.$extra;
      }
      $output   .= '    <img src="'.$this->_ambit->getImg('thumb_url', $obj).'" class="jg_photo'.$class.'" alt="'.$obj->imgtitle.'"'.$extra.' />';
      if($linked)
      {
        $output .= '  </a>';
      }
      if($div)
      {
        $output .= '</div>';
      }
      $routervars = $router->getVars();
      if(is_null($option))
      {
        // Delete the var from array
        unset($routervars['option']);
      }
      else
      {
        $routervars['option'] = $option;
      }
      if(is_null($Itemid))
      {
        unset($routervars['Itemid']);
      }
      else
      {
        $routervars['Itemid'] = $Itemid;
      }
      $router->setVars($routervars, false);
    }
    else
    {
      $output .= "    &nbsp;\n";
    }

    return $output;
  }

  /**
   * Creates HTML for linked detail image of one picture-$obj,
   * with display options & style just like in JG
   *
   * @param   object  $obj    DB-row coming from this interface, e.g. getPicsByCategory
   * @param   boolean $linked If true, we will link the thumbnail, defaults to true
   * @param   string  $class  Optional, addional css class name which is assigned to the img tag
   * @param   string  $div    Optional css class name which is assigned to a div around the img tag
   * @param   string  $extra  Optional, addional HTML code, which is placed in the img tag
   * @param   string  $type   Optional, image type the link shall open (thumb, img, orig)
   * @return  string  HTML displaying detail image (linked, like configured in JG if $linked = true)
   * @since   1.0.0
   */
  public function displayDetail($obj, $linked = true, $class = null, $div = null, $extra = null, $type = false)
  {
    $output = '';
    if($obj->id != '')
    {
      // Get the router
      $router = $this->_mainframe->getRouter();
      // Get current values of vars 'option' and 'Itemid'
      $option = $router->getVar('option');
      $Itemid = $router->getVar('Itemid');
      // Set vars 'option' and 'Itemid'
      $router->setVar('option', _JOOM_OPTION);
      $router->setVar('Itemid', $this->getJoomId(false));

      if($div)
      {
        $output .= '<div class="'.$div.'">';
      }
      if($linked)
      {
        // Check for link to category
        if(isset($this->_config['catlink']) && $this->_config['catlink'] == 1)
        {
          $link = JRoute::_('index.php?&view=category&catid='.$obj->catid);
        }
        else
        {
          $link = JHTML::_('joomgallery.openimage', $this->_config['openimage'], $obj, $type, $this->getConfig('group'));
          if($title = JHtml::_('joomgallery.getTitleforATag', $obj, false))
          {
            $link .= '" title="'.$title;
          }
        }

        $output .= '  <a href="'.$link.'" class="jg_catelem_photo">';
      }
      if($class)
      {
        $class = ' '.$class;
      }
      if($extra)
      {
        $extra = ' '.$extra;
      }
      $output   .= '    <img src="'.$this->_ambit->getImg('img_url', $obj).'" class="jg_photo'.$class.'" alt="'.$obj->imgtitle.'"'.$extra.' />';
      if($linked)
      {
        $output .= '  </a>';
      }
      if($div)
      {
        $output .= '</div>';
      }
      $routervars = $router->getVars();
      if(is_null($option))
      {
        // Delete the var from array
        unset($routervars['option']);
      }
      else
      {
        $routervars['option'] = $option;
      }
      if(is_null($Itemid))
      {
        unset($routervars['Itemid']);
      }
      else
      {
        $routervars['Itemid'] = $Itemid;
      }
      $router->setVars($routervars, false);
    }
    else
    {
      $output .= "    &nbsp;\n";
    }

    return $output;
  }

  /**
   * Creates HTML for description of one image,
   * with display options and style just like in JG.
   * Adjustments are possible via the interface options.
   *
   * @param   object  $obj  DB-row coming from this interface, e.g. getPicsByCategory
   * @return  string  HTML of thumb description (like configured in JG or in the interface)
   * @since   1.0.0
   */
  public function displayDesc($obj)
  {
    if($this->getConfig('disable_infos'))
    {
      return '';
    }

    // Get the router
    $router = $this->_mainframe->getRouter();
    // Get current values of vars 'option' and 'Itemid'
    $option = $router->getVar('option');
    $Itemid = $router->getVar('Itemid');
    // Set vars 'option' and 'Itemid'
    $router->setVar('option', _JOOM_OPTION);
    $router->setVar('Itemid', $this->getJoomId(false));

    $output = "<ul>\n";

    if($this->getConfig('showtitle') || $this->getConfig('showpicasnew'))
    {
      $output .= "  <li>";
      if($this->getConfig('showtitle'))
      {
        $output .= '<b>'.$obj->imgtitle.'</b>';
      }
      if($this->getConfig('showpicasnew'))
      {
        $output.= JoomHelper::checkNew($obj->imgdate, $this->_jg_config->get('jg_daysnew'));;
      }
      $output .= "  </li>\n";
    }

    if($this->getConfig('showauthor'))
    {
      if($obj->imgauthor)
      {
        $authorowner = $obj->imgauthor;
      }
      else
      {
        $authorowner = JHTML::_('joomgallery.displayname', $obj->owner);
      }

      $output .= "  <li>".JText::sprintf('COM_JOOMGALLERY_COMMON_AUTHOR_VAR', $authorowner);
      $output .= "</li>\n";
    }

    if($this->getConfig('showcategory'))
    {
      $catpath =
      $output .= "  <li>";

      if($this->getConfig('showcatlink'))
      {
        $catlink = '<a href="'.JRoute::_('index.php?view=category&catid='.$obj->catid)
                   .'">'.$obj->cattitle
                   .'</a>';
        $output .= JText::sprintf('COM_JOOMGALLERY_COMMON_CATEGORY_VAR',$catlink);
      }
      else
      {
        $output .= JText::sprintf('COM_JOOMGALLERY_COMMON_CATEGORY_VAR',$obj->cattitle);
      }
      $output .= "  </li>";
    }

    if($this->getConfig('showhits'))
    {
      $output .= "  <li>".JText::sprintf('COM_JOOMGALLERY_COMMON_HITS_VAR', $obj->hits)."</li>";
    }
    if($this->getConfig('showdownloads'))
    {
      $output .= "  <li>".JText::sprintf('COM_JOOMGALLERY_COMMON_DOWNLOADS_VAR', $obj->downloads)."</li>";
    }
    if($this->getConfig('showrate'))
    {
      $output .= '  <li>'.JHTML::_('joomgallery.rating', $obj, false, 'jg_starrating_cat').'</li>';
    }
    if ($this->getConfig('showimgdate'))
    {
      $output .= '<li>'.JText::sprintf('COM_JOOMGALLERY_COMMON_UPLOAD_DATE', '<br />'.JHTML::_('date', $obj->imgdate, JText::_($this->getConfig('dateformat')))).'</li>';
    }
    if($this->getConfig('shownumcomments'))
    {
      $output .='  <li>'. JText::sprintf('COM_JOOMGALLERY_COMMON_COMMENTS_VAR', $obj->cmtcount).'</li>';
    }
    if($this->getConfig('showdescription')  && $obj->imgtext)
    {
      $output .= '  <li>'. JText::sprintf('COM_JOOMGALLERY_COMMON_DESCRIPTION_VAR', $obj->imgtext).'</li>';
    }
    if($this->getConfig('showcmtdate') == 1 && !is_null($obj->cmtdate))
    {
      $output .= '<li>'.JText::sprintf('COM_JOOMGALLERY_COMMON_COMMENTS_LASTDATE', JHTML::_('date', $obj->cmtdate, JText::_($this->getConfig('dateformat')))).'</li>';
    }
    if($this->getConfig('showcmttext') == 1 && !is_null($obj->cmtdate))
    {
      // Comment username
      if($obj->cmtuserid != 0)
      {
        $cmtname = JHTML::_('joomgallery.displayname', $obj->cmtuserid);
      }
      else
      {
        $cmtname = $obj->cmtname;
      }

      // Comment text
      $output .= '<li>'.JText::sprintf('COM_JOOMGALLERY_COMMON_COMMENT_WITH_AUTHOR', $cmtname, $obj->cmttext).'</li>';
    }

    $results  = $this->_mainframe->triggerEvent('onJoomAfterDisplayThumb', array($obj->id));
    $output  .= trim(implode('', $results));

    $output .= '</ul>';

    $routervars = $router->getVars();
    if(is_null($option))
    {
      // Delete the var from array
      unset($routervars['option']);
    }
    else
    {
      $routervars['option'] = $option;
    }
    if(is_null($Itemid))
    {
      unset($routervars['Itemid']);
    }
    else
    {
      $routervars['Itemid'] = $Itemid;
    }
    $router->setVars($routervars, false);

    return $output;
  }

  /**
   * Creates HTML for the given thumbnails for displaying them like in category view
   *
   * @param   array   $rows   An array of database objects for the thumbnails
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public function displayThumbs($rows)
  {
    if(empty($rows))
    {
      return '';
    }

    $numcols = $this->getConfig('columns');
    if(!$numcols)
    {
      $numcols = $this->getConfig('default_columns');
      if(!$numcols)
      {
        $numcols = 2;
      }
    }

    $elem_width =  floor(99 / $numcols);

    $return     = '';
    //$return    .= "\n".'<div class="gallerytab">'."\n";
    $return    .= '<div class="jg_row jg_row1">';
    $rowcount   = 0;
    $itemcount  = 0;

    foreach($rows as $row)
    {
      if(($itemcount % $numcols == 0) && ($itemcount != 0))
      {
          $return .='</div><div class="jg_row jg_row'.($rowcount % 2 + 1).'">'."\n";
          $rowcount++;
      }

      $return .= '<div class="jg_element_cat" style="width:'.$elem_width.'%">'."\n";
      $type = 'img';
      if(   (!is_numeric($this->getConfig('openimage')) || $this->getConfig('openimage') > 0)
        &&  ($this->getJConfig('jg_lightboxbigpic') || $this->getConfig('type') == 'img' || $this->getConfig('type') == 'orig')
        &&  file_exists($this->_ambit->getImg('orig_path', $row))
        )
      {
        $type = 'orig';
      }
      if($this->getConfig('type') == 'img' || $this->getConfig('type') == 'orig')
      {
        $return .= '  '.$this->displayDetail($row, true, null, 'jg_imgalign_catimgs', null, $type);
      }
      else
      {
        $return .= '  '.$this->displayThumb($row, true, null, 'jg_imgalign_catimgs', null, $type);
      }

      if(!$this->getConfig('disable_infos'))
      {
        $return .= '  <div class ="jg_catelem_txt">'."\n";
        $return .= '    '.$this->displayDesc($row);
        $return .= '  </div>'."\n";
      }

      $return .= '</div>'."\n";

      $itemcount++;
    }

    $return.= '</div>'."\n";//.'</div>';

    return $return;
  }

  /**
   * Adds the WHERE conditions for filtering out disallowed images to a query
   *
   * @param   $query  object  The query to extend
   * @param   $access array   Access levels to filter for, null to use the ones of the current user
   * @return  void
   * @since   2.0
   */
  public function addImageWhere($query, $access = null)
  {
    $query->where('jgc.published = 1')
          ->where('jg.approved   = 1')
          ->where('jg.published  = 1');

    if(!$access)
    {
      $catids = array_keys($this->_ambit->getCategoryStructure());
      $query->where('jg.catid IN ('.implode(',', $catids).')');
      $query->where('jg.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
      $query->where('jgc.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
    }
    else
    {
      $query->where('jg.access IN ('.implode(',', $access).')');
      $query->where('jgc.access IN ('.implode(',', $access).')');
    }

    if(!$this->getConfig('showhidden'))
    {
      $query->where('jgc.hidden    = 0')
            ->where('jgc.in_hidden = 0')
            ->where('jg.hidden     = 0');
    }

    if($categoryfilter = $this->getConfig('categoryfilter'))
    {
      $query->where($categoryfilter);
    }

    if($hidebackend = $this->getConfig('hidebackend'))
    {
      $query->where($hidebackend);
    }

    return $query;
  }

  /**
   * Returns the number of images of a user
   *
   * @param   int   $userId The user ID of the user.
   * @param   array $access Access levels to filter for, null to use the ones of the current user
   * @return  int   Number of images the user has uploaded
   * @since   1.0.0
   */
  public function getNumPicsOfUser($userId, $access = null)
  {
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('COUNT(jg.id)')
          ->from(_JOOM_TABLE_IMAGES.' AS jg')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jg.owner = '.$userId);
    $this->addImageWhere($query, $access);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns the number of pictures a user is tagged in
   *
   * @param   int   $userId The ID of the user.
   * @param   array $access Access levels to filter for, null to use the ones of the current user
   * @return  int   Number of images the user is tagged in
   * @since   1.0.0
   */
  public function getNumPicsUserTagged($userId, $access = null)
  {
    $userId = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('COUNT(jgn.nid)')
          ->from(_JOOM_TABLE_NAMESHIELDS.' AS jgn')
          ->leftJoin(_JOOM_TABLE_IMAGES.' AS jg ON jgn.npicid = jg.id')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jgn.nuserid   = '.$userId);
    $this->addImageWhere($query, $access);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns the number of images a user has favoured
   *
   * @param   int   $userId The ID of the user.
   * @param   array $access Access levels to filter for, null to use the ones of the current user
   * @return  int   Number of images the user has favoured
   * @since   1.0.0
   */
  public function getNumPicsUserFavoured($userId, $access = null)
  {
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('piclist')
          ->from(_JOOM_TABLE_USERS)
          ->where('uuserid = '.$userId);

    $this->_db->setQuery($query);
    $piclist = $this->_db->loadResult();

    if(!$piclist)
    {
      return 0;
    }

    $query = $this->_db->getQuery(true)
          ->select('COUNT(jg.id)')
          ->from(_JOOM_TABLE_IMAGES.' AS jg')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jg.id IN ('.$piclist.')');
    $this->addImageWhere($query, $access);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns the number of images a user has commented on
   *
   * @param   int   $userId The ID of the user.
   * @param   array $access Access levels to filter for, null to use the ones of the current user
   * @return  int   Number of images the user has commented on
   * @since   1.0.0
   */
  public function getNumCommentsUser($userId, $access = null)
  {
    return $this->getNumComments($access, $userId);
  }

  /**
   * Returns the total number of comments (published) in the gallery
   *
   * @param   array $access Access levels to filter for, null to use the ones of the current user
   * @return  int   The number of comments published in the gallery
   * @since   1.0.0
   */
  public function getNumComments($access = null, $userId = 0)
  {
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('COUNT(cmtid)')
          ->from(_JOOM_TABLE_COMMENTS.' AS jgco')
          ->leftJoin(_JOOM_TABLE_IMAGES.' AS jg ON jgco.cmtpic = jg.id')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jgco.published = 1')
          ->where('jgco.approved  = 1');

    if($userId)
    {
      $query->where('jgco.userid  = '.$userId);
    }

    $this->addImageWhere($query, $access);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns a query for counting all comments of a specific image.
   * This query has to be used as a sub-query in which context jg.id is defined
   *
   * @return  object  A JDatabaseQuery object to be used as a sub-query
   * @since   2.0
   */
  public function getNumCommentsSubQuery()
  {
    return $this->_db->getQuery(true)
          ->select('COUNT(cmtid)')
          ->from(_JOOM_TABLE_COMMENTS.' AS jgcosub')
          ->where('cmtpic         = jg.id')
          ->where('jgcosub.published = 1')
          ->where('jgcosub.approved  = 1');
  }

  /**
   * Returns images of a user
   *
   * @param   int     $userId     Joomla ID of user
   * @param   array   $access     Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting    String for DB sorting
   * @param   int     $numPics    Limit number of pictures, leave away to return all
   * @param   int     $limitStart Where to start returning $numPics images
   * @return  array   An array of image objects from the database
   * @since   1.0.0
   */
  public function getPicsOfUser($userId, $access = null, $sorting = null, $numPics = null, $limitStart = 0)
  {
    // Validation
    $userId   = intval($userId);

    $query = $this->getImagesQuery($access)
          ->where('jg.owner = '.$userId);

    if($sorting)
    {
      $query->order($this->_db->escape($sorting));
    }

    $this->_db->setQuery($query, $limitStart, $numPics);

    return $this->_db->loadObjectList();
  }

   /**
   * Returns images a user is tagged in
   *
   * @param   int     $userId     Joomla ID of user
   * @param   array   $access     Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting    String for DB sorting
   * @param   int     $numPics    Limit number of pictures, leave away to return all
   * @param   int     $limitStart Where to start returning $numPics pictures
   * @return  array   An array of image objects from the database
   * @since   1.0.0
   */
  public function getPicsUserTagged($userId, $access = null, $sorting = null, $numPics = null, $limitStart = 0)
  {
    // Validation
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true);

    if($this->getConfig('shownumcomments'))
    {
      $query->select('('.$this->getNumCommentsSubQuery().') AS cmtcount');
    }

    $query->select('jg.id, jg.catid, jg.imgthumbname, jg.imgfilename, jg.owner, jg.imgauthor,
                    jg.imgdate, jg.imgtitle, jg.imgtext, jg.hits, jg.downloads, jg.imgvotes,
                    '.JoomHelper::getSQLRatingClause('jg').' AS rating,
                    jgc.name AS cattitle, jgc.catpath AS catpath')
          ->from(_JOOM_TABLE_NAMESHIELDS.' AS jgn')
          ->leftJoin(_JOOM_TABLE_IMAGES. ' AS jg ON jgn.npicid = jg.id')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jgn.nuserid   = '.$userId);

    if($this->getConfig('showlastcomment'))
    {
      $query->select('jgco.cmttext, jgco.cmtdate, jgco.userid, jgco.cmtid')
            ->leftJoin(_JOOM_TABLE_COMMENTS.' AS jgco ON jg.id = jgco.cmtpic')
            ->leftJoin(_JOOM_TABLE_COMMENTS.' AS jgco2 ON jgco.cmtpic = jgco2.cmtpic AND jgco.cmtdate  < jgco2.cmtdate')
            ->where('jgco2.cmtpic IS NULL');
    }

    $this->addImageWhere($query, $access);

    if($sorting)
    {
      $query->order($this->_db->escape($sorting));
    }

    $this->_db->setQuery($query, $limitStart, $numPics);

    return $this->_db->loadObjectList();
  }

  /**
   * Returns the images a user has favoured
   *
   * @param   int     $userId     Joomla ID of user
   * @param   array   $access     Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting    String for DB sorting
   * @param   int     $numPics    Limit number of images, leave away to return all
   * @param   int     $limitStart Where to start returning $numPics images
   * @return  array   An array of image objects from the database
   * @since   1.0.0
   */
  public function getPicsUserFavoured($userId, $access = null, $sorting = null, $numPics = null, $limitStart = 0)
  {
    // Validation
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('piclist')
          ->from(_JOOM_TABLE_USERS)
          ->where('uuserid = '.$userId);

    $this->_db->setQuery($query);
    $piclist = $this->_db->loadResult();

    if(!$piclist)
    {
      return array();
    }

    $query = $this->getImagesQuery($access)
          ->where('jg.id IN ('.$piclist.')');

    if($sorting)
    {
      $query->order($this->_db->escape($sorting));
    }

    $this->_db->setQuery($query, $limitStart, $numPics);

    return $this->_db->loadObjectList();
  }

  /**
   * Returns the comments of a user on images
   *
   * @param   int     $userId       Joomla ID of user
   * @param   array   $access       Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting      String for DB sorting (default: newest by ID)
   * @param   int     $numComments  Limit number of images, leave away to return all
   * @param   int     $limitStart   Where to start returning $numComments images
   * @return  array   An array of image objects from the database
   * @since   1.0.0
   */
  public function getCommentsUser($userId, $access = null, $sorting = 'jgco.cmtid DESC', $numComments = null, $limitStart = 0)
  {
    return $this->getComments($access, $sorting, $numComments, $limitStart, $userId);
  }

  /**
   * Returns all (or some ;) ) comments in the gallery as DB-rows
   *
   * @param   array   $access       Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting      String for DB sorting (default: Newest by ID)
   * @param   int     $numComments  Limit number of comments, leave away to return all
   * @param   int     $limitStart   Where to start returning $numComments comments
   * @param   int     $userId       Joomla ID of user
   * @return  array   An array of comment objects from the database
   * @since   1.0.0
   */
  public function getComments($access = null, $sorting = "jgco.cmtid DESC", $numComments = null, $limitStart = 0, $userId = 0)
  {
    $userId   = intval($userId);

    $query = $this->_db->getQuery(true)
          ->select('jg.id, jgco.cmttext, jgco.cmtdate, jgco.userid AS cmtuserid')
          ->select('jg.catid, jg.imgthumbname, jg.imgfilename, jg.owner, jg.imgauthor,
                    jg.imgdate, jg.imgtitle, jg.imgtext, jg.hits, jg.downloads, jg.imgvotes,
                    '.JoomHelper::getSQLRatingClause('jg').' AS rating,
                    jgc.name AS cattitle, jgc.catpath AS catpath')
          ->from(_JOOM_TABLE_COMMENTS.' AS jgco')
          ->leftJoin(_JOOM_TABLE_IMAGES.' AS jg ON jgco.cmtpic = jg.id')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jgco.published = 1')
          ->where('jgco.approved  = 1');

    if($userId)
    {
      $query->where('jgco.userid  = '.$userId);
    }

    $this->addImageWhere($query, $access);

    $this->_db->setQuery($query, $limitStart, $numComments);

    return $this->_db->loadObjectList();
  }

  /**
   * Returns db-row of one image, with optional access verification
   *
   * @param   int     $picid  ID of images in gallery
   * @param   array   $access Access levels to filter for, null to use the ones of the current user
   * @return  object  The image object from the database
   * @since   1.0.0
   */
  public function getPicture($picid, $access = null)
  {
    $picid    = intval($picid);

    $query = $this->getImagesQuery($access)
          ->where('jg.id = '.$picid);

    $this->_db->setQuery($query);

    return $this->_db->loadObject();
  }

  /**
   * Returns a query object containing the general data for requesting images data
   *
   * @param   array   $access Array of access levels to filter with
   * @return  object  A JDatabaseQuery object
   * @since   2.0
   */
  public function getImagesQuery($access = null)
  {
    $query = $this->_db->getQuery(true);

    if($this->getConfig('shownumcomments'))
    {
      $query->select('('.$this->getNumCommentsSubQuery().') AS cmtcount');
    }

    $query->select('jg.id, jg.catid, jg.imgthumbname, jg.imgfilename, jg.owner, jg.imgauthor,
                    jg.imgdate, jg.imgtitle, jg.imgtext, jg.hits, jg.downloads, jg.imgvotes,
                    '.JoomHelper::getSQLRatingClause('jg').' AS rating,
                    jgc.name AS cattitle, jgc.catpath AS catpath')
          ->from(_JOOM_TABLE_IMAGES.' AS jg')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid');

    if($this->getConfig('showlastcomment'))
    {
      $query->select('jgco.cmttext, jgco.cmtdate, jgco.userid, jgco.cmtid')
            ->leftJoin(_JOOM_TABLE_COMMENTS.' AS jgco ON jg.id = jgco.cmtpic')
            ->leftJoin(_JOOM_TABLE_COMMENTS.' AS jgco2 ON jgco.cmtpic = jgco2.cmtpic AND jgco.cmtdate  < jgco2.cmtdate')
            ->where('jgco2.cmtpic IS NULL');
    }

    $this->addImageWhere($query, $access);

    return $query;
  }

  /**
   * Returns the db-row of a random image, to which a user with has access to
   * (e.g. for a simple 1pic module)
   *
   * @param   array   $access   Access levels to filter for, null to use the ones of the current user
   * @return  object  An image object from the database
   * @since   1.0.0
   */
  public function getRandomPicture($access = null)
  {
    $query = $this->getImagesQuery($access)
          ->order('RAND()');

    $this->_db->setQuery($query);

    return $this->_db->loadObject();
  }

  /**
   * Returns the number of images in a category
   *
   * @param   int     $catid  ID of category
   * @param   array   $access Access levels to filter for, null to use the ones of the current user
   * @return  int     The number of images in the category
   * @since   1.0.0
   */
  public function getNumPicsByCategory($catid, $access = null)
  {
    $catid    = intval($catid);

    $query = $this->_db->getQuery(true)
          ->select('COUNT(jg.id)')
          ->from(_JOOM_TABLE_IMAGES.' AS jg')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid')
          ->where('jg.catid = '.$catid);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns image objects of all images in a category
   *
   * @param   int     $catid      The ID of the category
   * @param   array   $access     Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting    Sorting string
   * @param   int     $numPics    Limit number of images, leave away to return all
   * @param   int     $limitStart Where to start returning $numPics images
   * @return  array   An array of comment objects from the database
   * @since   1.0.0
   */
  public function getPicsByCategory($catid, $access = null, $sorting = null, $numPics = null, $limitStart = 0)
  {
    // Validation
    $catid    = intval($catid);

    $query = $this->getImagesQuery($access)
          ->where('jg.catid = '.$catid);

    if($sorting)
    {
      $query->order($this->_db->escape($sorting));
    }

    $this->_db->setQuery($query, $limitStart, $numPics);

    return $this->_db->loadObjectList();
  }

  /**
   * Extends a query by search conditions
   *
   * @param   object  $query        The query to extend
   * @param   string  $searchstring The string to search for
   * @return  void
   * @since   2.0
   */
  public function addSearchTerms($query, $searchstring)
  {
    $aliases = array('images' => 'jg', 'categories' => 'jgc');
    $plugins = JDispatcher::getInstance()->trigger('onJoomSearch', array($searchstring, $aliases, _JOOM_OPTION.'.interface'));

    $searchstring = $this->_db->quote('%'.$this->_db->escape(strtolower(trim($searchstring))).'%');

    $where = '(jg.imgtitle LIKE '.$searchstring.' OR LOWER(jg.imgtext) LIKE '.$searchstring;

    foreach($plugins as $plugin)
    {
      if(isset($plugin['images.select']))
      {
        $query->select($plugin['images.select']);
      }

      if(isset($plugin['images.leftjoin']))
      {
        $query->leftJoin($plugin['images.leftjoin']);
      }

      if(isset($plugin['images.where']))
      {
        $query->where($plugin['images.where']);
      }

      if(isset($plugin['images.where.or']))
      {
        $where .= ' OR '.$plugin['images.where.or'];
      }
    }

    $query->where($where);
  }

  /**
   * Returns number of images matching the search string
   * (e.g. for pre-filtering, pagination)
   *
   * @param   string  $searchstring The string to use for the search
   * @param   array   $access       Access levels to filter for, null to use the ones of the current user
   * @return  int     The number of images matching the search string
   * @since   1.0.0
   */
  public function getNumPicsBySearch($searchstring, $access = null)
  {
    $query = $this->_db->getQuery(true)
          ->select('COUNT(jg.id)')
          ->from(_JOOM_TABLE_IMAGES.' AS jg')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS jgc ON jgc.cid = jg.catid');
    $this->addImageWhere($query);
    $this->addSearchTerms($query);

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns db-rows of images matching the search string
   * E.g. useful for a search mambot
   *
   * @param   string  $searchstring The string to use for the search
   * @param   array   $access       Access levels to filter for, null to use the ones of the current user
   * @param   string  $sorting      Sorting string
   * @param   int     $numPics      Limit number of images, leave away to return all
   * @param   int     $limitStart   Where to start returning $numPics images
   * @return  array   An array of image objects from the database
   * @since   1.0.0
   */
  public function getPicsBySearch($searchstring, $access = null, $sorting = null, $numPics = null, $limitStart = 0)
  {
    $query = $this->getImagesQuery($access);

    $this->addSearchTerms($query, $searchstring);

    if($sorting)
    {
      $query->order($this->_db->escape($sorting));
    }

    $this->_db->setQuery($query, $limitStart, $numPics);

    return $this->_db->loadObjectList();
  }

  /**
   * Creates a new category out of the information of the given object
   *
   * @param   object  $obj  Should hold all the information about the new category
   * @param   int     The ID of the new category, false, if an error occured
   * @since   1.5.0
   */
  public function createCategory($obj)
  {
    jimport('joomla.filesystem.file');
    JLoader::register('JoomFile', JPATH_ADMINISTRATOR.'/components/'._JOOM_OPTION.'/helpers/file.php');

    $row = JTable::getInstance('joomgallerycategories', 'Table');
    $row->bind($obj);

    if(!$row->name)
    {
      $this->_mainframe->enqueueMessage(JText::_('No valid category name given'), 'error');

      return false;
    }

    $row->parent_id = (int) $row->parent_id;
    if($row->parent_id < 1)
    {
      $row->parent_id = 1;
    }

    // Determine location in category tree
    if(!isset($obj->ordering) || !$obj->ordering || $obj->ordering == 'first-child')
    {
      $row->setLocation($row->parent_id, 'first-child');
    }
    else
    {
      if($obj->ordering == 'last-child')
      {
        $row->setLocation($row->parent_id, 'last-child');
      }
      else
      {
        $row->setLocation($obj->ordering, 'after');
      }
    }

    // Ensure that the data is valid
    if(!$row->check())
    {
      $this->_mainframe->enqueueMessage($row->getError(), 'error');

      return false;
    }

    // Store the data in the database
    if(!$row->store())
    {
      $this->_mainframe->enqueueMessage($row->getError(), 'error');

      return false;
    }

    // Now we have the ID of the new category
    // and the catpath can be built
    $row->catpath = JoomFile::fixFilename($row->name).'_'.$row->cid;
    if($row->parent_id > 1)
    {
      $row->catpath = JoomHelper::getCatPath($row->parent_id).$row->catpath;
    }
    // So store again, but afore let's create the alias
    $row->check();
    if(!$row->store())
    {
      $this->_mainframe->enqueueMessage($row->getError(), 'error');

      return false;
    }

    // Create necessary folders and files
    $origpath   = JPATH_ROOT.'/'.$this->_jg_config->get('jg_pathoriginalimages').$row->catpath;
    $imgpath    = JPATH_ROOT.'/'.$this->_jg_config->get('jg_pathimages').$row->catpath;
    $thumbpath  = JPATH_ROOT.'/'.$this->_jg_config->get('jg_paththumbs').$row->catpath;
    $result     = array();
    $result[]   = JFolder::create($origpath);
    $result[]   = JoomFile::copyIndexHtml($origpath);
    $result[]   = JFolder::create($imgpath);
    $result[]   = JoomFile::copyIndexHtml($imgpath);
    $result[]   = JFolder::create($thumbpath);
    $result[]   = JoomFile::copyIndexHtml($thumbpath);

    if(in_array(false, $result))
    {
      // Delete the just stored database entry
      $row->delete();

      return false;
    }
    else
    {
      // New category successfully created
      return $row->cid;
    }
  }

  /**
   * Is automatically called when an unknown method is called.
   * This will happen if JoomGallery is not uptodate.
   *
   * @param   string  $name   Name of the unknown method
   * @param   array   $params Array of parameters given to the unknown method
   * @return  void
   * @since   1.5.0
   */
  public function __call($name, $params)
  {
    $this->_mainframe->enqueueMessage('JoomGallery is not uptodate. Function '.$name.' does not exist', 'error');
  }
}