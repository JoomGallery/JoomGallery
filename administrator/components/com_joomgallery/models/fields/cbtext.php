<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/cbtext.php $
// $Id: cbtext.php 4384 2014-05-09 12:40:47Z erftralle $
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
JFormHelper::loadFieldClass('text');

/**
 * Renders a text form field with checkbox in front of the label
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldCbtext extends JFormFieldText
{
  /**
   * The form field type
   *
   * @var     string
   * @since   2.0
   */
  protected  $type = 'Cbtext';

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
    $cbrequired = $this->element['cbrequired'] ? (string) $this->element['cbrequired'] : 'false';
    $cbid       = str_replace(array('[', ']'), array('', ''), $cbname.$cbvalue);

    $cbonclick = '';
    if($cbrequired == 'true' || $cbrequired == 'required' || $cbrequired == '1')
    {
      $cbonclick = "javascript: var el = jQuery('#".$this->id."'); if(jQuery('#".$cbid."').prop('checked')) { el.attr('aria-required', 'true').attr('required', 'required');} else {el.removeAttr('aria-required').removeAttr('required');}";
    }
    $cbhtml = '<input id="'.$cbid.'" type="checkbox" onclick="'.$cbonclick.'" name="'.$cbname.'" value="'.$cbvalue.'" />';

    $label = $cbhtml . parent::getLabel();

    return $label;
  }
}