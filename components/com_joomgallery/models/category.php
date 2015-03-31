<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/category.php $
// $Id: category.php 4345 2013-11-16 05:53:14Z chraneco $
/****************************************************************************************\
**   JoomGallery 3                                                                   **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Category view model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelCategory extends JoomGalleryModel
{
  /**
   * Category data object
   *
   * @var     object
   */
  protected $_category;

  /**
   * Categories data array
   *
   * @var     array
   */
  protected $_categories;

  /**
   * Categories number
   *
   * @var     int
   */
  protected $_totalcategories;

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
  private $_totalcategorieswithoutempty = null;

  /**
   * Images data array
   *
   * @var     array
   */
  protected $_images;

  /**
   * Images number
   *
   * @var     int
   */
  protected $_totalimages;

  /**
   * Images data array holding all images of
   * the current category an its sub-categories
   *
   * @var     array
   */
  protected $_allimages;

  /**
   * Constructor
   *
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    $id = JRequest::getInt('catid', 0);
    $this->setId($id);
  }

  /**
   * Method to set the category identifier
   *
   * @param   int     $id The Category ID
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new category ID if valid and wipe data
    if(!$id)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_NO_CATEGORY_SPECIFIED'), 'notice');
    }

    $this->_id                          = $id;
    $this->_category                    = null;
    $this->_categories                  = null;
    $this->_totalcategories             = null;
    $this->_categorieswithoutempty      = null;
    $this->_totalcategorieswithoutempty = null;
    $this->_images                      = null;
    $this->_totalimages                 = null;
  }

  /**
   * Method to get the data of the current category
   *
   * @return  object  An object with the category data
   * @since   1.5.5
   */
  public function getCategory()
  {
    if($this->_loadCategory())
    {
      return $this->_category;
    }

    return false;
  }

  /**
   * Retrieves the data of all sub-categories
   *
   * @return  array   Array of objects containing the categories data from the database
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
  public function getTotalCategories()
  {
    // Let's load the categories if they doesn't already exist
    if(empty($this->_totalcategories))
    {
      $query = $this->_buildCategoriesQuery();
      $this->_totalcategories = $this->_getListCount($query);
    }

    return $this->_totalcategories;
  }

  /**
   * Returns the array of all available sub-categories
   * of the the current category without empty categories
   *
   * @return  array Categories objects array
   * @since   1.5.7
   */
  public function getCategoriesWithoutEmpty()
  {
    if($this->_loadCategoriesWithoutEmpty())
    {
      // We still have to select the categories according to the pagination
      $limit      = $this->_config->get('jg_subperpage');
      $limitstart = JRequest::getInt('catlimitstart', 0);
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
  public function getTotalCategoriesWithoutEmpty()
  {
    // Let's calculate the number if it doesn't already exist
    if(empty($this->_totalcategorieswithoutempty))
    {
      if(!$this->_loadCategoriesWithoutEmpty())
      {
        return 0;
      }
    }

    return $this->_totalcategorieswithoutempty;
  }

  /**
   * Retrieves the data of all images of the current category
   *
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.5
   */
  public function getImages()
  {
    if($this->_loadImages())
    {
      return $this->_images;
    }

    return array();
  }

  /**
   * Retrieves the id from the first image of a category
   * needed for Option 'Skip category view'
   *
   * @param   int    category id
   * @return  int    id of detail image
   * @since   2.0
   */
  public function getImageCat($catId)
  {
    $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

    $query = $this->_db->getQuery(true)
          ->select('id')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
          ->where('a.published = 1')
          ->where('a.catid     = '.$catId)
          ->where('a.approved  = 1')
          ->where('a.hidden    = 0')
          ->where('a.access    IN ('.$authorisedViewLevels.')')
          ->where('c.access    IN ('.$authorisedViewLevels.')');
    if($this->_config->get('jg_firstorder'))
    {
      $query->order('a.'.$this->_config->get('jg_firstorder'));
    }
    if($this->_config->get('jg_secondorder'))
    {
      $query->order('a.'.$this->_config->get('jg_secondorder'));
    }
    if($this->_config->get('jg_thirdorder'))
    {
      $query->order('a.'.$this->_config->get('jg_thirdorder'));
    }

    $this->_db->setQuery($query);
    $img = $this->_db->loadResult();

    return $img;
  }

  /**
   * Method to get the total number of images
   *
   * @return  int     The total number of images
   * @since   1.5.5
   */
  public function getTotalImages()
  {
    // Let's load the categories if they doesn't already exist
    if(empty($this->_totalimages))
    {
      $query = $this->_buildImagesQuery();
      $this->_totalimages = $this->_getListCount($query);
    }

    return $this->_totalimages;
  }

  /**
   * Retrieves the data of all images of the current category and its sub-categories
   *
   * @return  array   Array of objects containing the images data from the database
   * @since   1.5.7
   */
  public function getAllImages()
  {
    if($this->_loadAllImages())
    {
      return $this->_allimages;
    }

    return array();
  }

  /**
   * Method to get one image selected by chance
   *
   * @return  object  An object with the data of the selected image
   * @since   1.5.5
   */
  public function getRandomImage($catid = 0, $random_catid = 0)
  {
    $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

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
    $query->where('p.access   IN ('.$authorisedViewLevels.')')
          ->where('p.published = 1')
          ->where('p.approved  = 1')
          ->where('c.access   IN ('.$authorisedViewLevels.')')
          ->where('c.published = 1');

    $this->_db->setQuery($query);
    if(!$rows = $this->_db->loadObjectList())
    {
      return false;
    }

    $row = $rows[mt_rand(0, count($rows) - 1)];

    return $row;
  }

  /**
   * Method to unlock a password protected category
   *
   * @param   int     $catid    ID of the category to unlock
   * @param   string  $password Password of the category to check
   * @return  boolean True on success, false otherwise
   * @since   3.1
   */
  public function unlock($catid, $password)
  {
    $query = $this->_db->getQuery(true)
          ->select('cid, password')
          ->from($this->_db->quoteName(_JOOM_TABLE_CATEGORIES))
          ->where('cid = '.(int) $catid);
    $this->_db->setQuery($query);

    if(!$category = $this->_db->loadObject())
    {
      throw new Exception($this->_db->getErrorMsg());
    }

    if(!$category->password)
    {
      throw new Exception('Category is not protected.');
    }

    $match = false;
    if(substr($category->password, 0, 4) == '$2y$')
    {
      // BCrypt passwords are always 60 characters, but it is possible that salt is appended although non standard.
      $password60 = substr($category->password, 0, 60);

      if(JCrypt::hasStrongPasswordSupport())
      {
        $match = password_verify($password, $password60);
      }
    }
    else
    {
      if(substr($category->password, 0, 8) == '{SHA256}')
      {
        // Check the password
        $parts  = explode(':', $category->password);
        $crypt  = $parts[0];
        $salt   = @$parts[1];
        $testcrypt = JUserHelper::getCryptedPassword($password, $salt, 'sha256', false);

        if($category->password == $testcrypt)
        {
          $match = true;
        }
      }
      else
      {
        // Check the password
        $parts  = explode(':', $category->password);
        $crypt  = $parts[0];
        $salt   = @$parts[1];

        $testcrypt = JUserHelper::getCryptedPassword($password, $salt, 'md5-hex', false);

        if($crypt == $testcrypt)
        {
          $match = true;
        }
      }
    }

    if(!$match)
    {
      throw new Exception(JText::_('COM_JOOMGALLERY_CATEGORY_WRONG_PASSWORD'));
    }

    $categories = $this->_mainframe->getUserState('joom.unlockedCategories', array(0));
    $categories = array_unique(array_merge($categories, array($catid)));
    $this->_mainframe->setUserState('joom.unlockedCategories', $categories);

    return true;
  }

  /**
   * Method to load the data of the current category
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadCategory()
  {
    // Check whether the requested category exists and
    // whether the current user is allowed to see it
    $allowed = true;
    $categories = $this->_ambit->getCategoryStructure();
    if(!isset($categories[$this->_id]))
    {
      $allowed = false;
    }

    // Let's load the data if it doesn't already exist
    if(empty($this->_category))
    {
      $query = $this->_db->getQuery(true)
            ->select('cid, name, parent_id, description, password, owner, metakey, metadesc, params')
            ->from(_JOOM_TABLE_CATEGORIES)
            ->where('cid       = '.$this->_id)
            ->where('published = 1')
            ->where('access    IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');

      $this->_db->setQuery($query);

      if(!$row = $this->_db->loadObject())
      {
        JError::raiseError(500, JText::sprintf('Category with ID %d not found', $this->_id));
      }

      $this->_category = $row;
    }

    if(!$allowed)
    {
      $this->_category->protected = true;
      if(     (!$this->_category->password || in_array($this->_id, $this->_mainframe->getUserState('joom.unlockedCategories', array(0))))
          &&  $this->_category->parent_id > 1
          &&  !isset($categories[$this->_category->parent_id]))
      {
        JError::raiseError(500, JText::sprintf('Category with ID %d not found', $this->_id));
      }
    }

    return true;
  }

  /**
   * Method to load the data of all sub-categories
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadCategories()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_categories))
    {
      // Get the pagination request variables
      $limit      = $this->_config->get('jg_subperpage');#JRequest::getVar('limit', 0, '', 'int');
      $limitstart = JRequest::getInt('catlimitstart', 0);

      $query = $this->_buildCategoriesQuery();

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
      $query = $this->_buildCategoriesQuery();
      if(!$cats = $this->_getList($query))
      {
        return false;
      }

      foreach($cats as $key => $cat)
      {
        // Get all sub-categories for each category which contain images
        $subcategories = JoomHelper::getAllSubCategories($cat->cid, true);
        if(!count($subcategories))
        {
          unset($cats[$key]);
        }
      }

      $this->_categorieswithoutempty      = $cats;
      $this->_totalcategorieswithoutempty = count($cats);
    }

    return true;
  }

  /**
   * Method to load the data of all images of the current category
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_images))
    {
      // Get the pagination request variables
      $limitstart = JRequest::getInt('limitstart', 0);
      if($limitstart == -1)
      {
        // If we want to display all images in a popup box we will need all images
        $limit  = null;
      }
      else
      {
        $limit  = JRequest::getInt('limit', 0);
      }

      $query = $this->_buildImagesQuery();

      if(!$rows = $this->_getList($query, $limitstart, $limit))
      {
        return false;
      }

      $this->_images = $rows;
    }

    return true;
  }

  /**
   * Method to load the data of all images of the current category and its sub-categories
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.7
   */
  protected function _loadAllImages()
  {
    // Let's load the data if it doesn't already exist
    if(empty($this->_allimages))
    {
      $limit = JRequest::getInt('limit', 0);

      if(!$limit)
      {
        // RSS in category view is disabled
        return false;
      }

      if($limit < 0)
      {
        // If $limit is negative all images will be loaded
        $limit  = null;
      }

      $catids = JoomHelper::getAllSubCategories($this->_id, true);

      $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

      $query = $this->_db->getQuery(true)
            ->select('*, a.owner AS owner')
            ->from(_JOOM_TABLE_IMAGES.' AS a')
            ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
            ->where('c.cid       IN ('.implode(',', $catids).')')
            ->where('a.published = 1')
            ->where('a.approved  = 1')
            ->where('a.hidden    = 0')
            ->where('a.access    IN ('.$authorisedViewLevels.')')
            ->where('c.published = 1')
            ->where('c.access    IN ('.$authorisedViewLevels.')')
            ->where('c.hidden    = 0')
            ->order('a.imgdate DESC');

      if(!$rows = $this->_getList($query, 0, $limit))
      {
        return false;
      }

      $this->_allimages = $rows;
    }

    return true;
  }

  /**
   * Returns the query for loading the categories
   *
   * @return  string    The query to be used to retrieve the categories data from the database
   * @since   1.5.5
   */
  protected function _buildCategoriesQuery()
  {
    $query = $this->_db->getQuery(true)
          ->select('c.*')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c');

    // Join over the images if necessary
    if($this->_config->get('jg_showsubthumbs') == 1 || $this->_config->get('jg_showsubthumbs') == 3)
    {
      $query->select('i.id, i.catid, i.imgthumbname, i.hidden AS imghidden')
            ->leftJoin(_JOOM_TABLE_IMAGES.' AS i ON (     c.thumbnail = i.id
                                                      AND i.published = 1
                                                      AND i.approved  = 1
                                                      AND i.access    IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).'))');
    }

    $query->where('c.published = 1')
          ->where('c.hidden    = 0')
          ->where('c.parent_id = '.$this->_id);

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

    if($this->_config->get('jg_ordersubcatbyalpha'))
    {
      $query->order('c.name');
    }
    else
    {
      $query->order('c.lft ASC');
    }

    return $query;
  }

  /**
   * Returns the query for loading the images
   *
   * @return  object  The query to be used to retrieve the images data from the database
   * @since   1.5.5
   */
  protected function _buildImagesQuery()
  {
    $authorisedViewLevels = implode(',', $this->_user->getAuthorisedViewLevels());

    $query = $this->_db->getQuery(true)
          ->select('*, a.owner AS owner, '.JoomHelper::getSQLRatingClause('a').'AS rating');
    if($this->_config->get('jg_showcatcom'))
    {
      $subquery = $this->_db->getQuery(true)
               ->select('COUNT(cmtid)')
               ->from(_JOOM_TABLE_COMMENTS)
               ->where('cmtpic    = a.id')
               ->where('published = 1')
               ->where('approved  = 1');

      $query->select('('.$subquery.') AS comments');
    }
    $query->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
          ->where('a.published = 1')
          ->where('a.catid     = '.$this->_id)
          ->where('a.approved  = 1')
          ->where('a.hidden    = 0')
          ->where('a.access    IN ('.$authorisedViewLevels.')')
          ->where('c.access    IN ('.$authorisedViewLevels.')');
    if($this->_config->get('jg_firstorder'))
    {
      $query->order('a.'.$this->_config->get('jg_firstorder'));
    }
    if($this->_config->get('jg_secondorder'))
    {
      $query->order('a.'.$this->_config->get('jg_secondorder'));
    }
    if($this->_config->get('jg_thirdorder'))
    {
      $query->order('a.'.$this->_config->get('jg_thirdorder'));
    }
    if($this->_config->get('jg_usercatorder'))
    {
      $user_orderby   = $this->_mainframe->getUserStateFromRequest('joom.category.images.orderby', 'orderby');
      $user_orderdir  = $this->_mainframe->getUserStateFromRequest('joom.category.images.orderdir', 'orderdir');

      $orderby = '';
      switch($user_orderby)
      {
        case 'user':
          $orderby = 'a.owner';
          break;
        case 'date':
          $orderby = 'a.imgdate';
          break;
        case 'rating':
          $orderby = 'rating';
          break;
        case 'title':
          $orderby = 'a.imgtitle';
          break;
        case 'hits':
          $orderby = 'a.hits';
          break;
        default:
          // Use selected ordering above
          break;
      }
      if(    $user_orderby == 'title'
          || $user_orderby == 'hits'
          || $user_orderby == 'user'
          || $user_orderby == 'date'
          || $user_orderby == 'rating'
        )
      {
        if($user_orderdir == 'desc')
        {
          $orderby .= ' DESC';
        }
        else
        {
          if($user_orderdir == 'asc')
          {
            $orderby .= ' ASC';
          }
        }
      }
      if($user_orderby == 'rating')
      {
          $orderby .= ', imgvotesum DESC';
      }

      if(!empty($orderby))
      {
        $query->clear('order');
        $query->order($orderby);
      }
    }

    return $query;
  }
}