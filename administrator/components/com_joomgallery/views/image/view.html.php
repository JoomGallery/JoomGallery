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
    $item   = $this->get('Data');
    $isNew  = ($item->id < 1);
    $rating = 0.0;

    // Get the form and fill the fields
    $form = $this->get('Form');

    if($isNew)
    {
      // Set some field attributes for javascript validation
      $form->setFieldAttribute('detail_catid', 'required', true);
      $form->setFieldAttribute('detail_catid', 'validate', 'joompositivenumeric');
      $form->setFieldAttribute('thumb_catid',  'required', true);
      $form->setFieldAttribute('thumb_catid',  'validate', 'joompositivenumeric');
      $form->setFieldAttribute('imgfilename',  'required', true);
      $form->setFieldAttribute('imgthumbname', 'required', true);

      // Detail images
      $detail_catpath     = JoomHelper::getCatPath($item->detail_catid);
      $detail_path        = $this->_ambit->get('img_path').$detail_catpath;
      $form->setFieldAttribute('imgfilename', 'directory', $detail_path);
      $imgfilename_field  = $this->_findFieldByFieldName($form, 'imgfilename');
      $imagelib_field     = $this->_findFieldByFieldName($form, 'imagelib2');

      // Thumbnail images
      $thumb_catpath       = JoomHelper::getCatPath($item->thumb_catid);
      $thumb_path          = $this->_ambit->get('thumb_path').$thumb_catpath;
      $form->setFieldAttribute('imgthumbname', 'directory', $thumb_path);
      $imgthumbname_field  = $this->_findFieldByFieldName($form, 'imgthumbname');
      $imagelib_field      = $this->_findFieldByFieldName($form, 'imagelib');
    }
    else
    {
      if($item->imgvotes > 0)
      {
        $rating = JoomHelper::getRating($item->id);
      }
    }

    // Set maximum allowed user count to switch from listbox to modal popup selection
    $form->setFieldAttribute('owner', 'useListboxMaxUserCount', $this->_config->get('jg_use_listbox_max_user_count'));

    // Bind the data to the form
    $form->bind($item);

    // Set some form fields manually
    if($isNew)
    {
      // Does the original image file exist
      if($form->getValue('imgfilename', null) == '')
      {
        $form->setValue('original_exists', null, JText::_('COM_JOOMGALLERY_IMGMAN_NO_IMAGE_SELECTED'));
      }
      else
      {
        if(JFile::exists($this->_ambit->getImg('orig_path', $item->imgfilename, null, $item->detail_catid)))
        {
          $orig_msg = JText::_('COM_JOOMGALLERY_IMGMAN_ORIGINAL_EXIST');
          $color = 'green';
        }
        else
        {
          $orig_msg = JText::_('COM_JOOMGALLERY_IMGMAN_ORIGINAL_NOT_EXIST');
          $color = 'red';
        }
        $form->setValue('original_exists', null, $orig_msg);
        $original_exists_field = $this->_findFieldByFieldName($form, 'original_exists');
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
      if($item->published)
      {
        $form->setValue('publishhiddenstate', null, $item->hidden ? JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN') : JText::_('COM_JOOMGALLERY_COMMON_STATE_PUBLISHED') );
      }
      else
      {
        $form->setValue('publishhiddenstate', null, JText::_('COM_JOOMGALLERY_COMMON_STATE_UNPUBLISHED'));
      }
      // Rating
      $form->setValue('rating', null, JText::sprintf('COM_JOOMGALLERY_IMGMAN_IMAGE_VOTES', $rating, $item->imgvotes));
      // Date
      $form->setValue('date', null, JHTML::_('date',  $item->imgdate,  JText::_('DATE_FORMAT_LC2')));
    }

    // Set image source for detail image preview
    if($item->imgfilename)
    {
      if($isNew)
      {
        // We have to look for the image ID fist because the image may have to be output through the script
        $id = $this->getModel()->getIdByFilename($item->imgfilename, $item->detail_catid);
        $imgsource = $this->_ambit->getImg('img_url', $id);
      }
      else
      {
        $imgsource = $this->_ambit->getImg('img_url', $item);
      }
    }
    else
    {
      $imgsource = '../media/system/images/blank.png';
    }
    $form->setValue('imagelib2', null, $imgsource);

    // Set image source for thumbnail preview
    if($item->imgthumbname)
    {
      if($isNew)
      {
        // We have to look for the image ID fist because the image may have to be output through the script
        $id = $this->getModel()->getIdByFilename($item->imgthumbname, $item->thumb_catid, true);
        $thumbsource = $this->_ambit->getImg('thumb_url', $id);
      }
      else
      {
        $thumbsource = $this->_ambit->getImg('thumb_url', $item);
      }
    }
    else
    {
      $thumbsource = '../media/system/images/blank.png';
    }
    $form->setValue('imagelib', null, $thumbsource);

    JHtml::_('jquery.framework');

    $this->assignRef('item',              $item);
    $this->assignRef('isNew',             $isNew);
    $this->assignRef('form',              $form);

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