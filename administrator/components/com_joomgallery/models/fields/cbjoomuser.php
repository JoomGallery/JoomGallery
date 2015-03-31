<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/cbjoomuser.php $
// $Id: cbjoomuser.php 4327 2013-09-07 14:49:38Z erftralle $
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
JFormHelper::loadFieldClass('joomuser');

/**
 * Renders a form field for user selection with checkbox in front of the label
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldCbjoomuser extends JFormFieldJoomuser
{
  /**
   * The form field type
   *
   * @var     string
   * @since   2.0
   */
  public $type = 'Cbjoomuser';

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
    $label      = $cbhtml . parent::getLabel();

    return $label;
  }
}