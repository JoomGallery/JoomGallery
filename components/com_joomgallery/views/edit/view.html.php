<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/edit/view.html.php $
// $Id: view.html.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * HTML View class for the edit view for images
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewEdit extends JoomGalleryView
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
    if(!$this->_config->get('jg_userspace'))
    {
      $msg = JText::_('JERROR_ALERTNOAUTHOR');

      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), $msg, 'notice');
    }

    // Additional security check for unregistered users
    if(!$this->_user->get('id') && !$this->_config->get('jg_unregistered_permissions'))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED'), 'notice');
    }

    $this->params = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'), 'index.php?view=userpanel');
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_EDIT_EDIT_IMAGE'));
    }

    // Header and footer
    JoomHelper::prepareParams($this->params);

    $this->pathway = null;
    if($this->_config->get('jg_showpathway'))
    {
      $this->pathway  = '<a href="'.JRoute::_('index.php?view=userpanel').'">'.JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL').'</a>';
      $this->pathway .= ' &raquo; '.JText::_('COM_JOOMGALLERY_EDIT_EDIT_IMAGE');
    }

    $this->backtarget = JRoute::_('index.php?view=userpanel'); //see above
    $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_USER_PANEL');

    // Get number of images and hits in gallery
    $numbers            = JoomHelper::getNumberOfImgHits();
    $this->numberofpics = $numbers[0];
    $this->numberofhits = $numbers[1];

    // Load modules at position 'top'
    $this->modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($this->modules['top']))
    {
      $this->params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $this->modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($this->modules['btm']))
    {
      $this->params->set('show_btm_modules', 1);
    }

    $model = $this->getModel();
    $array = $this->_mainframe->input->get('id', array(0), 'array');
    $model->setId((int)$array[0]);
    $this->image = $model->getImage();

    // Get the form and fill the fields
    $this->form = $this->get('Form');

    // Bind the data to the form
    $this->form->bind($this->image);

    // Set some form fields manually
    $this->form->setValue('imagelib', null, $this->image->thumb_url);

    // Get limitstart from request to set the correct limitstart (page) in userpanel when
    // leaving edit mode with save or cancel
    $limitstart = $this->_mainframe->input->get('limitstart', null);
    $this->slimitstart = ($limitstart != null ? '&limitstart='.(int)$limitstart : '');

    // Get redirect page, if any given by request
    $this->redirect     = $this->_mainframe->input->getBase64('redirect');
    $this->redirecturl  = '';
    if($this->redirect === null)
    {
      $this->redirect = '';
    }
    else
    {
      $this->redirecturl = base64_decode($this->redirect);
      if(!JURI::isInternal($this->redirecturl))
      {
        $this->redirecturl = '';
        $this->redirect    = '';
      }
      else
      {
        $this->redirect = '&redirect='.$this->redirect;
      }
    }

    parent::display($tpl);
  }
}