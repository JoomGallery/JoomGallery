<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/images.php $
// $Id: images.php 2015-03-23 $
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
 * Images model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelImages extends JoomGalleryModel
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
        'id', 'a.id',
        'imgtitle', 'a.imgtitle',
        'alias', 'a.alias',
        'catid', 'a.catid',
        'category', 'category_name',
        'published', 'a.published',
        'approved', 'a.approved',
        'featured', 'a.featured',
        'access', 'a.access', 'access_level',
        'owner', 'a.owner',
        'imgauthor', 'a.imgauthor',
        'imgdate', 'a.imgdate',
        'hits', 'a.hits',
        'downloads', 'a.downloads',
        'ordering', 'a.ordering',
        'state',
        'type'
        );
  }

  /**
   * Retrieves the images data
   *
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  public function getImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_images))
    {
      $query = $this->_buildQuery();
      $this->_images = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
    }

    return $this->_images;
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
   * Method to get the total number of images
   *
   * @return  int     The total number of images
   * @since   1.5.5
   */
  public function getTotal()
  {
    // Let's load the total number of images if it doesn't already exist
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
   * @return  int The starting number of items available in the data set.
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
    return $this->loadForm(_JOOM_OPTION . '.filter_images', 'filter_images', array('control' => '', 'load_data' => $loadData));
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
    $data = JFactory::getApplication()->getUserState('joom.images', new stdClass);

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
  protected function populateState($ordering = 'a.ordering', $direction = 'asc')
  {
    // Receive & set filters
    $filters = $this->getUserStateFromRequest('joom.images.filter', 'filter',
      array('search' => null, 'access' => '', 'state' => '', 'type' => '', 'category' => '', 'owner' => ''), 'array');

    if($filters)
    {
      foreach($filters as $name => $value)
      {
        // Special case for category filter to ensure that search tools will be hidden
        // , if 'None' has been selected in AJAX category selection box
        if($this->_config->get('jg_ajaxcategoryselection') && $name == 'category' && $value == 0)
        {
          $value = '';
          JFactory::getApplication()->setUserState('joom.images.filter.' . $name, $value);
        }

        $this->setState('filter.' . $name, $value);

        if($value)
        {
          $this->setState('filter.inuse', 1);
        }
      }
    }

    $limit = 0;

    // Receive & set list options
    $list = $this->getUserStateFromRequest('joom.images.list', 'list',
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

      $value      = $this->getUserStateFromRequest('joom.images.limitstart', 'limitstart', 0);
      $limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
      $this->setState('list.start', $limitstart);

    }
  }

  /**
   * Method to reorder images in categories
   *
   * @param    object  $table  Image table object
   * @return   array   $cats   Array of categories
   * @return   void
   * @since    3.1
   */
  protected function reorder($table, $cats)
  {
    if(!empty($cats))
    {
      $cats = array_unique($cats);

      // Execute reorder for each category
      foreach($cats as $cat)
      {
        $table->reorder('catid = '.(int) $cat);
      }
    }
  }

  /**
   * Method to delete one or more images
   *
   * @param   array $ids  IDs of images to delete
   * @return  int   Number of successfully deleted images, boolean false if an error occurred
   * @throws  RuntimeException
   * @since   1.5.5
   */
  public function delete($ids)
  {
    jimport('joomla.filesystem.file');

    $row = $this->getTable('joomgalleryimages');

    if(!count($ids))
    {
      throw new RuntimeException(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
    }

    $count         = 0;
    $catsToReorder = array();

    // Loop through selected images
    foreach($ids as $cid)
    {
      if(!$this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$cid))
      {
        JLog::add(JText::plural('COM_JOOMGALLERY_IMGMAN_ERROR_DELETE_NOT_PERMITTED', 1), JLog::ERROR, 'jerror');

        continue;
      }

      $row->load($cid);

      // Database query to check if there are other images which this
      // thumbnail is assigned to and how many of them exist
      $query = $this->_db->getQuery(true)
             ->select('COUNT(id)')
             ->from(_JOOM_TABLE_IMAGES)
             ->where("imgthumbname  = '".$row->imgthumbname."'")
             ->where('id != '.$row->id)
             ->where('catid = '.$row->catid);

      $this->_db->setQuery($query);
      $thumb_count = $this->_db->loadResult();

      // Database query to check if there are other images which this
      // detail image is assigned to and how many of them exist
      $query->clear('where')
            ->where("imgfilename = '".$row->imgfilename."'")
            ->where('id != '.$row->id)
            ->where('catid = '.$row->catid);

      $this->_db->setQuery($query);
      $img_count = $this->_db->loadResult();

      // Delete the thumbnail if there are no other images
      // in the same category assigned to it
      if(!$thumb_count)
      {
        $thumb = $this->_ambit->getImg('thumb_path', $row);
        if(!JFile::delete($thumb))
        {
          // If thumbnail is not deletable raise an error message
          JLog::add(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_THUMB', $thumb), JLog::WARNING, 'jerror');
        }
      }

      // Delete the detail image if there are no other detail and
      // original images from the same category assigned to it
      if(!$img_count)
      {
        $img = $this->_ambit->getImg('img_path', $row);
        if(!JFile::delete($img))
        {
          // If detail image is not deletable raise an error message
          JLog::add(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_IMG', $img), JLog::WARNING, 'jerror');
        }

        // Original exists?
        $orig = $this->_ambit->getImg('orig_path', $row);
        if(JFile::exists($orig))
        {
          // Delete it
          if(!JFile::delete($orig))
          {
            // If original is not deletable raise an error message and abort
            JLog::add(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_DELETE_ORIG', $orig), JLog::WARNING, 'jerror');
          }
        }
      }

      // Delete the corresponding database entries of the comments
      $query = $this->_db->getQuery(true)
            ->delete()
            ->from(_JOOM_TABLE_COMMENTS)
            ->where('cmtpic = '.$cid);

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        JLog::add(JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_COMMENTS', $cid), JLog::WARNING, 'jerror');
      }

      // Delete the corresponding database entries of the name tags
      $query = $this->_db->getQuery(true)
            ->delete()
            ->from(_JOOM_TABLE_NAMESHIELDS)
            ->where('npicid = '.$cid);

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        JLog::add(JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_NAMETAGS', $cid), JLog::WARNING, 'jerror');
      }

      // Delete the corresponding database entries of the votes
      $query = $this->_db->getQuery(true)
            ->delete()
            ->from(_JOOM_TABLE_VOTES)
            ->where('picid = '.$cid);

      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        JLog::add(JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_VOTES', $cid), JLog::WARNING, 'jerror');
      }

      // Delete the database entry of the image
      if(!$row->delete())
      {
        $this->reorder($row, $catsToReorder);

        throw new RuntimeException(JText::sprintf('COM_JOOMGALLERY_MAIMAN_MSG_NOT_DELETE_IMAGE_DATA', $cid));
      }

      $this->_mainframe->triggerEvent('onContentAfterDelete', array(_JOOM_OPTION.'.image', $row));

      // Image successfully deleted
      $count++;

      // Remember the categories for reordering later
      $catsToReorder[] = $row->catid;
    }

    // Execute reorder for each category
    $this->reorder($row, $catsToReorder);

    return $count;
  }

  /**
   * Publishes/unpublishes or approves/rejects one or more images
   *
   * @param   array   $cid      An array of image IDs to work with
   * @param   int     $publish  1 for publishing and approving, 0 otherwise
   * @param   string  $task     'publish' for publishing/unpublishing, anything else otherwise
   * @return  int     The number of successfully edited images, boolean false if an error occured
   * @since   1.5.5
   */
  public function publish($cid, $publish = 1, $task = 'publish')
  {
    JArrayHelper::toInteger($cid);
    $publish = intval($publish);
    $count = count($cid);

    $row = $this->getTable('joomgalleryimages');

    $column = 'approved';
    if($task == 'publish')
    {
      $column = 'published';
    }
    if($task == 'feature')
    {
      $column = 'featured';
    }

    foreach($cid as $id)
    {
      $row->load($id);
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
      // counter of successfully published or unpublished images
      if($row->$column != $publish)
      {
        $count--;
      }
    }

    return $count;
  }

  /**
   * Sends message about reason of rejection to image owner
   *
   * @param   int     $id       The image ID
   * @param   string  $message  The message to send
   * @return  boolean True on success, false otherwise
   * @since   3.1
   */
  public function sendRejectionMessage($id, $message)
  {
    if(!$image = $this->_ambit->getImgObject($id))
    {
      return false;
    }

    if(!$image->owner)
    {
      return false;
    }

    require_once JPATH_COMPONENT_SITE.'/helpers/messenger.php';
    $messenger  = new JoomMessenger();

    $message    = array(
                        'from'      => $this->_user->get('id'),
                        'recipient' => $image->owner,
                        'subject'   => JText::sprintf('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_SUBJECT', $image->imgtitle),
                        'body'      => $message,
                        'mode'      => 'rejectimg'
                      );

    return $messenger->send($message);
  }

  /**
   * Recreates thumbnails of the selected images.
   * If original image is existent, detail image will be recreated, too.
   *
   * @return  array   An array of result information (thumbnail number, detail image number, array with information which image types have been recreated)
   * @since   1.5.5
   */
  public function recreate()
  {
    jimport('joomla.filesystem.file');

    $cids         = $this->_mainframe->getUserStateFromRequest('joom.recreate.cids', 'cid', array(), 'array');
    $type         = $this->_mainframe->getUserStateFromRequest('joom.recreate.type', 'type', '', 'cmd');
    $thumb_count  = $this->_mainframe->getUserState('joom.recreate.thumbcount');
    $img_count    = $this->_mainframe->getUserState('joom.recreate.imgcount');
    $recreated    = $this->_mainframe->getUserState('joom.recreate.recreated');

    $row  = $this->getTable('joomgalleryimages');

    // Before first loop check for selected images
    if(is_null($thumb_count) && !count($cids))
    {
      $this->setError(JText::_('COM_JOOMGALLERY_COMMON_MSG_NO_IMAGES_SELECTED'));
      return array(false);
    }

    if(is_null($recreated))
    {
      $recreated = array();
    }

    require_once JPATH_COMPONENT.'/helpers/refresher.php';

    $refresher = new JoomRefresher(array('controller' => 'images', 'task' => 'recreate', 'remaining' => count($cids), 'start' => JRequest::getBool('cid')));

    $debugoutput = '';

    // Loop through selected images
    foreach($cids as $key => $cid)
    {
      $row->load($cid);

      $orig   = $this->_ambit->getImg('orig_path', $row);
      $img    = $this->_ambit->getImg('img_path', $row);
      $thumb  = $this->_ambit->getImg('thumb_path', $row);

      // Check if there is an original image
      if(JFile::exists($orig))
      {
        $orig_existent = true;
      }
      else
      {
        // If not, use detail image to create thumbnail
        $orig_existent = false;
        if(JFile::exists($img))
        {
          $orig = $img;
        }
        else
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_IMAGE_NOT_EXISTENT', $img));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }
      }

      // Recreate thumbnail
      if(!$type || $type == 'thumb')
      {
        // TODO: Move image into a trash instead of deleting immediately for possible rollback
        if(JFile::exists($thumb))
        {
          JFile::delete($thumb);
        }
        $return = JoomFile::resizeImage($debugoutput,
                                        $orig,
                                        $thumb,
                                        $this->_config->get('jg_useforresizedirection'),
                                        $this->_config->get('jg_thumbwidth'),
                                        $this->_config->get('jg_thumbheight'),
                                        $this->_config->get('jg_thumbcreation'),
                                        $this->_config->get('jg_thumbquality'),
                                        false,
                                        $this->_config->get('jg_cropposition')
                                        );
        if(!$return)
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_CREATE_THUMB', $thumb));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }

        //$this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_SUCCESSFULLY_CREATED_THUMB', $row->id, $row->imgtitle));
        $recreated[$cid][] = 'thumb';
        $thumb_count++;
      }

      // Recreate detail image if original image is existent
      if($orig_existent && (!$type || $type == 'img'))
      {
        // TODO: Move image into a trash instead of deleting immediately for possible rollback
        if(JFile::exists($img))
        {
          JFile::delete($img);
        }
        $return = JoomFile::resizeImage($debugoutput,
                                        $orig,
                                        $img,
                                        false,
                                        $this->_config->get('jg_maxwidth'),
                                        false,
                                        $this->_config->get('jg_thumbcreation'),
                                        $this->_config->get('jg_picturequality'),
                                        true,
                                        0
                                        );
        if(!$return)
        {
          JError::raiseWarning(100, JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_COULD_NOT_CREATE_IMG', $img));
          $this->_mainframe->setUserState('joom.recreate.cids', array());
          $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
          $this->_mainframe->setUserState('joom.recreate.imgcount', null);
          $this->_mainframe->setUserState('joom.recreate.recreated', null);
          return false;
        }

        //$this->_mainframe->enqueueMessage(JText::sprintf('COM_JOOMGALLERY_IMGMAN_MSG_SUCCESSFULLY_CREATED_IMG', $row->id, $row->imgtitle));
        $recreated[$cid][] = 'img';
        $img_count++;
      }

      unset($cids[$key]);

      // Check remaining time
      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.recreate.cids', $cids);
        $this->_mainframe->setUserState('joom.recreate.thumbcount', $thumb_count);
        $this->_mainframe->setUserState('joom.recreate.imgcount', $img_count);
        $this->_mainframe->setUserState('joom.recreate.recreated', $recreated);
        $refresher->refresh(count($cids));
      }
    }

    $this->_mainframe->setUserState('joom.recreate.cids', array());
    $this->_mainframe->setUserState('joom.recreate.type', null);
    $this->_mainframe->setUserState('joom.recreate.thumbcount', null);
    $this->_mainframe->setUserState('joom.recreate.imgcount', null);
    $this->_mainframe->setUserState('joom.recreate.recreated', null);

    return array($thumb_count, $img_count, $recreated);
  }

  /**
   * Returns the query for listing the images
   *
   * @return  string    The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('a.*')
          ->from(_JOOM_TABLE_IMAGES.' AS a');

    // Join over the categories
    $query->select('c.cid AS category, c.name AS category_name')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid');

    // Join over the access levels
    $query->select('v.title AS access_level')
          ->leftJoin('#__viewlevels AS v ON v.id = a.access');

    // Join over the users
    $query->leftJoin('#__users AS u ON u.id = a.owner');

    // Join over the categories again in order to check access levels
    if(!$this->_user->authorise('core.admin'))
    {
      $query->leftJoin(_JOOM_TABLE_CATEGORIES.' AS p ON c.lft BETWEEN p.lft AND p.rgt')
            ->select('c.level')
            ->where('p.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->group('a.id')
            ->having('COUNT(p.cid) > c.level')

      // Access level check for the image and the category the image is in
            ->where('a.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
    }

    // Filter by access level
    if($access = $this->getState('filter.access'))
    {
      $query->where('a.access = '.(int) $access);
    }

    // Filter by owner
    if($owner = $this->getState('filter.owner'))
    {
      $query->where('a.owner = '.(int) $owner);
    }

    // Filter by category
    if($category = $this->getState('filter.category'))
    {
      $query->where('a.catid = '.(int) $category);
    }

    // Filter by state
    $published = $this->getState('filter.state');
    switch($published)
    {
      case 1:
        // Published
        $query->where('a.published = 1');
        break;
      case 2:
        // Not published
        $query->where('a.published = 0');
        break;
      case 3:
        // Approved
        $query->where('a.approved = 1');
        break;
      case 4:
        // Not approved
        $query->where('a.approved = 0');
        break;
      case 5:
        // Rejected
        $query->where('a.approved = -1');
        break;
      case 6:
        // Featured
        $query->where('a.featured = 1');
        break;
      case 7:
        // Not featured
        $query->where('a.featured = 0');
        break;
      default:
        // No filter by state
        break;
    }

    // Filter by type
    $type = $this->getState('filter.type');
    switch($type)
    {
      case 1:
        // User images
        $query->where('a.owner != 0');
        break;
      case 2:
        // Administrator images
        $query->where('a.owner = 0');
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
        $query->where('a.id = '.(int) substr($search, 3));
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
          $query->where('(a.imgtitle LIKE '.$search.' OR a.alias LIKE '.$search.' OR LOWER(a.imgtext) LIKE '.$search.')');
        }
      }
    }

    // Add the order clause
    $orderCol   = $this->state->get('list.ordering');
    $orderDirn  = $this->state->get('list.direction');
    if($orderCol == 'a.ordering' || $orderCol == 'category_name')
    {
      $orderCol = 'category_name '.$orderDirn.', a.ordering';
    }
    $query->order($this->_db->escape($orderCol.' '.$orderDirn));

    return $query;
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

    // Special case for owner filter since Joomla! 3.5 when using modal user selection
    if(    !is_null($new_state) && isset($new_state['owner'])
        && ($new_state['owner'] === 0 || $new_state['owner'] === '0')
      )
    {
      $new_state['owner'] = '';
    }

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
