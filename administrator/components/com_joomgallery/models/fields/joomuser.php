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

defined('_JEXEC') or die('Restricted access');

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
    $link     = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
                  . (isset($groups) ? ('&amp;groups='.base64_encode(json_encode($groups))) : '')
                  . (isset($excluded) ? ('&amp;excluded='.base64_encode(json_encode($excluded))) : '');
    $required = '';

    if($this->required)
    {
      $required = ' required';

      if(!empty($this->class))
      {
        $this->class .= ' ';
      }
      $this->class .= 'validate-SelectUser_' . $this->id;
    }

    $attr = !empty($this->class) ? ' class="' . $this->class . '"' : '';
    $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
    $attr .= $this->required ? ' required' : '';

    JHtml::_('bootstrap.tooltip');

    // Build the script.
    $script   = array();

    if ($this->required)
    {
      $script[] = '  jQuery(document).ready(function() {';
      $script[] = '    document.formvalidator.setHandler("SelectUser_' . $this->id . '", function(value) {';
      $script[] = '      if (value == "" || value == "' . JText::_('COM_JOOMGALLERY_COMMON_NO_USER') . '") {';
      $script[] = '        return false;';
      $script[] = '      }';
      $script[] = '      return true;';
      $script[] = '    })';
      $script[] = '  });';
    }

    $script[] = '  function jSelectUser_' . $this->id . '(id, title) {';
    $script[] = '    var old_id = document.getElementById("' . $this->id . '").value;';
    $script[] = '    if (old_id != id) {';
    $script[] = '      document.getElementById("' . $this->id . '").value = id;';
    $script[] = '      if (id == "") {';
    $script[] = '        document.getElementById("' . $this->id . '_name").value = "' . JText::_('COM_JOOMGALLERY_COMMON_NO_USER', true) . '";';
    $script[] = '      }';
    $script[] = '      else {';
    $script[] = '        document.getElementById("' . $this->id . '_name").value = title;';
    $script[] = '      }';
    $script[] = '      ' . $this->onchange;

    if ($this->required)
    {
      $script[] = '      document.formvalidator.validate(document.getElementById("' . $this->id . '"));';
      $script[] = '      document.formvalidator.validate(document.getElementById("' . $this->id . '_name"));';
    }

    $script[] = '    }';
    $script[] = '    jQuery("#modalJoomuser").modal("hide");';
    $script[] = '  }';

    // Add the script to the document head.
    JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

    // Load the current user name if available.
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
      $this->value = '';
    }

    // Create a dummy text field with the user name.
    $html[] = '<div class="input-append">';
    $html[] = '  <input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($table->$type, ENT_COMPAT, 'UTF-8')
                . '"' . ' readonly' . $attr . ' />';

    // Create the user select button.
    if ($this->readonly === false)
    {
      $html[] = '<a href="#modalJoomuser" class="btn hasTooltip" role="button" data-toggle="modal"'
                  . ' title="' . JHtml::tooltipText('JLIB_FORM_CHANGE_USER') . '">'
                  . '<i class="icon-user"></i></a>';

      $html[] = JHtmlBootstrap::renderModal(
                  'modalJoomuser', array(
                    'url' => $link . '&amp;' . JSession::getFormToken() . '=1"',
                    'title' => JText::_('JLIB_FORM_CHANGE_USER'),
                    'width' => '800px',
                    'height' => '300px',
                    'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
                                  . JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
                  )
                );
    }

    $html[] = '</div>';

    // Create the real field, hidden, that stores the user id.
    $html[] = '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '"' . $required . ' />';

    return implode("\n", $html);
  }
}