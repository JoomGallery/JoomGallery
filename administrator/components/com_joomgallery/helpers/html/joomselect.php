<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/html/joomselect.php $
// $Id: joomselect.php 4398 2014-06-11 13:39:02Z erftralle $
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
 * Utility class for creating HTML select lists
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
abstract class JHtmlJoomSelect
{
  /**
   * Number of active users in the system.
   * This is not set until first call of method users().
   *
   * @var   int
   * @since 3.0
   */
  static $count = null;

  /**
   * Construct HTML List of selectable categories
   *
   * @param   int     $currentcat catid, current cat or parent
   * @param   string  $cname      Name of the HTML element
   * @param   string  $extra      Some extra code to add to the element
   * @param   int     $orig       A category to ignore (its sub-categories will be filtered out, too)
   * @param   string  $separator  A string with which the categories will be separated in the category paths
   * @param   string  $task       Null/filter
   * @param   string  $action     Action to check for each category
   * @param   mixed   $idtag      String to use as id tag for the select box
   * @return  string  The HTML output
   * @since   1.0.0
   */
  public static function categoryList($currentcat, $cname = 'catid', $extra = null, $orig = null, $separator  = '- ', $task = null, $action = 'core.create', $idtag = false)
  {
    $attribs = 'class="inputbox"';
    if($extra)
    {
      // Add the default class only if no class is given by caller
      if(strpos($extra, 'class=') === false && strpos($extra, 'class =') === false)
      {
        $attribs .= ' '.$extra;
      }
      else
      {
        $attribs = $extra;
      }
    }

    if(JoomConfig::getInstance()->get('jg_ajaxcategoryselection'))
    {
      $app = JFactory::getApplication();

      if(!$idtag)
      {
        $idtag = $cname;
      }

      if($currentcat == 1)
      {
        $name = '-';
      }
      else
      {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
              ->select('name')
              ->from(_JOOM_TABLE_CATEGORIES)
              ->where('cid = '.(int) $currentcat);
        $db->setQuery($query);
        $name = $db->loadResult();
      }

      $matches = array();
      $onchange = '';
      if(preg_match('/onchange="(.*)"/', $attribs, $matches))
      {
        $onchange = $matches[1];
        $attribs = trim(str_replace($matches[0], '', $attribs));
      }
      $attribs = preg_replace('/size="(.*)"/', '', $attribs);

      JText::script('COM_JOOMGALLERY_COMMON_REQUEST_ERROR');
      JText::script('COM_JOOMGALLERY_COMMON_CATEGORIES_NO_RESULTS');

      JHtml::_('behavior.framework', true);
      $doc = JFactory::getDocument();
      if($app->isSite())
      {
        $url = JRoute::_('index.php?option='._JOOM_OPTION.'&task=categories.getcategories', false);
        $doc->addStyleSheet(JoomAmbit::getInstance()->getStyleSheet('joomgallery.css'));
      }
      else
      {

        $url = JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories&task=getcategories', false);
        $doc->addStyleSheet(JoomAmbit::getInstance()->getStyleSheet('admin.joomgallery.css'));
      }
      $doc->addScript(JoomAmbit::getInstance()->getScript('categories.js'));
      $jscript = '
        <script type="text/javascript">
          var '.$idtag.'search;
          window.addEvent(\'domready\', function() {
            '.$idtag.'search = new JoomGallerySearchCategories({
              \'inputbox\': \'jg-'.$idtag.'-input\',
              \'resultbox\': \'jg-'.$idtag.'-results\',
              \'hiddenbox\': \''.$idtag.'\',
              \'moreresults\': \'jg-'.$idtag.'-more-results\',
              \'defaultcontent\': \''.($name ? addslashes($name) : JText::_('COM_JOOMGALLERY_COMMON_ALL', true)).'\',
              \'variablename\': \''.$idtag.'search\',
              \'action\': \''.($task ? '' : $action).'\',
              \'filter\': '.(int) $orig.',
              \'current\': '.(int) $currentcat.',
              \'onchange\': \''.str_replace('\'', '\\\'', $onchange).'\',
              \'url\': \''.$url.'&format=json\'
            });
          });
        </script>';

      return $jscript . '
        <input type="hidden" name="'.$cname.'" value="'.$currentcat.'" id="'.$idtag.'" '.$attribs.' />
        <input type="text" name="jg-input-'.$cname.'" value="'.($name ? addslashes($name) : JText::_('COM_JOOMGALLERY_COMMON_ALL')).'" id="jg-'.$idtag.'-input" '.str_replace('validate-joompositivenumeric', '', $attribs).' />
        <div id="jg-'.$idtag.'-results" class="jg-category-results">
          <div id="jg-'.$idtag.'-more-results" class="jg-category-more-results" onclick="'.$idtag.'search.loadMore();return false;">
            <a href="#" onclick="return false;">
              '.JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES_MORE_RESULTS').'</a>
          </div>
        </div>';
    }

    $user           = JFactory::getUser();
    $ambit          = JoomAmbit::getInstance();
    $cats           = $ambit->getCategoryStructure(true);
    $options        = array();
    //$filter         = ($cname == 'parent_id' && $orig != null) ? true : false;
    $origfound      = false;
    $filtercatkeys  = array();

    $paths          = array();

    $action2 = false;
    if($action == 'joom.upload')
    {
      $action2 = 'joom.upload.inown';
    }
    if($action == 'core.create')
    {
      $action2 = 'joom.create.inown';
    }
    if($action == 'core.edit')
    {
      $action2 = 'core.edit.own';
    }

    foreach($cats as $key => $cat)
    {
      // Avoid 'ROOT' entry in drop down list in the case that no category has been created yet
      if($cat->cid === 0)
      {
        continue;
      }

      // Check whether a certain category and it's sub-categories
      // have to be filtered out of the list
      if($orig)
      {
        if(!$origfound)
        {
          if($cat->cid == $orig)
          {
            $origfound            = true;
            $filtercatkeys[$orig] = true;
          }
        }
        else
        {
          if(isset($filtercatkeys[$cat->parent_id]))
          {
            $filtercatkeys[$key] = true;
          }
        }
      }

      if($cat->parent_id > 1)
      {
        //$paths[$key] = $paths[$cat->parent_id].$separator.$cat->name;
        $paths[$key] = $paths[$cat->parent_id] + 1;
      }
      else
      {
        $paths[$key] = 0;
      }

      if(     $task == 'filter'
          ||  $key == $currentcat
          ||  (     !isset($filtercatkeys[$key])
                &&  (     $user->authorise($action, _JOOM_OPTION.'.category.'.$key)
                      ||  (     $action2
                            &&  $cat->owner == $user->get('id')
                            &&  $user->authorise($action2, _JOOM_OPTION.'.category.'.$cat->cid)
                          )
                    )
              )
        )
      {
        // Build select option for that category
        $options[$key] = new stdClass();
        $options[$key]->cid   = $cat->cid;
        $options[$key]->path  = str_repeat($separator, $paths[$key]).$cat->name;
      }
    }

    if($task == 'filter' || $currentcat == 0 || ($action == 'core.create' && $user->authorise($action, _JOOM_OPTION)))
    {
      $rootcat = new stdClass();
      $rootcat->cid   = '';
      $rootcat->path  = ($task == 'filter') ? JText::_('COM_JOOMGALLERY_COMMON_ALL') : '';
      array_unshift($options, $rootcat);
    }

    $count = count($options);
    if(!$count || ($count == 1 && !reset($options)->cid))
    {
      // Return a hidden field in order to avoid JavaScript errors
      return '<input type="hidden" name="'.$cname.'"'.($idtag ? ' id="'.$idtag.'"' : '').' value="" />
      <input type="text" readonly="readonly" class="readonly" value="-" />';
    }

    // Sort the array with key pathname if more than one element
    if($count > 1)
    {
      //usort($options, array('JHTMLJoomSelect', 'sortCatArray'));
    }

    $output = JHTML::_('select.genericlist', $options, $cname, $attribs, 'cid', 'path', $currentcat, $idtag);

    return $output;
  }

  /**
   * Construct HTML list of users
   *
   * @param   string  $name       Name of the HTML select list to use
   * @param   array   $active     Array of selected users
   * @param   boolean $nouser     True, if 'No user' should be included on top of the list
   * @param   array   $additional Additional entries to add
   * @param   string  $javascript Additional code in the select list
   * @param   int     $multiple   Size of the box if it shall be a multiple select box, 0 otherwise
   * @param   mixed   $idtag      String to use as id tag for the select box
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public static function users($name, $active, $nouser = false, $additional = array(), $javascript = null, $multiple = 6, $idtag = false)
  {
    static $users = null;

    $config = JoomConfig::getInstance();

    if(is_null($users))
    {
      $db     = JFactory::getDbo();

      $type = $config->get('jg_realname') ? 'name' : 'username';

      $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from('#__users')
            ->where('block = 0');
      $db->setQuery($query);

      self::$count = $db->loadResult();

      if(self::$count <= $config->get('jg_use_listbox_max_user_count') || !$multiple)
      {
        $users = array();

        $query->clear()
              ->select('id AS value')
              ->select($type.' AS text')
              ->from('#__users')
              ->where('block = 0')
              ->order($type.' ASC');
        $db->setQuery($query);

        $users = $db->loadObjectList();
      }
    }

    if(self::$count > $config->get('jg_use_listbox_max_user_count') && $multiple)
    {
      return '<input type="text" name="'.$name.'" value="'.implode(',', $active).'" class="inputbox"'.($idtag ? ' id="'.$idtag.'"' : '').'/>';
    }

    $options = array();

    foreach($additional as $key => $value)
    {
      $options[] = JHtml::_('select.option',  $key, $value);
    }

    if($nouser)
    {
      if(JFactory::getApplication()->isSite())
      {
        $options[] = JHtml::_('select.option',  '0', JText::_('COM_JOOMGALLERY_DETAIL_NAMETAGS_SELECT_USER'));
      }
      else
      {
        $options[] = JHtml::_('select.option',  '0', JText::_('COM_JOOMGALLERY_COMMON_NO_USER'));
      }
    }

    $options = array_merge($options, $users);

    $multiple_box = '';
    if($multiple > 1)
    {
      $multiple_box = ' multiple="multiple" size="'.$multiple.'"';
    }

    if($javascript)
    {
      $javascript = ' onchange="'.$javascript.'"';
    }

    return JHtml::_('select.genericlist', $options, $name, 'class="inputbox"'.$multiple_box.$javascript, 'value', 'text', $active, $idtag);
  }

  /**
   * Callback function for sorting an array of objects to assembled names of
   * categories with alle parent categories
   *
   * @param   object $a Element one
   * @param   object $b Element two
   * @return  int     0 if names equal, -1 if a < b, 1 if a > b
   * @since   1.0.0
   */
  public static function sortCatArray($a, $b)
  {
    return strcmp($a->path, $b->path);
  }

  /**
   * Creates a HMTL select list of OpenImage options
   *
   * @param   string  $name         Name of the field
   * @param   string  $selected     Option that should be preselected.
   * @param   boolean $detail       True, if the default detail view shall be an available option, defaults to true
   * @param   boolean $default      True, if the 'like in JoomGallery configured' shall be an available option, defaults to false
   * @param   string  $lang_prefix  Prefix for the language constants of the options, defaults to 'COM_JOOMGALLERY_CONFIG_CV_GS_OPEN'
   * @param   string  $attr         Optional attributes for the HTML element
   * @param   array   $extra        Array of additional options for the select list
   * @return  string  The HTML select list of OpenImage options
   * @since   3.0
   */
  public static function openImage($name, $selected = null, $detail = true, $default = false, $lang_prefix = 'COM_JOOMGALLERY_CONFIG_CV_GS_OPEN_', $attr = null, $extra = array())
  {
    // Additional options are placed first in the list
    $options = $extra;

    if($default)
    {
      $options[] = JHtml::_('select.option', 'default', JText::_($lang_prefix.'LIKE_IN_JOOMGALLERY'));
    }

    if($detail)
    {
      $options[] = JHtml::_('select.option', '0', JText::_($lang_prefix.'DEFAULT'));
    }

    $options[] = JHtml::_('select.option', '1', JText::_($lang_prefix.'BLANK_WINDOW'));
    $options[] = JHtml::_('select.option', '2', JText::_($lang_prefix.'JS_WINDOW'));
    $options[] = JHtml::_('select.option', '3', JText::_($lang_prefix.'DHTML'));
    #$options[] = JHtml::_('select.option', '4', JText::_('COM_JOOMGALLERY_MODAL'));
    $options[] = JHtml::_('select.option', '5', JText::_($lang_prefix.'THICKBOX3'));
    $options[] = JHtml::_('select.option', '6', JText::_($lang_prefix.'SLIMBOX'));

    // Load plugins in order to search for additional OpenImage plugins
    JPluginHelper::importPlugin('joomgallery');
    $plugins = JDispatcher::getInstance()->trigger('onJoomOpenImageGetName');

    foreach($plugins as $plugin)
    {
      $options[] = JHtml::_('select.option', $plugin);
    }

    if(!$attr)
    {
      $attr = 'class="inputbox" size="'.count($options).'"';
    }

    return JHtml::_('select.genericlist', $options, $name, $attr, 'value', 'text', $selected);
  }
}