<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/joomgallery.php $
// $Id: joomgallery.php 4325 2013-09-03 21:00:28Z chraneco $
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

if(version_compare(JVERSION, '4.0', 'ge') || version_compare(JVERSION, '3.0', 'lt'))
{
  return JError::raiseWarning(500, 'JoomGallery 3.x is only compatible to Joomla! 3.x');
}

// Require the defines
require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/defines.php';

// Enable JoomGallery plugins
JPluginHelper::importPlugin('joomgallery');

// Register some classes
JLoader::register('JoomGalleryModel', JPATH_COMPONENT.'/model.php');
JLoader::register('JoomGalleryView',  JPATH_COMPONENT.'/view.php');
JLoader::register('JoomHelper',       JPATH_COMPONENT.'/helpers/helper.php');
JLoader::register('JoomAmbit',        JPATH_COMPONENT.'/helpers/ambit.php');
JLoader::register('JoomConfig',       JPATH_COMPONENT_ADMINISTRATOR.'/helpers/config.php');
JLoader::register('JoomFile',         JPATH_COMPONENT_ADMINISTRATOR.'/helpers/file.php');
JTable::addIncludePath(               JPATH_COMPONENT_ADMINISTRATOR.'/tables');

// Create the controller
jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('JoomGallery');

// Perform the request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();