<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/config.php $
// $Id: config.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Configuration Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerConfig extends JoomGalleryController
{
  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    // Access check
    if(!JFactory::getUser()->authorise('core.admin', _JOOM_OPTION))
    {
      $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl(''), false), 'You are not allowed to configure this component', 'notice');
      $this->redirect();
    }

    // Register tasks
    $this->registerTask('new',        'edit');
    $this->registerTask('apply',      'save');
    $this->registerTask('orderup',    'order');
    $this->registerTask('orderdown',  'order');

    // Set view
    if($this->_config->isExtended())
    {
      JRequest::setVar('view', 'configs');
    }
    else
    {
      JRequest::setVar('view', 'config');
    }
  }

  /**
   * Displays the edit form of a config row
   *
   * @return  void
   * @since   2.0
   */
  public function edit()
  {
    $id  = JRequest::getInt('id');
    $cid = JRequest::getVar('cid', array(), 'post', 'array');

    if(!$id && count($cid) && $cid[0])
    {
      JRequest::setVar('id', (int) $cid[0]);
    }

    JRequest::setVar('view', 'config');
    JRequest::setVar('hidemainmenu', 1);

    parent::display();
  }

  /**
   * Saves the configuration
   *
   * @return  void
   * @since   1.5.5
   */
  public function save()
  {
    $config = JoomConfig::getInstance('admin');

    $id = false;
    $existing_row = 0;
    $group_id = 0;
    if($config->isExtended())
    {
      $id = JRequest::getInt('id');
      $existing_row = JRequest::getInt('based_on');

      if(!$id)
      {
        $group_id = JRequest::getInt('group_id');
      }
    }
    else
    {
      $id = 1;
    }

    $post = JRequest::get('post');

    if(!$id = $config->save($post, $id, $existing_row, $group_id))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_CONFIG_MSG_SETTINGS_ERROR'), 'error');

      return;
    }

    $propagate_changes = JRequest::getBool('propagate_changes');

    // The changes have to be propagated to the other config rows
    // if the default row was changed or if propagation is requested
    $success = true;
    if(!$id || $id == 1 || $propagate_changes)
    {
      $success = $this->getModel('configs')->propagateChanges($post, $id, $propagate_changes);
    }

    if($success)
    {
      $controller = '';
      if(!$config->isExtended())
      {
        if(JRequest::getCmd('task') == 'apply')
        {
          $controller = 'config';
        }
      }
      else
      {
        $controller = null;
      }
      $this->setRedirect($this->_ambit->getRedirectUrl($controller, $id, 'id'), JText::_('COM_JOOMGALLERY_CONFIG_MSG_SETTINGS_SAVED'));
    }
    else
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), $this->getModel('configs')->getError(), 'error');
    }
  }

  /**
   * Removes one or more config rows
   *
   * @return  void
   * @since   2.0
   */
  public function remove()
  {
    $config = JoomConfig::getInstance('admin');
    $cid    = JRequest::getVar('cid', array(), 'post', 'array');

    if(!count($cid))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_CONFIGS_NO_ROWS_SELECTED'), 'notice');

      return;
    }

    $count = 0;
    foreach($cid as $config_id)
    {
      if($config->delete($config_id))
      {
        $count++;
      }
      else
      {
        JError::raiseWarning(500, $config->getError());
      }
    }

    if(!$count)
    {
      $msg  = JText::_('COM_JOOMGALLERY_CONFIGS_MSG_ERROR_DELETING');
      $type = 'error';
    }
    else
    {
      $type = 'message';
      $msg  = JText::plural('COM_JOOMGALLERY_CONFIGS_MSG_ROWS_DELETED', $count);
    }

    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg, $type);
  }

  /**
   * Moves the order of a config row
   *
   * @return  void
   * @since   2.0
   */
  public function order()
  {
    $cid = JRequest::getVar('cid', array(), 'post', 'array');

    // Direction
    $dir  = 1;
    $task = JRequest::getCmd('task');
    if($task == 'orderup')
    {
      $dir = -1;
    }

    if(isset($cid[0]))
    {
      $row = JTable::getInstance('joomgalleryconfig', 'Table');
      $row->load((int)$cid[0]);
      $row->move($dir);
      //$row->reorder();
    }

    $this->setRedirect($this->_ambit->getRedirectUrl());
  }

  /**
   * Saves the order of the config rows
   *
   * @return  void
   * @since   2.0
   */
  public function saveOrder()
  {
    $cid    = JRequest::getVar('cid', array(0), 'post', 'array');
    $order  = JRequest::getVar('order', array(0), 'post', 'array');

    // Create and load the categories table object
    $row = JTable::getInstance('joomgalleryconfig', 'Table');

    // Update the ordering for items in the cid array
    for($i = 0; $i < count($cid); $i ++)
    {
      $row->load((int)$cid[$i]);
      if($row->ordering != $order[$i])
      {
        $row->ordering = $order[$i];
        $row->check();
        if(!$row->store())
        {
          JError::raiseError( 500, $this->_db->getErrorMsg());

          return false;
        }
      }
    }

    //$row->reorder();

    $msg = JText::_('COM_JOOMGALLERY_COMMON_MSG_NEW_ORDERING_SAVED');
    $this->setRedirect($this->_ambit->getRedirectUrl(), $msg);
  }

  /**
   * Cancel editing or creating a config row
   *
   * @access  public
   * @return  void
   * @since   2.0
   */
  function cancel()
  {
    $this->setRedirect($this->_ambit->getRedirectUrl());
  }
}