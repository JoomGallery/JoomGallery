<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/userpanel.php $
// $Id: userpanel.php 4250 2013-05-02 16:49:22Z chraneco $
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
 * JoomGallery User Panel Model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelUserpanel extends JoomGalleryModel
{
  /**
   * Images data array
   *
   * @var array
   */
  protected $_images;

  /**
   * Images number
   *
   * @var int
   */
  protected $_total;

  /**
   * Categories data array
   *
   * @var array
   */
  protected $_categories;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    $this->filter_fields = array( 'imgtitle',
                                  'ordering',
                                  'imgdate',
                                  'hits',
                                  'downloads',
                                  'published',
                                  'approved',
                                  'catid'
                                );
    parent::__construct();
  }

  /**
   * Retrieves the images data
   *
   * @return  array An array of images objects
   * @since   1.5.5
   */
  public function getImages()
  {
    // Guests cannot own any images
    if(!$this->_user->get('id'))
    {
      return array();
    }

    if($this->_loadImages())
    {
      return $this->_images;
    }

    return array();
  }

  /**
   * Method to get the total number of images
   *
   * @return  mixed   The total number of images or false
   * @since   1.5.5
   */
  public function getTotal()
  {
    // Guests cannot own any images
    if(!$this->_user->get('id'))
    {
      return 0;
    }

    // Let's load the data if it doesn't already exist
    if(empty($this->_total))
    {
      $query = $this->_buildQuery();
      try
      {
        $this->_total = $this->_getListCount($query);
      }
      catch(RuntimeException $e)
      {
        $this->setError($e->getMessage());
        return false;
      }
    }

    return $this->_total;
  }

  /**
   * Method to get the starting number of items for the data set.
   *
   * @return  int The starting number of items available in the data set.
   * @since   3.0
   */
  public function getStart()
  {
    $start = $this->getState('list.start');
    $limit = $this->getState('list.limit');
    $total = $this->getTotal();
    if($start > $total - $limit)
    {
      $start = max(0, (int)(ceil($total / $limit) - 1) * $limit);
    }

    return $start;
  }

  /**
   * Method to get the pagination object for the list.
   * This method uses 'getTotal', 'getStart' and the current list limit of this view.
   *
   * @return  object  A pagination object
   * @since   3.0
   */
  public function getPagination()
  {
    jimport('joomla.html.pagination');
    return new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));
  }

  /**
   * Retrieves the categories data from the database
   *
   * @return  array An array of category IDs
   * @since   1.5.5
   */
  public function getCategories()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categories))
    {
      $query = $this->_db->getQuery(true)
            ->select('cid')
            ->from(_JOOM_TABLE_CATEGORIES);
      if(!$this->_config->get('jg_showallpicstoadmin') || !$this->_user->authorise('core.admin'))
      {
        $query->where('owner = '.$this->_user->get('id'));
      }

      $this->_db->setQuery($query);

      $this->_categories  = $this->_db->loadColumn();
    }

    return $this->_categories;
  }

  /**
   * Gets the value of a user state variable and sets it in the session
   * This is the same as the method in JApplication except that this also can optionally
   * force you back to the first page when a filter has changed
   *
   * @param   string  $key        The key of the user state variable
   * @param   string  $request    The name of the variable passed in a request
   * @param   string  $default    The default value for the variable if not found (optional)
   * @param   string  $type       Filter for the variable, for valid values see {@link JFilterInput::clean()} (optional)
   * @param   boolean $resetPage  If true, the limitstart in request is set to zero if the state has changed
   * @return  The requested user state
   * @since   3.0
   */
  public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
  {
    $old_state = $this->_mainframe->getUserState($key);
    $cur_state = (!is_null($old_state)) ? $old_state : $default;
    $new_state = JRequest::getVar($request, null, 'default', $type);

    if($cur_state != $new_state && !is_null($new_state) && !is_null($old_state) && $resetPage)
    {
      JRequest::setVar('limitstart', 0);
    }

    // Save the new value only if it was set in this request.
    if($new_state !== null)
    {
      $this->_mainframe->setUserState($key, $new_state);
    }
    else
    {
      $new_state = $cur_state;
    }

    return $new_state;
  }

  /**
   * Method to auto-populate the model state.
   *
   * This method should only be called once per instantiation and is designed
   * to be called on the first call to the getState() method unless the model
   * configuration flag to ignore the request is set.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  $ordering   An optional ordering field.
   * @param   string  $direction  An optional direction (asc|desc).
   *
   * @return  void
   *
   * @since   3.0
   */
  protected function populateState($ordering = 'imgdate', $direction = 'desc')
  {
    $search = $this->getUserStateFromRequest('joom.userpanel.filter.search', 'filter_search', '');
    $this->setState('filter.search', $search);

    $published = $this->getUserStateFromRequest('joom.userpanel.filter.state', 'filter_state', '');
    $this->setState('filter.state', $published);

    $category = $this->getUserStateFromRequest('joom.userpanel.filter.category', 'filter_category', '');
    $this->setState('filter.category', $category);

    $value = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
    $limit = $value;
    $this->setState('list.limit', $limit);

    $value = $this->getUserStateFromRequest('joom.userpanel.limitstart', 'limitstart', 0);
    $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
    $this->setState('list.start', $limitstart);

    // Check if the ordering field is in the white list, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.userpanel.ordercol', 'filter_order', $ordering);
    if(!in_array($value, $this->filter_fields))
    {
      $value = $ordering;
      $this->_mainframe->setUserState('joom.userpanel.ordercol', $value);
    }
    $this->setState('list.ordering', $value);

    // Check if the ordering direction is valid, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.userpanel.orderdirn', 'filter_order_Dir', $direction);
    if(!in_array(strtoupper($value), array('ASC', 'DESC', '')))
    {
      $value = $direction;
      $this->_mainframe->setUserState('joom.userpanel.orderdirn', $value);
    }
    $this->setState('list.direction', $value);

    if($search || $published || $category)
    {
      $this->setState('filter.inuse', 1);
    }
  }

  /**
   * Loads the images data from the database
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_images))
    {
      jimport('joomla.filesystem.file');

      $query = $this->_buildQuery();

      if(!$rows = $this->_getList($query, $this->getState('list.start'), $this->getState('list.limit')))
      {
        return false;
      }

      $this->_images = $rows;
    }

    return true;
  }

  /**
   * Returns the query to get the images rows from the database
   *
   * @return  string  The query to get the image rows from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('*')
          ->from(_JOOM_TABLE_IMAGES);

    // Filter by state
    $filter = $this->getState('filter.state');
    switch($filter)
    {
      case 1:
        // Approved
        $query->where('approved = 1');
        break;
      case 2:
        // Not yet approved / rejected
        $query->where('(approved = 0 OR approved = -1)');
        break;
      case 3:
        // Published
        $query->where('published = 1');
        break;
      case 4:
        // Not published
        $query->where('published = 0');
        break;
      default:
        // No filter by state
        break;
    }

    // Filter by category
    $catid = $this->getState('filter.category');
    if(!empty($catid))
    {
      $query->where('catid = '.$catid);
    }

    // A Super User will see all images if the correspondent backend option is enabled
    if(!$this->_config->get('jg_showallpicstoadmin') || !$this->_user->authorise('core.admin'))
    {
      $query->where('owner = '.$this->_user->get('id'));
    }

    // Search
    $search = $this->getState('filter.search');
    if(!empty($search))
    {
      if(stripos($search, 'id:') === 0)
      {
        $query->where('id = '.(int) substr($search, 3));
      }
      else
      {
        $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
        $query->where('(imgtitle LIKE '.$search.' OR alias LIKE '.$search.' OR LOWER(imgtext) LIKE '.$search.')');
      }
    }

    // Add the order clause
    $orderCol   = $this->state->get('list.ordering');
    $orderDirn  = $this->state->get('list.direction');
    if($orderCol == 'ordering' || $orderCol == 'catid')
    {
      $orderCol = 'catid '.$orderDirn.', ordering';
    }
    $query->order($this->_db->escape($orderCol.' '.$orderDirn));

    return $query;
  }
}