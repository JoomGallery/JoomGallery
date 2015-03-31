<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/joomdatabase.php $
// $Id: joomdatabase.php 4270 2013-05-21 14:10:18Z chraneco $
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

JFormHelper::loadFieldClass('checkbox');

/**
 * Form Field class of JoomGallery.
 * 
 * This renders a checkbox in the form and all remaining fields
 * for configuring a database connection to the form.
 *
 * @package JoomGallery
 * @since   3.1
 */
class JFormFieldJoomDatabase extends JFormFieldCheckbox
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  3.1
   */
  public $type = 'JoomDatabase';

  /**
   * Method to get the field label markup.
   *
   * @return  string  The field label markup.
   * @since   3.1
   */
  protected function getLabel()
  {
    if(!(string) $this->element['label'])
    {
      $this->element['label'] = 'COM_JOOMGALLERY_MIGMAN_FIELD_JOOMDATABASE_LABEL';
    }

    if(!$this->description)
    {
      $this->description = 'COM_JOOMGALLERY_MIGMAN_FIELD_JOOMDATABASE_DESC';
    }
 
    return parent::getLabel();
  }

  /**
   * Method to get the field input markup.
   * Adds the remaining fields for the database connection to the form
   *
   * @return  string  The field input markup
   * @since   3.1
   */
  protected function getInput()
  {
    $numberOfFields = 6;

    $this->form->loadFile('database');

    // Remove field 'prefix' is required
    $prefix = (string) $this->element['prefix'];
    if($prefix == 'false' || $prefix == '0')
    {
      $this->form->removeField('prefix', 'db');

      $numberOfFields--;
    }

    // If second database connection is required remove
    // checkbox and change 'required' field attributes 
    if($this->required)
    {
      $this->form->removeField('database');
      $this->form->setFieldAttribute('db_type', 'required', 'true', 'db');
      $this->form->setFieldAttribute('db_host', 'required', 'true', 'db');
      $this->form->setFieldAttribute('db_user', 'required', 'true', 'db');
      $this->form->setFieldAttribute('db_name', 'required', 'true', 'db');
      $this->form->setFieldAttribute('prefix', 'required', 'true', 'db');
    }

    // Create individual IDs for each of the fields
    $id = str_replace('database_', '', $this->id);
    $this->form->setFieldAttribute('enabled', 'id', 'enabled_'.$id, 'db');
    $this->form->setFieldAttribute('db_type', 'id', 'db_type_'.$id, 'db');
    $this->form->setFieldAttribute('db_host', 'id', 'db_host_'.$id, 'db');
    $this->form->setFieldAttribute('db_user', 'id', 'db_user_'.$id, 'db');
    $this->form->setFieldAttribute('db_pass', 'id', 'db_pass_'.$id, 'db');
    $this->form->setFieldAttribute('db_name', 'id', 'db_name_'.$id, 'db');
    $this->form->setFieldAttribute('prefix', 'id', 'prefix_'.$id, 'db');

    $script = 'var group = jQuery(\'#'.$this->id.'\').parent().parent();';
    $script .= 'for(var i = 0; i < '.$numberOfFields.'; i++){group = group.next();group.toggleClass(\'hide\');}';

    if(!$this->value && !isset($this->form->joomScriptLoaded))
    {
      JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function(){
      if(!jQuery(\'#'.$this->id.'\').prop(\'checked\')){jQuery(\'#db_enabled_'.$id.'\').val(\'0\');
      '.$script.'}else{jQuery(\'#db_enabled_'.$id.'\').val(\'1\');}});');

      $this->form->joomScriptLoaded = true;
    }

    $this->element['onclick'] = $script.'jQuery(\'#db_enabled_'.$id.'\').val(1 - jQuery(\'#db_enabled_'.$id.'\').val());';

    return parent::getInput();
  }
}