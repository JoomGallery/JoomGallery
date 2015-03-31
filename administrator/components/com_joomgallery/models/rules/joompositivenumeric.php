<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/rules/joompositivenumeric.php $
// $Id: joompositivenumeric.php 4076 2013-02-12 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die;

jimport('joomla.form.formrule');

/**
 * Form Rule class to check for a positive numeric field value.
 *
 * @package     JoomGallery
 * @since       2.0
 */
class JFormRuleJoomPositiveNumeric extends JFormRule
{
  /**
   * The regular expression to use in testing a form field value.
   *
   * @var   string
   * @since 2.0
   */
  protected $regex = '^[1-9]+[0-9]*$';
}