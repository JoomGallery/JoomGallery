<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/detail.php $
// $Id: detail.php 4379 2014-04-27 19:17:00Z erftralle $
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
 * Detail view model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelDetail extends JoomGalleryModel
{
  /**
   * Image ID
   *
   * @var     int
   */
  protected $_id = 0;

  /**
   * Image data object
   *
   * @var     object
   */
  protected $_image = null;

  /**
   * Images data array
   *
   * @var     array
   */
  protected $_images = array();

  /**
   * Name tags data array
   *
   * @var     array
   */
  protected $_nametags = array();

  /**
   * Comments data array
   *
   * @var     array
   */
  protected $_comments = array();

  /**
   * Exif data string
   *
   * @var     string
   */
  protected $_exifdata = '';

  /**
   * Map data array
   *
   * @var     string
   */
  protected $_mapdata = array();

  /**
   * IPTC data string
   *
   * @var     string
   */
  protected $_iptcdata = '';

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    $id = JRequest::getVar('id', 0, '', 'int');
    $this->setId((int)$id);
  }

  /**
   * Method to set the image identifier
   *
   * @param   int     $id Image ID number
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new image ID if valid and wipe data
    if(!$id)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'), 'notice');
    }
    $this->_id    = $id;
    $this->_image = null;
  }

  /**
   * Method to get the identifier
   *
   * @return  int     The image ID
   * @since   1.5.5
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Method to get the image data
   *
   * @return  object  Image data object
   * @since   1.5.5
   */
  public function getImage()
  {
    jimport('joomla.filesystem.file');

    if(empty($this->_image))
    {
      if($this->_loadImages())
      {
        $images = $this->_images;
      }
      else
      {
        JError::raiseError(500, JText::_('Unable to load images'));
      }

      #$image  = $images[$this->_id]; see _loadImages()
      foreach($images as $key => $row)
      {
        if($row->id == $this->_id)
        {
          $images[$key]->position = $key;
          $image                  = $row;
          break;
        }
      }

      if(!isset($image))
      {
        $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::sprintf('Unable to load image with ID %d', $this->_id), 'error');
      }

      // Check whether the requested image is in a category
      // which the current user is allowed to see it
      $categories = $this->_ambit->getCategoryStructure();
      if(!isset($categories[$image->catid]))
      {
        JError::raiseError(500, JText::sprintf('Unable to load image with ID %d', $this->_id), 'error');
      }

      // Source url
      $image->img_src    = $this->_ambit->getImg('img_url', $image);

      // Information about original image if available
      $orig = $this->_ambit->getImg('orig_path', $image);
      if(JFile::exists($orig))
      {
        $image->orig_exists   = true;
        $orig_info            = getimagesize($orig);
        $orig_size            = filesize($orig);
        $image->orig_size      = number_format($orig_size / 1024,
                                                2,
                                                JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'),
                                                JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR')
                                              );
      }
      else
      {
        $image->orig_exists   = false;
        $orig_info[0]  = 0;
        $orig_info[1]  = 0;
        $image->orig_size     = JText::_('COM_JOOMGALLERY_DETAIL_INFO_FILESIZE_ORIGINAL_NOT_AVAILABLE');
      }

      // Information about detail image
      $img = $this->_ambit->getImg('img_path', $image);
      $img_info               = getimagesize($img);
      $img_size               = filesize($img);

      $image->img_size        = number_format($img_size / 1024,
                                                2,
                                                JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'),
                                                JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR')
                                             );

      $image->orig_width      = $orig_info[0];
      $image->orig_height     = $orig_info[1];
      $image->img_width       = $img_info[0];
      $image->img_height      = $img_info[1];

      $image->bigger_orig     = false;
      if(    $image->orig_exists
          && $image->orig_width  > $image->img_width
          && $image->orig_height > $image->img_height
        )
      {
        $image->bigger_orig   = true;
      }

      if($this->_config->get('jg_resizetomaxwidth'))
      {
        $ratio                = max($image->img_width, $image->img_height);
        $ratio                = ($ratio / $this->_config->get('jg_maxwidth'));
        $ratio                = max($ratio, 1.0);
        $image->width         = (int)($image->img_width / $ratio);
        $image->height        = (int)($image->img_height / $ratio);
      }
      else
      {
        $image->width         = $image->img_width;
        $image->height        = $image->img_height;
      }

      if($image->imgauthor)
      {
        $image->author        = $image->imgauthor;
      }
      else
      {
        if($this->_config->get('jg_showowner'))
        {
          $image->author      = JHTML::_('joomgallery.displayname', $image->imgowner, 'detail');
        }
        else
        {
          $image->author      = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
        }
      }

      if(     (($this->_config->get('jg_bigpic') == 1 && $this->_user->get('id'))
                || ($this->_config->get('jg_bigpic_unreg') == 1 && !$this->_user->get('id'))
              )
          #&&  !$this->slideshow
          &&  $image->bigger_orig
        )
      {
        $image->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_bigpic_open'), $image);
      }
      else
      {
        $image->link = '';
      }

      $this->_image = $image;
    }

    return $this->_image;
  }

  /**
   * Method to get all the images of the current image's category
   * The image has to be loaded first, because we need the catid
   *
   * @return  array   The images data array
   * @since   1.5.5
   */
  public function getImages()
  {
    if($this->_loadImages())
    {
      if($this->getImage())
      {
        if(!in_array($this->_image->access, $this->_user->getAuthorisedViewLevels()))
        {
          $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false),
                                      JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_VIEW_IMAGE'), 'notice');
        }
      }
      else
      {
        $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false),JText::sprintf('Unable to load image with ID %d', $this->_id), 'error');
      }

      return $this->_images;
    }

    return false;
  }

  /**
   * Method to get all the nametags of the current image
   *
   * @return  array   Nametags data array
   * @since   1.5.5
   */
  public function getNametags()
  {
    if($this->_loadNametags())
    {
      return $this->_nametags;
    }

    return array();
  }

  /**
   * Method to get all the comments of the current image
   *
   * @return  array   Comments data array
   * @since   1.5.5
   */
  public function getComments()
  {
    if($this->_loadComments())
    {
      return $this->_comments;
    }

    return array();
  }

  /**
   * Method to get the Exif data (HTML ouput) of the current image
   *
   * @return  array   Exif data array
   * @since   1.5.5
   */
  public function getExifdata()
  {
    if($this->_loadExifdata())
    {
      return $this->_exifdata;
    }

    return false;
  }

  /**
   * Method to get the map data of the current image
   *
   * @return  array   Map data array
   * @since   1.5.5
   */
  public function getMapdata()
  {
    if($this->_loadExifdata())
    {
      if($this->_mapdata)
      {
        return $this->_mapdata;
      }
    }

    return false;
  }

  /**
   * Method to get the IPTC data (HTML ouput) of the current image
   *
   * @return  array   Iptc data data array
   * @since   1.5.5
   */
  public function getIptcdata()
  {
    if($this->_loadIptcdata())
    {
      return $this->_iptcdata;
    }

    return false;
  }

  /**
   * Method to increment the hit counter for the image
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function hit()
  {
    if($this->_id)
    {
      $image = $this->getTable('joomgalleryimages');
      $image->hit($this->_id);
      return true;
    }

    return false;
  }

  /**
   * Tests if image is checked out
   *
   * @param   int     $uid  A user id
   * @return  boolean True if it is checked out, false otherwise
   * @since   1.5.5
   */
  public function isCheckedOut($uid = 0)
  {
    if($this->_loadImage())
    {
      if($uid)
      {
        return ($this->_image->checked_out && $this->_image->checked_out != $uid);
      }
      else
      {
        return $this->_image->checked_out;
      }
    }
    else
    {
      if(!$this->_id)
      {
        return false;
      }
      else
      {
        JError::raiseWarning( 0, 'Unable to Load Data');
        return false;
      }
    }
  }

  /**
   * Method to checkin/unlock the image
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function checkin()
  {
    if($this->_id)
    {
      $image = JTable::getInstance('joomgalleryimages');
      return $image->checkin($this->_id);
    }

    return false;
  }

  /**
   * Method to checkout/lock the image
   *
   * @param   int     $uid  User ID of the user checking the article out
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function checkout($uid = null)
  {
    if($this->_id)
    {
      // Make sure we have a user id to checkout the image with
      if(is_null($uid))
      {
        $user = JFactory::getUser();
        $uid  = $user->get('id');
      }
      // Let's get to it and checkout the thing...
      $image = JTable::getInstance('joomgalleryimages');
      return $image->checkout($uid, $this->_id);
    }

    return false;
  }

  /**
   * Method to load the images data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadImages()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the images data if it doesn't already exist
    if(empty($this->_images))
    {
      $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

      // First get the category the image belongs too
      $query = $this->_db->getQuery(true)
            ->select('catid')
            ->from(_JOOM_TABLE_IMAGES)
            ->where('id  = '.$this->_id);

      $this->_db->setQuery($query);
      if(!($catid = $this->_db->loadResult()))
      {
        $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::sprintf('COM_JOOMGALLERY_COMMON_ERROR_IMAGE_NOT_FOUND', $this->_id), 'error');
      }

      // Get all the images data of that category
      $query->clear()
            ->select('a.*, a.owner AS imgowner, c.metadesc AS catmetadesc, c.metakey AS catmetakey')
            ->select(JoomHelper::getSQLRatingClause('a').' AS rating')
            ->from(_JOOM_TABLE_IMAGES.' AS a')
            ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
            ->where('a.catid = '.$catid)
            ->where('a.published = 1')
            ->where('a.approved  = 1')
            ->where('(a.hidden = 0 OR a.id = '.$this->_id.')')
            ->where('a.access   IN ('.$authorisedViewLevels.')')
            ->where('c.access   IN ('.$authorisedViewLevels.')')
            ->order('a.'.$this->_config->get('jg_firstorder'));
      if($this->_config->get('jg_secondorder'))
      {
        $query->order('a.'.$this->_config->get('jg_secondorder'));

        if($this->_config->get('jg_thirdorder'))
        {
          $query->order('a.'.$this->_config->get('jg_thirdorder'));
        }
      }

      $this->_db->setQuery($query);
      if(!$rows = $this->_db->loadObjectList())
      {
        $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::sprintf('COM_JOOMGALLERY_COMMON_ERROR_IMAGE_NOT_FOUND', $this->_id), 'error');
      }

      $this->_images = $rows;

      return true;
    }

    return true;
  }

  /**
   * Method to load the nametags data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadNametags()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the nametags data if it doesn't already exist
    if(empty($this->_nametags))
    {
      $query = $this->_db->getQuery(true)
            ->select('*, 500 AS maxzindex')
            ->from(_JOOM_TABLE_NAMESHIELDS)
            ->where('npicid = '.$this->_id);

      $this->_db->setQuery($query);
      if(!$rows = $this->_db->loadObjectList())
      {
        return false;
      }

      $this->_nametags = $rows;
    }

    return true;
  }

  /**
   * Method to load comments data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadComments()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the comments data if it doesn't already exist
    if(empty($this->_comments))
    {
      $orderby = 'ASC';
      if($this->_config->get('jg_showcommentsarea') == 1)
      {
        $orderby = 'DESC';
      }

      $query = $this->_db->getQuery(true)
            ->select('*')
            ->from(_JOOM_TABLE_COMMENTS)
            ->where('cmtpic    = '.$this->_id)
            ->where('published = 1')
            ->where('approved  = 1')
            ->order('cmtid '.$orderby);

      $this->_db->setQuery($query);
      if(!$rows = $this->_db->loadObjectList())
      {
        return false;
      }

      $this->_comments = $rows;
    }

    return true;
  }

  /**
   * Method to load Exif data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadExifdata()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the Exif data if it doesn't already exist
    if(empty($this->_exifdata) && $this->_exifdata !== false)
    {
      if(empty($this->image))
      {
        if(!$this->getImage())
        {
          return false;
        }
      }

      $mapdata = false;

      // PHP's exif only accepts JPEGs or TIFFs
      $valid_extensions = array('jpg', 'jpeg', 'jpe');
      $fileextension    = strtolower(JFile::getExt($this->_image->imgfilename));
      $exif_array       = array();
      if(in_array($fileextension, $valid_extensions))
      {
        $exif_array = @exif_read_data($this->_ambit->getImg('orig_path', $this->_image), 'EXIF, IFD0, GPS', true);
        if(!$exif_array)
        {
          return false;
        }
      }
      else
      {
        return false;
      }

      $language = JFactory::getLanguage();
      $language->load('com_joomgallery.exif');

      require_once(JPATH_COMPONENT_ADMINISTRATOR.'/includes/exifarray.php');

      $ii = 0;

      $ifdotags        = explode(',', $this->_config->get('jg_ifdotags'));
      $subifdtags      = explode(',', $this->_config->get('jg_subifdtags'));
      $gpstags         = explode(',', $this->_config->get('jg_gpstags'));

      // For GPS tags check for enabled geotagging
      if($this->_config->get('jg_showgeotagging'))
      {
        $gpstags = array_unique(array_merge($gpstags, array(1, 2, 3, 4, 5)));
        sort($gpstags);
      }

      $countifdotags   = count($ifdotags);
      $countsubifdtags = count($subifdtags);
      $countgpstags    = count($gpstags);

      $definitions = array(
        1 => array ('TAG' => 'IFD0', 'FORS' => $ifdotags,   'FOR' => '$ifdotag'),
        2 => array ('TAG' => 'EXIF', 'FORS' => $subifdtags, 'FOR' => '$subifdtag'),
        3 => array ('TAG' => 'GPS',  'FORS' => $gpstags,    'FOR' => '$gpstags')
      );
      $count  = count($definitions);
      $output = '';

      for($ii = 1; $ii <= $count; $ii++)
      {
        $tagcat   = $definitions[$ii]['TAG'];
        $jgtags   = $definitions[$ii]['FORS'];
        $jgtag    = $definitions[$ii]['FOR'];

        $k = 0;
        foreach($jgtags as $jgtag)
        {
          if(!empty($jgtag) && isset($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]))
          {
            // Disable output of GPS tags if they aren't selected for displaying
            // (the information has to be collected if geotagging is enabled, so they are also in the array)
            if($tagcat != 'GPS' || strpos($this->_config->get('jg_gpstags').',', $jgtag.',') !== false)
            {
              $kk      = $k % 2 + 1;
              $tagdata = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']];
              $output .= "      <div class=\"jg_row".$kk."\">\n";
              $output .= "        <div class=\"jg_exif_left\">\n";
//              $output .= "        ".$jgtag."\n";
//              $output .= "        &nbsp;\n";
              $output .= "          ".$exif_config_array[$tagcat][$jgtag]['Name']."\n";
              $output .= "        </div>\n";
              $output .= "        <div class=\"jg_exif_right\">\n";
            }
            if($exif_config_array[$tagcat][$jgtag]['Calculation'] =='Denum')
            {
              list($numerator, $denumerator) = explode('/', $tagdata);
              $tagdata = ($numerator /  $denumerator);
              $tagdata = round($tagdata,2);
              if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'FNumber')
              {
                $tagdata = JText::_('COM_JOOMGALLERY_SUBIFD_FNUMBER_F').$tagdata;
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Calculation'] == 'Array')
            {
              $tagdata = $exif_config_array[$tagcat][$jgtag][$exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]];
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'ImageDescription'
               || $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'Artist'
               || $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'Copyright')
            {
              $tagdata = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']];
              $from_charset = 'ASCII';
              $to_charset   = 'UTF-8';
              if(function_exists('iconv'))
              {
                $fixedenteties = htmlentities($tagdata);
                $fixedcharset  = iconv($from_charset, $to_charset, $fixedenteties);
              }
              else
              {
                $fixedcharset = $tagdata;
              }
              if(!$this->isUtf8($fixedcharset))
              {
                $tagdata = htmlspecialchars_decode($this->utf8EncodeMix($fixedcharset, false));
              }
              else
              {
                $tagdata =  htmlspecialchars_decode($fixedcharset);
              }
            }
            if(   $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'ReferenceBlackWhite'
               || $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'PrimaryChromaticities'
               || $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'WhitePoint'
               || $exif_config_array[$tagcat][$jgtag]['Attribute'] == 'YCbCrCoefficients'
              )
            {
              if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'WhitePoint')
              {
                $arraynum = 2;
                $counter  = 1;
              }
              elseif($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'YCbCrCoefficients')
              {
                $arraynum = 3;
                $counter  = 2;
              }
              else
              {
                $arraynum = 6;
                $counter  = 5;
              }
              $tagdata  = '[';
              for($num = 0; $num < $arraynum; $num++)
              {
                $data = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']][$num];
                list($numerator, $denumerator) = explode('/', $data);
                $data = ($numerator / $denumerator);
                $tagdata .= $data;
                if($num < $counter)
                {
                  $tagdata .= ', ';
                }
              }
              $tagdata .= ']';
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'ExifVersion')
            {
              if($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']] == '0220')
              {
                $tagdata  = JText::_('COM_JOOMGALLERY_SUBIFD_EXIFVERSION_VERSION') . ' 2.2';
              }
              elseif($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']] == '0210')
              {
                $tagdata  = JText::_('COM_JOOMGALLERY_SUBIFD_EXIFVERSION_VERSION') . ' 2.1';
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'ComponentsConfiguration')
            {
              $tagdata = '';
              for($num = 0; $num < 4; $num++ )
              {
                $value = ord($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]{$num});
                $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_COMPONENT') . ( $num + 1 ) . ': ';
                switch($value)
                {
                  case 0:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_0');
                    break;
                  case 1:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_1');
                    break;
                  case 2:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_2');
                    break;
                  case 3:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_3');
                    break;
                  case 4:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_4');
                    break;
                  case 5:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_5');
                    break;
                  case 6:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_6');
                    break;
                  default:
                    $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_COMPONENTSCONFIGURATION_UNKNOWN') . $value;
                }
                $tagdata .= '<br />';
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'FileSource')
            {
              $tagdata = '';
              $value = ord($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]{0});
              switch($value)
              {
                case 3:
                  $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_FILESOURCE_3');
                break;
              default:
                $tagdata = JText::_('COM_JOOMGALLERY_SUBIFD_FILESOURCE_UNKNOWN') . $value;
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'SceneType')
            {
              $tagdata = '';
              $value = ord($exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]{0});
              switch($value)
              {
                case 1:
                  $tagdata .= JText::_('COM_JOOMGALLERY_SUBIFD_SCENETYPE_1');
                break;
              default:
                $tagdata = JText::_('COM_JOOMGALLERY_SUBIFD_SCENETYPE_UNKNOWN') . $value;
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'GPSLatitudeRef')
            {
              $tagdata = '';
              $value = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']];
              switch($value)
              {
                case 'N':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSLATITUDEREF_N');
                break;
                case 'S':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSLATITUDEREF_S');
                break;
              }

              // Geotagging
              if($this->_config->get('jg_showgeotagging'))
              {
                $map_direction = $value;
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Calculation'] == 'DegMinSec')
            {
              $tagdata  = '';
              $degree   = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']][0];
              list($numerator, $denumerator) = explode('/', $degree);
              $degree   = ($numerator/$denumerator);
              $tagdata .= $degree.'&deg;';
              $tagdata .= '&nbsp;&nbsp;';
              $minutes  = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']][1];
              list($numerator, $denumerator) = explode('/', $minutes);
              $minutes  = ($numerator/$denumerator);
              $tagdata .= $minutes."'";
              $tagdata .= "&nbsp;&nbsp;";
              $seconds  = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']][2];
              list($numerator, $denumerator) = explode('/', $seconds);
              $seconds  = ($numerator / $denumerator);
              $tagdata .= $seconds."''";

              // Geotagging
              if($this->_config->get('jg_showgeotagging'))
              {
                $mapdata[$map_direction] = $degree + ( $minutes / 60 ) + ( $seconds / 3600 );
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'GPSLongitudeRef')
            {
              $tagdata  = '';
              $value = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']];
              switch($value)
              {
                case 'E':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSLONGITUDEREF_E');
                break;
                case 'W':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSLONGITUDEREF_W');
                break;
              }

              // Geotagging
              if($this->_config->get('jg_showgeotagging'))
              {
                $map_direction = $value;
              }
            }
            if($exif_config_array[$tagcat][$jgtag]['Attribute'] == 'GPSAltitudeRef')
            {
              $tagdata = '';
              $value = $exif_array[$tagcat][$exif_config_array[$tagcat][$jgtag]['Attribute']]{0};
              $value = bindec($value);
              switch($value)
              {
                case '0':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSALTITUDEREF_0');
                break;
                case '1':
                  $tagdata .= JText::_('COM_JOOMGALLERY_GPS_GPSALTITUDEREF_1');
                break;
              }
            }

            // Disable output of GPS tags if they aren't selected for displaying
            // (the information has to be collected if geotagging is enabled, so they are also in the array)
            if($tagcat == 'GPS' && strpos($this->_config->get('jg_gpstags').',', $jgtag.',') === false)
            {
              continue;
            }

            if($tagdata == '')
            {
              $tagdata = '&nbsp;';
            }

            $tagdata = str_replace('&Acirc;', '', $tagdata);

            $output .= '          '.$tagdata;

            if($exif_config_array[$tagcat][$jgtag]['Units'] != '')
            {
              $output .= '&nbsp;';
              $output .= $exif_config_array[$tagcat][$jgtag]['Units']."\n";
            }
            else
            {
              $output .= "&nbsp;\n";
            }
            $output .= "        </div>\n";
            $output .= "      </div>\n";
            $k++;
          }
  //        else
  //        {
  //          $kk = $k%2+1;
  //          $output .= "    <div class=\"jg_row".$kk."\">\n";
  //          $output .= "      <div class=\"jg_exif_left\">\n";
  //          $output .= "        ".$jgtag."\n";
  //          $output .= "        &nbsp;\n";
  //          $output .= "        ".$exif_config_array[$tagcat][$jgtag]['Name']."\n";
  //          $output .= "      </div>\n";
  //          $output .= "      <div class=\"jg_exif_right\">\n";
  //          $output .= "        nicht definiert";
  //          $output .= "      </div>\n";
  //          $output .= "    </div>\n";
  //          $k++;
  //        }
        }
      }

      if($output)
      {
        $this->_exifdata  = $output;
      }
      else
      {
        $this->_exifdata  = false;
      }

      $this->_mapdata     = $mapdata;

      return true;
    }

    return true;
  }

  /**
   * Method to load iptc data
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadIptcdata()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the iptc data if it doesn't already exist
    if(empty($this->_iptcdata))
    {
      if(empty($this->_image))
      {
        if(!$this->getImage())
        {
          return false;
        }
      }

      $valid_extensions = array('jpg', 'jpeg', 'jpe');
      $fileextension    = strtolower(JFile::getExt($this->_image->imgfilename));
      $iptc_array       = array();
      if(in_array($fileextension, $valid_extensions))
      {
        $iptcimage = getimagesize($this->_ambit->getImg('orig_path', $this->_image), $info);
        if(isset($info['APP13']))
        {
          $iptc_array = iptcparse($info['APP13']);
        }
        if(!$iptc_array)
        {
          return false;
        }
      }
      else
      {
        return false;
      }

      $language = JFactory::getLanguage();
      $language->load('com_joomgallery.iptc');

      require_once(JPATH_COMPONENT_ADMINISTRATOR.'/includes/iptcarray.php');

      $ii = 0;

      $iptctags     = explode(',', $this->_config->get('jg_iptctags'));

      $charsets = array(
                        'macintosh',
                        'ASCII',
                        'ISO-8859-1',
                        'UCS-4',
                        'UCS-4BE',
                        'UCS-4LE',
                        'UCS-2',
                        'UCS-2BE',
                        'UCS-2LE',
                        'UTF-32',
                        'UTF-32BE',
                        'UTF-32LE',
                        'UTF-16',
                        'UTF-16BE',
                        'UTF-16LE',
                        'UTF-7',
                        'UTF7-IMAP',
                        'UTF-8',
                        'EUC-JP',
                        'SJIS',
                        'eucJP-win',
                        'SJIS-win',
                        'ISO-2022-JP',
                        'JIS',
                        'ISO-8859-2',
                        'ISO-8859-3',
                        'ISO-8859-4',
                        'ISO-8859-5',
                        'ISO-8859-6',
                        'ISO-8859-7',
                        'ISO-8859-8',
                        'ISO-8859-9',
                        'ISO-8859-10',
                        'ISO-8859-13',
                        'ISO-8859-14',
                        'ISO-8859-15',
                        'byte2be',
                        'byte2le',
                        'byte4be',
                        'byte4le',
                        'BASE64',
                        '7bit',
                        '8bit',
                        'EUC-CN',
                        'CP936',
                        'HZ',
                        'EUC-TW',
                        'CP950',
                        'BIG-5',
                        'EUC-KR',
                        'UHC',
                        'ISO-2022-KR',
                        'Windows-1251',
                        'Windows-1252',
                        'CP866',
                        'KOI8-R'
                        );

      if((isset($iptc_array['1#090'][0])) && in_array($iptc_array['1#090'][0], $charsets))
      {
        $from_charset = $iptc_array['1#090'][0];
      }
      else
      {
        $from_charset = '';
      }

      $to_charset = 'UTF-8';

      $k = 0;
      $output = '';
      foreach($iptctags as $iptctag)
      {
        if(!empty($iptctag) && isset($iptc_config_array['IPTC'][$iptctag]['IMM']))
        {
          $realiptctag = str_replace(':', '#', $iptc_config_array['IPTC'][$iptctag]['IMM']);
          if(isset($iptc_array[$realiptctag]))
          {
            if($realiptctag != '2#025')
            {
              $kk = $k%2+1;
              $output .= "      <div class=\"jg_row".$kk."\">\n";
              $output .= "        <div class=\"jg_exif_left\">\n";
              $output .= "          ".$iptc_config_array['IPTC'][$iptctag]['Name']."\n";
              $output .= "        </div>\n";
              $output .= "        <div class=\"jg_exif_right\">\n";
              if(function_exists('iconv'))
              {
                $fixedenteties = htmlentities($iptc_array[$realiptctag][0]);
                $fixedcharset  = iconv($from_charset, $to_charset, $fixedenteties);
              }
              else
              {
                $fixedcharset = $iptc_array[$realiptctag][0];
              }
              if(!$this->isUtf8($fixedcharset))
              {
                $tagdata = htmlspecialchars_decode($this->utf8EncodeMix($fixedcharset, false));
              }
              else
              {
                $tagdata = htmlspecialchars_decode($fixedcharset);
              }
              if($tagdata == '')
              {
                $tagdata = '&nbsp;';
              }
              $output .= "          ".$tagdata."";
              $output .= "        </div>\n";
              $output .= "      </div>\n";
              $k++;
            }
            else
            {
              $num = count($iptc_array['2#025']);
              if($num > 0)
              {
                $kk = $k % 2 + 1;
                $tagdata = '';
                $output .= "      <div class=\"jg_row".$kk."\">\n";
                $output .= "        <div class=\"jg_exif_left\">\n";
                $output .= "          ".$iptc_config_array['IPTC'][$iptctag]['Name']." \n";
                $output .= "        </div>\n";
                $output .= "        <div class=\"jg_exif_right\">\n";
                for($i = 0; $i < $num; $i++)
                {
                  if(function_exists('iconv'))
                  {
                    $fixedenteties = htmlentities($iptc_array[$realiptctag][$i]);
                    $fixedcharset  = iconv($from_charset, $to_charset, $fixedenteties);
                  }
                  else
                  {
                    $fixedcharset = $iptc_array[$realiptctag][$i];
                  }
                  if(!$this->isUtf8($fixedcharset))
                  {
                    $tagdata .= htmlspecialchars_decode(utf8_encode_mix($fixedcharset, false));
                  }
                  else
                  {
                    $tagdata .= htmlspecialchars_decode($fixedcharset);
                  }
                  if($i < $num-1)
                  {
                    $tagdata .= ', ';
                  }
                }
                if(empty($tagdata))
                {
                  $tagdata = '&nbsp;';
                }
                $output .= '          '.$tagdata;
                $output .= "        </div>\n";
                $output .= "      </div>\n";
              }
            }
          }
        }
      }

      $this->_iptcdata  = $output;

      return true;
    }

    return true;
  }

  /**
   * Returns true if $string is valid UTF-8 and false otherwise.
   *
   * From http://w3.org/International/questions/qa-forms-utf-8.html
   *
   * @param   string  $string The string to be checked
   * @return  boolean True if $string is valid UTF-8, false otherwise
   * @since   1.0.0
   */
  public function isUtf8($string)
  {
    return preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string);

  }

  /**
   * Encodes an ISO-8859-1 string to UTF-8
   *
   * Recursive call
   *
   * @param   string/array  $input        The string(s) to be modified
   * @param   boolean       $encode_keys  True if the array keys should be encoded, too
   * @return  string/array  The encoded string(s)
   * @since   1.0.0
   */
  public function utf8EncodeMix($input, $encode_keys = false)
  {
    if(is_array($input))
    {
      $result = array();
      foreach($input as $k => $v)
      {
        $key = ($encode_keys) ? utf8_encode($k) : $k;
        $result[$key] = $this->utf8EncodeMix($v, $encode_keys);
      }
    }
    else
    {
      $result = utf8_encode($input);
    }

    return $result;
  }
}