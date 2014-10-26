<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/cbeditor.php $
// $Id: cbeditor.php 4076 2013-02-12 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('editor');

/**
 * Renders a editor form field with checkbox in front of the label
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldCbeditor extends JFormFieldEditor
{
  /**
   * The form field type
   *
   * @var     string
   * @since   2.0
   */
  public $type = 'Cbeditor';

  /**
   * Method to get the field label markup
   *
   * @return  string  The field label markup
   * @since   2.0
   */
  protected function getLabel()
  {
    $label = '';

    $cbname     = $this->element['cbname'] ? $this->element['cbname'] : 'change[]';
    $cbvalue    = $this->element['cbvalue'] ? $this->element['cbvalue'] : $this->name;

    $cbid       = str_replace(array('[', ']'), array('', ''), $cbname.$cbvalue);
    $cbhtml     = '<input id="'.$cbid.'" type="checkbox" name="'.$cbname.'" value="'.$cbvalue.'" />';
    $label      = parent::getLabel();
    $insertpos  = strpos($label, '>');
    $label      = substr_replace($label, $cbhtml, $insertpos + 1, 0);

    return $label;
  }
}