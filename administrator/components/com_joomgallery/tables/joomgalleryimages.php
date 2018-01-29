<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/tables/joomgalleryimages.php $
// $Id: joomgalleryimages.php 4350 2014-01-18 15:16:40Z erftralle $
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
 * JoomGallery images table class
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class TableJoomgalleryImages extends JTable
{
  /** @var int Primary key */
  var $id           = null;
  /** @var int */
  var $asset_id     = null;
  /** @var int */
  var $catid        = null;
  /** @var string */
  var $imgtitle     = null;
  /** @var string */
  var $alias        = null;
  /** @var string */
  var $imgauthor    = null;
  /** @var string */
  var $imgtext      = null;
  /** @var string */
  var $imgdate      = null;
  /** @var int */
  var $hits         = 0;
  /** @var int */
  var $downloads    = 0;
  /** @var int */
  var $imgvotes     = null;
  /** @var int */
  var $imgvotesum   = null;
  /** @var int */
  var $published    = null;
  /** @var int */
  var $hidden       = 0;
  /** @var int */
  var $featured     = 0;
  /** @var string */
  var $imgfilename  = null;
  /** @var string */
  var $imgthumbname = null;
  /** @var string */
  var $checked_out  = null;
  /** @var string */
  var $owner        = 0;
  /** @var int */
  var $approved     = null;
  /** @var int */
  var $access       = null;
  /** @var int */
  var $useruploaded = null;
  /** @var int */
  var $ordering     = null;
  /** @var string */
  var $params       = null;
  /** @var string */
  var $metakey      = null;
  /** @var string */
  var $metadesc     = null;

  /**
   * Constructor
   *
   * @param   object  $db A database connector object
   * @since   1.5.5
   */
  public function __construct($db)
  {
    parent::__construct(_JOOM_TABLE_IMAGES, 'id', $db);
  }

  /**
   * Overloaded check function
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function check()
  {
    if(empty($this->imgtitle))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_ERROR_IMAGE_MUST_HAVE_TITLE'));
      return false;
    }

    if(empty($this->catid))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_ERROR_NO_CATEGORY_SELECTED'));
      return false;
    }

    /*// Check whether state is allowed regarding selected category
    if($this->published)
    {
      $query = "SELECT
                  published
                FROM
                  "._JOOM_TABLE_CATEGORIES."
                WHERE
                  cid = ".$this->catid;
      $this->_db->setQuery($query);
      if($category = $this->_db->loadObject())
      {
        if(!$category->published)
        {
          $this->published = 0;
          if($this->id)
          {
            JError::raiseNotice('100', JText::sprintf('COM_JOOMGALLERY_COMMON_NOT_ALLOWED_TO_PUBLISH_IMAGE', $this->id));
          }
          else
          {
            JError::raiseNotice('100', JText::_('COM_JOOMGALLERY_COMMON_NOT_ALLOWED_TO_PUBLISH_NEW_IMAGE'));
          }
        }
      }
    }*/

    // clean up keywords -- eliminate extra spaces between phrases
    // and cr (\r) and lf (\n) characters from string
    if(!empty($this->metakey))
    {
      // array of characters to remove
      $bad_characters = array("\n", "\r", "\"", '<', '>');
      // remove bad characters
      $after_clean = JString::str_ireplace($bad_characters, '', $this->metakey);
      // create array using commas as delimiter
      $keys = explode(',', $after_clean);
      $clean_keys = array();
      foreach($keys as $key)
      {
        // ignore blank keywords
        if(trim($key))
        {
          $clean_keys[] = trim($key);
        }
      }
      // put array back together delimited by ', '
      $this->metakey = implode(', ', $clean_keys);
    }

    // clean up description -- eliminate quotes and <> brackets
    if(!empty($this->metadesc))
    {
      $bad_characters = array("\"", '<', '>');
      $this->metadesc = JString::str_ireplace($bad_characters, '', $this->metadesc);
    }

    // Create the alias only if none was entered and if ID of image is already available
    if($this->id && empty($this->alias))
    {
      $this->alias = $this->imgtitle.'-'.$this->id;
    }

    if(!empty($this->alias))
    {
      $this->alias = JApplication::stringURLSafe($this->alias);
    }

    return true;
  }

  /**
   * Overloaded store function
   *
   * @param   boolean $updateNulls  True to update fields even if they are null.
   * @return  boolean True on success, false otherwise
   * @since   1.5.7
   */
  public function store($updateNulls = false)
  {
    if(trim(str_replace('-', '', $this->alias)) == '')
    {
      // Store the row in order to get the image ID
      if(!parent::store())
      {
        return false;
      }

      $this->alias = $this->imgtitle.'-'.$this->id;
      $this->alias = JApplication::stringURLSafe($this->alias);

      if(trim(str_replace('-', '', $this->alias)) == '')
      {
        $datenow      = JFactory::getDate();
        $this->alias  = $datenow->format('Y-m-d-H-i-s');
      }
    }

    return parent::store($updateNulls);
  }

  /**
   * Method to delete a row from the database table by primary key value
   *
   * @param   mixed    $pk  An optional primary key value to delete. If
   *                        not set the instance property value is used.
   * @return  boolean  True on success, false otherwise
   * @since   2.0
   */
  public function delete($pk = null)
  {
    $k = $this->_tbl_key;
    $pk = (is_null($pk)) ? $this->$k : $pk;

    if(!parent::delete($pk))
    {
      return false;
    }

    // Delete references for category thumbnails in categories table
    $query = $this->_db->getQuery(true)
          ->update(_JOOM_TABLE_CATEGORIES)
          ->set('thumbnail = 0')
          ->where('thumbnail = '.$pk);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    return true;
  }

  /**
   * Method to increment the downloads counter for an image row.
   *
   * @param   mixed  $pk  An optional primary key value to increment.
   *                      If not set the instance property value is used.
   *
   * @return  boolean  True on success, false otherwise.
   *
   * @since   3.1
   */
  public function download($pk = null)
  {
    $k = $this->_tbl_key;
    $pk = (is_null($pk)) ? $this->$k : $pk;

    // If no primary key is given, return false.
    if ($pk === null)
    {
      return false;
    }

    // Check the row in by primary key.
    $query = $this->_db->getQuery(true)
          ->update($this->_tbl)
          ->set($this->_db->quoteName('downloads').' = ('.$this->_db->quoteName('downloads').' + 1)')
          ->where($this->_tbl_key.' = '.$this->_db->quote($pk));

    $this->_db->setQuery($query);
    $this->_db->execute();

    // Set table values in the object.
    $this->downloads++;

    return true;
  }

  /**
   * Reorders the images according
   * to the latest changes
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function reorderAll()
  {
    $query = $this->_db->getQuery(true)
          ->select('DISTINCT catid')
          ->from($this->_db->quoteName($this->_tbl));
    $this->_db->setQuery($query);
    $catids = $this->_db->loadColumn();

    foreach($catids as $catid)
    {
      $this->reorder('catid = '.$catid);
    }
  }

  /**
   * Returns the ordering value to place a new item first in its group
   *
   * @param   string  $where  query WHERE clause for selecting MAX(ordering).
   * @return  int     The ordring number
   * @since   1.5.5
   */
  public function getPreviousOrder($where = '')
  {
    if(!in_array('ordering', array_keys($this->getProperties())))
    {
      $this->setError(get_class($this).' does not support ordering');
      return false;
    }

    $query = 'SELECT MIN(ordering)' .
        ' FROM ' . $this->_tbl .
        ($where ? ' WHERE '.$where : '');

    $this->_db->setQuery($query);
    $maxord = $this->_db->loadResult();

    if($this->_db->getErrorNum())
    {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return $maxord - 1;
  }

  /**
   * Method to compute the name of the asset
   *
   * @return  string  The asset name
   * @since   2.0
   */
  protected function _getAssetName()
  {
    return _JOOM_OPTION.'.image.'.$this->id;
  }

  /**
   * Method to return the title to use for the asset table
   *
   * @return  string The title of the asset
   * @since   2.0
   */
  protected function _getAssetTitle()
  {
    return $this->imgtitle;
  }

  /**
   * Get the parent asset id for the current image
   *
   * @param   JTable   $table  A JTable object for the asset parent.
   * @param   integer  $id     Id to look up
   * @return  int The parent asset id for the image
   * @since   2.0
   */
  protected function _getAssetParentId(JTable $table = null, $id = null)
  {
    // Get the database object
    $db = $this->getDbo();

    // Build the query to get the asset id for the category
    $query = $db->getQuery(true)
          ->select('asset_id')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('cid = '.(int) $this->catid);

    // Get the asset id from the database
    $db->setQuery($query);
    if($result = $db->loadResult())
    {
      return $result;
    }
    else
    {
      // If the parser reaches this point there was something wrong
      throw new JException(JText::_('Parent asset ID could not be found'));
    }
  }
}