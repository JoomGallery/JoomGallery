<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/ftpfolders.php $
// $Id: ftpfolders.php 4231 2013-04-25 20:41:10Z erftralle $
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
 * Renders a folder selection form field
 *
 * @package     JoomGallery
 * @since       2.1
 */
class JFormFieldFtpFolders extends JFormField
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 2.1
   */
  protected $type = 'ftpfolders';

  /**
   * Returns the HTML for a folder selection form field.
   *
   * @return  string  The folder selection form field or empty string if no folder was found
   * @since   2.1
   */
  protected function getInput()
  {
    // Get files
    $directory  = (string)$this->element['directory'];
    if(!is_dir($directory))
    {
      $directory = JPath::clean(JPATH_ROOT.'/'.$directory);
    }
    $folders = JFolder::folders($directory, '.', true, true);

    if(!$folders || !count($folders))
    {
      return '';
    }

    // Create attributes
    $class  = $this->element['class'] ? (string) $this->element['class'] : '';
    if($this->element['required'] && $this->element['required'] == true && strpos($class, 'required') === false)
    {
      if(!empty($class))
      {
        $class .= ' ';
      }
      $class .= 'required';
    }
    $attr = !empty($class) ? ' class="'.$class.'"' : '';
    $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
    $attr .= $this->multiple ? ' multiple="multiple"' : '';

    if(!$this->element['onchange'])
    {
      $attr .= ' onchange="document.adminForm.task.value=\'\';document.adminForm.submit();"';
    }
    else
    {
      $attr .= ' onchange="' . (string) $this->element['onchange'] . '"';
    }

    // To avoid user's confusion, readonly="true" should imply disabled="true".
    if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
    {
      $attr .= ' disabled="disabled"';
    }

    // Create HTML
    $html = '<select name="'.$this->name.'"'.$attr.'>';
    array_unshift($folders, '');
    foreach($folders as $folder)
    {
      $folder = str_replace($directory, '', $folder).'/';
      $selected = '';
      if($folder == $this->value)
      {
        $selected = ' selected="selected"';
      }

      $html .= '
      <option'.$selected.'>'.$folder.'</option>';
    }
    $html .= '
  </select>';

    return $html;
  }
}