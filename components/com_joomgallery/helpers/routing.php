<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/helpers/routing.php $
// $Id: routing.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Routing Helper
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomRouting
{
  /**
   * Returns the ID of an image or a category by searching the alias
   * in the database tables.
   *
   * @param   array   $segments An array of segments of the given URL
   * @return  array   Associative array of view and ID, boolean false if nothing was found
   * @since   1.5.5
   */
  public static function getId($segments)
  {
    $db = JFactory::getDBO();

    $path = implode('/', $segments);

    $query = $db->getQuery(true)
            ->select('cid')
            ->from(_JOOM_TABLE_CATEGORIES)
            ->where('alias = '.$db->quote(str_replace(':', '-', $path)));
    $db->setQuery($query);
    if($result = $db->loadResult())
    {
      return array('view' => 'category', 'id' => $result);
    }

    $count = count($segments);
    $query->clear()
          ->select('id')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('alias = '.$db->quote(str_replace(':', '-', $segments[$count-1])));
    $db->setQuery($query);
    if($result = $db->loadResult())
    {
      return array('view' => 'detail', 'id' => $result);
    }

    return false;
  }

  /**
   * Checks an Itemid whether it is related to the gallery view.
   * If not, an Itemid which is related to the gallery view is
   * returned, if found.
   *
   * @param   int         $Itemid The Itemid to check
   * @return  int/boolean Found Itemid, false if correct Itemid was not found or passed Itemid is correct
   * @since   1.5.5
   */
  public static function checkItemid($Itemid)
  {
    $mainframe  = JApplication::getInstance('site');
    $menu       = $mainframe->getMenu();
    $menuItem   = $menu->getItem($Itemid);
    if(!isset($menuItem->query['view']) || $menuItem->query['view'] == 'gallery')
    {
      return false;
    }

    $db    = JFactory::getDBO();
    $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu')
            ->where('link LIKE '.$db->quote('%option=com_joomgallery&view=gallery%'));
    $db->setQuery($query);
    $Itemid = intval($db->loadResult());

    if($Itemid)
    {
      return $Itemid;
    }

    return false;
  }
}