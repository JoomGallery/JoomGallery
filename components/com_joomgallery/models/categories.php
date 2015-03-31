<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/categories.php $
// $Id: categories.php 4360 2014-02-20 16:51:51Z erftralle $
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
 * @since   2.1
 */
class JoomGalleryModelCategories extends JoomGalleryModel
{
  /**
   * Constructor
   *
   * @param   array An optional associative array of configuration settings
   * @return  void
   * @since   2.1
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
        'level', 'c.level'
        );
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
    JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html');

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
                  ||  !$result[$i]->owner
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
        $results['results'][$j]->path = JHtml::_('joomgallery.categorypath', $result[$i]->cid, false, ' &raquo; ', false, false, true);
        $j++;
      }
    }
    catch(JDatabaseException $e)
    {
      return $e;
    }

    return $results;
  }
}