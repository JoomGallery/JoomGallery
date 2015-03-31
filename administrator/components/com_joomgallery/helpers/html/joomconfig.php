<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/html/joomconfig.php $
// $Id: joomconfig.php 4076 2013-02-12 10:35:29Z erftralle $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
class JHTMLJoomConfig
{
  /**
   * Displays the start of one table
   *
   * @param $id string the name of the id assigned to the div
   */
  public static function start($id = 'page')
  {
?>
  <div id="<?php echo $id; ?>">
    <table class="adminlist table table-bordered">
<?php
  }

  /**
   * Displays the end of one table
   */
  public static function end()
  {
?>
    </table>
  </div>
<?php
  }

  /**
   * Displays a row (colspan="3") in the config table for additional informations.
   * The text will not be translated, so please use JText::_() afore.
   *
   * @param $text string the text which will be displayed in the row
   */
  public static function intro($text = '&nbsp;')
  {
?>
    <tr>
      <td colspan="3"><div class="alert alert-info"><?php echo $text; ?></div></td>
    </tr>
<?php
  }

  /**
   * Displays the title, the current setting and the description of
   * one single option of the configuration manager in a table row
   *
   * @param $key      string      the identifier of the configuration option, e.g. 'jg_pathimages'
   * @param $type     string      'text' => textfield, 'yesno' => yes/no selectbox, 'custom' => custom selectbox or textfield
   * @param $name     string      language constant for the title and the description (+_LONG) of the option, will be translated
   * @param $info     string/int  current setting of the option, if $type = 'custom', we will assume that it holds the complete HTML string
   * @param $display  boolean     if set to false, we won't display this option, defaults to true
   * @param $attribs  string      additional tag attributes (e.g. size = "50"), for now only effective for type 'text'
   * @param $text     string      Alternative description text, if null the default language constant will be used for each option
   */
  public static function row($key, $type, $name, $info, $display = true, $attribs = '', $text = null)
  {
    if(!$display)
    {
      return;
    }
?>
    <tr align="center" valign="middle">
      <td align="left" valign="top"><strong><?php echo JText::_($name); ?></strong></td>
      <td align="left" valign="top"><?php
    switch($type) {
      case 'text':
        ?><input type="text" name="<?php echo $key; ?>" value="<?php echo $info; ?>" <?php echo $attribs; ?>/><?php
        break;
      case 'yesno':
        static $yesno;
        if(!isset($yesno)){
          $yesno = array();
          $yesno[] = JHTML::_('select.option', '0', JText::_('JNO'));
          $yesno[] = JHTML::_('select.option', '1', JText::_('JYES'));
        }
        echo JHTML::_('select.genericlist', $yesno, $key, 'class="inputbox" size="2"', 'value', 'text', $info);
        break;
      case 'custom':
        echo $info;
        break;
      default:
        break;
    } ?></td>
      <td align="left" valign="top"><?php echo $text ? $text : JText::_($name.'_LONG'); ?></td>
    </tr>
<?php
  }
}