<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/configs.php $
// $Id: configs.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Configs model
 *
 * @package JoomGallery
 * @since   2.0
 */
class JoomGalleryModelConfigs extends JoomGalleryModel
{
  /**
   * Configs data array
   *
   * @access  protected
   * @var     array
   */
  var $_configs = null;

  /**
   * Configs number
   *
   * @access  protected
   * @var     int
   */
  var $_total = null;

  /**
   * Configs data array (holding all config rows, but
   * only with the most important information)
   *
   * @access  protected
   * @var     array
   */
  var $_allconfigs = null;

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
        'id', 'c.id',
        'title', 'g.title',
        'lft', 'g.lft',
        'ordering', 'c.ordering'
        );
  }

  /**
   * Retrieves the data of the config rows
   *
   * @access  public
   * @return  array   Array of objects containing the config rows from the database
   * @since   2.0
   */
  function getConfigs()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_configs))
    {
      // Get the data of the categories which will actually be displayed
      $query = $this->_buildQuery();
      $this->_db->setQuery($query, $this->getState('list.start'), $this->getState('list.limit'));
      $configs = $this->_db->loadObjectList('group_id');

      $this->_configs = array();
      foreach($configs as $key => $config)
      {
        // If there is at least one config row with a higher ordering value which belongs
        // to a parent user group the current config row can/will never be applied to a user
        if($this->getActiveParentConfigs($config->group_id, $config->ordering))
        {
          $config->usergroups = false;

          continue;
        }

        // With the following code we want to get all user groups
        // for which the current config row applies.
        $subgroups = $this->getTree($config->group_id);
        $config->usergroups = array();
        $removing = false;
        foreach($subgroups as $key => $subgroup)
        {
          // If the removing flag is set we won't store user groups
          // which are children of the group stored in $removing
          if($removing)
          {
            if($subgroup->rgt > $removing)
            {
              // If rgt of current group is greater than the
              // stored one the complete sub-tree was parsed
              $removing = false;
            }
            else
            {
              continue;
            }
          }

          // If there is a config row for the current user group with a higher ordering value
          // that one will be used for the current user group and all of its sub-groups
          if(isset($configs[$subgroup->id]) && $configs[$subgroup->id]->ordering > $config->ordering)
          {
            $removing = $subgroup->rgt;

            continue;
          }

          // If we reach this point the current config row will
          // be applied to the current user group, so store it
          $config->usergroups[] = $subgroup->title;
        }

        // Prepend the user group the config row belongs to
        array_unshift($config->usergroups, $config->title);
      }

      $this->_configs = $configs;
    }

    return $this->_configs;
  }

  /**
   * Retrieves the most important data of each of the config rows
   *
   * @access  public
   * @return  array   Array of objects containing the config rows from the database
   * @since   2.1
   */
  function getAllConfigs()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_allconfigs))
    {
      // Create a new query object
      $query = $this->_db->getQuery(true);

      // Select the required fields from the table
      $query->select('c.id, c.group_id, c.ordering')
            ->from(_JOOM_TABLE_CONFIG.' AS c');

      // Join over the user groups
      $query->select('g.title')
            ->leftJoin('#__usergroups AS g ON g.id = c.group_id');

      $this->_db->setQuery($query);

      $this->_allconfigs = $this->_db->loadObjectList();
    }

    return $this->_allconfigs;
  }

  /**
   * Method to get all available usergroups for which there isn't a config row yet
   *
   * @return  array Array of all available usergroups without an existing config row
   * @since   2.0
   */
  public function getUsergroups()
  {
    $configs = $this->getAllConfigs();

    $group_ids = array();
    foreach($configs as $config)
    {
      $group_ids[] = $config->group_id;
    }

		$query = $this->_db->getQuery(true)
          ->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level')
          ->from('#__usergroups AS a')
          ->leftJoin('#__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt')
          ->where('a.id NOT IN ('.implode(',', $group_ids).')')
          ->group('a.id')
          ->order('a.lft ASC');
		$this->_db->setQuery($query);

    try
    {
      $options = $this->_db->loadObjectList();
    }
    catch(DatabaseException $e)
    {
      $this->setError($this->_db->getErrorMsg());

      return array();
    }

    foreach($options as $key => $option)
    {
			$option->text = str_repeat('- ', $option->level).$option->text;
		}

    return $options;
  }

  /**
   * Returns the number of config rows for parent groups of a specific user group
   * which have a higher ordering value than that user group
   *
   * @param   int   The ID of the user group
   * @param   int   The ordering value of the user group
   * @return  int   The number of config rows which match the conditions above
   * @since   2.0
   */
  protected function getActiveParentConfigs($group, $ordering)
  {
    $query = $this->_db->getQuery(true)
          ->select('COUNT(c.id)')
          ->from('#__usergroups AS a')
          ->leftJoin('#__usergroups AS b ON b.lft BETWEEN a.lft AND a.rgt')
          ->leftJoin(_JOOM_TABLE_CONFIG.' AS c ON a.id = c.group_id')
          ->where('b.id = '.(int) $group)
          ->where('c.ordering > '.(int) $ordering)
          ->order('a.lft');
    $this->_db->setQuery($query);

    try
    {
      $subgroups = $this->_db->loadResult();
    }
    catch(DatabaseException $e)
    {
      $this->setError($e->getMessage());

      return array();
    }

    return $subgroups;
  }

  /**
   * Returns the sub-tree of groups for a specific user group
   *
   * @param   int   The ID of the user group
   * @return  array The sub-tree of the specified group
   * @since   2.0
   */
  protected function getTree($group)
  {
    $query = $this->_db->getQuery(true)
          ->select('a.id, a.rgt, a.title, c.id AS config_id, c.ordering')
          ->from('#__usergroups AS a')
          ->leftJoin('#__usergroups AS b ON a.lft > b.lft AND a.lft < b.rgt')
          ->leftJoin(_JOOM_TABLE_CONFIG.' AS c ON a.id = c.group_id')
          ->where('b.id = '.(int) $group)
          ->order('a.lft');
    $this->_db->setQuery($query);

    try
    {
      $subgroups = $this->_db->loadObjectList();
    }
    catch(DatabaseException $e)
    {
      $this->setError($e->getMessage());

      return array();
    }

    return $subgroups;
  }

  /**
   * Method to get the pagination object for the list.
   * This method uses 'getTotel', 'getStart' and the current
   * list limit of this view.
   *
   * @return  object  A pagination object
   * @since   2.0
   */
  function getPagination()
  {
    jimport('joomla.html.pagination');
    return new JPagination($this->getTotal(), $this->getStart(), $this->getState('list.limit'));
  }

  /**
   * Method to get the total number of categories
   *
   * @access  public
   * @return  int     The total number of categories
   * @since   2.0
   */
  function getTotal()
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
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  An optional ordering field.
   * @param   string  An optional direction (asc|desc).
   * @return  void
   * @since   2.0
   */
  protected function populateState($ordering = 'c.ordering', $direction = 'ASC')
  {
    $search = $this->getUserStateFromRequest('joom.configs.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    $type = $this->getUserStateFromRequest('joom.configs.filter.type', 'filter_type', '');
    $this->setState('filter.type', $type);

    $value = $this->getUserStateFromRequest('global.list.limit', 'limit', $this->_mainframe->getCfg('list_limit'));
    $limit = $value;
    $this->setState('list.limit', $limit);

    $value = $this->getUserStateFromRequest('joom.configs.limitstart', 'limitstart', 0);
    $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
    $this->setState('list.start', $limitstart);

    // Check if the ordering field is in the white list, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.configs.ordercol', 'filter_order', $ordering);
    if(!in_array($value, $this->filter_fields))
    {
      $value = $ordering;
      $this->_mainframe->setUserState('joom.configs.ordercol', $value);
    }

    $this->setState('list.ordering', $value);

    // Check if the ordering direction is valid, otherwise use the incoming value
    $value = $this->getUserStateFromRequest('joom.configs.orderdirn', 'filter_order_Dir', $direction);
    if(!in_array(strtoupper($value), array('ASC', 'DESC', '')))
    {
      $value = $direction;
      $this->_mainframe->setUserState('joom.configs.orderdirn', $value);
    }

    $this->setState('list.direction', $value);
  }

  /**
   * Returns the query for listing the config rows
   *
   * @return  object    The query to be used to retrieve the config rows data from the database
   * @since   2.0
   */
  protected function _buildQuery()
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('c.id, c.group_id, c.ordering')
          ->from(_JOOM_TABLE_CONFIG.' AS c');

    // Join over the user groups
    $query->select('g.title')
          ->leftJoin('#__usergroups AS g ON g.id = c.group_id');

    // Add the level in the tree.
    $query->select('COUNT(DISTINCT g2.id) AS level')
          ->leftJoin('#__usergroups AS g2 ON g.lft > g2.lft AND g.rgt < g2.rgt')
          ->group('g.id');

    // Filter by search
    $search = $this->getState('filter.search');
    if(!empty($search))
    {
      if(stripos($search, 'id:') === 0)
      {
        $query->where('c.id = '.(int) substr($search, 3));
      }
      else
      {
        $search = $this->_db->Quote('%'.$this->_db->escape($search, true).'%');
        $query->where('LOWER(g.title) LIKE '.$search);
      }
    }

    // Add the order clause
    $query->order($this->_db->escape($this->getState('list.ordering', 'c.ordering')).' '.$this->_db->escape($this->getState('list.direction', 'ASC')));

    return $query;
  }

  /**
   * Propagates changes in settings to all config rows
   *
   * @param   array   Array of changed config settings
   * @param   int     ID of the initially changed config row
   * @param   boolean Determines whether all changes shall be propagated (true) or only the global ones (false)
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function propagateChanges($data, $id = 1, $all = false)
  {
    // Sanitise variables
    $data = (array) $data;
    if(!$id)
    {
      $id = 1;
    }

    $global_settings = $this->_getGlobalSettings();

    // Unset fields which must not be changed
    foreach($data as $key => $value)
    {
      if(   strpos($key, 'jg_') !== 0
        || (!$all && !in_array($key, $global_settings)))
      {
        unset($data[$key]);
      }
    }

    if(!count($data))
    {
      // Nothing to do
      return true;
    }

    // Get the IDs of all config rows except the initially changed one
    $query = $this->_db->getQuery(true)
          ->select('id')
          ->from(_JOOM_TABLE_CONFIG)
          ->where('id != '.$id);
    $this->_db->setQuery($query);

    try
    {
      $ids = $this->_db->loadColumn();
    }
    catch(DatabaseException $e)
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    if(!count($ids))
    {
      // Nothing to do
      return true;
    }

    $config = JoomConfig::getInstance('admin');

    foreach($ids as $config_id)
    {
      if(!$config->save($data, $config_id))
      {
        $this->setError($config->getError());

        return false;
      }
    }

    return true;
  }

  /**
   * Returns the names of settings which can't be different in different config rows
   *
   * @return  array An array of global settings
   * @since   2.0
   */
  protected function _getGlobalSettings()
  {
    return  array('jg_paththumbs', 'jg_pathimages', 'jg_pathoriginalimages', 'jg_pathtemp',
                  'jg_filenamewithjs', 'jg_filenamereplace',
                  'jg_thumbcreation', 'jg_fastgd2thumbcreation', 'jg_impath', 'jg_resizetomaxwidth', 'jg_maxwidth', 'jg_picturequality', 'jg_useforresizedirection', 'jg_cropposition', 'jg_thumbwidth', 'jg_thumbheight', 'jg_thumbquality',
                  'jg_download_unreg',
                  'jg_anoncomment', 'jg_namedanoncomment', 'jg_anonapprovecom',
                  'jg_report_unreg',
                  'jg_showuserpanel_unreg',
                  'jg_showcommentsunreg',
                  'jg_nameshields_unreg', 'jg_show_nameshields_unreg',
                  'jg_usefavouritesforpubliczip'
                  );
  }

  /**
   * Gets the value of a user state variable and sets it in the session
   * This is the same as the method in JApplication except that this also can optionally
   * force you back to the first page when a filter has changed
   *
   * @param   string  The key of the user state variable.
   * @param   string  The name of the variable passed in a request.
   * @param   string  The default value for the variable if not found. Optional.
   * @param   string  Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
   * @param   boolean If true, the limitstart in request is set to zero
   * @return  The requested user state.
   * @since   2.0
   */
  public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true)
  {
    $app = JFactory::getApplication();
    $old_state = $app->getUserState($key);
    $cur_state = (!is_null($old_state)) ? $old_state : $default;
    $new_state = JRequest::getVar($request, null, 'default', $type);

    if (($cur_state != $new_state) && ($resetPage)){
      JRequest::setVar('limitstart', 0);
    }

    // Save the new value only if it was set in this request.
    if ($new_state !== null) {
      $app->setUserState($key, $new_state);
    }
    else {
      $new_state = $cur_state;
    }

    return $new_state;
  }
}