<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/ambit.php $
// $Id: ambit.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Ambit Class
 *
 * @package     JoomGallery
 * @since       1.5.5
 */
class JoomAmbit extends JObject
{
  /**
   * URL of the folder for the icons
   *
   * @var string
   */
  protected $icon_url   = '';

  /**
   * URL of the folder for the CSS files
   *
   * @var string
   */
  protected $css_url    = '';

  /**
   * URL of the folder for the JavaScript files
   *
   * @var string
   */
  protected $js_url     = '';

  /**
   * URL of the folder for thumbnails
   *
   * @var string
   */
  protected $thumb_url  = '';

  /**
   * URL of the folder for the detail images
   *
   * @var string
   */
  protected $img_url    = '';

  /**
   * URL of the folder for the original images
   *
   * @var string
   */
  protected $orig_url   = '';

  /**
   * Absolute path of the folder for the thumbnails
   *
   * @var string
   */
  protected $thumb_path = '';

  /**
   * Absolute path of the folder for the details images
   *
   * @var string
   */
  protected $img_path   = '';

  /**
   * Absolute path of the folder for the original images
   *
   * @var string
   */
  protected $orig_path  = '';

  /**
   * Absolute path of the folder for temporary stored files and folders
   *
   * @var string
   */
  protected $temp_path  = '';

  /**
   * Absolute path of the folder for the FTP upload
   *
   * @var string
   */
  protected $ftp_path   = '';

  /**
   * Absolute path of the folder for the watermark
   *
   * @var string
   */
  protected $wtm_path   = '';

  /**
   * Version string of JoomGallery
   *
   * @var string
   */
  protected $version    = '';

  /**
   * If $_external[$type] is set to true the images of
   * type $type will be output through the PHP script
   *
   * @var array
   */
  protected $_external  = array();

  /**
   * The structure of the categories
   *
   * @var array
   */
  protected $_categorystructure = null;

  /**
   * The structure of all categories
   *
   * @var array
   */
  protected $_allcategorystructure = null;

  /**
   * Constructor
   *
   * Presets all variables
   *
   * @access  protected
   * @return  void
   * @since   1.5.5
   */
  function __construct()
  {
    jimport('joomla.filesystem.folder');

    $config     = JoomConfig::getInstance();
    $mainframe  = JFactory::getApplication('administrator');

    // Fill all variables
    $this->icon_url   = JURI::root().'media/joomgallery/images/';
    $this->css_url    = JURI::root().'media/joomgallery/css/';
    $this->js_url     = JURI::root().'media/joomgallery/js/';

    $this->_external['thumb'] = false;
    $this->thumb_url  = JURI::root().$config->get('jg_paththumbs');
    $this->thumb_path = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_paththumbs'));
    if(!JFolder::exists($this->thumb_path))
    {
      $this->_external['thumb'] = true;
      $this->thumb_url  = '';
      $this->thumb_path = JPath::clean($config->get('jg_paththumbs'));
    }

    $this->_external['img'] = false;
    $this->img_url    = JURI::root().$config->get('jg_pathimages');
    $this->img_path   = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_pathimages'));
    if(!JFolder::exists($this->img_path))
    {
      $this->_external['img'] = true;
      $this->img_url    = '';
      $this->img_path   = JPath::clean($config->get('jg_pathimages'));
    }

    $this->_external['orig'] = false;
    $this->orig_url   = JURI::root().$config->get('jg_pathoriginalimages');
    $this->orig_path  = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_pathoriginalimages'));
    if(!JFolder::exists($this->orig_path))
    {
      $this->_external['orig'] = true;
      $this->orig_url   = '';
      $this->orig_path  = JPath::clean($config->get('jg_pathoriginalimages'));
    }

    $this->temp_path  = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_pathtemp'));
    if(!JFolder::exists($this->temp_path))
    {
      $this->temp_path  = JPath::clean($config->get('jg_pathtemp'));
    }
    $this->ftp_path = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_pathftpupload'));
    if(!JFolder::exists($this->ftp_path))
    {
      $this->ftp_path = JPath::clean($config->get('jg_pathftpupload'));
    }
    $this->wtm_path = JPath::clean(JPATH_ROOT.'/'.$config->get('jg_wmpath'));
    if(!JFolder::exists($this->wtm_path))
    {
      $this->wtm_path = JPath::clean($config->get('jg_wmpath'));
    }

    if(!$this->version = $mainframe->getUserState('joom.version.string'))
    {
      $this->version = JoomExtensions::getGalleryVersion();
      $mainframe->setUserState('joom.version.string', $this->version);
    }
  }

