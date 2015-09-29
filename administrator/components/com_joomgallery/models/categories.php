<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/categories.php $
// $Id: categories.php 4405 2014-07-02 07:13:31Z chraneco $
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
 * Categories model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelCategories extends JoomGalleryModel
{
  /**
   * Categories data array
   *
   * @var array
   */
  protected $_categories = null;

  /**
   * Categories number
   *
   * @var int
   */
  protected $_total = null;

  /**
   * Constructor
   *
   * @param   array An optional associative array of configuration settings
   * @return  void
   * @since   2.0
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->filter_fields = array(
        'cid', 'c.cid',
        'name', 'c.name',
        'alias', 'c.alias',
        'parent_id', 'c.parent_id',
        'published', 'c.published',
        'access', 'c.access', 'access_level',
        'owner', 'c.owner',
        'lft', 'c.lft',
        'rgt', 'c.rgt',
        'level', 'c.level',
        'type'
        );
  }

  /**
   * Retrieves the data of the categories
   *
   * @return  array Array of objects containing the categories data from the database
   * @since   1.5.5
   */
  public function getCategories()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categories))
    {
      // Get the data of the categories which will actually be displayed
      $query = $this->_buildQuery();
      $this->_db->setQuery($query, $this->getStart(), $this->getState('list.limit'));
      $current_categories = $this->_db->loadObjectList('cid');

      // Get the complete category structure (containing only
      // the categories which we are allowed to display)
      $categories = $this->_ambit->getCategoryStructure();

      $levels             = array();
      $ordering           = array();
      $this->_categories  = array();
      foreach($categories as $key => $category)
      {
        // Check whether the current category will be displayed
        if(isset($current_categories[$key]))
        {
          // If yes insert it into the array which will be used later on
          $this->_categories[$key] = $current_categories[$key];

          // Create an array which will help to organize ordering later on
          $ordering[$category->parent_id][] = $key;
        }
      }

      $this->setState('ordering.array', $ordering);

      // Check whether we aren't displaying all categories in default order
      if($this->getState('list.ordering') != 'c.lft')
      {
        $this->_categories = $current_categories;

        foreach($this->_categories as $key => $category)
        {
          // Unset all categories which aren't in category structure
          // because we aren't allowed to display them
          if(!isset($categories[$key]))
          {
            unset($this->_categories[$key]);
          }
        }
      }
    }

    return $this->_categories;
  }

  /**
   * Function to get the active filters
   *
   * @return  array  Associative array in the format: array('filter_published' => 0)
   *
   * @since   3.2.3
   */
  public function getActiveFilters()
  {
    $activeFilters = array();

    if (!empty($this->filter_fields))
    {
      foreach ($this->filter_fields as $filter)
      {
        $filterName = 'filter.' . $filter;

        if (property_exists($this->state, $filterName) && (!empty($this->state->{$filterName}) || is_numeric($this->state->{$filterName})))
        {
          $activeFilters[$filter] = $this->state->get($filterName);
        }
      }
    }

    return $activeFilters;
  }

  /**
   * Method to get the pagination object for the list.
   * This method uses 'getTotel', 'getStart' and the current
   * list limit of this view.
   *
   * @return  object  A pagination object
   * @since   2.0
   */
  public function getPagination()
  {
    return new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));
  }

  /**
   * Method to get the total number of categories
   *
   * @return  int   The total number of categories
   * @since   1.5.5
   */
  public function getTotal()
  {
    // Let's load the number of categories if it doesn't already exist
    if(empty($this->_total))
    {
      $query = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }

  /**
   * Method to get the starting number of items for the data set.
   *
   * @return  int   The starting number of items available in the data set.
   * @since   2.0
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
   * Get the filter form
   *
   * @param   array    $data      data
   * @param   boolean  $loadData  load current data
   *
   * @return  JForm/false  the JForm object or false
   *
   * @since   3.2.3
   */
  public function getFilterForm($data = array(), $loadData = true)
  {
    return $this->loadForm(_JOOM_OPTION . '.filter_categories', 'filter_categories', array('control' => '', 'load_data' => $loadData));
  }

  /**
   * Method to get a form object.
   *
   * @param   string   $name     The name of the form.
   * @param   string   $source   The form source. Can be XML string if file flag is set to false.
   * @param   array    $options  Optional array of options for the form creation.
   * @param   boolean  $clear    Optional argument to force load a new form.
   * @param   string   $xpath    An optional xpath to search for the fields.
   *
   * @return  mixed  JForm object on success, False on error.
   *
   * @see     JForm
   * @since   3.2.3
   */
  protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
  {
    // Handle the optional arguments.
    $options['control'] = JArrayHelper::getValue($options, 'control', false);

    // Get the form.
    JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

    try
    {
      $form = JForm::getInstance($name, $source, $options, false, $xpath);

      $form->setFieldAttribute('owner', 'useListboxMaxUserCount', $this->_config->get('jg_use_listbox_max_user_count'), 'filter');

      if(isset($options['load_data']) && $options['load_data'])
      {
        // Get the data for the form.
        $data = $this->loadFormData();
      }
      else
      {
        $data = array();
      }

      // Load the data into the form after the plugins have operated.
      $form->bind($data);
    }
    catch(Exception $e)
    {
      $this->setError($e->getMessage());

      return false;
    }

    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   *
   * @since  3.2.3
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    $data = JFactory::getApplication()->getUserState('joom.categories', new stdClass);

    // Pre-fill the list options
    if (!property_exists($data, 'list'))
    {
      $data->list = array(
          'direction' => $this->state->{'list.direction'},
          'limit'     => $this->state->{'list.limit'},
          'ordering'  => $this->state->{'list.ordering'},
          'start'     => $this->state->{'list.start'}
      );
    }

    return $data;
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  An optional ordering field.
   * @param   string  An optional direction (asc|desc).
   * @return  void
   * @since   2.0
   */
  protected function populateState($ordering = 'c.lft', $direction = 'ASC')
  {
    // Receive & set filters
    $filters = $this->getUserStateFromRequest('joom.categories.filter', 'filter',
      array('search' => null, 'access' => '', 'published' => '', 'type' => '', 'owner' => ''), 'array');

    if($filters)
    {
      foreach($filters as $name => $value)
      {
        $this->setState('filter.' . $name, $value);

        if($value)
        {
          $this->setState('filter.inuse', 1);
        }
      }
    }

    $limit = 0;

    // Receive & set list options
    $list = $this->getUserStateFromRequest('joom.categories.list', 'list',
      array('ordering' => $ordering, 'direction' => $direction, 'fullordering' => $ordering . ' ' . $direction,
        'limit' => $this->_mainframe->getCfg('list_limit'), 'start' => 0), 'array');

    if($list)
    {
      foreach($list as $name => $value)
      {
        // Extra validations
        switch($name)
        {
          case 'fullordering':
            $orderingParts = explode(' ', $value);

            if(count($orderingParts) >= 2)
            {
              // Latest part will be considered the direction
              $fullDirection = end($orderingParts);

              if(in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
              {
                $this->setState('list.direction', $fullDirection);
              }

              unset($orderingParts[count($orderingParts) - 1]);

              // The rest will be the ordering
              $fullOrdering = implode(' ', $orderingParts);

              if(in_array($fullOrdering, $this->filter_fields))
              {
                $this->setState('list.ordering', $fullOrdering);
              }
            }
            else
            {
              $this->setState('list.ordering', $ordering);
              $this->setState('list.direction', $direction);
            }
            break;
          case 'ordering':
            if(!in_array($value, $this->filter_fields))
            {
              $value = $ordering;
            }
            break;

          case 'direction':
            if(!in_array(strtoupper($value), array('ASC', 'DESC', '')))
            {
              $value = $direction;
            }
            break;

          case 'limit':
            $limit = $value;
            break;

            // Just to keep the default case
          default:
            $value = $value;
            break;
        }

        $this->setState('list.' . $name, $value);
      }

      $value      = $this->getUserStateFromRequest('joom.categories.limitstart', 'limitstart', 0);
      $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);

      $this->setState('list.start', $limitstart);
    }
  }

  /**
   * Returns the query for listing the categories
   *
   * @return  object  The query to be used to retrieve the categories data from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('c.*')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c');

    // Join over the access levels
    $query->select('v.title AS access_level')
          ->join('LEFT', '#__viewlevels AS v ON v.id = c.access');

    // Join over the users
    $query->join('LEFT', '#__users AS u ON u.id = c.owner');

    // ROOT category shouldn't be selected
    $query->where('parent_id > 0');

    // Filter by allowed access levels
    if(!$this->_user->authorise('core.admin'))
    {
      $query->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
    }

    // Filter by access level
    if($access = $this->getState('filter.access'))
    {
      $query->where('c.access = '.(int) $access);
    }

    // Filter by state
    $published = $this->getState('filter.published');
    if(is_numeric($published))
    {
      switch($published)
      {
        case 1:
          // Published
          $query->where('published = 1');
          break;
        case 0:
          // Not published
          $query->where('published = 0');
          break;
        default:
          // No filter by state
          break;
      }
    }

    // Filter by owner
    if($owner = $this->getState('filter.owner'))
    {
      $query->where('c.owner = '.(int) $owner);
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // User categories
        $query->where('c.owner != 0');
        break;
      case 2:
        // Administrator categories
        $query->where('c.owner = 0');
        break;
      default:
        // No filter by type
        break;
    }

    // Filter by search
    $search = $this->getState('filter.search');
    if(!empty($search))
    {
      if(stripos($search, 'id:') === 0)
      {
        $query->where('c.cid = '.(int) substr($search, 3));
      }
      else
      {
        if(stripos($search, 'author:') === 0)
        {
          $search = $this->_db->Quote('%'.$this->_db->escape(substr($search, 7), true).'%');
          $query->where('(u.name LIKE '.$search.' OR u.username LIKE '.$search.')');
        }
        else
        {
          $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
          $query->where('(c.name LIKE '.$search.' OR c.alias LIKE '.$search.' OR LOWER(c.description) LIKE '.$search.')');
        }
      }
    }

    // Add the order clause
    $query->order($this->_db->escape($this->getState('list.ordering', 'c.lft')).' '.$this->_db->escape($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Method to delete one or more categories
   *
   * @param   array $ids  IDs of categories to delete
   * @return  int   Number of successfully deleted categories, boolean false if an error occured
   * @throws  RuntimeException
   * @since   1.5.5
   */
  public function delete($ids)
  {
    if(!count($ids))
    {
      throw new RuntimeException(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_CATEGORIES_SELECTED'));
    }

    $delete_categories  = array();
    $delete_images      = array();

    $count  = 0;
    $row    = $this->getTable('joomgallerycategories');

    // Loop through selected categories
    foreach($ids as $cid)
    {
      // Check whether we are allowed to delete this category
      if(!$this->_user->authorise('core.delete', _JOOM_OPTION.'.category.'.$cid))
      {
        JLog::add(JText::sprintf('COM_JOOMGALLERY_CATMAN_ERROR_DELETE_NOT_PERMITTED', $cid), JLog::ERROR, 'jerror');

        continue;
      }

      $delete = true;

      // Check whether there are sub-categories
      // We can't use category structure here because sub-categories may have
      // already been deleted in the same request, so the structure isn't up-to-date
      $query = $this->_db->getQuery(true)
            ->select('c.cid')
            ->from(_JOOM_TABLE_CATEGORIES.' AS c')
            ->from(_JOOM_TABLE_CATEGORIES.' AS n')
            ->where('c.lft BETWEEN n.lft AND n.rgt')
            ->where('n.cid = '.$cid);
      $this->_db->setQuery($query);
      $subcategories = $this->_db->loadColumn();

      if(count($subcategories) > 1)
      {
        $msg = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_NOT_EMPTY_CATEGORIES', $cid);
        $this->_mainframe->enqueueMessage($msg, 'notice');

        $delete = false;

        $delete_categories = array_unique(array_merge($delete_categories, $subcategories));
      }

      // Database query to check assigned images to category
      $query = $this->_db->getQuery(true)
            ->select('id')
            ->from(_JOOM_TABLE_IMAGES)
            ->where('catid IN ('.implode(',', $subcategories).')');
      $this->_db->setQuery($query);
      $images = $this->_db->loadColumn();

      if(count($images))
      {
        $msg = JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_CATEGORY_NOT_EMPTY_IMAGES', $cid);
        $this->_mainframe->enqueueMessage($msg, 'notice');

        $delete = false;

        $delete_images      = array_unique(array_merge($delete_images, $images));
        $delete_categories  = array_unique(array_merge($delete_categories, array($cid)));
      }

      if($delete)
      {
        $catpath = JoomHelper::getCatPath($cid);
        if(!$this->_deleteFolders($catpath))
        {
          JLog::add(JText::_('COM_JOOMGALLERY_CATMAN_MSG_ERROR_DELETING_DIRECTORIES'), JLog::WARNING, 'jerror');
        }

        $row->load($cid);
        if(!$row->delete())
        {
          throw new RuntimeException($row->getError());
        }

        $this->_mainframe->triggerEvent('onContentAfterDelete', array(_JOOM_OPTION.'.category', $row));

        // Category successfully deleted
        $count++;
      }
    }

    $this->_mainframe->setUserState('joom.categories.delete.categories', $delete_categories);
    $this->_mainframe->setUserState('joom.categories.delete.images', $delete_images);

    $cat_count = count($delete_categories);
    $img_count = count($delete_images);

    if($cat_count || $img_count)
    {
      $msg  = '<br />'.JText::_('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY');
      if($img_count)
      {
        if($img_count == 1)
        {
          $msg .= '<br />'.JText::_('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_IMAGES_NUMBER_1');
        }
        else
        {
          $msg .= '<br />'.JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_IMAGES_NUMBER', $img_count);
        }
      }
      if($cat_count)
      {
        if($cat_count == 1)
        {
          $msg .= '<br />'.JText::_('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_CATEGORIES_NUMBER_1');
        }
        else
        {
          $msg .= '<br />'.JText::sprintf('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_CATEGORIES_NUMBER', $cat_count);
        }
      }
      $msg .= '<br /><br />'.JText::_('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_NOTE').'<p/>
      <form action="index.php?option='._JOOM_OPTION.'&amp;controller=categories&amp;task=deletecompletely" method="post" onsubmit="if(!this.security_check.checked){return false;}">
        <span><input type="checkbox" name="security_check" value="1" /> <button class="btn">'.JText::_('COM_JOOMGALLERY_CATMAN_MSG_DELETECOMPLETELY_BUTTON_LABEL').'</button></span>
      </form><p/>';
      $this->_mainframe->enqueueMessage($msg, 'notice');
    }

    // Reset the user state variable 'catid' for filtering in images manager
    $this->_mainframe->setUserState('joom.images.catid', 0);

    return $count;
  }

  /**
   * Returns given category IDs in a specific ordering
   *
   * @param   array   $categories The category IDs to order
   * @param   string  $ordering   The database table column to use for ordering
   * @param   string  $direction  The direction to use for ordering
   * @return  array   An array with the ordered category IDs
   * @since   2.0
   */
  public function getOrderedCategories($categories, $ordering = 'lft', $direction = 'ASC')
  {
    // Sanitise variables
    JArrayHelper::toInteger($categories);
    if(!in_array($ordering, $this->filter_fields))
    {
      $ordering = 'lft';
    }
    if(!in_array(strtoupper($direction), array('ASC', 'DESC', '')))
    {
      $direction = 'ASC';
    }

    $query = $this->_db->getQuery(true)
          ->select('cid')
          ->from(_JOOM_TABLE_CATEGORIES)
          ->where('cid IN ('.implode(',', $categories).')')
          ->order($ordering.' '.$direction);
    $this->_db->setQuery($query);

    return $this->_db->loadColumn();
  }

  /**
   * Publishes/unpublishes or approves/rejects one or more categories
   *
   * @param   array   $cid      An array of category IDs to work with
   * @param   int     $publish  1 for publishing and approving, 0 otherwise
   * @param   string  $task     'publish' for publishing/unpublishing, anything else otherwise
   * @return  int     The number of successfully edited categories, boolean false if an error occured
   * @since   1.5.5
   */
  public function publish($cid, $publish = 1, $task = 'publish')
  {
    JArrayHelper::toInteger($cid);
    $publish = intval($publish);
    $count = count($cid);

    $row = $this->getTable('joomgallerycategories');

    $column = 'approved';
    if($task == 'publish')
    {
      $column = 'published';
    }

    foreach($cid as $catid)
    {
      $row->load($catid);
      $row->$column = $publish;
      if(!$row->check())
      {
        $count--;
        continue;
      }

      if(!$row->store())
      {
        $count--;
        continue;
      }

      // If publishing or unpublishung wasn't successful, decrease the
      // counter of successfully published or unpublished categories
      if($row->$column != $publish)
      {
        $count--;
      }
    }

    return $count;
  }

  /**
   * Deletes folders of an existing category
   *
   * @param   string  $catpath  The catpath of the category
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _deleteFolders($catpath)
  {
    if(!$catpath)
    {
      return false;
    }

    $orig_path  = JPath::clean($this->_ambit->get('orig_path').$catpath);
    $img_path   = JPath::clean($this->_ambit->get('img_path').$catpath);
    $thumb_path = JPath::clean($this->_ambit->get('thumb_path').$catpath);

    // Delete the folder of the category for the original images
    if(!JFolder::delete($orig_path))
    {
      // If not successfull
      return false;
    }
    else
    {
      // Delete the folder of the category for the detail images
      if(!JFolder::delete($img_path))
      {
        // If not successful
        if(JFolder::create($orig_path))
        {
          JoomFile::copyIndexHtml($orig_path);
        }

        return false;
      }
      else
      {
        // Delete the folder of the category for the thumbnails
        if(!JFolder::delete($thumb_path))
        {
          // If not successful
          if(JFolder::create($orig_path))
          {
            JoomFile::copyIndexHtml($orig_path);
          }
          if(JFolder::create($img_path))
          {
            JoomFile::copyIndexHtml($img_path);
          }

          return false;
        }
      }
    }

    return true;
  }

  /**
   * Method for retreiving categories allowed for a certain action
   *
   * @param   string  $action       Optional action to check the categories against
   * @param   int     $filter       Optional category ID which will be filtered out together with its sub-categories
   * @param   string  $searchstring Optional string for searching specific categories (name of category will be used for searching)
   * @param   int     $limitstart   Optional limit start parameter for database query
   * @return  array   A result set with an array of categories and indicator whether there are more results left on success, Exception object otherwise
   * @since   2.1
   */
  public function getAllowedCategories($action = null, $filter = null, $searchstring = '', $limitstart = 0, $current = 0)
  {
    JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

    // Initialise variables
    $results = array('results' => array());

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

    $filtered = array();
    if($filter)
    {
      $filtered = JoomHelper::getAllSubCategories($filter, true, true, true, false);
    }

    try
    {
      // Create the search query
      $query = $this->_db->getQuery(true)
            ->select('cid, name, owner')
            ->from($this->_db->qn(_JOOM_TABLE_CATEGORIES))
            ->where('cid != 1')
            ->order('lft');
      if($searchstring)
      {
        $searchstring = $this->_db->q('%'.$searchstring.'%');
        $query->where('name LIKE '.$searchstring);
      }

      // Load all results
      $this->_db->setQuery($query);
      $result = $this->_db->loadObjectList();

      // Check the results starting from limit start
      $count = count($result);
      $j = 0;

      if(   !$limitstart
        &&  ($current == 0 || !$action || ($action == 'core.create' && $this->_user->authorise($action, _JOOM_OPTION)))
        )
      {
        $none = new stdclass();
        $none->cid  = 0;
        $none->name = JText::_('COM_JOOMGALLERY_COMMON_NO_CATEGORY');
        $none->path = '';
        $none->none = true;
        $results['results'][$j] = $none;
        $j++;
      }

      for($i = $limitstart; $i < $count; $i++)
      {
        if(in_array($result[$i]->cid, $filtered))
        {
          continue;
        }

        if(     $result[$i]->cid != $current
            &&  $action && !$this->_user->authorise($action, _JOOM_OPTION.'.category.'.$result[$i]->cid)
            &&  (     !$action2
                  ||  $result[$i]->owner != $this->_user->get('id')
                  ||  !$this->_user->authorise($action2, _JOOM_OPTION.'.category.'.$result[$i]->cid)
                )
          )
        {
          continue;
        }

        // Stop as soon as we have 10 results.
        // This check is done not earlier than at this point because
        // we want to know whether there is at least one more result.
        if($j == 10)
        {
          // If 'more' is set a 'More Results' link will be displayed in the view
          $results['more'] = $i;
          break;
        }

        $results['results'][$j] = $result[$i];
        $results['results'][$j]->path = JHtml::_('joomgallery.categorypath', $result[$i]->cid, false);
        $j++;
      }
    }
    catch(JDatabaseException $e)
    {
      return $e;
    }

    return $results;
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
   * @since   2.0
   */
  public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
  {
    $app = JFactory::getApplication();

    $old_state = $app->getUserState($key);
    $cur_state = (!is_null($old_state)) ? $old_state : $default;
    $new_state = JRequest::getVar($request, null, 'default', $type);

    if($cur_state != $new_state && !is_null($new_state) && !is_null($old_state) && $resetPage)
    {
      JRequest::setVar('limitstart', 0);
    }

    // Save the new value only if it was set in this request.
    if($new_state !== null)
    {
      $app->setUserState($key, $new_state);
    }
    else
    {
      $new_state = $cur_state;
    }

    return $new_state;
  }
}