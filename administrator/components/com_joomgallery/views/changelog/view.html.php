<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/changelog/view.html.php $
// $Id: view.html.php 4232 2013-04-25 21:11:34Z erftralle $
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
 * HTML View class for the JoomGallery Changelog
 *
 * @package JoomGallery
 * @since   3.1
 */
class JoomGalleryViewChangelog extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   3.1
   */
  public function display($tpl = null)
  {
    $doc = JFactory::getDocument();

    $xml_file        = JPath::clean(JPATH_COMPONENT.'/changelog.xml');
    $this->changelog = simplexml_load_file($xml_file);

    parent::display($tpl);
  }
}