  /**
   * Returns a reference to the global Ambit object, only creating it if it
   * doesn't already exist.
   *
   * This method must be invoked as:
   *    <pre>  $ambit = JoomAmbit::getInstance();</pre>
   *
   * @return  JoomAmbit The Ambit object.
   * @since   1.5.5
   */
  public static function getInstance()
  {
    static $instance;

    if(!isset($instance))
    {
      $instance = new JoomAmbit();
    }

    return $instance;
  }

  /**
   * Returns the URL to an icon
   *
   * @param   string  $icon The filename of the icon
   * @return  string  The URL to the icon
   * @since   1.5.5
   */
  public function getIcon($icon)
  {
    return $this->get('icon_url').$icon;
  }

  /**
   * Returns the URL to a style sheet
   *
   * @param   string  $stylesheet The filename of the style sheet
   * @return  string  The URL to the style sheet
   * @since   1.5.5
   */
  public function getStyleSheet($stylesheet)
  {
    return $this->get('css_url').$stylesheet;
  }

  /**
   * Returns the URL to a script file
   *
   * @param   string  $script The filename of the script file
   * @return  string  The URL to the script file
   * @since   1.5.5
   */
  public function getScript($script)
  {
    return $this->get('js_url').$script;
  }

  /**
   * Returns the URL for a redirect
   *
   * @param   string  $controller The controller used in the redirect url
   *                              if it is null, we will use the same
   *                              controller as in the current request,
   *                              if it is an empty string, we will redirect
   *                              to the control panel of the gallery
   * @param   int     $id         The ID of a category or image to redirect to
   *                              if the task was 'apply'
   * @param   string  $key        The parameter name to use in the URL for the ID
   * @return  string  The redirect URL
   * @since   1.5.5
   */
  public function getRedirectUrl($controller = null, $id = null, $key = 'cid')
  {
    $url = 'index.php?option='._JOOM_OPTION;

    if(is_null($controller))
    {
      $url .= '&controller='.JRequest::getCmd('controller');
      if(!is_null($id) && JRequest::getCmd('task') == 'apply')
      {
        $url .= '&task=edit&'.$key.'='.$id;
      }
    }
    else
    {
      if($controller)
      {
        $url .= '&controller='.$controller;
      }
    }

    return $url;
  }

  /**
   * Returns the URL or the path to an image
   *
   * @param   string            $type   The type of the URL or path
   * @param   string/object/int $img    Filename, database object or ID of the image
   * @param   int               $catid  The ID of the category in which the image is stored
   * @return  string            The URL or the path to the image
   * @since   1.5.5
   */
  public function getImg($type, $img, $id = 0, $catid = 0)
  {
    $types = array('thumb_path', 'thumb_url', 'img_path', 'img_url', 'orig_path', 'orig_url');
    if(!in_array($type, $types))
    {
      JError::raiseError(500, JText::sprintf('Wrong image type: %s', $type));
    }

    if(!is_object($img))
    {
      if(is_numeric($img))
      {
        $img = $this->getImgObject($img);
      }
      else
      {
        if(!is_null($id))
        {
          $img = $this->getImgObject($id);
        }
      }
    }

    if(is_object($img))
    {
      $id     = $img->id;
      $catid  = $img->catid;
      if($type == 'thumb_path' || $type == 'thumb_url')
      {
        $img = $img->imgthumbname;
      }
      else
      {
        $img = $img->imgfilename;
      }
    }

    // Check whether the image shall be output through the PHP script or with its real path
    if(   strpos($type, 'url')
      &&
        (    $this->_external[str_replace('_url','', $type)]
          #|| $this->_config->get('jg_watermark')
          || strpos($type, 'img')   !== false
          || strpos($type, 'orig')  !== false
        )
      )
    {
      $type = str_replace('_url','', $type);
      return  JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&view=image&format=raw&type='.$type.'&cid='.$id);
    }

    $catpath  = JoomHelper::getCatPath($catid);

    // Create the complete path
    $img      = $this->$type . $catpath . $img;

    if(strpos($type, 'path'))
    {
      $img = JPath::clean($img);
    }

    return $img;
  }

