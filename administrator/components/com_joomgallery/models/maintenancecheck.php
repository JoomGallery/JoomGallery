<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/maintenancecheck.php $
// $Id: maintenancecheck.php 4324 2013-09-03 13:07:49Z erftralle $
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

jimport('joomla.filesystem.file');

/**
 * Maintenance check model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelMaintenancecheck extends JoomGalleryModel
{
  /**
   * Number of images and categories to check in one go without checking remaining time
   *
   * @var   int
   * @since 1.5.5
   */
  private $limit = 50;

  /**
   * Cleans the database from previous check runs and starts a check
   *
   * @return  void
   * @since   1.5.5
   */
  public function check()
  {
    $this->_mainframe->setUserState('joom.maintenance.check', null);

    $this->_db->truncateTable(_JOOM_TABLE_MAINTENANCE);
    $this->_db->truncateTable(_JOOM_TABLE_ORPHANS);

    $this->_mainframe->setUserState('joom.maintenance.checkoriginals', JRequest::getBool('check_originals'));

    // Prepare next step: Calculate an approximate value for the number of folders to parse
    $query = $this->_db->getQuery(true)
          ->select('COUNT(cid)')
          ->from(_JOOM_TABLE_CATEGORIES);
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $total = 3 * $total;

    $types = array('thumb', 'img', 'orig');
    $this->_mainframe->setUserState('joom.maintenance.check.types', $types);
    $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
    $this->_mainframe->setUserState('joom.maintenance.check.parsefolders', true);

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_PARSE_FOLDERS'),
                                          'remaining' => $total,
                                          'start'     => true)
                                  );
    // Next step
    $refresher->refresh(null, 'parsefolders');
  }

  /**
   * Parses all folders in JoomGallery's file system and
   * inserts them and the found images into the database
   *
   * @return  void
   * @since   1.5.5
   */
  public function parseFolders()
  {
    // Calculate an approximate value for the number of folders to parse
    $query = $this->_db->getQuery(true);

    $query->select('COUNT(c.cid)')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c');
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $total = 3 * $total;

    $start = false;
    if(!$this->_mainframe->getUserState('joom.maintenance.check.parsefolders'))
    {
      $start = true;
      $types = array('thumb', 'img', 'orig');
      $this->_mainframe->setUserState('joom.maintenance.check.types', $types);
      $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
      $this->_mainframe->setUserState('joom.maintenance.check.parsefolders', true);
    }

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_PARSE_FOLDERS'),
                                          'remaining' => $total,
                                          'start'     => $start)
                                  );

    $paths  = array('thumb' => JPath::clean(rtrim($this->_ambit->get('thumb_path'), '/\\'), '/'),
                    'img'   => JPath::clean(rtrim($this->_ambit->get('img_path'), '/\\'), '/'),
                    'orig'  => JPath::clean(rtrim($this->_ambit->get('orig_path'), '/\\'), '/')
                    );
    $types  = $this->_mainframe->getUserState('joom.maintenance.check.types');
    $index  = $this->_mainframe->getUserState('joom.maintenance.check.index');

    $img_types = array('gif', 'jpg', 'png', 'jpeg', 'jpe');

    // Create a list for all found folders and files
    $continue = true;
    while($continue)
    {
      $query->clear();
      $query->select('fullpath')
            ->from(_JOOM_TABLE_ORPHANS)
            ->where("type = 'folder'");
      $this->_db->setQuery($query, $index, 1);

      $folder = $this->_db->loadResult();

      // Check whether there are folders we have to parse
      if(!$folder)
      {
        // Check whether we haven't started yet or if we have just finished with a type
        if(!$this->_mainframe->getUserState('joom.maintenance.check.'.$types[0].'.started'))
        {
          // If we haven't started yet start with the root folder and set flag
          $folder = $paths[$types[0]];
          $this->_mainframe->setUserState('joom.maintenance.check.'.$types[0].'.started', true);
        }
        else
        {
          // If we have just finished unset the type so that we aren't working with it again later
          unset($types[0]);

          // Reindex the array (new first element has now index 0 again)
          $types = array_values($types);
          $this->_mainframe->setUserState('joom.maintenance.check.types', $types);

          if(!count($types))
          {
            // If there are no types left we have completely parsed all folders
            $continue = false;
          }

          continue;
        }
      }
      else
      {
        $index++;
      }

      $folder = JPath::clean(rtrim($folder, '/\\'), '/');

      // Collect all sub-folders of the current folder
      $folders = JFolder::folders($folder, '.', false, true);

      $query->clear()
            ->insert(_JOOM_TABLE_ORPHANS);
            //->columns('type, fullpath');

      $count = count($folders);
      if($count)
      {
        foreach($folders as $new_folder)
        {
          // Exclude root directories of image types (this may happen if
          // the directory of an image type is a sub-directory of another
          // image type) because they are parsed anyway
          if(in_array(JPath::clean(rtrim($new_folder, '/\\'), '/'), $paths))
          {
            continue;
          }

          //$query->values("'folder', '".JPath::clean($new_folder, '/')."'");
          $query->clear('set');
          $query->set("type = 'folder'");
          $query->set("fullpath = '".JPath::clean($new_folder, '/')."'");
          $this->_db->setQuery($query);
          $this->_db->query();
        }
        //$this->_db->setQuery($query);
        //$this->_db->query();
      }

      // Collect all images of the current folder
      $files = JFolder::files($folder, '.', false, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

      $query->clear('values')->clear('set');
      $query->columns('type, fullpath');
      if(count($files))
      {
        foreach($files as $file)
        {
          if(in_array(strtolower(JFile::getExt($file)), $img_types))
          {
            $type = $types[0];
          }
          else
          {
            $type = 'unknown';
          }

          $query->values("'".$type."', '".JPath::clean($file, '/')."'");
        }
        $this->_db->setQuery($query);
        $this->_db->query();
      }

      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.maintenance.check.index', $index);
        $refresher->refresh($total - $index);
      }
    }

    // Prepare next step
    $query = $this->_db->getQuery(true)
          ->select('COUNT(id)')
          ->from(_JOOM_TABLE_IMAGES);
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $this->_mainframe->setUserState('joom.maintenance.check.limitstart', 0);
    $this->_mainframe->setUserState('joom.maintenance.check.checkimages', true);

    $refresher->reset($total, true, JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_IMAGES'));

    // Next step
    $refresher->refresh(0, 'checkimages');
  }

  /**
   * Checks all images of the gallery registered in the database
   *
   * @return  void
   * @since   1.5.5
   */
  public function checkImages()
  {
    $start = false;
    if(!$this->_mainframe->getUserState('joom.maintenance.check.checkimages'))
    {
      $start = true;
      $this->_mainframe->setUserState('joom.maintenance.check.limitstart', 0);
      $this->_mainframe->setUserState('joom.maintenance.check.checkimages', true);
    }

    $query = $this->_db->getQuery(true);

    $query->select('COUNT(a.id)')
          ->from(_JOOM_TABLE_IMAGES.' AS a');
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_IMAGES'),
                                          'remaining' => $total,
                                          'start'     => $start)
                                  );

    $query->clear('select')
          ->select('a.id, a.catid, a.imgthumbname, a.imgfilename, a.imgtitle, a.owner, a.alias, c.cid AS category, u.id AS user')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON a.catid = c.cid')
          ->leftJoin('#__users AS u ON a.owner = u.id');

    $types = array('thumb', 'img', 'orig');

    $table = $this->getTable('joomgallerymaintenance');

    $start = $this->_mainframe->getUserState('joom.maintenance.check.limitstart', 0);

    for($limitstart = $start; $limitstart < $total; $limitstart += $this->limit)
    {
      $images = $this->_getList($query, $limitstart, $this->limit);

      foreach($images as $image)
      {
        $corrupt = false;

        // Check for valid category
        if(!$image->category || $image->category == 1)
        {
          $image->catid = -1;
          $corrupt = true;
        }

        // Check for valid owner
        if($image->owner && !$image->user)
        {
          $image->owner = -1;
          $corrupt = true;
        }

        // Look for image files
        foreach($types as $type)
        {
          $file = $this->_ambit->getImg($type.'_path', $image);
          if(JFile::exists($file))
          {
            $image->$type = $file;

            // Delete the corresponding entry in orphans table
            $delete_query = $this->_db->getQuery(true)
                  ->delete()
                  ->from(_JOOM_TABLE_ORPHANS)
                  ->where("fullpath = '".JPath::clean($file, '/')."'")
                  ->where("(type = 'thumb' OR type = 'img' OR type = 'orig')");
            $this->_db->setQuery($delete_query);
            $this->_db->query();
          }
          else
          {
            $corrupt = true;
          }
        }

        // Check whether the image is corrupt
        if($corrupt)
        {
          // If yes, store the gathered information in the database
          $table->reset();
          $table->bind($image);
          $table->id    = 0;
          $table->refid = $image->id;
          $table->title = $image->imgtitle;
          $table->type  = 0;
          $table->check();
          $table->store();
        }
      }

      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.maintenance.check.limitstart', $limitstart + $this->limit);
        $refresher->refresh($total - ($limitstart + $this->limit));
      }
    }

    // Prepare next step
    $query = $this->_db->getQuery(true)
          ->select('COUNT(cid)')
          ->from(_JOOM_TABLE_CATEGORIES);
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $this->_mainframe->setUserState('joom.maintenance.check.limitstart', 0);
    $this->_mainframe->setUserState('joom.maintenance.check.checkcategories', true);

    $refresher->reset($total, true, JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_CATEGORIES'));

    // Next step
    $refresher->refresh(0, 'checkcategories');
  }

  /**
   * Checks all categories of the gallery registered in the database
   *
   * @return  void
   * @since   1.5.5
   */
  public function checkCategories()
  {
    $start = false;
    if(!$this->_mainframe->getUserState('joom.maintenance.check.checkcategories'))
    {
      $start = true;
      $this->_mainframe->setUserState('joom.maintenance.check.limitstart', 0);
      $this->_mainframe->setUserState('joom.maintenance.check.checkcategories', true);
    }

    $query = $this->_db->getQuery(true);

    $query->select('COUNT(c.cid)')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c');
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_CATEGORIES'),
                                          'remaining' => $total,
                                          'start'     => $start)
                                  );

    $query->clear('select')
          ->select('c.cid, c.parent_id, c.name, c.owner, c.alias, p.cid AS parent_category, u.id AS user')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS p ON c.parent_id = p.cid')
          ->leftJoin('#__users AS u ON c.owner = u.id');

    $types = array('thumb', 'img', 'orig');

    $table = $this->getTable('joomgallerymaintenance');

    $start = $this->_mainframe->getUserState('joom.maintenance.check.limitstart', 0);

    for($limitstart = $start; $limitstart < $total; $limitstart += $this->limit)
    {
      $categories = $this->_getList($query, $limitstart, $this->limit);

      foreach($categories as $category)
      {
        // Skip ROOT category
        if($category->cid == 1)
        {
          continue;
        }

        $corrupt = false;

        // Check for valid parent category
        if(!$category->parent_category)
        {
          $category->parent_id = -1;
          $corrupt = true;
        }

        // Check for valid owner
        if($category->owner && !$category->user)
        {
          $category->owner = -1;
          $corrupt = true;
        }

        // Look for folders
        foreach($types as $type)
        {
          $folder = JPath::clean($this->_ambit->get($type.'_path').rtrim(JoomHelper::getCatPath($category->cid), '/'), '/');

          if(JFolder::exists($folder))
          {
            $category->$type = $folder;

            // Delete the corresponding entry in orphans table
            $delete_query = $this->_db->getQuery(true)
                  ->delete()
                  ->from(_JOOM_TABLE_ORPHANS)
                  ->where("fullpath = '".JPath::clean($folder, '/')."'")
                  ->where("type = 'folder'");
            $this->_db->setQuery($delete_query);
            $this->_db->query();
          }
          else
          {
            $corrupt = true;
          }
        }

        // Check whether the image is corrupt
        if($corrupt)
        {
          // If yes, store the gathered information in the database
          $table->reset();
          $table->bind($category);
          $table->id    = 0;
          $table->refid = $category->cid;
          $table->title = $category->name;
          $table->catid = $category->parent_id;
          $table->type  = 1;
          $table->check();
          $table->store();
        }
      }

      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.maintenance.check.limitstart', $limitstart + $this->limit);
        $refresher->refresh($total - ($limitstart + $this->limit));
      }
    }

    // Prepare next step
    $query = $this->_db->getQuery(true)
          ->select('COUNT(id)')
          ->from(_JOOM_TABLE_ORPHANS)
          ->where("(type = 'thumb' OR type = 'img' OR type = 'orig')");
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
    $this->_mainframe->setUserState('joom.maintenance.check.createfilesuggestions', true);

    $refresher->reset($total, true, JText::_('COM_JOOMGALLERY_MAIMAN_CREATE_FILE_SUGGESTIONS'));

    // Next step
    $refresher->refresh(0, 'createfilesuggestions');
  }

  /**
   * Creates suggestions for correcting problems with orphaned files
   *
   * @return  void
   * @since   1.5.5
   */
  public function createFileSuggestions()
  {
    $query = $this->_db->getQuery(true);

    $query->select('COUNT(id)')
          ->from(_JOOM_TABLE_ORPHANS)
          ->where("(type = 'thumb' OR type = 'img' OR type = 'orig')");
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $start = false;
    if(!$this->_mainframe->getUserState('joom.maintenance.check.createfilesuggestions'))
    {
      $start = true;
      $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
      $this->_mainframe->setUserState('joom.maintenance.check.createfilesuggestions', true);
    }

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_CREATE_FILE_SUGGESTIONS'),
                                          'remaining' => $total,
                                          'start'     => $start)
                                  );

    $index = $this->_mainframe->getUserState('joom.maintenance.check.index', 0);

    $query->clear('select')
          ->select('id, type, fullpath');
    $this->_db->setQuery($query, $index, 1);

    while($file = $this->_db->loadObject())
    {
      $filename = basename($file->fullpath);

      $query->clear()
            ->select('a.id AS image_id')
            ->select('m.id AS orphan_id')
            ->select('a.imgtitle')
            ->from(_JOOM_TABLE_IMAGES.' AS a')
            ->leftJoin(_JOOM_TABLE_MAINTENANCE.' AS m ON a.id = m.refid');
      if($file->type == 'thumb')
      {
        $query->where("a.imgthumbname = '".$filename."'");
      }
      else
      {
        $query->where("a.imgfilename = '".$filename."'");
      }

      $this->_db->setQuery($query);
      if($suggestion = $this->_db->loadObject())
      {
        $query->clear()
              ->update(_JOOM_TABLE_ORPHANS)
              ->set('refid = '.$suggestion->image_id)
              ->set("title = '".$suggestion->imgtitle."'")
              ->where('id = '.$file->id);
        $this->_db->setQuery($query);
        $this->_db->query();

        if($suggestion->orphan_id)
        {
          $query->clear()
                ->update(_JOOM_TABLE_MAINTENANCE)
                ->set($file->type.'orphan = '.$file->id)
                ->where('id = '.$suggestion->orphan_id);
          $this->_db->setQuery($query);
          $this->_db->query();
        }
      }

      $index++;

      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.maintenance.check.index', $index);
        $refresher->refresh($total - $index);
      }

      // Set query for next loop
      $query->clear()
            ->select('id, type, fullpath')
            ->from(_JOOM_TABLE_ORPHANS)
            ->where("(type = 'thumb' OR type = 'img' OR type = 'orig')");
      $this->_db->setQuery($query, $index, 1);
    }

    // Prepare next step
    $query = $this->_db->getQuery(true)
          ->select('COUNT(id)')
          ->from(_JOOM_TABLE_ORPHANS)
          ->where("type = 'folder'");
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
    $this->_mainframe->setUserState('joom.maintenance.check.createfoldersuggestions', true);

    $refresher->reset($total, true, JText::_('COM_JOOMGALLERY_MAIMAN_CREATE_FOLDER_SUGGESTIONS'));

    // Next step
    $refresher->refresh(0, 'createfoldersuggestions');
  }

  /**
   * Creates suggestions for correcting problems with orphaned folders
   *
   * @return  void
   * @since   1.5.5
   */
  public function createFolderSuggestions()
  {
    $query = $this->_db->getQuery(true);

    $query->select('COUNT(id)')
          ->from(_JOOM_TABLE_ORPHANS)
          ->where("type = 'folder'");
    $this->_db->setQuery($query);
    $total = $this->_db->loadResult();

    $start = false;
    if(!$this->_mainframe->getUserState('joom.maintenance.check.createfoldersuggestions'))
    {
      $start = true;
      $this->_mainframe->setUserState('joom.maintenance.check.index', 0);
      $this->_mainframe->setUserState('joom.maintenance.check.createfoldersuggestions', true);
    }

    $refresher = new JoomRefresher( array('name'      => JText::_('COM_JOOMGALLERY_MAIMAN_CREATE_FOLDER_SUGGESTIONS'),
                                          'remaining' => $total,
                                          'start'     => $start)
                                  );

    $index = $this->_mainframe->getUserState('joom.maintenance.check.index');

    $query->clear('select')
          ->select('id, type, fullpath');
    $this->_db->setQuery($query, $index, 1);

    while($folder = $this->_db->loadObject())
    {
      $folder_name_parts = explode('_', $folder->fullpath);
      $count_parts  = count($folder_name_parts);
      if(!$count_parts)
      {
        $index++;
        $this->_db->setQuery($query, $index, 1);
        continue;
      }

      if(is_numeric($folder_name_parts[$count_parts - 1]))
      {
        $category_id  = (int) $folder_name_parts[$count_parts - 1];

        if(strpos($folder->fullpath, JPath::clean($this->_ambit->get('thumb_path'), '/')) !== false)
        {
          $type = 'thumb';
        }
        else
        {
          if(strpos($folder->fullpath, JPath::clean($this->_ambit->get('img_path'), '/')) !== false)
          {
            $type = 'img';
          }
          else
          {
            $type = 'orig';
          }
        }

        $query->clear()
              ->select('c.cid AS category_id')
              ->select('m.id AS orphan_id')
              ->select('c.name')
              ->from(_JOOM_TABLE_CATEGORIES.' AS c')
              ->leftJoin(_JOOM_TABLE_MAINTENANCE.' AS m ON c.cid = m.refid')
              ->where('c.cid = '.$category_id);

        $this->_db->setQuery($query);
        if($suggestion = $this->_db->loadObject())
        {
          $query->clear()
                ->update(_JOOM_TABLE_ORPHANS)
                ->set('refid = '.$suggestion->category_id)
                ->set("title = '".$suggestion->name."'")
                ->where('id = '.$folder->id);
          $this->_db->setQuery($query);
          $this->_db->query();

          if($suggestion->orphan_id)
          {
            $query->clear()
                  ->update(_JOOM_TABLE_MAINTENANCE)
                  ->set($type.'orphan = '.$folder->id)
                  ->where('id = '.$suggestion->orphan_id);
            $this->_db->setQuery($query);
            $this->_db->query();
          }
        }
      }

      $index++;

      if(!$refresher->check())
      {
        $this->_mainframe->setUserState('joom.maintenance.check.index', $index);
        $refresher->refresh($total - $index);
      }

      // Set query for next loop
      $query->clear()
            ->select('id, type, fullpath')
            ->from(_JOOM_TABLE_ORPHANS)
            ->where("type = 'folder'");
      $this->_db->setQuery($query, $index, 1);
    }

    // Finished
    $this->_mainframe->setUserState('joom.maintenance.checked', time());
    $refresher->reset(null, null, JText::_('COM_JOOMGALLERY_MAIMAN_FINALIZING'));
    $refresher->refresh(0, 'display');
  }
}