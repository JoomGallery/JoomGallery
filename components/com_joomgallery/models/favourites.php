<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/favourites.php $
// $Id: favourites.php 4331 2013-09-08 08:27:42Z erftralle $
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
 * JoomGallery Favourites Model
 *
 * Handles the favourites of a user and the zip download
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelFavourites extends JoomGalleryModel
{
  /**
   * The ID of the image to work with
   *
   * @var   int
   * @since 1.5.5
   */
  protected $_id;

  /**
   * A comma separated list of favoured images
   *
   * @var   string
   * @since 1.5.5
   */
  protected $piclist;

  /**
   * Determines whether the database is used or the session to store the images
   *
   * @var   boolean
   * @since 1.5.5
   */
  protected $using_database;

  /**
   * Determines whether the current user already has an entry
   * in the database table for the favourites and the zip download
   *
   * @var   boolean
   * @since 1.5.5
   */
  protected $user_exists;

  /**
   * Holds the current layout
   *
   * @var   string
   * @since 1.5.5
   */
  protected $layout;

  /**
   * Holds the prefix of the language constants for the favourites
   *
   * @var   string
   * @since 1.5.5
   */
  protected $_output;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.0.0
   */
  public function __construct()
  {
    parent::__construct();

    // Check access rights
    if(   !$this->_config->get('jg_favourites')
       || (!$this->_config->get('jg_usefavouritesforpubliczip') && !$this->_user->get('id'))
      )
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_PERMISSION_DENIED'), 'notice');
    }

    // Set the image id
    $view = JRequest::getCmd('view');
    $task = JRequest::getCmd('task');
    if(   $view != 'favourites'
      &&  $view != 'downloadzip'
      &&  $task != 'removeall'
      &&  $task != 'switchlayout'
      &&  $task != 'createzip'
      &&  $task != 'addimages'
      )
    {
      $id = JRequest::getInt('id');
      $this->setId($id);
    }

    // Check whether we will work with the database or the session
    if($this->_user->get('id') && $this->_config->get('jg_usefavouritesforzip') != 1)
    {
      $this->using_database = true;
      $this->_output        = 'COM_JOOMGALLERY_FAVOURITES_MSG_';

      $query = $this->_db->getQuery(true)
            ->select('piclist, layout')
            ->from(_JOOM_TABLE_USERS)
            ->where('uuserid = '.$this->_user->get('id'));
      $this->_db->setQuery($query);

      if($row = $this->_db->loadObject())
      {
        $this->user_exists  = true;
        $this->piclist      = $row->piclist;
        $this->layout       = $row->layout;
      }
      else
      {
        $this->user_exists  = false;
        $this->piclist      = null;
        $this->layout       = 0;
      }
    }
    else
    {
      $this->using_database = false;
      $this->_output        = 'COM_JOOMGALLERY_FAVOURITES_ZIP_MSG_';

      $this->piclist = $this->_mainframe->getUserState('joom.favourites.pictures');
      $this->layout  = $this->_mainframe->getUserState('joom.favourites.layout');
    }
  }

  /**
   * Method to set the image id
   *
   * @param   int   Image ID number
   * @return  void
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new image ID if valid
    if(!$id)
    {
      JError::raiseError(500, JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'));
    }
    $this->_id  = $id;
  }

  /**
   * Method to get the identifier
   *
   * @return  int   The image ID
   * @since   1.5.5
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Method to get the current layout
   *
   * @return  string  The name of the current layout
   * @since   1.5.5
   */
  public function getLayout()
  {
    return $this->layout;
  }

  /**
   * Method to add an image to the favourites or the zip download
   *
   * @return  boolean True on success, false otherwise
   * @since   1.0.0
   */
  public function addImage()
  {
    $query = $this->_db->getQuery(true)
          ->select('id')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON a.catid = c.cid')
          ->where('a.id = '.$this->_id)
          ->where('a.approved = 1')
          ->where('a.published = 1')
          ->where('a.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.published = 1');
    $this->_db->setQuery($query);
    if(!$this->_db->loadResult())
    {
      $this->setError('Stop Hacking attempt!');

      return false;
    }

    if(is_null($this->piclist))
    {
      if($this->using_database)
      {
        if($this->user_exists)
        {
          $query->clear()
                ->update(_JOOM_TABLE_USERS)
                ->set('piclist = '.$this->_db->q($this->_id))
                ->where('uuserid = '.$this->_user->get('id'));
        }
        else
        {
          $query->clear()
                ->insert(_JOOM_TABLE_USERS)
                ->columns('uuserid, piclist')
                ->values($this->_user->get('id').', '.$this->_db->q($this->_id));
        }

        $this->_db->setQuery($query);
        $return = $this->_db->query();
      }
      else
      {
        $this->_mainframe->setUserState('joom.favourites.pictures', $this->_id);
      }
    }
    else
    {
      $piclist_array = explode(',', $this->piclist);

      if(in_array($this->_id, $piclist_array))
      {
        // Image is already in there
        $this->_mainframe->enqueueMessage($this->output('ALREADY_IN'));

        return true;
      }
      if($this->_config->get('jg_maxfavourites') && count($piclist_array) >= $this->_config->get('jg_maxfavourites'))
      {
        // Maximum number of images already reached
        $this->setError($this->output('ALREADY_MAX'));

        return false;
      }

      if($this->using_database)
      {
        $query->clear()
              ->update(_JOOM_TABLE_USERS)
              ->set('piclist = '.$this->_db->q($this->piclist.', '.$this->_id))
              ->where('uuserid = '.$this->_user->get('id'));
        $this->_db->setQuery($query);
        $return = $this->_db->query();
      }
      else
      {
        $this->_mainframe->setUserState('joom.favourites.pictures', $this->piclist.','.$this->_id);
      }
    }

    if(isset($return) && !$return)
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    $this->_mainframe->enqueueMessage($this->output('SUCCESSFULLY_ADDED'));

    $this->_mainframe->triggerEvent('onJoomAfterAddFavourite', array($this->_id));

    return true;
  }

  /**
   * Method to add all images of a category (but none of the sub-categories) to the favourites or the zip download
   *
   * @param   int     The ID of the category from which all images should be added to the favourites or the zip download
   * @return  boolean True on success, false otherwise
   * @since   1.0.0
   */
  public function addImages($catid)
  {
    $query = $this->_db->getQuery(true)
          ->select('id')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON a.catid = c.cid')
          ->where('c.cid = '.(int) $catid)
          ->where('a.approved = 1')
          ->where('a.published = 1')
          ->where('a.hidden = 0')
          ->where('a.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.access IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.published = 1');
    $this->_db->setQuery($query);
    if(!$images = $this->_db->loadColumn())
    {
      $this->setError(JText::_('COM_JOOMGALLERY_FAVOURITES_NO_IMAGES_TO_ADD'));

      return false;
    }

    if(is_null($this->piclist))
    {
      if($this->_config->get('jg_maxfavourites') && count($images) > $this->_config->get('jg_maxfavourites'))
      {
        $this->setError($this->output('WOULD_EXCEED'));

        return false;
      }

      if($this->using_database)
      {
        if($this->user_exists)
        {
          $query->clear()
                ->update(_JOOM_TABLE_USERS)
                ->set('piclist = '.$this->_db->q(implode(',', $images)))
                ->where('uuserid = '.$this->_user->get('id'));
        }
        else
        {
          $query->clear()
                ->insert(_JOOM_TABLE_USERS)
                ->columns('uuserid, piclist')
                ->values($this->_user->get('id').', '.$this->_db->q(implode(',', $images)));
        }

        $this->_db->setQuery($query);
        $return = $this->_db->query();
      }
      else
      {
        $this->_mainframe->setUserState('joom.favourites.pictures', implode(',', $images));
      }
    }
    else
    {
      $piclist_array = explode(',', $this->piclist);

      $images = array_diff($images, $piclist_array);

      // If there is nothing in the difference we don't have to do anything
      if(!count($images))
      {
        return true;
      }

      $new_piclist_array = array_unique(array_merge($piclist_array, $images));

      if($this->_config->get('jg_maxfavourites') && count($new_piclist_array) > $this->_config->get('jg_maxfavourites'))
      {
        // Maximum number of images already reached
        $this->_mainframe->enqueueMessage($this->output('WOULD_EXCEED'));

        return false;
      }

      if($this->using_database)
      {
        $query->clear()
              ->update(_JOOM_TABLE_USERS)
              ->set('piclist = '.$this->_db->q(implode(',', $new_piclist_array)))
              ->where('uuserid = '.$this->_user->get('id'));
        $this->_db->setQuery($query);
        $return = $this->_db->query();
      }
      else
      {
        $this->_mainframe->setUserState('joom.favourites.pictures', implode(',', $new_piclist_array));
      }
    }

    if(isset($return) && !$return)
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    $this->_mainframe->triggerEvent('onJoomAfterAddFavourite', $images);

    return true;
  }

  /**
   * Method to remove an image from the favourites or the zip download
   *
   * @return  boolean True on success, false otherwise
   * @since   1.0.0
   */
  public function removeImage()
  {
    $piclist = explode(',', $this->piclist);
    if(!in_array($this->_id, $piclist))
    {
      $this->setError($this->output('NOT_IN'));

      return false;
    }

    $new_piclist = array();
    foreach($piclist as $picid)
    {
      if($picid != $this->_id)
      {
        array_push($new_piclist, $picid);
      }
    }

    $query = $this->_db->getQuery(true);

    if(!count($new_piclist))
    {
      $new_piclist = null;
      $query->set('piclist = NULL');
    }
    else
    {
      $new_piclist = implode(',', $new_piclist);
      $query->set('piclist = '.$this->_db->q($new_piclist));
    }

    if($this->using_database)
    {
      $query->update(_JOOM_TABLE_USERS)
            ->where('uuserid = '.$this->_user->get('id'));
      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($this->_db->getErrorMsg());

        return false;
      }
    }
    else
    {
      $this->_mainframe->setUserState('joom.favourites.pictures', $new_piclist);
    }

    $this->_mainframe->triggerEvent('onJoomAfterRemoveFavourite', array($this->_id));

    return true;
  }

  /**
   * Method to remove all images from the favourites or the zip download
   *
   * @return  boolean True on success, false otherwise
   * @since   1.0.0
   */
  public function removeAll()
  {
    if($this->using_database)
    {
      $query = $this->_db->getQuery(true)
            ->update(_JOOM_TABLE_USERS)
            ->set('piclist = NULL')
            ->where('uuserid = '.$this->_user->get('id'));
      $this->_db->setQuery($query);
      if(!$this->_db->query())
      {
        $this->setError($this->_db->getErrorMsg());

        return false;
      }
    }
    else
    {
      $this->_mainframe->setUserState('joom.favourites.pictures', null);
    }

    $this->_mainframe->triggerEvent('onJoomAfterClearFavourites');

    return true;
  }

  /**
   * Method to switch the current layout, currently switches the layout
   * only if the user has already added some images to his list
   *
   * @return  boolean True
   * @since   1.0.0
   */
  public function switchLayout()
  {
    $layout = JRequest::getCmd('layout');
    if(
        ($layout && $layout == 'list')
      ||
         $this->layout
      )
    {
      $new_layout = 0;
    }
    else
    {
      $new_layout = 1;
    }

    if($this->using_database)
    {
      $query = $this->_db->getQuery(true)
            ->update(_JOOM_TABLE_USERS)
            ->set('layout = '.$new_layout)
            ->where('uuserid = '.$this->_user->get('id'));
      $this->_db->setQuery($query);
      $this->_db->query();
    }
    else
    {
      $this->_mainframe->setUserState('joom.favourites.layout', $new_layout);
    }

    return true;
  }

  /**
   * Method to create the zip archive with all selected images
   *
   * @return  boolean True on success, false otherwise
   * @since   1.0.0
   */
  public function createZip()
  {
    jimport('joomla.filesystem.file');
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.archive');

    $zip_adapter = JArchive::getAdapter('zip');

    // Check whether zip download is allowed
    if(    !$this->_config->get('jg_zipdownload')
        && ($this->_user->get('id') || !$this->_config->get('jg_usefavouritesforpubliczip'))
      )
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false), JText::_('COM_JOOMGALLERY_FAVOURITES_MSG_NOT_ALLOWED'), 'notice');
    }

    if(is_null($this->piclist))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false), $this->output('NO_IMAGES'), 'notice');
    }

    $query = $this->_db->getQuery(true)
          ->select('id')
          ->select('catid')
          ->select('imgfilename')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->from(_JOOM_TABLE_CATEGORIES.' AS c')
          ->where('id IN ('.$this->piclist.')')
          ->where('a.catid      = c.cid')
          ->where('a.published  = 1')
          ->where('a.approved   = 1')
          ->where('c.published  = 1')
          ->where('a.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')');

    $this->_db->setQuery($query);
    $rows = $this->_db->loadObjectList();

    if(!count($rows))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false), $this->output('NO_IMAGES'), 'notice');
    }

    // Name of the zip archive
    $zipname = 'components/'._JOOM_OPTION.'/joomgallery_'.date('d_m_Y').'__';
    if($userid = $this->_user->get('id'))
    {
      $zipname .= $userid.'_';
    }
    $zipname .= mt_rand(10000, 99999).'.zip';

    $files  = array();

    if($this->_config->get('jg_downloadwithwatermark'))
    {
      $include_watermark = true;

      // Get the 'image' model
      $imageModel = parent::getInstance('image', 'joomgallerymodel');

      // Get the temp path for storing the watermarked image temporarily
      if(!JFolder::exists($this->_ambit->get('temp_path')))
      {
        $this->setError(JText::_('COM_JOOMGALLERY_UPLOAD_ERROR_TEMP_MISSING'));

        return false;
      }
      else
      {
        $tmppath = $this->_ambit->get('temp_path');
      }
    }
    else
    {
      $include_watermark = false;
    }

    $categories = $this->_ambit->getCategoryStructure();
    foreach($rows as &$row)
    {
      if(!isset($categories[$row->catid]))
      {
        continue;
      }

      // Get the original image if existent, otherwise the detail image
      $orig = $this->_ambit->getImg('orig_path', $row->id);
      $img = $this->_ambit->getImg('img_path', $row->id);

      if(file_exists($orig))
      {
        $image = $orig;
      }
      else if(file_exists($img))
      {
        $image = $img;
      }
      else
      {
        $image = null;
        continue;
      }
      $files[$row->id]['name'] = $row->imgfilename;

      // Watermark the image before if needed
      if($include_watermark)
      {
        // Get the image resource of watermarked image
        $imgres = $imageModel->includeWatermark($image);

        // Start output buffering
        ob_start();

        // According to mime type output the watermarked image resource to file
        $info = getimagesize($image);
        switch($info[2])
        {
          case 1:
            imagegif($imgres);
            break;
          case 2:
            imagejpeg($imgres);
            break;
          case 3:
            imagepng($imgres);
            break;
          default:
            JError::raiseError(404, JText::sprintf('COM_JOOMGALLERY_COMMON_MSG_MIME_NOT_ALLOWED', $mime));
            break;
        }

        // Read the content from output buffer and fill the array element
        $files[$row->id]['data'] = ob_get_contents();

        // Delete the output buffer
        ob_end_clean();
      }
      else
      {
        $files[$row->id]['data'] = JFile::read($image);
      }

      // Increase download counter for that image
      $this->download($row->id);
    }

    if(!count($files))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false), $this->output('NO_IMAGES'), 'notice');
    }

    // Trigger event 'onJoomBeforeZipDownload'
    $plugins = $this->_mainframe->triggerEvent('onJoomBeforeZipDownload', array(&$files));
    if(in_array(false, $plugins, true))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=favourites', false));
    }

    $createzip = $zip_adapter->create($zipname, $files);

    if(!$createzip)
    {
      // Workaround for servers with wwwwrun problem
      JoomFile::chmod(JPATH_COMPONENT, '0777', true);
      $createzip = $zip_adapter->create( $zipname, $files, 'zip');
      JoomFile::chmod(JPATH_COMPONENT, '0755', true);
    }

    if(!$createzip)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_FAVOURITES_ERROR_CREATEZIP'));

      return false;
    }

    if($this->_user->get('id'))
    {
      if($this->user_exists)
      {
        $query = $this->_db->getQuery(true)
              ->select('zipname')
              ->from(_JOOM_TABLE_USERS)
              ->where('uuserid = '.$this->_user->get('id'));

        $this->_db->setQuery($query);

        if($old_zip = $this->_db->loadResult())
        {
          if(file_exists($old_zip))
          {
            jimport('joomla.filesystem.file');
            JFile::delete($old_zip);
          }
        }
        $query = $this->_db->getQuery(true)
              ->update(_JOOM_TABLE_USERS)
              ->set('time = NOW()')
              ->set('zipname = '.$this->_db->q($zipname))
              ->where('uuserid = '.$this->_user->get('id'));

        $this->_db->setQuery($query);
      }
      else
      {
        $query = $this->_db->getQuery(true)
              ->insert(_JOOM_TABLE_USERS)
              ->set('uuserid = '.$this->_user->get('id'))
              ->set('time    = NOW()')
              ->set('zipname = '.$this->_db->q($zipname));

        $this->_db->setQuery($query);
      }
    }
    else
    {
      $query = $this->_db->getQuery(true)
            ->insert(_JOOM_TABLE_USERS)
            ->set('time = NOW()')
            ->set('zipname = '.$this->_db->q($zipname));

      $this->_db->setQuery($query);
    }
    $this->_db->query();

    $this->_mainframe->setUserState('joom.favourites.zipname', $zipname);

    // Message about new zip download
    if(!$this->_user->get('username'))
    {
      $username = JText::_('COM_JOOMGALLERY_COMMON_GUEST');
    }
    else
    {
      $username = $this->_config->get('jg_realname') ? $this->_user->get('name') : $this->_user->get('username');
    }

    if($this->_config->get('jg_msg_zipdownload'))
    {
      $imagefiles = implode(",\n", $files);
      require_once JPATH_COMPONENT.'/helpers/messenger.php';
      $messenger    = new JoomMessenger();
      $message      = array(
                            'subject'   => JText::_('COM_JOOMGALLERY_MESSAGE_NEW_ZIPDOWNLOAD_SUBJECT'),
                            'body'      => JText::sprintf('COM_JOOMGALLERY_MESSAGE_NEW_ZIPDOWNLOAD_BODY',
                                           $zipname, $username, $imagefiles),
                            'mode'      => 'zipdownload'
                            );
      $messenger->send($message);
    }

    return true;
  }

  /**
   * Method to get all the favourites of the current user
   *
   * @return  array An array of images data
   * @since   1.5.5
   */
  public function getFavourites()
  {
    if($this->_loadFavourites())
    {
      return $this->_favourites;
    }

    return array();
  }

  /**
   * Method to increment the download counter for an image.
   *
   * @param   int     $imgid  Image Id.
   * @return  boolean True on success, false otherwise.
   * @since   3.1
   */
  protected function download($imgid)
  {
    if($imgid)
    {
      $image = $this->getTable('joomgalleryimages');
      $image->download($imgid);
      return true;
    }

    return false;
  }

  /**
   * Method to auto-populate the model state.
   *
   * This method should only be called once per instantiation and is designed
   * to be called on the first call to the getState() method unless the model
   * configuration flag to ignore the request is set.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  $ordering   An optional ordering field.
   * @param   string  $direction  An optional direction (asc|desc).
   * @return  void
   * @since   3.0
   */
  protected function populateState($ordering = 'imgtitle', $direction = 'asc')
  {
    $filter_fields = array( 'imgtitle',
                            'hits',
                            'downloads',
                            'catid'
                          );

    // Check if the ordering field is in the white list, otherwise use the incoming value
    $value = $this->_mainframe->getUserStateFromRequest('joom.favourites.ordercol', 'filter_order', $ordering);
    if(!in_array($value, $filter_fields))
    {
      $value = $ordering;
      $this->_mainframe->setUserState('joom.favourites.ordercol', $value);
    }
    $this->setState('list.ordering', $value);

    // Check if the ordering direction is valid, otherwise use the incoming value
    $value = $this->_mainframe->getUserStateFromRequest('joom.favourites.orderdirn', 'filter_order_Dir', $direction);
    if(!in_array(strtoupper($value), array('ASC', 'DESC', '')))
    {
      $value = $direction;
      $this->_mainframe->setUserState('joom.favourites.orderdirn', strotoupper($value));
    }
    $this->setState('list.direction', $value);
  }

  /**
   * Method to load the image data from the database
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  protected function _loadFavourites()
  {
    // Load the images if they don't already exist
    if(empty($this->_favourites))
    {
      $query = $this->_db->getQuery(true)
            ->select('*, a.catid, a.owner AS imgowner, '.JoomHelper::getSQLRatingClause('a').' AS rating');
      if($this->_config->get('jg_showcatcom'))
      {
        $subquery = $this->_db->getQuery(true)
                  ->select('COUNT(*)')
                  ->from(_JOOM_TABLE_COMMENTS)
                  ->where('cmtpic = a.id')
                  ->where('published = 1')
                  ->where('approved = 1');
        $query->select('('.$subquery.') AS comments');
      }
      $query->from(_JOOM_TABLE_IMAGES.' AS a')
            ->from(_JOOM_TABLE_CATEGORIES.' AS c')
            ->where('a.catid = c.cid')
            ->where('a.published  = 1')
            ->where('a.approved   = 1')
            ->where('c.published  = 1')
            ->where('a.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('c.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
            ->where('c.hidden     = 0')
            ->where('c.in_hidden  = 0');

      if(is_null($this->piclist))
      {
        $query->where('0');
      }
      else
      {
        $query->where('a.id IN ('.$this->piclist.')');
      }

      $query->order($this->_db->escape($this->getState('list.ordering').' '.$this->getState('list.direction')));
      $this->_db->setQuery($query);

      $rows = $this->_db->loadObjectList();
      if($error = $this->_db->getErrorMsg())
      {
        $this->setError($error);

        return false;
      }

      $categories = $this->_ambit->getCategoryStructure();
      foreach($rows as $key => $row)
      {
        if(!isset($categories[$row->catid]))
        {
          unset($rows[$key]);
        }
      }

      $this->_favourites = $rows;

      // The list of favourites is filtered now, so that only valid images are chosen.
      // So we store this list now in order to delete invalid images from the list.
      if($this->using_database)
      {
        $ids = '';
        foreach($rows as $row)
        {
          $ids .= $row->id.',';
        }
        $query->clear()
              ->update(_JOOM_TABLE_USERS)
              ->set('piclist = '.((count($rows)) ? $this->_db->q(trim($ids, ',')) : 'NULL'))
              ->where('uuserid = '.$this->_user->get('id'));
        $this->_db->setQuery($query);
        $this->_db->query();
      }

      return true;
    }
  }

  /**
   * Returns a language string depending on the used mode for the zip download
   *
   * @param   string  The main part of the language constant to use
   * @return  string  The translated string of the selected and completed language constant
   * @since   1.5.5
   */
  public function output($msg)
  {
    return JText::_($this->_output.$msg);
  }
}