  /**
   * Returns the database row of a specific image
   *
   * @param   int     $id The ID of the image to load
   * @return  object  The database row of the image
   */
  public function getImgObject($id)
  {
    static $images  = array();
    static $row;

    if(!isset($images[$id]))
    {
      if(!isset($row))
      {
        $row = JTable::getInstance('joomgalleryimages', 'Table');
      }

      if(!$row->load($id))
      {
        JError::raiseError(500, JText::sprintf('Image with ID %d not found', $id));
      }

      $properties   = $row->getProperties();
      $images[$id]  = new stdClass();
      foreach($properties as $key => $value)
      {
        $images[$id]->$key = $value;
      }
    }

    return $images[$id];
  }

  /**
   * Returns the category structure of the gallery
   *
   * @param   boolean True, if a structure with all categories should be constructed.
   * @return  array   An array of categories/sub-categories
   * @since   1.5.5
   */
  public function getCategoryStructure($all = false)
  {
    // Check if already read from database
    if(    (is_null($this->_categorystructure)    && !$all)
        || (is_null($this->_allcategorystructure) && $all)
       )
    {
      // Creation of array
      $database = JFactory::getDBO();
      $user     = JFactory::getUser();

      // Read all categories from database
      $query = $database->getQuery(true)
            ->select('c.cid, c.parent_id, c.name, c.access, c.published, c.hidden, c.owner')
            ->from(_JOOM_TABLE_CATEGORIES.' AS c')
            ->where('lft > 0')
            ->order('c.lft');

      if(!$user->authorise('core.admin') && !$all)
      {
        $query->where('c.access IN ('.implode(',', $user->getAuthorisedViewLevels()).')');
      }

      $database->setQuery($query);
      $categories = $database->loadObjectList('cid');

      // Get picture count and hits count
      $query->clear()
            ->select('catid, COUNT(id) as piccount, SUM(hits) as hitcount')
            ->from(_JOOM_TABLE_IMAGES)
            ->group('catid');

      if(!$user->authorise('core.admin') && !$all)
      {
        $query->where('access IN ('.implode(',', $user->getAuthorisedViewLevels()).')');
      }

      $database->setQuery($query);
      $catcounts = $database->loadObjectList('catid');

      // Merge the arrays
      foreach($categories as $key => $category)
      {
        if($category->parent_id != 1 && !isset($categories[$category->parent_id]))
        {
          unset($categories[$key]);
          continue;
        }

        // Cast to int where needful
        $categories[$key]->cid       = (int) $key;
        $categories[$key]->parent_id = (int) $categories[$key]->parent_id;

        if(isset($catcounts[$key]))
        {
           $categories[$key]->piccount = (int) $catcounts[$key]->piccount;
           $categories[$key]->hitcount = (int) $catcounts[$key]->hitcount;
        }
        else
        {
           $categories[$key]->piccount = 0;
           $categories[$key]->hitcount = 0;
        }
      }

      if($all)
      {
        $this->_allcategorystructure = $categories;
      }
      else
      {
        $this->_categorystructure = $categories;
      }
    }

    if(!$all)
    {
      return $this->_categorystructure;
    }
    else
    {
      return $this->_allcategorystructure;
    }
  }
}