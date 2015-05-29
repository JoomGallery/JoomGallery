<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/upload/view.html.php $
// $Id: view.html.php 4398 2014-06-11 13:39:02Z erftralle $
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
 * HTML View class for the upload view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewUpload extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  public function display($tpl = null)
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

    if(!$this->_config->get('jg_disableunrequiredchecks') && !count(JoomHelper::getAuthorisedCategories('joom.upload')))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=userpanel', false), JText::_('COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_ALLOWED_TO_UPLOAD'), 'notice');
    }

    $params = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'), 'index.php?view=userpanel');
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_UPLOAD_NEW_IMAGE'));
    }

    // Header and footer
    JoomHelper::prepareParams($params);

    $pathway = null;
    if($this->_config->get('jg_showpathway'))
    {
      $pathway  = '<a href="'.JRoute::_('index.php?view=userpanel').'">'.JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL').'</a>';
      $pathway .= ' &raquo; '.JText::_('COM_JOOMGALLERY_COMMON_UPLOAD_NEW_IMAGE');
    }

    $backtarget = JRoute::_('index.php?view=gallery');
    $backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');

    // Get number of images and hits in gallery
    $numbers  = JoomHelper::getNumberOfImgHits();

    // Load modules at position 'top'
    $modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($modules['top']))
    {
      $params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($modules['btm']))
    {
      $params->set('show_btm_modules', 1);
    }

    $count = $this->get('ImageNumber');

    if($count >= $this->_config->get('jg_maxuserimage'))
    {
      $timespan = $this->_config->get('jg_maxuserimage_timespan');
      $msg = JText::sprintf('COM_JOOMGALLERY_UPLOAD_OUTPUT_MAY_ADD_MAX_OF', $this->_config->get('jg_maxuserimage'), $timespan > 0 ? JText::plural('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_MAXCOUNT_TIMESPAN', $timespan) : '');
      $this->_mainframe->redirect(JRoute::_('index.php?view=userpanel', false), $msg, 'notice');
    }

    $inputcounter   = $this->_config->get('jg_maxuserimage') - $count;
    $remainder      = $inputcounter;
    if($inputcounter > $this->_config->get('jg_maxuploadfields'))
    {
      $inputcounter = $this->_config->get('jg_maxuploadfields');
    }

    $this->assignRef('count',         $count);
    $this->assignRef('remainder',     $remainder);
    $this->assignRef('inputcounter',  $inputcounter);
    $this->_doc->addScriptDeclaration('    var jg_inputcounter = '.$inputcounter.';');

    $this->assignRef('params',          $params);
    $this->assignRef('pathway',         $pathway);
    $this->assignRef('modules',         $modules);
    $this->assignRef('backtarget',      $backtarget);
    $this->assignRef('backtext',        $backtext);
    $this->assignRef('numberofpics',    $numbers[0]);
    $this->assignRef('numberofhits',    $numbers[1]);

    JHtml::_('behavior.formvalidation');
    JHtml::_('behavior.tooltip');

    $this->uploads = array();
    $types = array('single', 'ajax', 'batch', 'java');
    $tab = $this->_mainframe->input->get('tab');
    $active_found = false;
    foreach($types as $type)
    {
      if($this->_config->get('jg_userupload'.$type))
      {
        $this->uploads[$type] = array('title'   => 'COM_JOOMGALLERY_UPLOAD_TAB_'.strtoupper($type).'_UPLOAD',
                                      'active'  => false
                                      );
        if($tab == $type)
        {
          $this->uploads[$type]['active'] = true;
          $active_found = true;
        }
      }
    }

    // One tab has to be active
    if(!$active_found && count($this->uploads))
    {
      // Reset the array in order to be able to use the key of the first element
      reset($this->uploads);
      $this->uploads[key($this->uploads)]['active'] = true;
    }

    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    $this->single_form  = JForm::getInstance(_JOOM_OPTION.'.upload', 'upload');
    $this->ajax_form    = JForm::getInstance(_JOOM_OPTION.'.ajaxupload', 'ajaxupload');
    $this->batch_form   = JForm::getInstance(_JOOM_OPTION.'.batchupload', 'batchupload');
    $this->applet_form  = JForm::getInstance(_JOOM_OPTION.'.jupload', 'jupload');
    $this->single_form->setFieldAttribute('arrscreenshot', 'quantity', $inputcounter);

    $this->_doc->addScriptDeclaration('    var jg_filenamewithjs = '.($this->_config->get('jg_filenamewithjs') ? 'true' : 'false').';');
    $this->_doc->addScript($this->_ambit->getScript('upload.js'));
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_ONE_IMAGE');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_WRONG_EXTENSION');
    JText::script('COM_JOOMGALLERY_COMMON_ALERT_WRONG_FILENAME');
    JText::script('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_ONE');
    JText::script('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_TWO');

    // AJAX Drag'n'Drop Upload
    if($this->_config->get('jg_useruploadajax'))
    {
      $this->_doc->addStyleSheet($this->_ambit->getScript('fineuploader/fineuploader.css'));
      $this->_doc->addScript($this->_ambit->getScript('fineuploader/js/fineuploader'.(JFactory::getConfig()->get('debug') ? '' : '.min').'.js'));

      $this->ajax_form->setFieldAttribute('ajaxupload', 'redirect', $this->getModel()->getRedirectUrlAfterUpload('ajax'));
    }

    if($this->_config->get('jg_useorigfilename'))
    {
      $this->single_form->setFieldAttribute('imgtitle', 'required', 'false');
      $this->ajax_form->setFieldAttribute('imgtitle', 'required', 'false');
      $this->batch_form->setFieldAttribute('imgtitle', 'required', 'false');
      $this->applet_form->setFieldAttribute('imgtitle', 'required', 'false');
    }

    if(!$this->_config->get('jg_disableunrequiredchecks'))
    {
      // Set default user upload category
      $defaultUserUploadCategory = $this->get('DefaultUserUploadCategory');
      $this->single_form->setValue('catid', null, $defaultUserUploadCategory);
      $this->ajax_form->setValue('catid', null, $defaultUserUploadCategory);
      $this->batch_form->setValue('catid', null, $defaultUserUploadCategory);
      $this->applet_form->setValue('catid', null, $defaultUserUploadCategory);
    }

    parent::display($tpl);
  }
}