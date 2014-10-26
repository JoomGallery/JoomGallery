<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controller.php $
// $Id: controller.php 4157 2013-03-30 02:41:56Z chraneco $
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

jimport('joomla.application.component.controller');

/**
 * JoomGallery Component Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryController extends JControllerLegacy
{
  /**
   * JApplication object
   *
   * @var   object
   */
  protected $_mainframe;

  /**
   * JoomConfig object
   *
   * @var   object
   */
  protected $_config;

  /**
   * JoomAmbit object
   *
   * @var   object
   */
  protected $_ambit;

  /**
   * JUser object, holds the current user data
   *
   * @var   object
   */
  protected $_user;

  /**
   * JDatabase object
   *
   * @var   object
   */
  protected $_db;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->_ambit     = JoomAmbit::getInstance();
    $this->_config    = JoomConfig::getInstance();

    /*$this->_mainframe = JFactory::getApplication('site');
    $this->_user      = JFactory::getUser();*/
  }

  /**
   * Parent display method for all views
   *
   * @param   boolean $cachable   If true, the view output will be cached
   * @param   array   $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
   * @return  void
   * @since   1.5.5
   */
  public function display($cachable = false, $urlparams = false)
  {
    // Set a default view if none exists
    if(!$view = JRequest::getCmd('view'))
    {
      JRequest::setVar('view', 'gallery');
    }

    // Increase hit counter in detail view if image view isn't used
    if($view == 'detail' && $this->_config->get('jg_use_real_paths'))
    {
      $this->getModel('image')->hit(JRequest::getInt('id'));
    }

    parent::display($cachable, $urlparams);

    // Possibly delete zips
    if(   $view == 'favourites'
      &&
          $this->_config->get('jg_favourites')
      &&
        (   $this->_config->get('jg_zipdownload')
        ||  $this->_config->get('jg_usefavouritesforpubliczip')
        )
      )
    {
      $this->_db = JFactory::getDBO();
      $query = $this->_db->getQuery(true)
            ->select('uid, uuserid, zipname')
            ->from(_JOOM_TABLE_USERS)
            ->where('zipname != '.$this->_db->q(''))
            ->where('time != '.$this->_db->q(''))
            ->where('time < NOW()-INTERVAL 60 SECOND');
      $this->_db->setQuery($query);
      $ziprows = $this->_db->loadObjectList();
      if(count($ziprows))
      {
        jimport('joomla.filesystem.file');
        foreach($ziprows as $row)
        {
          if(JFile::exists($row->zipname))
          {
            JFile::delete($row->zipname);
          }
          if($row->uuserid != 0)
          {
            $query->clear()
                  ->update(_JOOM_TABLE_USERS)
                  ->set('zipname = '.$this->_db->q(''))
                  ->set('time = '.$this->_db->q(''))
                  ->where('uid = '.(int) $row->uid);
            $this->_db->setQuery($query);
          }
          else
          {
            $query->clear()
                  ->delete(_JOOM_TABLE_USERS)
                  ->where('uuserid = 0')
                  ->where('zipname = '.$this->_db->q($row->zipname));
            $this->_db->setQuery($query);
          }
          $this->_db->query();
        }
      }
    }
  }

  /**
   * Redirects to the image view with the download parameter set to 1.
   * The image will be offered as a downloadable file then.
   *
   * @return  void
   * @since   1.5.5
   */
  public function download()
  {
    // Check permissions
    if(   !$this->_config->get('jg_download')
      ||  (!$this->_config->get('jg_download_unreg') && !JFactory::getUser()->get('id'))
      )
    {
      $this->setRedirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_VIEW_IMAGE'), 'notice');

      return;
    }

    $type = $this->_config->get('jg_downloadfile') ? 'orig' : 'img';

    $this->setRedirect(JRoute::_('index.php?view=image&format=raw&id='.JRequest::getInt('id').'&download=1&type='.$type, false));
  }
}