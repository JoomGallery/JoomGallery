<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/openimage.php $
// $Id: openimage.php 4342 2013-11-10 17:25:31Z erftralle $
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

JFormHelper::loadFieldClass('list');

/**
 * Renders a select box form field for choosing an OpenImage method
 *
 * @package     JoomGallery
 * @since       3.0
 */
class JFormFieldOpenimage extends JFormFieldList
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 3.0
   */
  protected $type = 'Openimage';

  /**
   * Returns the HTML for a OpenImage select box form field.
   *
   * @return  string  The OpenImage select box form field.
   * @since   3.0
   */
  protected function getInput()
  {
    JHtml::addIncludePath(JPATH_BASE.'/components/com_joomgallery/helpers/html');

    $class = $this->element['class'] ? (string) $this->element['class'] : '';
    if($this->element['required'] && $this->element['required'] == true && strpos($class, 'required') === false)
    {
      if(!empty($class))
      {
        $class .= ' ';
      }
      $class .= 'required';
    }

    $attr    = '';
    $attr   .= !empty($class) ? ' class="'.$class.'"' : '';
    $attr   .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
    $attr   .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
    $attr   .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

    $detail = true;
    if((string) $this->element['detail'] == 'false')
    {
      $detail = false;
    }

    $default = false;
    if((string) $this->element['defaultMethod'] == 'true')
    {
      $default = true;
    }

    $prefix = 'COM_JOOMGALLERY_CONFIG_CV_GS_';
    if($this->element['prefix'])
    {
      $prefix = (string) $this->element['prefix'];
    }

    return JHtml::_('joomselect.openimage', $this->name, $this->value, $detail, $default, $prefix, $attr, parent::getOptions());
  }
}