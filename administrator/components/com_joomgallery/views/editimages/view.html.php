<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/editimages/view.html.php $
// $Id: view.html.php 4361 2014-02-24 18:03:18Z erftralle $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * HTML View class for the images edit view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewEditimages extends JoomGalleryView
{
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
    $this->items = $this->get('Images');

    $this->cids = $this->_mainframe->input->get('cid', array(), 'array');
    $this->cids = implode(',', $this->cids);

    // Get the form and fill the fields
    $this->form = $this->get('Form');

    // Set maximum allowed user count to switch from listbox to modal popup selection
    $this->form->setFieldAttribute('owner', 'useListboxMaxUserCount', $this->_config->get('jg_use_listbox_max_user_count'));

    // Bind the data to the form
    $this->form->bind($this->items[0]);

    // Set some form fields manually
    $this->form->setValue('txtclearhits', null, JText::_('COM_JOOMGALLERY_IMGMAN_CLEAR_HITS_FOR_ALL_IMAGES'));
    $this->form->setValue('txtclearvotes', null, JText::_('COM_JOOMGALLERY_IMGMAN_CLEAR_VOTES_FOR_ALL_IMAGES'));
    $this->form->setValue('txtcleardownloads', null, JText::_('COM_JOOMGALLERY_IMGMAN_CLEAR_DOWNLOADS_FOR_ALL_IMAGES'));

    $this->addToolbar();

    parent::display($tpl);
  }

  /**
   * Add the page title and toolbar.
   *
   * @access  public
   * @return  void
   *
   * @since 2.0
   */
  function addToolbar()
  {
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_MANAGER').' :: '.JText::_('COM_JOOMGALLERY_FIELDSET_EDITIMAGES'), 'images');

    JToolbarHelper::apply('apply');
    JToolbarHelper::save('save');
    JToolbarHelper::cancel('cancel');
    JToolbarHelper::spacer();
  }
}