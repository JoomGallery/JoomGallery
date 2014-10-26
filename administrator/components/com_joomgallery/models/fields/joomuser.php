<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/joomuser.php $
// $Id: joomuser.php 4404 2014-06-26 21:23:58Z chraneco $
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
JFormHelper::loadFieldClass('user');

/**
 * Renders a form field for user selection
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldJoomUser extends JFormFieldUser
{
  /**
   * The form field type
   *
   * @var     string
   * @since   2.0
   */
  public $type = 'Joomuser';

  /**
   * Method to get the field input markup
   *
   * @return  string  The field input markup
   * @since 2.0
   */
  protected function getInput()
  {
    // Initialize variables.
    $html     = array();
    $groups   = $this->getGroups();
    $excluded = $this->getExcluded();
    $link     = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field='.$this->id.(isset($groups) ? ('&amp;groups='.base64_encode(json_encode($groups))) : '').(isset($excluded) ? ('&amp;excluded='.base64_encode(json_encode($excluded))) : '');

    // Initialize some field attributes.
    $attr  = $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
    $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

    // Initialize JavaScript field attributes.
    $onchange = (string) $this->element['onchange'];

    // Load the modal behavior script.
    JHtml::_('behavior.modal', 'a.modal_'.$this->id);

    // Build the script.
    $script   = array();
    $script[] = ' function jSelectUser_'.$this->id.'(id, title) {';
    $script[] = '   var old_id = document.getElementById("'.$this->id.'_id").value;';
    $script[] = '   if (old_id != id) {';
    $script[] = '     document.getElementById("'.$this->id.'_id").value = id;';
    $script[] = '     if (id == "") {';
    $script[] = '       document.getElementById("'.$this->id.'_name").value = "'.JText::_('COM_JOOMGALLERY_COMMON_NO_USER', true).'";';
    $script[] = '     }';
    $script[] = '     else {';
    $script[] = '       document.getElementById("'.$this->id.'_name").value = title;';
    $script[] = '     }';
    $script[] = '     '.$onchange;
    $script[] = '   }';
    $script[] = '   SqueezeBox.close();';
    $script[] = ' }';

    // Add the script to the document head.
    JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

    // Load the current user if available.
    $config = JoomConfig::getInstance();
    $type   = $config->get('jg_realname') ? 'name' : 'username';
    $table  = JTable::getInstance('user');
    if($this->value)
    {
      $table->load($this->value);
    }
    else
    {
      $table->$type = JText::_('COM_JOOMGALLERY_COMMON_NO_USER');
    }

    // Create a dummy text field with the user name.
    $html[] = '<div class="fltlft">';
    $html[] = ' <input type="text" id="'.$this->id.'_name"' .
          ' value="'.htmlspecialchars($table->$type, ENT_COMPAT, 'UTF-8').'"' .
          ' disabled="disabled"'.$attr.' />';
    $html[] = '</div>';

    // Create the user select button.
    $html[] = '<div class="button2-left">';
    $html[] = '  <div class="blank">';
    if($this->element['readonly'] != 'true')
    {
      $html[] = '   <a class="modal_'.$this->id.'" title="'.JText::_('JLIB_FORM_CHANGE_USER').'"' .
              ' href="'.$link.'"' .
              ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
      $html[] = '     '.JText::_('JLIB_FORM_CHANGE_USER').'</a>';
    }
    $html[] = '  </div>';
    $html[] = '</div>';

    // Create the real field, hidden, that stored the user id.
    $html[] = '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.(int) $this->value.'" />';

    return implode("\n", $html);
  }
}