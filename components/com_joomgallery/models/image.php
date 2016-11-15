<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/image.php $
// $Id: image.php 4331 2013-09-08 08:27:42Z erftralle $
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
 * Image Model
 *
 * Creates the output of a single image.
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelImage extends JoomGalleryModel
{
  /**
   * The ID of the image
   *
   * @var   int
   * @since 1.5.5
   */
  protected $_id;

  /**
   * The image data
   *
   * @var   int
   * @since 1.5.5
   */
  protected $_image;

  /**
   * Method to get the identifier
   *
   * @return  int   The image ID
   * @since   1.5.5
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Method to get the image data
   *
   * @param   int     $id ID of the image to display
   * @return  object  The image data
   * @since   1.5.5
   */
  public function getImage($id)
  {
    $id = (int) $id;

    // Set new image ID if valid and wipe data
    if(!$id)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));

      return false;
    }

    $this->_id    = $id;
    $this->_image = null;

    if(!$this->_loadImage())
    {
      return false;
    }

    return $this->_image;
  }

  /**
   * Method to increment the hit counter for the image
   *
   * @param   int     $id Image ID to use if no image data was loaded in the object afore
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function hit($id = null)
  {
    if($id)
    {
      $this->_id = $id;
    }

    if($this->_id && !$this->countstop())
    {
      $image = $this->getTable('joomgalleryimages');
      $image->hit($this->_id);

      return true;
    }

    return false;
  }

  /**
   * Method to increment the download counter for the image
   *
   * @param   int     $id Image ID to use if no image data was loaded in the object afore.
   * @return  boolean True on success, false otherwise.
   * @since   3.1
   */
  public function download($id = null)
  {
    if($id)
    {
      $this->_id = $id;
    }

    if($this->_id)
    {
      $image = $this->getTable('joomgalleryimages');
      $image->download($this->_id);

      return true;
    }

    return false;
  }

  /**
   * Method to check whether the hit counter should be incremented
   *
   * @return  boolean True, if the hit counter is locked, false otherwise
   * @since   1.5.5
   */
  public function countstop()
  {
    $session    = JFactory::getSession();
    $session_id = $session->getToken();

    $stoptime   = $this->_mainframe->getCfg('lifetime') * 60;
    $ip         = $_SERVER['REMOTE_ADDR'];

    // Delete all dated entries
    $query = $this->_db->getQuery(true)
          ->delete(_JOOM_TABLE_COUNTSTOP)
          ->where('NOW() > date_add(cstime, interval '.(int) $stoptime.' SECOND)');
    $this->_db->setQuery($query);
    $this->_db->query();

    // Check whether entry exists
    $query->clear()
          ->select('COUNT(cspicid)')
          ->from(_JOOM_TABLE_COUNTSTOP)
          ->where('cssessionid = '.$this->_db->q($session_id))
          ->where('csip = '.$this->_db->q($ip))
          ->where('cspicid = '.$this->_id);
    $this->_db->setQuery($query);

    if($this->_db->loadResult())
    {
      // Lock the counter
      return true;
    }
    else
    {
      // New entry
      $query->clear()
            ->insert(_JOOM_TABLE_COUNTSTOP)
            ->columns('csip, cssessionid, cspicid, cstime')
            ->values($this->_db->q($ip).','.$this->_db->q($session_id).','.$this->_id.', NOW()');
      $this->_db->setQuery($query);
      $this->_db->query();

      return false;
    }
  }

  /**
   * Method to check whether a given image is a gif file
   *
   * @param   string  $file Path to the image to check
   * @return  boolean True, if the given image is a gif file, false otherwise
   * @since   1.5.5
   */
  public function isGif($file)
  {
    jimport('joomla.filesystem.file');

    // Reads file content into string
    $filecontents = JFile::read($file);
    $str_loc      = 0;
    $count        = 0;

    // Checks if there is more than one frame
    while($count < 2)
    {
      $where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);
      if(!$where1)
      {
        break;
      }
      else
      {
        $str_loc = $where1+1;
        $where2  = strpos($filecontents, "\x00\x2C", $str_loc);
        if(!$where2)
        {
          break;
        }
        else
        {
          if($where1+8 == $where2)
          {
            $count++;
          }
        $str_loc = $where2+1;
        }
      }
    }

    // Returns true if more then one frame is found
    if($count > 1)
    {
      return true;
    }

    return false;
  }

  /**
   * Method to include the watermark selected in
   * the configuration manager into a given image
   *
   * @param   string    $file     Path to the image into which the watermark shall be included
   * @param   resource  $src_img  GD image resource, will be used instead of $file if it is set
   * @return  resource  image resource
   * @since   1.5.5
   */
  public function includeWatermark($file, $src_img = null, $cropwidth = 0, $cropheight = 0)
  {
    // Path to the watermarkfile
    $watermark = JPath::clean($this->_ambit->get('wtm_path').$this->_config->get('jg_wmfile'));

    // Checks if watermark file is existent
    if(!JFile::exists($watermark))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_ERROR_WATERMARK_NOT_EXIST'));

      return false;
    }

    // Gets information of the image (height, width, mime)
    if(!$src_img)
    {
      $info_img = getimagesize($file);
    }
    else
    {
      $info_img = array(0 => imagesx($src_img), 1 => imagesy($src_img));
    }

    $info_wat = getimagesize($watermark);

    switch($info_wat[2])
    {
      case 1:
        $watermark  = imagecreatefromgif($watermark);
        $mime_wat   = 'image/gif';
        break;
      case 2:
        $watermark  = imagecreatefromjpeg($watermark);
        $mime_wat   = 'image/jpeg';
        break;
      case 3:
        $watermark  = imagecreatefrompng($watermark);
        $mime_wat   = 'image/png';
        break;
      default:
        $this->setError(JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $info_wat[2]));

        return false;
    }

    $watermarkzoom = $this->_config->get('jg_watermarkzoom');

    if($watermarkzoom)
    {
      $watermarksize = $this->_config->get('jg_watermarksize');

      if($watermarksize <= 0)
      {
        $watermarksize = 1;
      }
      elseif($watermarksize > 100)
      {
        $watermarksize = 100;
      }

      $widthwm  = $info_wat[0];
      $heightwm = $info_wat[1];

      if($watermarkzoom == 1)
      {
        // Resize by height
        $newheight_watermark = $info_img[1] * $watermarksize / 100;
        $newwidth_watermark  = $newheight_watermark * $widthwm / $heightwm;

        if($newwidth_watermark > $info_img[0])
        {
          $newwidth_watermark  = $info_img[0];
        }
      }
      else
      {
        // Resize by width
        $newwidth_watermark  = $info_img[0] * $watermarksize / 100;
        $newheight_watermark = $newwidth_watermark * $heightwm / $widthwm;

        if($newheight_watermark > $info_img[1])
        {
          $newheight_watermark = $info_img[1];
        }
      }

      $newwatermark = ImageCreateTrueColor($newwidth_watermark, $newheight_watermark);
      imagealphablending($newwatermark, false);
      imagecopyresampled($newwatermark, $watermark, 0, 0, 0, 0, $newwidth_watermark, $newheight_watermark, $widthwm, $heightwm);

      $info_wat[0] = $newwidth_watermark;
      $info_wat[1] = $newheight_watermark;

      imagedestroy($watermark);

      $watermark = $newwatermark;
    }

    // Gets the position of the watermark
    $position = $this->_config->get('jg_watermarkpos');

    // Position x
    switch(($position - 1) % 3)
    {
      case 1:
        $pos_x = round(($info_img[0] - $info_wat[0]) / 2, 0);
        break;
      case 2:
        $pos_x = $info_img[0] - $info_wat[0];
        break;
      default:
        $pos_x = 0;
        break;
    }

    // Position y
    switch(floor(($position - 1) / 3))
    {
      case 1:
        $pos_y = round(($info_img[1] - $info_wat[1]) / 2, 0);
        break;
      case 2:
        $pos_y = $info_img[1] - $info_wat[1];
        break;
      default:
        $pos_y = 0;
        break;
    }

    if(!$src_img)
    {
      switch($info_img[2])
      {
        case 1:
          $src_img  = imagecreatefromgif($file);
          $mime_img = 'image/gif';
          break;
        case 2:
          $src_img  = imagecreatefromjpeg($file);
          $mime_img = 'image/jpeg';
          break;
        case 3:
          $src_img  = imagecreatefrompng($file);
          $mime_img = 'image/png';
          break;
        default:
          $this->setError(JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $info_img[2]));

          return false;
      }
    }

    // Check if image is smaller than watermark and return image without watermark
    if($info_img[0] < $info_wat[0] || $info_img[1] < $info_wat[1])
    {
      imagedestroy($watermark);

      return $src_img;
    }

    imagealphablending($src_img, true);
    imagecopyresampled($src_img, $watermark, $pos_x, $pos_y, 0, 0, $info_wat[0], $info_wat[1], $info_wat[0], $info_wat[1]);
    imagedestroy($watermark);

    return $src_img;
  }

  /**
   * Method to load image data
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadImage()
  {
    if(!$this->_id)
    {
      return false;
    }

    // Load the image data if it doesn't already exist
    if(empty($this->_image))
    {
      $query = $this->_db->getQuery(true)
            ->select('a.*, a.owner AS imgowner, c.*')
            ->from(_JOOM_TABLE_IMAGES.' AS a')
            ->from(_JOOM_TABLE_CATEGORIES.' AS c')
            ->from(_JOOM_TABLE_CATEGORIES.' AS p')
            ->where('a.id = '.$this->_id)
            ->where('a.published  = 1')
            ->where('a.approved   = 1')
            ->where('c.published  = 1')
            ->where('c.cid        = a.catid')
            ->where('c.lft BETWEEN p.lft AND p.rgt')
            ->where('p.cid         != 1')
            ->where('a.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('c.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('p.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('(p.password = '.$this->_db->q('').' OR p.cid IN ('.implode(',', $this->_mainframe->getUserState('joom.unlockedCategories', array(0))).'))')
            ->group('a.id')
            ->having('COUNT(p.cid) >= c.level');
      $this->_db->setQuery($query);

      if(!$row = $this->_db->loadObject())
      {
        $this->setError(JText::sprintf('Image with ID %d not found', $this->_id));

        return false;
      }

      $this->_image = $row;

      return true;
    }

    return true;
  }

  /**
   * Method to crop a image
   *
   * @param   string    $img        Path to image
   * @param   int       $cropwidth  Width of resulting image
   * @param   int       $cropheight Height of resulting image
   * @param   int       $croppos    Offset position of cropping window
   * @param   int       $offsetx    Offset x-coordinate
   * @param   int       $offsety    Offset y-coordinate
   * @return  image     Image ressource of cropped image or image if no cropping
   *                    false if no cropping has been done
   * @since   1.5.6
   */
  public function cropImage(&$img, &$cropwidth, &$cropheight, &$croppos, $offsetx = 0, $offsety = 0)
  {
    // Get information of image
    $imginfo    = getimagesize($img);
    // Height/width
    $srcWidth    = $imginfo[0];
    $srcHeight   = $imginfo[1];
    $srcImgtype  = $imginfo[2];

    // If both crop settings identical to the source dimensions, return null
    if($srcWidth == $cropwidth && $srcHeight == $cropheight)
    {
      return null;
    }

    if($croppos)
    {
      // Calculate the offsets for cropping the source image according
      // to thumbposition
      switch($croppos)
      {
        // Right upper corner
        case 1:
          $offsetx = floor($srcWidth - $cropwidth);
          $offsety = 0;
          break;
        // Left lower corner
        case 3:
          $offsetx = 0;
          $offsety = floor($srcHeight - $cropheight);
          break;
        // Right lower corner
        case 4:
          $offsetx = floor($srcWidth - $cropwidth);
          $offsety = floor($srcHeight - $cropheight);
          break;
        // default center
        default:
          $offsetx = floor(($srcWidth - $cropwidth) * 0.5);
          $offsety = floor(($srcHeight - $cropheight) * 0.5);
          break;
      }
    }

    switch($srcImgtype)
    {
      // GIF
      case 1:
        $src_img = imagecreatefromgif($img);
        break;
      // JPEG
      case 2:
        $src_img = imagecreatefromjpeg($img);
        break;
      // PNG
      case 3:
        $src_img = imagecreatefrompng($img);
        break;
      default:
        $src_img = imagecreatefromjpeg($img);
        break;
    }

    $cropimg = imagecreatetruecolor($cropwidth, $cropheight);

    // Check if the cropped image should be filled with background color
    if($cropwidth > $srcWidth || $cropheight > $srcHeight)
    {
      // Get background color for cropped image
      $cropbgcol = $this->_config->get('jg_dyncropbgcol');
      if(!$cropbgcol || in_array($cropbgcol, array('none', 'transparent')))
      {
        $cropbgcol = 'FFFFFF';
      }
      elseif($cropbgcol['0'] == '#')
      {
        $cropbgcol = substr($cropbgcol, 1);
      }

      // Calculate a rgb code from hex value
      $rgb[0] = hexdec(substr($cropbgcol, 0, 2));
      $rgb[1] = hexdec(substr($cropbgcol, 2, 2));
      $rgb[2] = hexdec(substr($cropbgcol, 4, 2));

      // Allocate the color and fill the background of cropped image
      $bgcolor=imagecolorallocate($cropimg, $rgb[0], $rgb[1], $rgb[2]);
      imagefill($cropimg, 0, 0, $bgcolor);
    }

    $dst_x = 0;
    $dst_y = 0;
    $src_w = $cropwidth;
    $src_h = $cropheight;

    // The starting position '$offsetx' for the crop must be within the source image
    if($offsetx < 0 || $offsetx > $srcWidth)
    {
      $offsetx = 0;
    }
    // Check, if the cropped image width will be larger than the source image width
    if($cropwidth > $srcWidth)
    {
      // Center source image horizontal
      $dst_x = floor(($cropwidth - $srcWidth) * 0.5);
      // Reduce width to copy to the source image width
      $src_w = $srcWidth;
    }
    // The starting position '$offsety' for the crop must be within the source image
    if($offsety < 0 || $offsety > $srcHeight)
    {
      $offsety = 0;
    }
    // Check, if the cropped image height will be larger than the source image height
    if($cropheight > $srcHeight)
    {
      // Center source image vertical
      $dst_y = floor(($cropheight - $srcHeight) * 0.5);
      // Reduce height to copy to the source image height
      $src_h = $srcHeight;
    }

    imagecopy($cropimg, $src_img, $dst_x, $dst_y, $offsetx, $offsety, $src_w, $src_h);

    return $cropimg;
  }
}