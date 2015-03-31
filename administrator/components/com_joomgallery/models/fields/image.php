<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/image.php $
// $Id: image.php 4383 2014-05-07 14:44:33Z erftralle $
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
 * Renders an image selection form field
 *
 * @package     JoomGallery
 * @since       2.0
 */
class JFormFieldImage extends JFormField
{
  /**
   * The form field type.
   *
   * @var     string
   * @since   2.0
   */
  protected $type = 'Image';

  /**
   * Returns the HTML for a image select box form field.
   *
   * @return  object    The image select box form field.
   * @since   2.0
   */
  protected function getInput()
  {
    require_once(JPATH_BASE.'/components/com_joomgallery/includes/defines.php');

    $db       = JFactory::getDBO();
    $doc      = JFactory::getDocument();
    $class    = $this->element['class'] ? (string) $this->element['class'] : '';
    $validate = false;

    if($this->element['required'] && $this->element['required'] == true && strpos($class, 'required') === false)
    {
      if(!empty($class))
      {
        $class .= ' ';
      }
      $class .= 'required';
    }

    // Store class attribute for input box displaying the image title
    $sclass = !empty($class) ? ' class="'.$class.'"' : '';

    if($this->element['validate'] && (string) $this->element['validate'] == 'joompositivenumeric')
    {
      $validate = true;
      // Add a validation script for form validation
      $js_validate = "
        jQuery(document).ready(function() {
          document.formvalidator.setHandler('joompositivenumeric', function(value) {
            regex=/^[1-9]+[0-9]*$/;
            return regex.test(value);
          })
        });";
      $doc->addScriptDeclaration($js_validate);

      // Element class needs attribute validate-...
      if(!empty($class))
      {
        $class .= ' ';
      }
      $class .= 'validate-'.(string) $this->element['validate'];
    }

    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_joomgallery/tables');
    $img = JTable::getInstance('joomgalleryimages', 'Table');
    if($this->value)
    {
      $img->load($this->value);
    }
    else
    {
      $img->imgtitle = "";
    }

    $js = "
    function joom_selectimage(id, title, object) {
      document.getElementById(object).value            = id;
      document.getElementById(object + '_name').value  = title;
      window.parent.SqueezeBox.close();";
    if($validate)
    {
      $js .= "
      document.formvalidator.validate(document.getElementById(object));
      document.formvalidator.validate(document.getElementById(object + '_name'));";
    }
      $js .= "
    }";
    $doc->addScriptDeclaration($js);

    $link = 'index.php?option=com_joomgallery&view=mini&extended=0&format=raw&catid=0&object='.$this->id;

    JHTML::_('behavior.modal', 'a.modal');
    $html = '
    <div style="float: left;">
      <input'.$sclass.' type="text" size="30" id="'.$this->id.'_name" value="'.htmlspecialchars($img->imgtitle, ENT_QUOTES, 'UTF-8').'" readonly="readonly" />
    </div>
    <div class="button2-left">
      <div class="blank">
        <a class="modal" title="'.JText::_('COM_JOOMGALLERY_LAYOUT_COMMON_CHOOSE_IMAGE').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 620, y: 550}}">'.JText::_('COM_JOOMGALLERY_COMMON_PLEASE_SELECT_IMAGE').'</a>
      </div>
    </div>
    <input class="'.$class.'" type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.(int)$this->value.'"/>';

    return $html;
  }
}