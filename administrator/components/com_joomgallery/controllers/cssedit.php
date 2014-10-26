<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/controllers/cssedit.php $
// $Id: cssedit.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery CSS Edit Controller
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryControllerCssedit extends JoomGalleryController
{
  /**
   * Holds the name and the path to the CSS file to edit ('joom_local.css')
   *
   * @var     string
   * @access  public
   */
  var $file;

  /**
   * Constructor
   *
   * @access  protected
   * @return  void
   * @since   1.5.5
   */
  function __construct()
  {
    parent::__construct();

    // Access check
    if(!JFactory::getUser()->authorise('core.admin', _JOOM_OPTION))
    {
      $this->setRedirect(JRoute::_($this->_ambit->getRedirectUrl(''), false), 'You are not allowed to configure this component', 'notice');
      $this->redirect();
    }

    // Set view
    JRequest::setVar('view', 'cssedit');

    $this->file = JPATH_ROOT.'/media/joomgallery/css/joom_local.css';

    // Register task
    $this->registerTask('apply', 'save');
  }

  /**
   * Saves the CSS file
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function save()
  {
    jimport('joomla.filesystem.file');

    $content  = stripcslashes(JRequest::getVar('csscontent'));

    if(!$content)
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_CSSMAN_MSG_EMPTY'), 'notice');
      return;
    }

    if(JFile::write($this->file, $content))
    {
      $controller = '';
      if(JRequest::getCmd('task') == 'apply')
      {
        $controller = 'cssedit';
      }

      $this->setRedirect($this->_ambit->getRedirectUrl($controller), JText::_('COM_JOOMGALLERY_CSSMAN_MSG_CSS_SAVED'));

    }
    else
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(), JText::_('COM_JOOMGALLERY_CSSMAN_CSS_ERROR_WRITING').$this->file, 'error');
    }
  }

  /**
   * Deletes CSS file
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function remove()
  {
    jimport('joomla.filesystem.file');

    if(JFile::delete($this->file))
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(''), JText::_('COM_JOOMGALLERY_CSSMAN_MSG_CSS_DELETED'));
    }
    else
    {
      $this->setRedirect($this->_ambit->getRedirectUrl(''), JText::_('COM_JOOMGALLERY_CSSMAN_CSS_ERROR_DELETING').$this->file, 'error');
    }
  }

  /**
   * Cancel editing or creating the CSS file
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function cancel()
  {
    $this->setRedirect($this->_ambit->getRedirectUrl(''));
  }
}