<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/toplist.php $
// $Id: toplist.php 4251 2013-05-05 17:27:13Z chraneco $
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
 * JoomGallery toplist model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelToplist extends JoomGalleryModel
{
  /**
   * Images data array with the last commented images
   *
   * @var   object array
   */
  protected $_lastCommented = null;

  /**
   * Images data array with the last added images
   *
   * @var   object array
   */
  protected $_lastAdded = null;

  /**
   * Images data array with the top rated images
   *
   * @var   object array
   */
  protected $_topRated = null;

  /**
   * Images data array with the most viewed images
   *
   * @var   object array
   */
  protected $_mostViewed;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Method to get the last commented images
   *
   * @return  object  An object containing the last commented images
   * @since   1.5.5
   */
  public function getLastCommented()
  {
    if($this->_loadLastCommented())
    {
      return $this->_lastCommented;
    }

    return array();
  }

  /**
   * Method to load the last commented images from the database
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadLastCommented()
  {
    if(empty($this->_lastCommented))
    {
      $rows = null;
      $this->_mainframe->triggerEvent('onJoomGetLastComments', array(&$rows, $this->_config->get('jg_toplist')));

      // If the data was not delivered by any plugin
      if(!$rows)
      {
        $categories = $this->_ambit->getCategoryStructure();
        $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

        $query = $this->_db->getQuery(true)
              ->select('a.*, cc.*, ca.*, a.owner AS owner, '.JoomHelper::getSQLRatingClause('a').' AS rating');
        if($this->_config->get('jg_showcatcom'))
        {
          $subquery = $this->_db->getQuery(true)
                   ->select('COUNT(*)')
                   ->from(_JOOM_TABLE_COMMENTS)
                   ->where('cmtpic = a.id')
                   ->where('published = 1')
                   ->where('approved  = 1');
          $query->select('('.$subquery->__toString().') As comments');
        }
        $query->from(_JOOM_TABLE_IMAGES.' AS a')
              ->from(_JOOM_TABLE_CATEGORIES.' AS ca')
              ->from(_JOOM_TABLE_COMMENTS.' AS cc')
              ->where('a.id = cc.cmtpic')
              ->where('a.catid = ca.cid')
              ->where('a.published = 1')
              ->where('a.approved = 1')
              ->where('a.access IN ('.$authorisedViewLevels.')')
              ->where('cc.published = 1')
              ->where('cc.approved  = 1')
              ->where('ca.published = 1')
              ->where('ca.access IN ('.$authorisedViewLevels.')')
              ->where('ca.cid IN ('.implode(',', array_keys($categories)).')')
              ->where('a.hidden = 0')
              ->where('ca.hidden = 0')
              ->where('ca.in_hidden = 0')
              ->where('ca.exclude_toplists = 0')
              ->order('cc.cmtdate DESC');

        $this->_db->setQuery($query, 0, (int) $this->_config->get('jg_toplist'));

        if(!$rows = $this->_db->loadObjectList())
        {
          return false;
        }
      }

      $this->_lastCommented = $rows;
    }

    return true;
  }

  /**
   * Method to get the last added images
   *
   * @return  object  An object containing the last added images
   * @since   1.5.5
   */
  public function getLastAdded()
  {
    if($this->_loadLastAdded())
    {
      return $this->_lastAdded;
    }

    return array();
  }

  /**
   * Method to load the last added images from the database
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadLastAdded()
  {
    if(empty($this->_lastAdded))
    {
      $categories = $this->_ambit->getCategoryStructure();
      $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

      $query = $this->_db->getQuery(true)
            ->select('*, a.owner AS owner, '.JoomHelper::getSQLRatingClause('a').' AS rating');
      if($this->_config->get('jg_showcatcom'))
      {
        $subquery = $this->_db->getQuery(true)
                 ->select('COUNT(*)')
                 ->from(_JOOM_TABLE_COMMENTS)
                 ->where('cmtpic = a.id')
                 ->where('published = 1')
                 ->where('approved  = 1');
        $query->select('('.$subquery->__toString().') As comments');
      }
      $query->from(_JOOM_TABLE_IMAGES.' AS a')
            ->from(_JOOM_TABLE_CATEGORIES.' AS ca')
            ->where('a.catid = ca.cid')
            ->where('a.published = 1')
            ->where('a.approved = 1')
            ->where('a.access IN ('.$authorisedViewLevels.')')
            ->where('ca.published = 1')
            ->where('ca.access IN ('.$authorisedViewLevels.')')
            ->where('ca.cid IN ('.implode(',', array_keys($categories)).')')
            ->where('a.hidden = 0')
            ->where('ca.hidden = 0')
            ->where('ca.in_hidden = 0')
            ->where('ca.exclude_toplists = 0')
            ->order('a.id DESC');

      $this->_db->setQuery($query, 0, (int) $this->_config->get('jg_toplist'));

      if(!$rows = $this->_db->loadObjectList())
      {
        return false;
      }

      $this->_lastAdded = $rows;
    }

    return true;
  }

  /**
   * Method to get the top rated images
   *
   * @return  object  An object containing the top rated images
   * @since   1.5.5
   */
  public function getTopRated()
  {
    if($this->_loadTopRated())
    {
      return $this->_topRated;
    }

    return array();
  }

  /**
   * Method to load the top rated images from the database
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadTopRated()
  {
    if(empty($this->_topRated))
    {
      $categories           = $this->_ambit->getCategoryStructure();
      $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

      $query = $this->_db->getQuery(true)
            ->select('*, a.owner AS owner, '.JoomHelper::getSQLRatingClause('a').' AS rating');
      if($this->_config->get('jg_showcatcom'))
      {
        $subquery = $this->_db->getQuery(true)
                 ->select('COUNT(*)')
                 ->from(_JOOM_TABLE_COMMENTS)
                 ->where('cmtpic = a.id')
                 ->where('published = 1')
                 ->where('approved  = 1');
        $query->select('('.$subquery->__toString().') As comments');
      }
      $query->from(_JOOM_TABLE_IMAGES.' AS a')
            ->from(_JOOM_TABLE_CATEGORIES.' AS ca')
            ->where('a.catid = ca.cid')
            ->where('a.imgvotes > 0')
            ->where('a.published = 1')
            ->where('a.approved = 1')
            ->where('a.access IN ('.$authorisedViewLevels.')')
            ->where('ca.published = 1')
            ->where('ca.access IN ('.$authorisedViewLevels.')')
            ->where('ca.cid IN ('.implode(',', array_keys($categories)).')')
            ->where('a.hidden = 0')
            ->where('ca.hidden = 0')
            ->where('ca.in_hidden = 0')
            ->where('ca.exclude_toplists = 0')
            ->order('rating DESC, imgvotesum DESC');

      $this->_db->setQuery($query, 0, (int) $this->_config->get('jg_toplist'));

      if(!$rows = $this->_db->loadObjectList())
      {
        return false;
      }

      $this->_topRated = $rows;
    }

    return true;
  }

  /**
   * Method to get the most viewed images
   *
   * @return  object  An object containing the most viewed images
   * @since   1.5.5
   */
  public function getMostViewed()
  {
    if($this->_loadMostViewed())
    {
      return $this->_mostViewed;
    }

    return array();
  }

  /**
   * Method to load the most viewd images from the database
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadMostViewed()
  {
    if(empty($this->_mostViewed))
    {
      $categories = $this->_ambit->getCategoryStructure();
      $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

      $query = $this->_db->getQuery(true)
            ->select('*, a.owner AS owner, '.JoomHelper::getSQLRatingClause('a').' AS rating');
      if($this->_config->get('jg_showcatcom'))
      {
        $subquery = $this->_db->getQuery(true)
                 ->select('COUNT(*)')
                 ->from(_JOOM_TABLE_COMMENTS)
                 ->where('cmtpic = a.id')
                 ->where('published = 1')
                 ->where('approved  = 1');
        $query->select('('.$subquery->__toString().') As comments');
      }
      $query->from(_JOOM_TABLE_IMAGES.' AS a')
            ->from(_JOOM_TABLE_CATEGORIES.' AS ca')
            ->where('a.hits > 0')
            ->where('a.catid = ca.cid')
            ->where('a.published = 1')
            ->where('a.approved = 1')
            ->where('a.access IN ('.$authorisedViewLevels.')')
            ->where('ca.published = 1')
            ->where('ca.access IN ('.$authorisedViewLevels.')')
            ->where('ca.cid IN ('.implode(',', array_keys($categories)).')')
            ->where('a.hidden = 0')
            ->where('ca.hidden = 0')
            ->where('ca.in_hidden = 0')
            ->where('ca.exclude_toplists = 0')
            ->order('hits DESC');

      $this->_db->setQuery($query, 0, (int) $this->_config->get('jg_toplist'));

      if(!$rows = $this->_db->loadObjectList())
      {
        return false;
      }

      $this->_mostViewed = $rows;
    }

    return true;
  }
}