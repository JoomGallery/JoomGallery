<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/tables/joomgallerycategories.php $
// $Id: joomgallerycategories.php 4350 2014-01-18 15:16:40Z erftralle $
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

jimport('joomla.database.tablenested');

/**
 * JoomGallery categories table class
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class TableJoomgalleryCategories extends JTableNested
{
  /** @var int Primary key */
  public $cid               = null;
  /** @var int */
  public $asset_id          = null;
  /** @var int */
  public $owner             = 0;
  /** @var string */
  public $name              = null;
  /** @var string */
  public $alias             = null;
  /** @var string */
  public $description       = null;
  /** @var string */
  public $access            = 0;
  /** @var int */
  public $published         = 0;
  /** @var int */
  public $hidden            = 0;
  /** @var int */
  public $in_hidden         = 0;
  /** @var string */
  public $password          = '';
  /** @var int */
  public $thumbnail         = 0;
  /** @var int */
  public $img_position      = -1;
  /** @var string */
  public $catpath           = null;
  /** @var string */
  public $params            = null;
  /** @var string */
  public $metakey           = null;
  /** @var string */
  public $metadesc          = null;
  /** @var int */
  public $exclude_toplists  = 0;
  /** @var int */
  public $exclude_search    = 0;

  /**
   * Helper variable for checking whether
   * 'hidden' is changed
   *
   * @var int
   */
  private $_hidden = 0;

  /**
   * Helper variable for checking whether
   * 'in_hidden' is changed
   *
   * @var int
   */
  private $_in_hidden = 0;

  /**
   * Constructor
   *
   * @param   object  $db A database connector object
   * @since   1.5.5
   */
  public function __construct($db)
  {
    parent::__construct(_JOOM_TABLE_CATEGORIES, 'cid', $db);
  }

  /**
   * Overloaded load function, loads a specific row.
   *
   * @param   mixed   The primary key, if it is not specified the value of the current key is used
   * @return  boolean True on success, false otherwise
   * @since   1.5.7
   */
  public function load($oid = null, $reset = true)
  {
    if(!parent::load($oid, $reset))
    {
      return false;
    }

    // Store the current values of 'hidden' and 'in_hidden' in
    // order to be able to detect changes of this state later on
    $this->_hidden    = $this->hidden;
    $this->_in_hidden = $this->in_hidden;

    return true;
  }

  /**
   * Overloaded check function, validates the row.
   * This method should always be called afore calling 'store'.
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function check()
  {
    if(empty($this->name))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_ERROR_CATEGORY_MUST_HAVE_TITLE'));

      return false;
    }

    $this->parent_id = (int) $this->parent_id;
    if($this->parent_id < 1)
    {
      $this->setError('Invalid parent category ID: '.$this->parent_id);

      return false;
    }

    JFilterOutput::objectHTMLSafe($this->name);

    // For the the next two checks get published
    // state and hidden state of parent category
    $query = $this->_db->getQuery(true)
          ->select('published, hidden, in_hidden')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('cid = '.(int) $this->parent_id);
    $this->_db->setQuery($query);
    if(!$parent = $this->_db->loadObject())
    {
      $this->setError($this->_db->getErrorMsg() ? $this->_db->getErrorMsg() : 'Parent category could not be found: '.$this->parent_id);

      return false;
    }

    // Check whether state is allowed regarding parent categories
    if($this->published && $this->parent_id > 1 && !$parent->published)
    {
      $this->published = 0;
      if($this->cid)
      {
        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_COMMON_NOT_ALLOWED_TO_PUBLISH_CATEGORY', $this->cid), 'notice');
      }
      else
      {
        JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMON_NOT_ALLOWED_TO_PUBLISH_NEW_CATEGORY'), 'notice');
      }
    }

    // Check whether 'in_hidden' flag has to be set to 1 or 0
    if($parent->hidden || $parent->in_hidden)
    {
      $this->in_hidden = 1;
    }
    else
    {
      $this->in_hidden = 0;
    }

    // Trim slashes from catpath
    $this->catpath = trim($this->catpath, '/');

    if(empty($this->alias))
    {
      if(!empty($this->catpath))
      {
        $catpath  = explode('/', trim($this->catpath, '/'));
        $segments = array();
        foreach($catpath as $segment)
        {
          $segment = str_replace('_', ' ', rtrim(rtrim($segment, '0123456789'), '_'));
          $segment = JApplication::stringURLSafe($segment);
          if($segment)
          {
            $segments[] = $segment;
          }
          else
          {
            $datenow = JFactory::getDate();
            $segments[] = $datenow->format('Y-m-d-H-i-s');
          }
        }
        $this->alias = implode('/', $segments);
      }
    }
    else
    {
      $alias = explode('/', trim($this->alias, '/'));
      $segments = array();
      foreach($alias as $segment)
      {
        $segment = JApplication::stringURLSafe($segment);
        if($segment)
        {
          $segments[] = $segment;
        }
        else
        {
          $datenow    = JFactory::getDate();
          $segments[] = $datenow->format('Y-m-d-H-i-s');
        }
      }
      $this->alias = implode('/', $segments);
    }

    if(trim(str_replace('-', '', $this->alias)) == '' && !empty($this->catpath))
    {
      $datenow      = JFactory::getDate();
      $this->alias  = $datenow->format('Y-m-d-H-i-s');
    }

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

    return true;
  }

  /**
   * Overloaded store function
   *
   * @param   boolean   $updateNulls  True to update null values as well.
   * @return  boolean   True on success, false otherwise
   * @since   1.5.7
   */
  public function store($updateNulls = false)
  {
    if(!parent::store($updateNulls))
    {
      return false;
    }

    // If there aren't any sub categories there isn't anything to do anymore
    $cats = JoomHelper::getAllSubCategories($this->cid, false, true, true, false);
    if(!count($cats))
    {
      return true;
    }

    // Set state of all sub-categories
    // according to the settings of this category
    $query = $this->_db->getQuery(true)
          ->update(_JOOM_TABLE_CATEGORIES)
          ->set('published = '.(int) $this->published)
          ->where('cid IN ('.implode(',', $cats).')');
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    // Set 'in_hidden' of all sub-categories
    // according to hidden state of this category
    // (but only if there was a change of this state)
    if(   ($this->_hidden != $this->hidden && !$this->in_hidden)
      ||  $this->_in_hidden != $this->in_hidden
      )
    {
      if($this->hidden == 0 && $this->in_hidden == 0)
      {
        // If 'hidden' is 0 only the categories
        // which aren't set to hidden must be changed
        // because they form a hidden group themselves
        // anyway and have to stay hidden
        $cats = JoomHelper::getAllSubCategories($this->cid, false, true, true, true);
      }

      $query = $this->_db->getQuery(true)
            ->update(_JOOM_TABLE_CATEGORIES)
            ->set('in_hidden = '.(int) ($this->hidden || $this->in_hidden))
            ->where('cid IN ('.implode(',', $cats).')');
      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($this->_db->getErrorMsg());

        return false;
      }
    }

    return true;
  }

  /**
   * Method to compute the name of the asset
   *
   * @return  string  The asset name
   * @since   2.0
   */
  protected function _getAssetName()
  {
    return _JOOM_OPTION.'.category.'.$this->cid;
  }

  /**
   * Method to return the title to use for the asset table
   *
   * @return  string The title of the asset
   * @since   2.0
   */
  protected function _getAssetTitle()
  {
    return $this->name;
  }

  /**
   * Get the parent asset id for the current category
   *
   * @param   JTable   $table  A JTable object for the asset parent.
   * @param   integer  $id     Id to look up
   * @return  int      The parent asset id for the category
   * @since   2.0
   */
  protected function _getAssetParentId(JTable $table = null, $id = null)
  {
    // Get the database object
    $db = $this->getDbo();

    // Check whether the category has a parent category
    if($this->parent_id > 1)
    {
      // Build the query to get the asset id for the parent category
      $query  = $db->getQuery(true);
      $query->select('asset_id');
      $query->from(_JOOM_TABLE_CATEGORIES);
      $query->where('cid = '.(int) $this->parent_id);

      // Get the asset id from the database
      $db->setQuery($query);
      if($result = $db->loadResult())
      {
        return $result;
      }
    }

    // Build the query to get the asset id of the component asset
    $query  = $db->getQuery(true);
    $query->select('id');
    $query->from('#__assets');
    $query->where('name = '.$db->quote(_JOOM_OPTION));

    // Get the asset id from the database.
    $db->setQuery($query);
    if($result = $db->loadResult())
    {
      return $result;
    }

    // If the parser reaches this point there was something wrong
    throw new JException(JText::_('Parent asset ID could not be found'));
  }

  /**
   * Method to recursively rebuild the whole nested set tree.
   *
   * @param   integer  $parentId  The root of the tree to rebuild.
   * @param   integer  $leftId    The left id to start with in building the tree.
   * @param   integer  $level     The level to assign to the current nodes.
   * @param   string   $path      The path to the current nodes.
   *
   * @return  integer  1 + value of root rgt on success, false on failure
   *
   * @link    http://docs.joomla.org/JTableNested/rebuild
   * @since   11.1
   */
  public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
  {
    // If no parent is provided, try to find it.
    if($parentId === null)
    {
      // Get the root item.
      $parentId = $this->getRootId();
      if($parentId === false)
      {
        return false;
      }
    }

    // Build the structure of the recursive query.
    if(!isset($this->_cache['rebuild.sql']))
    {
      $query  = $this->_db->getQuery(true)
            ->select($this->_tbl_key.', alias')
            ->from($this->_tbl)
            ->where('parent_id = %d')
            ->order('parent_id, lft');

      $this->_cache['rebuild.sql'] = (string) $query;
    }

    // Make a shortcut to database object.

    // Assemble the query to find all children of this node.
    $this->_db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));
    $children = $this->_db->loadObjectList();

    // The right value of this node is the left value + 1
    $rightId = $leftId + 1;

    // execute this function recursively over all children
    foreach($children as $node)
    {
      // $rightId is the current right value, which is incremented on recursion return.
      // Increment the level for the children.
      // Add this item's alias to the path (but avoid a leading /)
      $rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1, $path.(empty($path) ? '' : '/').$node->alias);

      // If there is an update failure, return false to break out of the recursion.
      if($rightId === false)
      {
        return false;
      }
    }

    // We've got the left value, and now that we've processed
    // the children of this node we also know the right value.
    $query = $this->_db->getQuery(true)
          ->update($this->_tbl)
          ->set('lft = '. (int) $leftId)
          ->set('rgt = '. (int) $rightId)
          ->set('level = '.(int) $level)
          ->where($this->_tbl_key.' = '. (int)$parentId);
    $this->_db->setQuery($query);

    // If there is an update failure, return false to break out of the recursion.
    if(!$this->_db->query())
    {
      $e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBUILD_FAILED', get_class($this), $this->_db->getErrorMsg()));
      $this->setError($e);

      return false;
    }

    // Return the right value of this node + 1.
    return $rightId + 1;
  }
}