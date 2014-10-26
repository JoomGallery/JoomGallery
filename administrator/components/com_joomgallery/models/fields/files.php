<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/files.php $
// $Id: files.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * Renders form fields for uploading files
 *
 * @package     JoomGallery
 * @since       2.1
 */
class JFormFieldFiles extends JFormField
{
  /**
   * The form field type
   *
   * @var     string
   * @since   2.1
   */
  public $type = 'Files';

  /**
   * Method to get the field input markup for the file fields.
   * The field attribute 'quantity' allow specification of the number of file fields to display.
   * All other field attributes are the same as for JFormFieldFile
   *
   * @return  string  The field input markup
   * @since   2.1
   */
  protected function getInput()
  {
    $count = ((int) $this->element['quantity']) ? (int) $this->element['quantity'] : 10;

    // Initialize some field attributes
    $accept   = $this->element['accept'] ? ' accept="'.(string) $this->element['accept'].'"' : '';
    $size     = $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
    $class    = $this->element['class'] ? ' class="'.trim(str_replace('required', '', (string) $this->element['class'])).'"' : '';
    $fsclass  = $this->element['fieldsetclass'] ? ' '.(string) $this->element['fieldsetclass'] : '';
    $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
    $onchange = $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

    $fields = array();
    for($i = 0; $i < $count; $i++)
    {
      $name = $this->name.'['.$i.']';
      $id   = $this->id.$i;
      $fields[] = '<input type="file" name="'.$name.'" id="'.$id.'" value=""'.$accept.$disabled.$class.$size.$onchange.' />';
    }

    return '<fieldset class="validate-joomfiles'.$fsclass.'" id="'.$this->id.'">'.implode('', $fields).'</fieldset>';
  }
}
