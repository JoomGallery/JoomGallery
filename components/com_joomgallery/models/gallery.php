<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/gallery.php $
// $Id: gallery.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * Gallery view model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelGallery extends JoomGalleryModel
{
  /**
   * Categories data array
   *
   * @var     array
   */
  private $_categories;

  /**
   * Categories number
   *
   * @var     int
   */
  private $_total = null;

  /**
   * Categories data array (without empty categories)
   *
   * @var     array
   */
  private $_categorieswithoutempty;

  /**
   * Categories number (without empty categories)
   *
   * @var     int
   */
  private $_totalwithoutempty = null;

  /**
   * Returns the array of all available main categories of the gallery
   *
   * @return  array   Categories objects array
   * @since   1.5.5
   */
  public function getCategories()
  {
    if($this->_loadCategories())
    {
      return $this->_categories;
    }

    return array();
  }

  /**
   * Method to get the total number of categories
   *
   * @return  int     The total number of categories
   * @since   1.5.5
   */
  public function getTotal()
  {
    // Let's load the categories if they doesn't already exist
    if(empty($this->_total))
    {
      $query        = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }

  /**
   * Returns the array of all available main categories
   * of the gallery without empty categories
   *
   * @return  array   Categories objects array
   * @since   1.5.7
   */
  public function getCategoriesWithoutEmpty()
  {
    if($this->_loadCategoriesWithoutEmpty())
    {
      // We still have to select the categories according to the pagination
      $limit      = $this->_config->get('jg_catperpage');#JRequest::getVar('limit', 0, '', 'int');
      $limitstart = JRequest::getInt('limitstart', 0);
      $cats       = array_slice($this->_categorieswithoutempty, $limitstart, $limit);

      return $cats;
    }

    return array();
  }

  /**
   * Method to get the total number of categories without empty categories
   *
   * @return  int     The total number of categories without empty ones
   * @since   1.5.7
   */
  public function getTotalWithoutEmpty()
  {
    // Let's calculate the number if it doesn't already exist
    if(empty($this->_totalwithoutempty))
    {
      if(!$this->_loadCategoriesWithoutEmpty())
      {
        return 0;
      }
    }

    return $this->_totalwithoutempty;
  }

  /**
   * Method to get the data of a random image
   *
   * @access  public
   * @return  object  The data of a random image
   * @since   1.5.5
   */
  public function getRandomImage($catid = 0, $random_catid = 0)
  {
    $query = $this->_db->getQuery(true)
          ->select('*, c.access')
          ->from(_JOOM_TABLE_IMAGES.' AS p')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = p.catid');
    if($this->_config->get('jg_showrandomcatthumb') == 1)
    {
        $query->where('p.catid = '.$catid);
    }
    else
    {
      if($this->_config->get('jg_showrandomcatthumb') >= 2)
      {
          $query->where('p.catid = '.$random_catid);
      }
    }
    $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());
    $query->where('p.access IN ('.$authorisedViewLevels.')')
          ->where('p.published = 1')
          ->where('p.approved = 1')
          ->where('p.hidden != 1')
          ->where('c.access   IN ('.$authorisedViewLevels.')')
          ->where('c.published = 1');
    $this->_db->setQuery($query);

    if(!$rows = $this->_db->loadObjectList())
    {
      return false;
    }

    $row = $rows[mt_rand(0, count($rows)-1)];

    return $row;
  }

  /**
   * Loads the data of all available categories from the database
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  private function _loadCategories()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categories))
    {
      // Get the pagination request variables
      $limit      = $this->_config->get('jg_catperpage');#JRequest::getVar('limit', 0, '', 'int');
      $limitstart = JRequest::getInt('limitstart', 0);

      $query = $this->_buildQuery();

      if(!$rows = $this->_getList($query, $limitstart, $limit))
      {
        return false;
      }

      $this->_categories = $rows;
    }

    return true;
  }

  /**
   * Loads the data of all available categories from the database
   * and checks whether the categories are empty.
   * This method stores only the categories which aren't empty after that.
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.7
   */
  private function _loadCategoriesWithoutEmpty()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categorieswithoutempty))
    {
      $query = $this->_buildQuery();
      if(!$cats = $this->_getList($query))
      {
        return false;
      }

      foreach($cats as $key => $cat)
      {
        // Get all sub-categories for each category which contain images
        $show_all = $this->_config->get('jg_showrestrictedcats');
        $subcategories = JoomHelper::getAllSubCategories($cat->cid, true, false, $show_all);
        if(!count($subcategories))
        {
          unset($cats[$key]);
        }
      }

      $this->_categorieswithoutempty  = $cats;
      $this->_totalwithoutempty       = count($cats);
    }

    return true;
  }

  /**
   * Returns the query for loading all available categories of the gallery
   *
   * @return  string  The query to be used to retrieve the categories from the database
   * @since   1.5.5
   */
  private function _buildQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('c.*')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c');

    // Join over the images if necessary
    if($this->_config->get('jg_showcatthumb') >= 2)
    {
      $query->select('i.id, i.catid, i.imgthumbname, i.hidden AS imghidden')
            ->leftJoin(_JOOM_TABLE_IMAGES.' AS i ON (     c.thumbnail = i.id
                                                      AND i.published = 1
                                                      AND i.approved  = 1
                                                      AND i.access    IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).'))');
    }

    $query->where('c.published = 1')
          ->where('c.hidden    = 0')
          ->where('c.parent_id = 1');

    if(!$this->_config->get('jg_showrestrictedcats'))
    {
      $query->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');
    }

    if($this->_config->get('jg_hideemptycats'))
    {
      $query->where(' (((SELECT COUNT(id) FROM '._JOOM_TABLE_IMAGES.' AS i WHERE i.catid = c.cid) != 0)
                    OR
                      ((SELECT COUNT(cid) FROM '._JOOM_TABLE_CATEGORIES.' AS sc WHERE sc.parent_id = c.cid) != 0))');
    }

    if($this->_config->get('jg_ordercatbyalpha'))
    {
      $query->order('c.name');
    }
    else
    {
      $query->order('c.lft');
    }

    return $query;
  }
}