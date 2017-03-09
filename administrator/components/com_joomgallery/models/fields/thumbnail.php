<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/thumbnail.php $
// $Id: thumbnail.php 4201 2013-04-15 21:35:52Z chraneco $
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
 * Renders a thumbnail selection form field
 *
 * @package JoomGallery
 * @since   2.0
 */
class JFormFieldThumbnail extends JFormField
{
  /**
   * The form field type.
   *
   * @var     string
   * @since   2.0
   */
  protected $type = 'Thumbnail';

  /**
   * Returns the HTML for a thumbnail selection form field.
   *
   * @return  object    The thumbnail selection form field.
   * @since   2.0
   */
  protected function getInput()
  {
    JHtml::_('bootstrap.tooltip');

    $app         = JFactory::getApplication();
    $db          = JFactory::getDBO();
    $doc         = JFactory::getDocument();
    $imagelib_id = $this->element['imagelib_id'] ? $this->element['imagelib_id'] : 'imagelib';
    $script      = array();
    $html        = array();
    $css         = array();
    $catid       = 0;

    if($app->isAdmin())
    {
      // Get category id from request
      $cids   = JRequest::getVar('cid', array(), '', 'array');

      if(isset($cids[0]))
      {
        $catid = intval($cids[0]);
      }

      // Prepare the path for the thumbnail preview
      $path = JRoute::_('index.php?option=' . _JOOM_OPTION . '&controller=images&view=image&format=raw&type=thumb', false) . '&cid=';
    }
    else
    {
      // Get category id from request
      $catid = JRequest::getInt('catid', 0);

      // Prepare the path for the thumbnail preview
      $path = JRoute::_('index.php?option=' . _JOOM_OPTION . '&view=image&format=raw&type=thumb', false) . '&id=';
    }

    $script[] = '  function joom_selectimage(id, title, object, filename) {';
    $script[] = '    document.getElementById(object + "_id").value = id;';
    $script[] = '    document.getElementById(object + "_name").value = title;';
    $script[] = '    jQuery("#' . $this->id . '_clear").removeClass("hidden");';
    $script[] = '    if(id != "") {';
    $script[] = '      document.getElementById("' . $imagelib_id . '").src = "' . $path . '" + id';
    $script[] = '    } else {';
    $script[] = '      document.getElementById("' . $imagelib_id . '").src = "' . JURI::root(true) . '/media/system/images/blank.png";';
    $script[] = '    }';
    $script[] = '    jQuery("#modalSelectThumbnail").modal("hide");';
    $script[] = '  }';
    $script[] = '  function joom_clearthumb() {';
    $script[] = '    jQuery("#' . $this->id . '_clear").addClass("hidden");';
    $script[] = '    document.getElementById("' . $this->id . '_id").value = 0;';
    $script[] = '    document.getElementById("' . $this->id . '_name").value = "-";';
    $script[] = '    document.getElementById("' . $imagelib_id . '").src = "' . JURI::root(true) . '/media/system/images/blank.png";';
    $script[] = '    return false';
    $script[] = '  }';

    $doc->addScriptDeclaration(implode("\n", $script));

    // Remove bottom border from modal header as we will not have a title
    $css[] = '  #modalSelectThumbnail .modal-header {';
    $css[] = '    border-bottom: none;';
    $css[] = '  }';

    $doc->addStyleDeclaration(implode("\n", $css));

    // Get the image title
    $img = JTable::getInstance('joomgalleryimages', 'Table');

    if(!empty($this->value))
    {
      $img->load($this->value);
    }
    else
    {
      $img->imgtitle = '-';
    }

    $title = htmlspecialchars($img->imgtitle, ENT_QUOTES, 'UTF-8');

    $link = 'index.php?option=com_joomgallery&amp;view=mini&amp;extended=0&amp;prefix=joom&amp;format=raw&amp;object='
              . $this->id . '&amp;type=category&amp;catid=' . $catid;

    $html[] = '<span class="input-append">';
    $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '"'
                . ' readonly="readonly" disabled="disabled" size="35" />';
    $html[] = '<a href="#modalSelectThumbnail"  class="btn hasTooltip" role="button"  data-toggle="modal"'
                . ' title="'
                . ($app->isAdmin() ? JHtml::tooltipText('COM_JOOMGALLERY_CATMAN_SELECT_THUMBNAIL_TIP') : JHtml::tooltipText('COM_JOOMGALLERY_COMMON_SELECT_THUMBNAIL_TIP')) . '">'
                . '<i class="icon-image"></i> '
                . ($app->isAdmin() ? JText::_('COM_JOOMGALLERY_CATMAN_SELECT_THUMBNAIL') : JText::_('COM_JOOMGALLERY_COMMON_SELECT'))
                . '</a>';

    $html[] = JHtmlBootstrap::renderModal(
                'modalSelectThumbnail', array(
                  'url'     => $link . '&amp;' . JSession::getFormToken() . '=1"',
                  'width'   => '620px',
                  'height'  => '390px'
                 )
              );

    $html[] = '<button id="' . $this->id . '_clear" class="btn' . ($this->value ? '' : ' hidden') . ' hasTooltip" title="'
                . ($app->isAdmin() ? JHtml::tooltipText('COM_JOOMGALLERY_CATMAN_REMOVE_CATTHUMB_TIP') : JHtml::tooltipText('COM_JOOMGALLERY_COMMON_REMOVE_CATTHUMB_TIP'))
                . '" onclick="return joom_clearthumb()"><span class="icon-remove"></span></button>';

    $html[] = '</span>';
    $html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $this->value . '"/>';

    return implode("\n", $html);
  }
}