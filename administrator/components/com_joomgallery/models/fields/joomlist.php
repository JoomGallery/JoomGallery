<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/joomlist.php $
// $Id: joomlist.php 4076 2015-09-02 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2015  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Renders a Joomlist field
 *
 * @package     JoomGallery
 * @since       3.2
 */
class JFormFieldJoomlist extends JFormFieldList
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 3.2
   */
  protected $type = 'Joomlist';

  /**
   * Method to get the field options.
   *
   * @return  array  The field option objects.
   *
   * @since   3.2
   */
  protected function getOptions()
  {
    $options = array();

    foreach($this->element->children() as $option)
    {
      // Only add <option /> elements.
      if($option->getName() != 'option')
      {
        continue;
      }

      // Filter requirements
      if($requires = explode(',', (string) $option['requires']))
      {
        // Requires multilanguage
        if(in_array('multilanguage', $requires) && !JLanguageMultilang::isEnabled())
        {
          continue;
        }

        // Requires associations
        if(in_array('associations', $requires) && !JLanguageAssociations::isEnabled())
        {
          continue;
        }
      }

      $value = (string) $option['value'];

      $disabled = (string) $option['disabled'];
      $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

      $disabled = $disabled || ($this->readonly && $value != $this->value);

      // Create a new option object based on the <option /> element.
      $optionLabel      = "";
      $optionLabelParts = explode(',', trim((string) $option));

      foreach($optionLabelParts As $optionLabelPart)
      {
        $optionLabel .= ' ' . JText::alt(trim($optionLabelPart), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname));
      }

      $tmp = JHtml::_('select.option', $value, trim($optionLabel), 'value', 'text', $disabled);

      // Set some option attributes.
      $tmp->class = (string) $option['class'];

      // Set some JavaScript option attributes.
      $tmp->onclick = (string) $option['onclick'];

      // Add the option object to the result set.
      $options[] = $tmp;
    }

    reset($options);

    return $options;
  }
}