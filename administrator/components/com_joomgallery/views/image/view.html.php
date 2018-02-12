<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/image/view.html.php $
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
 * HTML View class for the image edit view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewImage extends JoomGalleryView
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
    $this->item   = $this->get('Data');
    $this->isNew  = ($this->item->id < 1);
    $rating = 0.0;

    // Get the form and fill the fields
    $this->form = $this->get('Form');

    if($this->isNew)
    {
      // Set some field attributes for javascript validation
      $this->form->setFieldAttribute('detail_catid', 'required', true);
      $this->form->setFieldAttribute('detail_catid', 'validate', 'joompositivenumeric');
      $this->form->setFieldAttribute('thumb_catid',  'required', true);
      $this->form->setFieldAttribute('thumb_catid',  'validate', 'joompositivenumeric');
      $this->form->setFieldAttribute('imgfilename',  'required', true);
      $this->form->setFieldAttribute('imgthumbname', 'required', true);

      // Detail images
      $detail_catpath     = JoomHelper::getCatPath($this->item->detail_catid);
      $detail_path        = $this->_ambit->get('img_path').$detail_catpath;
      $this->form->setFieldAttribute('imgfilename', 'directory', $detail_path);
      $imgfilename_field  = $this->_findFieldByFieldName($this->form, 'imgfilename');
      $imagelib_field     = $this->_findFieldByFieldName($this->form, 'imagelib2');

      // Thumbnail images
      $thumb_catpath       = JoomHelper::getCatPath($this->item->thumb_catid);
      $thumb_path          = $this->_ambit->get('thumb_path').$thumb_catpath;
      $this->form->setFieldAttribute('imgthumbname', 'directory', $thumb_path);
      $imgthumbname_field  = $this->_findFieldByFieldName($this->form, 'imgthumbname');
      $imagelib_field      = $this->_findFieldByFieldName($this->form, 'imagelib');
    }
    else
    {
      if($this->item->imgvotes > 0)
      {
        $rating = JoomHelper::getRating($this->item->id);
      }
    }

    // Set maximum allowed user count to switch from listbox to modal popup selection
    $this->form->setFieldAttribute('owner', 'useListboxMaxUserCount', $this->_config->get('jg_use_listbox_max_user_count'));

    // Bind the data to the form
    $this->form->bind($this->item);

    // Set some form fields manually
    if($this->isNew)
    {
      // Does the original image file exist
      if($this->form->getValue('imgfilename', null) == '')
      {
        $this->form->setValue('original_exists', null, JText::_('COM_JOOMGALLERY_IMGMAN_NO_IMAGE_SELECTED'));
      }
      else
      {
        if(JFile::exists($this->_ambit->getImg('orig_path', $this->item->imgfilename, null, $this->item->detail_catid)))
        {
          $orig_msg = JText::_('COM_JOOMGALLERY_IMGMAN_ORIGINAL_EXIST');
          $color = 'green';
        }
        else
        {
          $orig_msg = JText::_('COM_JOOMGALLERY_IMGMAN_ORIGINAL_NOT_EXIST');
          $color = 'red';
        }
        $this->form->setValue('original_exists', null, $orig_msg);
        $original_exists_field = $this->_findFieldByFieldName($this->form, 'original_exists');
        $js = "
        window.addEvent('domready', function() {
          $('".$original_exists_field->id."').setStyle('color', '".$color."');
        });";
        $this->_doc->addScriptDeclaration($js);
      }
    }
    else
    {
      // Plublished and hidden state
      if($this->item->published)
      {
        $this->form->setValue('publishhiddenstate', null, $this->item->hidden ? JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN') : JText::_('COM_JOOMGALLERY_COMMON_STATE_PUBLISHED') );
      }
      else
      {
        $this->form->setValue('publishhiddenstate', null, JText::_('COM_JOOMGALLERY_COMMON_STATE_UNPUBLISHED'));
      }
      // Rating
      $this->form->setValue('rating', null, JText::sprintf('COM_JOOMGALLERY_IMGMAN_IMAGE_VOTES', $rating, $this->item->imgvotes));
      // Date
      $this->form->setValue('date', null, JHTML::_('date',  $this->item->imgdate,  JText::_('DATE_FORMAT_LC2')));
    }

    // Set image source for detail image preview
    if($this->item->imgfilename)
    {
      if($this->isNew)
      {
        // We have to look for the image ID fist because the image may have to be output through the script
        $id = $this->getModel()->getIdByFilename($this->item->imgfilename, $this->item->detail_catid);
        $imgsource = $this->_ambit->getImg('img_url', $id);
      }
      else
      {
        $imgsource = $this->_ambit->getImg('img_url', $this->item);
      }
    }
    else
    {
      $imgsource = '../media/system/images/blank.png';
    }
    $this->form->setValue('imagelib2', null, $imgsource);

    // Set image source for thumbnail preview
    if($this->item->imgthumbname)
    {
      if($this->isNew)
      {
        // We have to look for the image ID fist because the image may have to be output through the script
        $id = $this->getModel()->getIdByFilename($this->item->imgthumbname, $this->item->thumb_catid, true);
        $thumbsource = $this->_ambit->getImg('thumb_url', $id);
      }
      else
      {
        $thumbsource = $this->_ambit->getImg('thumb_url', $this->item);
      }
    }
    else
    {
      $thumbsource = '../media/system/images/blank.png';
    }
    $this->form->setValue('imagelib', null, $thumbsource);

    JHtml::_('jquery.framework');

    $this->addToolbar();

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
    $canDo = JoomHelper::getActions('image', $this->item->id);

    $title = JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_MANAGER').' :: ';

    if($this->isNew)
    {
      $title .= JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_ADD');
    }
    else
    {
      $title .= JText::_('COM_JOOMGALLERY_IMGMAN_IMAGE_EDIT');
    }

    JToolBarHelper::title($title, 'image');

    // For new images check the create permission
    if($this->isNew && ($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('joom.upload') || count(JoomHelper::getAuthorisedCategories('joom.upload'))))
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
        if($canDo->get('joom.upload') || count(JoomHelper::getAuthorisedCategories('joom.upload')))
        {
          JToolBarHelper::save2new();
        }
      }
    }

    // If it's an already existing category a copy may be saved (only if creating categories is allowed)
    if(!$this->isNew && ($this->_config->get('jg_disableunrequiredchecks') || $canDo->get('joom.upload') || count(JoomHelper::getAuthorisedCategories('joom.upload'))))
    {
      // TODO: How to implement save as copy?
      //JToolBarHelper::save2copy();
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