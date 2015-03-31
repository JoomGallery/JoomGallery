<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controller.php $
// $Id: controller.php 4076 2013-02-12 10:35:29Z erftralle $
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
   * @access  protected
   * @var     object
   */
  #var $_mainframe;

  /**
   * JoomConfig object
   *
   * @access  protected
   * @var     object
   */
  var $_config;

  /**
   * JoomAmbit object
   *
   * @access  protected
   * @var     object
   */
  var $_ambit;

  /**
   * JUser object, holds the current user data
   *
   * @access  protected
   * @var     object
   */
  #var $_user;

  /**
   * JDatabase object
   *
   * @access  protected
   * @var     object
   */
  var $_db;

  /**
   * Constructor
   *
   * @access  protected
   * @return  void
   * @since   1.5.5
   */
  function __construct($config = array())
  {
    parent::__construct($config);

    $this->_ambit     = JoomAmbit::getInstance();
    $this->_config    = JoomConfig::getInstance();

    /*$this->_mainframe = JFactory::getApplication('site');
    $this->_user      = JFactory::getUser();*/
    $this->_db        = JFactory::getDBO();

    // Uncomment following line to disable update check
    // $this->_config->set('jg_checkupdate', 0);
  }
}