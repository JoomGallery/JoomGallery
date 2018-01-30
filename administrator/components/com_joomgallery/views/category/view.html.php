<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/category/view.html.php $
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
 * HTML View class for the category edit view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewCategory extends JoomGalleryView
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
    // Get the category data
    $item = $this->get('Data');

    $isNew = ($item->cid < 1);
    if($isNew)
    {
      $item->published = 1;
    }

    // Get image source for the thumbnail preview
    if($item->thumbnail && $item->thumbnail_available)
    {
      $imgsource = $this->_ambit->getImg('thumb_url', $item->thumbnail);
    }
    else
    {
      $imgsource = '../media/system/images/blank.png';
    }

    // Get the form and fill the fields
    $form = $this->get('Form');
    if(!$isNew)
    {
      // Add additional attribute for category form field to exclude current
      // category id from select box
      $form->setFieldAttribute('parent_id', 'exclude', $item->cid);
    }

    // Ordering box is not available if option for performance improvement has been enabled
    if(!$this->_config->get('jg_disableunrequiredchecks'))
    {
      // Set some additional attributes for the ordering select box
      $form->setFieldAttribute('ordering', 'originalOrder', $item->cid);
      $form->setFieldAttribute('ordering', 'originalParent', $item->parent_id == 1 ? 0 : $item->parent_id);
      $form->setFieldAttribute('ordering', 'orderings', base64_encode(serialize($this->getModel()->getOrderings($item->cid ? $item->parent_id : null))));
      // Perhaps there is a better way to set the field attribute
      $parent_field = $this->_findFieldByFieldName($form, 'parent_id');
      if($parent_field !== false)
      {
        $form->setFieldAttribute('ordering', 'parent_id', $parent_field->id);
      }
    }

    $imagelib_field = $this->_findFieldByFieldName($form, 'imagelib');
    // Set additional attribute for the thumbnail select box
    if($imagelib_field !== false)
    {
      $form->setFieldAttribute('thumbnail', 'imagelib_id', $imagelib_field->id);
    }

    // Set maximum allowed user count to switch from listbox to modal popup selection
    $form->setFieldAttribute('owner', 'useListboxMaxUserCount', $this->_config->get('jg_use_listbox_max_user_count'));

    // Bind the data to the form
    $form->bind($item);

    // Set thumbnail image source for thumbnail preview form field
    $form->setValue('imagelib', null, $imgsource);

    // Set immutable fields
    if($item->published)
    {
      $form->setValue('publishhiddenstate', null, $item->hidden ? JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN') : JText::_('COM_JOOMGALLERY_COMMON_STATE_PUBLISHED') );
    }
    else
    {
      $form->setValue('publishhiddenstate', null, JText::_('COM_JOOMGALLERY_COMMON_STATE_UNPUBLISHED'));
    }

    if($item->thumbnail && !$item->thumbnail_available)
    {
      $form->setValue('notice', null, JText::sprintf('COM_JOOMGALLERY_CATMAN_THUMBNAIL_NOT_AVAILABLE', $item->thumbnail));
    }

    JHtml::_('jquery.framework');

    $this->assignRef('item', $item);
    $this->assignRef('isNew', $isNew);
    $this->assignRef('form', $form);

    $this->addToolbar();
    parent::display($tpl);
  }

  /**
   * Find a form field by field name
   *
   * @access private
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


  /**
   * Add the page title and toolbar.
   *
   * @return  void
   *
   * @since 2.0
   */
  public function addToolbar()
  {
    // Get the results for each action
    $canDo = JoomHelper::getActions('category', $this->item->cid);

    $title = JText::_('COM_JOOMGALLERY_CATMAN_CATEGORY_MANAGER').' :: ';
    if($this->isNew)
    {
      $title .= JText::_('COM_JOOMGALLERY_CATMAN_ADD_CATEGORY');
    }
    else
    {
      $title .= JText::_('COM_JOOMGALLERY_CATMAN_EDIT_CATEGORY');
    }
    $title .= ' ' .JText::_('COM_JOOMGALLERY_COMMON_CATEGORY');

    JToolBarHelper::title($title, 'folder');

    // For new categories check the create permission
    if($this->isNew && ($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.create') || count(JoomHelper::getAuthorisedCategories('core.create'))))
    {
      JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
      JToolBarHelper::save('save', 'JTOOLBAR_SAVE');
      JToolBarHelper::custom('save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
    }
    else
    {
      if(($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->owner == $this->_user->get('id'))))
      {
        JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('save', 'JTOOLBAR_SAVE');
        if($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.create') || count(JoomHelper::getAuthorisedCategories('core.create')))
        {
          JToolBarHelper::save2new();
        }
      }
    }

    // If it's an already existing category a copy may be saved (only if creating categories is allowed)
    if(!$this->isNew && ($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('core.create') || count(JoomHelper::getAuthorisedCategories('core.create'))))
    {
      JToolBarHelper::save2copy();
    }

    if($this->isNew)
    {
      JToolBarHelper::cancel('cancel','JTOOLBAR_CANCEL');
    }
    else
    {
      JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
    }
    JToolbarHelper::spacer();
  }
}