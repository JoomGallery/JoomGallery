<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/joompassword.php $
// $Id: joompassword.php 4326 2013-09-03 22:38:43Z chraneco $
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

JFormHelper::loadFieldClass('password');

/**
 * Renders a form field for encrypted passwords
 *
 * @package JoomGallery
 * @since   3.1
 */
class JFormFieldJoomPassword extends JFormFieldPassword
{
  /**
   * The form field type
   *
   * @var   string
   * @since 3.1
   */
  public $type = 'Joompassword';

  /**
   * Method to get the field input markup
   *
   * @return  string  The field input markup
   * @since   3.1
   */
  protected function getInput()
  {
    if($this->value)
    {
      // Hide password field and change its name
      $name = $this->name;
      $this->name = $this->name.'-disabled';
      $this->value = '';
      if($this->element['class'])
      {
        $this->element['class'] = $this->element['class'].' hidden';
      }
      else
      {
        $this->element['class'] = 'hidden';
      }

      return '<span id="'.$this->id.'_info">
        <span class="label label-info hasTooltip" title="'.JText::_('COM_JOOMGALLERY_CATMAN_PASSWORD_PROTECTED_TIP').'">'.JText::_('COM_JOOMGALLERY_CATMAN_PASSWORD_PROTECTED').'</span>
        <button class="btn btn-mini hasTooltip" title="'.JText::_('COM_JOOMGALLERY_CATMAN_PASSWORD_RESET_TIP').'" onclick="jQuery(\'#'.$this->id.'\').attr(\'name\', \''.$name.'\').removeClass(\'hidden\').focus();jQuery(\'#'.$this->id.'_info\').addClass(\'hide\');return false;">
          <i class="icon-cancel"></i>
        </button>
      </span>'.parent::getInput();
    }
    else
    {
      return parent::getInput();
    }
  }
}