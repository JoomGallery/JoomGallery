<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/control.php $
// $Id: control.php 4224 2013-04-22 15:46:14Z erftralle $
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
 * Control panel model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelControl extends JoomGalleryModel
{
  /**
   * Menu data array
   *
   * @var     array
   */
  protected $_data;

  /**
   * Returns the query for loading the menu entries
   *
   * @return  object  The query to be used to retrieve the menu entries from the database
   * @since   1.5.5
   */
  protected function _buildQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('*')
          ->from('#__menu')
          ->where('parent_id != 1')
          ->where("menutype = 'main'");

    $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=images%'";
    $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=categories%'";
    $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=comments%'";
    $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=migration%'";
    $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=help%'";

    $canDo = JoomHelper::getActions();
    if($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('joom.upload') || count(JoomHelper::getAuthorisedCategories('joom.upload')))
    {
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=upload%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=ajaxupload%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=batchupload%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=ftpupload%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=jupload%'";
    }
    if($canDo->get('core.admin'))
    {
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=config%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=cssedit%'";
      $where[] = "link LIKE 'index.php?option=com_joomgallery&controller=maintenance%'";
    }

    $query->where('('.implode(' OR ', $where).')')
          ->order('id');

    return $query;
  }

  /**
   * Retrieves the data of the backend menu entries for JoomGallery
   *
   * @return  array   An array of objects containing the data of the menu entries from the database
   * @since   1.5.5
   */
  public function getData()
  {
    // Lets load the data if it doesn't already exist
    if(empty($this->_data))
    {
      $query = $this->_buildQuery();
      $this->_data = $this->_getList($query);
    }

    return $this->_data;
  }

  /**
   * Retrieves the images data
   *
   * @param   string  $orderby  The database ordering clause
   * @param   boolean $approved Flag, if only approved images should queried
   * @param   int     $limit    Query limit
   * @param   string  $where    Additional database where clause
   * @return  array   Array of objects containing the images data from the database
   * @since   3.0
   */
  public function getImages($orderby = 'a.hits desc', $approved = true, $limit = 5, $where = null)
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('a.*')
          ->from(_JOOM_TABLE_IMAGES.' AS a');

    // Join over the categories
    $query->select('c.cid AS category, c.name AS category_name')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid');

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

    $query->where('a.published = 1');
    $query->where('a.approved = '.($approved ? '1' : '0'));

    if(!empty($where))
    {
      $query->where($where);
    }

    $query->order($orderby);

    return $this->_getList($query, 0, $limit);
  }

  /**
   * Retrieves the images data
   *
   * @param   string  $orderby  The database ordering clause
   * @param   boolean $approved Flag, if only approved comments should queried
   * @param   int     $limit    Query limit
   * @return  array   Array of objects containing the images data from the database
   * @since   3.0
   */
  public function getComments($orderby = 'c.cmtdate desc', $approved = true, $limit = 5)
  {
    // Create a new query object
    $query = $this->_db->getQuery(true);

    // Select the required fields from the table
    $query->select('c.*')
          ->from(_JOOM_TABLE_COMMENTS.' AS c');

    // Join over the images
    $query->select('i.id, i.imgtitle, i.imgthumbname, i.catid, i.owner')
          ->join('LEFT', _JOOM_TABLE_IMAGES.' AS i ON i.id = c.cmtpic');

    $query->where('c.published = 1');
    $query->where('c.approved = '.($approved ? '1' : '0'));

    $query->order($orderby);

    return $this->_getList($query, 0, $limit);
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @return  void
   * @since   3.0
   */
  protected function populateState()
  {
    $this->setState('message', $this->_mainframe->getUserState('joom.control.message'));
    $this->setState('extension_message', $this->_mainframe->getUserState('joom.control.extension_message'));
    $this->_mainframe->setUserState('joom.control.message', null);
    $this->_mainframe->setUserState('joom.control.extension_message', null);

    parent::populateState();
  }
}