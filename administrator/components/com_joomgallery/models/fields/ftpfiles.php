<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/ftpfiles.php $
// $Id: ftpfiles.php 4231 2013-04-25 20:41:10Z erftralle $
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
 * Renders a file selection form field
 *
 * @package     JoomGallery
 * @since       2.1
 */
class JFormFieldFtpFiles extends JFormField
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 2.1
   */
  protected $type = 'ftpfiles';

  /**
   * Returns the HTML for a file selection form field.
   *
   * @return  string  The field input markup
   * @since   2.1
   */
  protected function getInput()
  {
    // Get files
    $directory  = (string)$this->element['directory'];
    if(!is_dir($directory))
    {
      $directory = JPATH_ROOT.'/'.$directory;
    }
    $files = JFolder::files($directory, '\.bmp$|\.gif$|\.jpg$|\.png$|\.jpeg$|\.jpe$|\.BMP$|\.GIF$|\.JPG$|\.PNG$|\.JPEG$|\.JPE$');
    if(!$files)
    {
      $files = array();
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
    $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

    // To avoid user's confusion, readonly="true" should imply disabled="true".
    if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
    {
      $attr .= ' disabled="disabled"';
    }

    // Create HTML
    $html = '<select name="'.$this->name.'"'.$attr.'>';
    foreach($files as $file)
    {
      $html .= '
      <option class="jg-file">'.$file.'</option>';
    }
    $html .= '
  </select>
  <p>
    <button onclick="$$(\'.jg-file\').each(function(el) { el.selected = true; });return false;" type="button" class="btn">'.JText::_('JGLOBAL_SELECTION_ALL').'</button>
    <button onclick="$$(\'.jg-file\').each(function(el) { el.selected = !el.selected; });return false;" type="button" class="btn">'.JText::_('JGLOBAL_SELECTION_INVERT').'</button>
  </p>';

    return $html;
  }
}