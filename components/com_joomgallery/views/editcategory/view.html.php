<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/editcategory/view.html.php $
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
 * HTML View class for the edit view for categories
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewEditcategory extends JoomGalleryView
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

    $this->params = $this->_mainframe->getParams();

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs = $this->_mainframe->getPathway();
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'), 'index.php?view=userpanel');
      $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES'), 'index.php?view=usercategories');
      if($this->_mainframe->input->getInt('catid'))
      {
        $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_EDITCATEGORY_MODIFY_CATEGORY'));
      }
      else
      {
        $breadcrumbs->addItem(JText::_('COM_JOOMGALLERY_COMMON_NEW_CATEGORY'));
      }
    }

    // Header and footer
    JoomHelper::prepareParams($this->params);

    $this->pathway = null;
    if($this->_config->get('jg_showpathway'))
    {
      $this->pathway  = '<a href="'.JRoute::_('index.php?view=userpanel').'">'.JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL').'</a>';
      $this->pathway .= ' &raquo; <a href="'.JRoute::_('index.php?view=usercategories').'">'.JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES').'</a>';
      if($this->_mainframe->input->getInt('catid'))
      {
        $this->pathway .= ' &raquo; '.JText::_('COM_JOOMGALLERY_EDITCATEGORY_MODIFY_CATEGORY');
      }
      else
      {
        $this->pathway .= ' &raquo; '.JText::_('COM_JOOMGALLERY_COMMON_NEW_CATEGORY');
      }
    }

    $this->backtarget = JRoute::_('index.php?view=userpanel');
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

    // Load the category data
    $this->category = $this->get('Category');

    // Get the form and fill the fields
    $this->form = $this->get('Form');

    // Set some additional field attributes
    // Category slect box
    if($this->category->cid)
    {
      // Exclude current category id from select box
      $this->form->setFieldAttribute('parent_id', 'exclude', $this->category->cid);
    }
    else
    {
      // If we are creating a new category pre-select a valid
      // parent category in select box in order to fix the content
      // of the parent category box and of the ordering box
      // (if the user is allowed to create main categories
      // 0 is already a valid value for the category select box)
      if(!$this->_config->get('jg_disableunrequiredchecks') && !$this->_user->authorise('core.create', _JOOM_OPTION))
      {
        $parent_cats = JoomHelper::getAuthorisedCategories('core.create');
        if(isset($parent_cats[0]))
        {
          $this->category->parent_id = $parent_cats[0]->cid;
        }
        else
        {
          $msg = JText::_('COM_JOOMGALLERY_EDITCATEGORY_ERROR_NO_AVAILABLE_PARENT_CATEGORIES');
          $this->_mainframe->redirect(JRoute::_('index.php?view=usercategories', false), $msg, 'notice');
        }
      }
    }

    // Ordering box is not available if option for performance improvement has been enabled
    if(!$this->_config->get('jg_disableunrequiredchecks'))
    {
      // Set some additional attributes for the ordering select box
      $this->form->setFieldAttribute('ordering', 'originalOrder', $this->category->cid);
      $this->form->setFieldAttribute('ordering', 'originalParent', $this->category->parent_id == 1 ? 0 : $this->category->parent_id);
      $this->form->setFieldAttribute('ordering', 'orderings', base64_encode(serialize($this->getModel()->getOrderings($this->category->cid ? $this->category->parent_id : null))));
      // Perhaps there is a better way to set the field attribute
      $parent_field = $this->_findFieldByFieldName($this->form, 'parent_id');
      if($parent_field !== false)
      {
        $this->form->setFieldAttribute('ordering', 'parent_id', $parent_field->id);
      }
    }
    // Thumbnail preview
    $imagelib_field = $this->_findFieldByFieldName($this->form, 'imagelib');
    // Set additional attribute for the thumbnail select box
    if($imagelib_field !== false)
    {
      $this->form->setFieldAttribute('thumbnail', 'imagelib_id', $imagelib_field->id);
    }

    // Bind the data to the form
    $this->form->bind($this->category);

    // Set some form fields manually
    $this->form->setValue('imagelib', null, $this->category->catimage_src);

    // Get limitstart from request to set the correct limitstart (page) in usercategories when
    // leaving edit/new mode with save or cancel
    $limitstart        = $this->_mainframe->input->get('limitstart', null);
    $this->slimitstart = ($limitstart != null ? '&limitstart='.(int)$limitstart : '');

    parent::display($tpl);
  }

  /**
   * Find a form field by field name
   *
   * @param  object   $form       The form object to search in
   * @param  string   $fieldname  The field name to search
   * @return mixed    The form field object or false, if field could not be found
   * @since 2.0
   */
  private function _findFieldByFieldName($form, $fieldname)
  {
    foreach($form->getFieldset() As $field)
    {
      if($field->fieldname == $fieldname)
      {
        return $field;
      }
    }

    return false;
  }
}