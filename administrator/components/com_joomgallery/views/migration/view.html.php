<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/migration/view.html.php $
// $Id: view.html.php 4361 2014-02-24 18:03:18Z erftralle $
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
 * HTML View class for the migration manager view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewMigration extends JoomGalleryView
{
  /**
   * The output of the migration script
   *
   * @var   string
   * @since 3.1
   */
  protected $output;

  /**
   * HTML view display method
   *
   * @access  public
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  function display($tpl = null)
  {
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_MIGMAN_MIGRATION_MANAGER'), 'loop');

    $this->sidebar = JHtmlSidebar::render();

    $this->output = $this->_mainframe->getUserState('joom.migration.output', null);
    $this->_mainframe->setUserState('joom.migration.output', null);

    $this->files = JFolder::files(JPATH_COMPONENT.'/helpers/migration', '.php$', false, true);

    parent::display($tpl);
  }
}