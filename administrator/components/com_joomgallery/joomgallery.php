<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/joomgallery.php $
// $Id: joomgallery.php 4385 2014-05-11 11:48:49Z erftralle $
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

JHtml::_('behavior.tabstate');

if(version_compare(JVERSION, '4.0', 'ge') || version_compare(JVERSION, '3.0', 'lt'))
{
  JToolBarHelper::title('JoomGallery');

  return JError::raiseWarning(500, 'JoomGallery 3.x is only compatible to Joomla! 3.x');
}

// Require the base controller and the defines
require_once(JPATH_COMPONENT.'/controller.php');
require_once(JPATH_COMPONENT.'/includes/defines.php');

// Access check
if(!JFactory::getUser()->authorise('core.manage', _JOOM_OPTION))
{
  return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Enable JoomGallery plugins
JPluginHelper::importPlugin('joomgallery');

// Require specific controller if requested
if($controller = JRequest::getCmd('controller', 'control'))
{
  $format = JRequest::getCmd('format', 'html');
  $path = JPATH_COMPONENT.'/controllers/'.$controller.(($format != 'html') ?  '.'.$format : '').'.php';
  if(file_exists($path))
  {
    require_once $path;
  }
  else
  {
    $controller = '';
  }
}

// Register some classes
JLoader::register('JoomGalleryModel', JPATH_COMPONENT.'/model.php');
JLoader::register('JoomGalleryView',  JPATH_COMPONENT.'/view.php');
JLoader::register('JoomExtensions',   JPATH_COMPONENT.'/helpers/extensions.php');
JLoader::register('JoomHelper',       JPATH_COMPONENT.'/helpers/helper.php');
JLoader::register('JoomConfig',       JPATH_COMPONENT.'/helpers/config.php');
JLoader::register('JoomAmbit',        JPATH_COMPONENT.'/helpers/ambit.php');
JLoader::register('JoomFile',         JPATH_COMPONENT.'/helpers/file.php');
JTable::addIncludePath(               JPATH_COMPONENT.'/tables');

// Create the controller
$classname  = 'JoomGalleryController'.$controller;
$controller = new $classname();

// Perform the request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();