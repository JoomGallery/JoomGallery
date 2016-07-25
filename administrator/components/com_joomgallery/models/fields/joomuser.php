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
   * User count
   *
   * @var     int
   * @since   3.2
   */
  protected static $userCount = null;

  /**
   * User list
   *
   * @var    array
   * @since   3.2
   */
  protected static $userList = null;

  /**
   * Use listbox for user selection until specified maximum number
   * of system users is reached, otherwise use modal popup
   *
   * @var    array
   * @since   3.2
   */
  protected $useListboxMaxUserCount = 0;

  /**
   * Method to get certain otherwise inaccessible properties from the form field object.
   *
   * @param   string  $name  The property name for which to the the value.
   *
   * @return  mixed  The property value or null.
   *
   * @since   3.2
   */
  public function __get($name)
  {
    switch ($name)
    {
      case 'useListboxMaxUserCount':
        return $this->$name;
    }

    return parent::__get($name);
  }

  /**
   * Method to set certain otherwise inaccessible properties of the form field object.
   *
   * @param   string  $name   The property name for which to the the value.
   * @param   mixed   $value  The value of the property.
   *
   * @return  void
   *
   * @since   3.2
   */
  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'useListboxMaxUserCount':
        $this->$name = (int) $value;
        break;

      default:
        parent::__set($name, $value);
    }
  }

  /**
   * Method to attach a JForm object to the field.
   *
   * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
   * @param   mixed             $value    The form field value to validate.
   * @param   string            $group    The field name group control value. This acts as as an array container for the field.
   *                                      For example if the field has name="foo" and the group value is set to "bar" then the
   *                                      full field name would end up being "bar[foo]".
   *
   * @return  boolean  True on success.
   *
   * @see     JFormField::setup()
   * @since   3.2
   */
  public function setup(SimpleXMLElement $element, $value, $group = null)
  {
    $return = parent::setup($element, $value, $group);

    if ($return)
    {
      $this->useListboxMaxUserCount  = (int) $this->element['useListboxMaxUserCount'];
    }

    return $return;
  }

  /**
   * Method to get the field input markup
   *
   * @return  string  The field input markup
   * @since 2.0
   */
  protected function getInput()
  {
    if($this->useListboxMaxUserCount)
    {
      // Get the number of not blocked users
      if(self::$userCount === null)
      {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
              ->select('COUNT(id)')
              ->from('#__users');
        $db->setQuery($query);

        self::$userCount = $db->loadResult();
      }

      if(self::$userCount != null && (self::$userCount <= $this->useListboxMaxUserCount))
      {
        return $this->getListboxInput();
      }
    }

    if(version_compare(JVERSION, '3.5', 'lt'))
    {
      return $this->getPopupInput();
    }

    return parent::getInput();
  }

  /**
   * Method to get the field input markup in the case of
   * using a listbox for user selection
   *
   * @return  string  The field input markup
   * @since 3.2
   */
  protected function getListboxInput()
  {
    $options = array();
    $attr    = '';

    // Initialize some field attributes.
    $attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
    $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
    $attr .= $this->multiple ? ' multiple' : '';
    $attr .= $this->required ? ' required aria-required="true"' : '';
    $attr .= $this->autofocus ? ' autofocus' : '';

    // Initialize JavaScript field attributes.
    $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

    if(self::$userList === null)
    {
      $config = JoomConfig::getInstance();
      $type   = $config->get('jg_realname') ? 'name' : 'username';

      $db = JFactory::getDbo();

      $query = $db->getQuery(true)
            ->select('id AS value')
            ->select($type . ' AS text')
            ->from('#__users')
            ->order($type . ' ASC');
      $db->setQuery($query);

      self::$userList = $db->loadObjectList();
    }

    $hint = (!empty($this->hint) ? $this->hint : 'COM_JOOMGALLERY_COMMON_NO_USER');

    $options[] = JHtml::_('select.option',  '', JText::_($hint));

    if(!empty(self::$userList))
    {
      foreach(self::$userList as $user)
      {
        $options[] = JHtml::_('select.option', $user->value , $user->text);
      }
    }

    return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
  }

  /**
   * Method to get the field input markup in the case of
   * using a modal popup for user selection
   *
   * @return  string  The field input markup
   * @since 3.2
   */
  protected function getPopupInput()
  {
    // Initialize variables.
    $html     = array();
    $groups   = $this->getGroups();
    $excluded = $this->getExcluded();
    $link     = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
                  . (isset($groups) ? ('&amp;groups='.base64_encode(json_encode($groups))) : '')
                  . (isset($excluded) ? ('&amp;excluded='.base64_encode(json_encode($excluded))) : '');
    $required = '';
    $hint     = (!empty($this->hint) ? $this->hint : 'COM_JOOMGALLERY_COMMON_NO_USER');

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
      $script[] = '      if (value == "" || value == "' . JText::_($hint) . '") {';
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
    $script[] = '        document.getElementById("' . $this->id . '_name").value = "' . JText::_($hint, true) . '";';
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

    // Create a dummy text field with the user name.
    $html[] = '<div class="input-append">';
    $html[] = '  <input type="text" id="' . $this->id . '_name"' . ' value="' . htmlspecialchars($this->loadUser(), ENT_COMPAT, 'UTF-8')
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

  /**
   * Get the data that is going to be passed to the layout
   *
   * @return  array
   * @since 3.2
   */
  public function getLayoutData()
  {
    // Get the basic field data
    $data = JFormField::getLayoutData();

    $extraData = array(
        'userName'  => $this->loadUser(),
        'groups'    => $this->getGroups(),
        'excluded'  => $this->getExcluded()
    );

    if(empty($this->onchange))
    {
      $extraData['onchange'] = 'if (this.val() == 0) {'
                                 . 'setTimeout(function(){ jQuery("#' . $this->id. '").val("'
                                 . JText::_((!empty($this->hint) ? $this->hint : 'COM_JOOMGALLERY_COMMON_NO_USER'))
                                 . '"); }, 200);}';
    }

    return array_merge($data, $extraData);
  }

  /**
   * Load the user if available.
   *
   * @return  string  User's name resp. user's username.
   * @since 3.2
   */
  protected function loadUser()
  {
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
      $table->$type = JText::_((!empty($this->hint) ? $this->hint : 'COM_JOOMGALLERY_COMMON_NO_USER'));
      $this->value = '';
    }

    return $table->$type;
  }
}