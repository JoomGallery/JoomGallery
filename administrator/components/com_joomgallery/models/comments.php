<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/comments.php $
// $Id: comments.php 4175 2013-04-05 11:13:27Z chraneco $
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
 * Comments model
 *
 * Saves, removes, publishes, unpublishes, approves,
 * rejects and loads comments.
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelComments extends JoomGalleryModel
{
  /**
   * Comments data array
   *
   * @var     array
   */
  protected $_comments;

  /**
   * Comments number
   *
   * @var     int
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
        'cmtid', 'c.cmtid',
        'user',
        'cmttext', 'c.cmttext',
        'published', 'c.published',
        'approved', 'c.approved',
        'cmtip', 'c.cmtip',
        'i.imgtitle',
        'cmtdate', 'c.cmtdate',
        'state'
        );
  }

  /**
   * Retrieves the comments data
   *
   * @return  array   Array of objects containing the comments data from the database
   * @since   1.5.5
   */
  public function getComments()
  {
    // Lets load the data if it doesn't already exist
    if(empty($this->_comments))
    {
      $query = $this->_buildQuery();
      $this->_comments = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));

      foreach($this->_comments as $key => $comment)
      {
        if($comment->userid > 0)
        {
          $this->_comments[$key]->cmtname = JHTML::_('joomgallery.displayname', $comment->userid);
        }

        $this->_comments[$key]->cmttext   = JoomHelper::processText($comment->cmttext);
      }
    }

    return $this->_comments;
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
   * Method to get the total number of comments
   *
   * @return  int     The total number of comments in the gallery
   * @since   1.5.5
   */
  public function getTotal()
  {
    // Lets load the comments number if it doesn't already exist
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
    return $this->loadForm(_JOOM_OPTION . '.filter_comments', 'filter_comments', array('control' => '', 'load_data' => $loadData));
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
    $data = JFactory::getApplication()->getUserState('joom.comments', new stdClass);

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
  protected function populateState($ordering = 'c.cmtdate', $direction = 'desc')
  {
    // Receive & set filters
    $filters = $this->getUserStateFromRequest('joom.comments.filter', 'filter', array('search' => null, 'state' => ''), 'array');

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
    $list = $this->getUserStateFromRequest('joom.comments.list', 'list',
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

      $value      = $this->getUserStateFromRequest('joom.comments.limitstart', 'limitstart', 0);
      $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
      $this->setState('list.start', $limitstart);
    }
  }

  /**
   * Returns the query for loading all comments
   *
   * @return  string    The query to be used to retrieve the rows from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('c.*')
          ->from(_JOOM_TABLE_COMMENTS.' AS c');

    // Join over the images
    $query->select('i.id, i.imgtitle, i.imgthumbname, i.catid, i.owner')
          ->join('LEFT', _JOOM_TABLE_IMAGES.' AS i ON i.id = c.cmtpic');

    // Join over the users
    $query->select('u.username AS user')
          ->join('LEFT', '#__users AS u ON u.id = c.userid');

    /*// Filter by owner
    $owner = $this->getState('filter.owner');
    if($owner !== '')
    {
      $query->where('a.owner = '.(int) $owner);
    }*/

    // Filter by state
    $published = $this->getState('filter.state');
    switch($published)
    {
      case 1:
        // Published
        $query->where('c.published = 1');
        break;
      case 2:
        // Not published
        $query->where('c.published = 0');
        break;
      case 3:
        // Approved
        $query->where('c.approved = 1');
        break;
      case 4:
        // Not approved / rejected
        $query->where('c.approved = 0');
        break;
      default:
        // No filter by state
        break;
    }

    // Filter by search
    $search = $this->getState('filter.search');
    if(!empty($search))
    {
      if(stripos($search, 'id:') === 0)
      {
        $query->where('c.cmtid = '.(int) substr($search, 3));
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
          $query->where('LOWER(c.cmttext) LIKE '.$search);
        }
      }
    }

    // Add the order clause
    $query->order($this->_db->escape($this->getState('list.ordering', 'c.cmtdate')).' '.$this->_db->escape($this->getState('list.direction', 'DESC')));

    if($this->getState('list.ordering') == 'user')
    {
      $query->order('c.cmtname '.$this->getState('list.direction', 'ASC'));
    }

    return $query;
  }

  /**
   * Method to delete one or more comments
   *
   * @return  int    The number of successfully deleted comments, boolean false if an error occured
   * @since   1.5.5
   */
  public function delete()
  {
    $cids = JRequest::getVar('cid', array(0), 'post', 'array');

    $row = $this->getTable('joomgallerycomments');

    if(count($cids))
    {
      foreach($cids as $cid)
      {
        if (!$row->delete($cid))
        {
          $this->setError($row->getErrorMsg());
          return false;
        }
      }

      return count($cids);
    }

    return false;
  }

  /**
   * Method to publish, unpublish, approve or reject one or more comments
   *
   * @param   array   $cid      Array of comment IDs to perform the task on
   * @param   int     $publish  1 for publishing or approving, 0 for unpublishing or rejecting
   * @param   string  $task     The task to perform ('publish' or 'approve')
   * @return  int     The number of successfully processed comments, false otherwise
   * @since   1.5.5
   */
  public function publish($cid, $publish = 1, $task = 'publish')
  {
    JArrayHelper::toInteger($cid);
    $cids = implode(',', $cid);

    $column = 'approved';
    if($task == 'publish')
    {
      $column = 'published';
    }

    $query = $this->_db->getQuery(true)
          ->update(_JOOM_TABLE_COMMENTS)
          ->set($column.' = '.(int) $publish)
          ->where('cmtid IN ('.$cids.' )');
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      return false;
    }

    // Message about new comment to image owner
    if($column == 'approved' && $publish && $this->_config->get('jg_msg_comment_toowner'))
    {
      require_once(JPATH_COMPONENT_SITE.'/helpers/messenger.php');
      $messenger  = new JoomMessenger();

      foreach($cid as $id)
      {
        // Load comment data
        $comment  = $this->getTable('joomgallerycomments');
        $comment->load($id);

        if(!$name = $comment->cmtname)
        {
          $user = JFactory::getUser($comment->userid);
          $name = $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username');
        }

        // Load image data
        $image    = $this->getTable('joomgalleryimages');
        $image->load($comment->cmtpic);

        if($image->owner &&  $image->owner != $comment->userid)
        {
          $mode       = $messenger->getModeData('comment');
          $message    = array(
                              'from'      => $this->_user->get('id'),
                              'subject'   => JText::_('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_TO_OWNER_SUBJECT'),
                              'body'      => JText::sprintf('COM_JOOMGALLERY_MESSAGE_NEW_COMMENT_TO_OWNER_BODY', $name, $image->imgtitle, $image->id),
                              'type'      => $mode['type']
                            );

          $message['recipient'] = $image->owner;

          $messenger->send($message);
        }
      }
    }

    return count($cid);
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