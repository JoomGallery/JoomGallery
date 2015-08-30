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
    require_once(JPATH_BASE . '/components/com_joomgallery/includes/defines.php');

    $db       = JFactory::getDBO();
    $doc      = JFactory::getDocument();
    $required = $this->required ? ' required="required"' : '';
    $validate = ($this->validate && $this->validate == 'joompositivenumeric') ? true : false;
    $class    = '';
    $script   = array();
    $html     = array();
    $css      = array();

    JHtml::_('bootstrap.tooltip');

    if($validate)
    {
      $class = 'validate-' . $this->validate;

      // Add a validation script for form validation
      $script[] = '  jQuery(document).ready(function() {';
      $script[] = '    document.formvalidator.setHandler("joompositivenumeric", function(value) {';
      $script[] = '      regex = /^[1-9]+[0-9]*$/;';
      $script[] = '      return regex.test(value);';
      $script[] = '    })';
      $script[] = '  });';
    }

    // Add script for fetching the selected image in the modal dialog
    $script[] = '  function joom_selectimage(id, title, object) {';
    $script[] = '    document.getElementById(object).value            = id;';
    $script[] = '    document.getElementById(object + "_name").value  = title;';
    $script[] = '    jQuery("#modalSelectImage").modal("hide");';
    if($validate)
    {
      $script[] = '    document.formvalidator.validate(document.getElementById(object));';
      $script[] = '    document.formvalidator.validate(document.getElementById(object + "_name"));';
    }
    $script[] = '  }';

    $doc->addScriptDeclaration(implode("\n", $script));

    // Remove bottom border from modal header as we will not have a title
    $css[] = '  #modalSelectImage .modal-header {';
    $css[] = '    border-bottom: none;';
    $css[] = '  }';

    $doc->addStyleDeclaration(implode("\n", $css));

    JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_joomgallery/tables');
    $img = JTable::getInstance('joomgalleryimages', 'Table');

    if($this->value)
    {
      $img->load($this->value);
    }
    else
    {
      $img->imgtitle = '';
    }

    $link  = 'index.php?option=com_joomgallery&amp;view=mini&amp;extended=0&amp;format=raw&amp;catid=0&amp;object=' . $this->id;
    $title = htmlspecialchars($img->imgtitle, ENT_QUOTES, 'UTF-8');

    $html[] = '<span class="input-append">';
    $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '"' . $required . ' readonly="readonly" size="40" />';
    $html[] = '<a href="#modalSelectImage"  class="btn hasTooltip" role="button"  data-toggle="modal"'
                . ' title="' . JHtml::tooltipText('COM_JOOMGALLERY_LAYOUT_COMMON_CHOOSE_IMAGE') . '">'
                . '<i class="icon-image"></i> ' . JText::_('JSELECT')
                . '</a>';

    $html[] = JHtmlBootstrap::renderModal(
                'modalSelectImage', array(
                  'url'     => $link . '&amp;' . JSession::getFormToken() . '=1"',
                  'width'   => '620px',
                  'height'  => '390px'
                 )
              );

    $html[] = '</span>';

    if($this->required)
    {
      if(!empty($class))
      {
        $class .= ' ';
      }
      $class .= 'required';
    }

    $html[] = '<input class="' . $class . '" type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . (int) $this->value . '"/>';

    return implode("\n", $html);
  }
}