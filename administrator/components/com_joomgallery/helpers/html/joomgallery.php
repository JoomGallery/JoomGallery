<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/html/joomgallery.php $
// $Id: joomgallery.php 4404 2014-06-26 21:23:58Z chraneco $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package JoomGallery
 * @since   1.5.5
 */
abstract class JHtmlJoomGallery
{
  /**
   * Displays the type of an image or category
   *
   * @param   object  $row              Data object
   * @param   string  $user_uploaded    String to display in case of user created objects
   * @param   string  $admin_uploaded   String to display in case of objects created by an administrator
   * @return  string  Html code to display the upload type
   * @since   1.5.5
   */
  public static function type($row, $user_uploaded = 'COM_JOOMGALLERY_COMMON_USER_UPLOAD', $admin_uploaded = 'COM_JOOMGALLERY_COMMON_ADMIN_UPLOAD')
  {
    $ambit = JoomAmbit::getInstance();
    if(   (isset($row->useruploaded) && $row->useruploaded)
       ||
          (!isset($row->useruploaded) && $row->owner)
      )
    {
      $img    = 'users.png';
      $title  = JText::_($user_uploaded);
    }
    else
    {
      $img    = 'admin.png';
      $title  = JText::_($admin_uploaded);
    }

    $imgsrc = $ambit->getIcon($img);
    $html   = '<img src="'.$imgsrc.'" alt="'.$title.'" title="'.$title.'" />'
    ;

    return $html;
  }

  /**
   * Displays the name or user name of a category, image or comment owner
   * and may link it to the profiles of other extensions (if available).
   *
   * @param   int     $userId   The ID of the user who will be displayed
   * @param   boolean $context  The context in which the user will be dispayed
   * @return  string  The user's name
   * @since   1.5.5
   */
  public static function displayName($userId, $context = null)
  {
    $userId = intval($userId);

    if(!$userId)
    {
      if(JFactory::getApplication()->isSite())
      {
        return JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
      }
      else
      {
        return JText::_('COM_JOOMGALLERY_COMMON_NO_USER');
      }
    }

    $config     = JoomConfig::getInstance();
    $dispatcher = JDispatcher::getInstance();

    $realname   = $config->get('jg_realname') ? true : false;

    $plugins    = $dispatcher->trigger('onJoomDisplayUser', array($userId, $realname, $context));

    foreach($plugins as $plugin)
    {
      if($plugin)
      {
        return $plugin;
      }
    }

    $user = JFactory::getUser($userId);

    if($realname)
    {
      $username = $user->get('name');
    }
    else
    {
      $username = $user->get('username');
    }

    return $username;
  }

  /**
   * Fires onPrepareContent for a text if configured in the gallery
   *
   * @param   string  $text The text to be transformed.
   * @return  string  The text after transformation.
   * @since   1.5.5
   */
  public static function text($text)
  {
    $config = JoomConfig::getInstance();

    if($config->get('jg_contentpluginsenabled'))
    {
      $text = JHTML::_('content.prepare', $text);
    }

    return $text;
  }

  /**
   * Returns the HTML tag of a specified icon
   *
   * @param   string  $icon       Filename of the icon
   * @param   string  $alt        Alternative text of the icon
   * @param   string  $extra      Additional HTML code in the tag
   * @param   string  $path       Path to the icon, if null the default path is used
   * @param   boolean $translate  Determines whether the text will be translated, defaults to true.
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public static function icon($icon, $alt = 'Icon', $extra = null, $path = null, $translate = true)
  {
    if(is_null($path))
    {
      $ambit = JoomAmbit::getInstance();
      $path = $ambit->get('icon_url');

      // Make the icons overridable by layout packages
      if($layout = $ambit->get('layout'))
      {
        if(is_file(JPATH_ROOT.'/media/joomgallery/images/'.$layout.'/'.$icon))
        {
          $path = $path.$layout.'/';
        }
      }
    }

    if($extra)
    {
      $extra = ' '.$extra;
    }

    if($translate)
    {
      $alt = JText::_($alt);
    }

    $class = 'jg-icon-'.str_replace(array('.jpg', '.png', '.gif'), '', $icon);

    return '<img src="'.$path.$icon.'" alt="'.$alt.'" class="pngfile jg_icon '.$class.'"'.$extra.' />';
  }

  /**
   * Displays the toplist bar
   *
   * @return  void
   * @since   1.5.5
   */
  public static function toplistbar()
  {
    $config = JoomConfig::getInstance();
    $separator = "    -\n";

    echo JText::sprintf('COM_JOOMGALLERY_TOPLIST_TOP', $config->get('jg_toplist')); ?>
<?php
    if($config->get('jg_showrate'))
    {
?>
    <a href="<?php echo JRoute::_('index.php?view=toplist&type=toprated'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_TOPLIST_TOP_RATED'); ?></a>
<?php
      if($config->get('jg_showlatest') || $config->get('jg_showcom') || $config->get('jg_showmostviewed'))
      {
        echo $separator;
      }
    }
    if($config->get('jg_showlatest'))
    {
?>
    <a href="<?php echo JRoute::_('index.php?view=toplist&type=lastadded'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_TOPLIST_LAST_ADDED'); ?></a>
<?php
      if($config->get('jg_showcom') || $config->get('jg_showmostviewed'))
      {
        echo $separator;
      }
    }
    if($config->get('jg_showcom'))
    {
?>
    <a href="<?php echo JRoute::_('index.php?view=toplist&type=lastcommented'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_TOPLIST_LAST_COMMENTED'); ?></a>
<?php
      if($config->get('jg_showmostviewed'))
      {
        echo $separator;
      }
    }
    if($config->get('jg_showmostviewed'))
    {
?>
    <a href="<?php echo JRoute::_('index.php?view=toplist'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_TOPLIST_MOST_VIEWED'); ?></a>
<?php
    }
  }

  /**
   * Creates the name tags
   *
   * @param   array   $rows An array of name tag objects
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public static function nametags(&$rows)
  {
    if(!count($rows))
    {
      return '';
    }

    $config = JoomConfig::getInstance();
    $width  = $config->get('jg_nameshields_width');

    $html   = '';
    $i      = 1;
    foreach($rows as $row)
    {
      $name     = JHTMLJoomGallery::displayName($row->nuserid, 'nametag');
      $length   = strlen(trim(strip_tags($name))) * $width;

      $icon     = '';
      $html    .= '<div id="jg-nametag-'.$i.'" style="position:absolute; top:'.$row->nxvalue.'px; left:'.$row->nyvalue.'px; width:'.$length.'px; z-index:'.$row->nzindex.'" class="nameshield';
      //if($config->get('jg_nameshields_others'))
      //{
        $user = JFactory::getUser();
        if($row->by == $user->get('id') || $row->nuserid == $user->get('id') || $user->authorise('core.manage', _JOOM_OPTION))
        {
          $html .= '" onmouseover="javascript:document.id(\'jg-nametag-remove-icon-'.$row->nid.'\').position({relativeTo: \'jg-nametag-'.$i.'\', position: \'upperRight\', edge: \'bottomLeft\'}).wink(3000);';
          $icon = '<a id="jg-nametag-remove-icon-'.$row->nid.'" class="jg-nametag-remove-icon jg_displaynone" href="javascript:if(confirm(\''.JText::_('COM_JOOMGALLERY_DETAIL_NAMETAGS_ALERT_SURE_DELETE_OTHERS', true).'\')){ location.href=\''.JRoute::_('index.php?task=nametags.remove&id='.$row->npicid.'&nid='.$row->nid, false).'\';}">'
                        .JHTML::_('joomgallery.icon', 'tag_delete.png', 'COM_JOOMGALLERY_DETAIL_NAMETAGS_DELETE_OTHERS_TIPCAPTION').'</a>';
        }
      //}
      $html    .= '">';
      $html    .= $name;
      $html    .= '</div>';
      $html    .= $icon;

      $i++;
    }

    return $html;
  }

  /**
   * Returns the string of an anchor for a URL if using anchors is enabled
   *
   * @param   string  $name Name of the anchor
   * @return  string  The string of the anchor
   * @since   1.5.5
   */
  public static function anchor($name = 'joomimg')
  {
    $config = JoomConfig::getInstance();

    $anchor = '';
    if($name && $config->get('jg_anchors'))
    {
      $anchor = '#'.$name;
    }

    return $anchor;
  }

  /**
   * Returns the HTML output of a tooltip if showing tooltips is enabled
   *
   * @param   string  $text       The text of the tooltip
   * @param   string  $title      The title of the tooltip
   * @param   boolean $addclass   True, if the class attribute shall be added and false if it's already there
   * @param   boolean $translate  True, if the text and the title shall be translated
   * @param   string  $class      The name of the class used by Mootools to detect the tooltips
   * @return  string  The HTML output created
   * @since   1.5.5
   */
  public static function tip($text = 'Tooltip', $title = null, $addclass = false, $translate = true, $class = 'hasHint')
  {
    $config = JoomConfig::getInstance();

    $html = '';
    if($config->get('jg_tooltips'))
    {
      static $loaded = false;

      if(!$loaded)
      {
        $params = array();
        if($config->get('jg_tooltips') == 2)
        {
          $params['template']  = '<div class="jg-tooltip-wrap tooltip"><div class="tooltip-inner tip"></div></div>';
        }

        JHtml::_('bootstrap.tooltip', '.'.$class, $params);
        $loaded = true;
      }

      if($config->get('jg_tooltips') == 2)
      {
        $tmp = "";
        if($title)
        {
          $tmp = '<div class="tip-title">';

          if($translate)
          {
            $title = JText::_($title);
          }

          $tmp .= $title . '</div>';
        }

        $tmp .= '<div class="tip-text">' . ($translate ? JText::_($text) : $text) . '</div>';
        $text = htmlspecialchars($tmp, ENT_QUOTES, 'UTF-8');
      }
      else
      {
        $text = JHtml::tooltipText($title, $text, $translate);
      }

      if($addclass)
      {
        $html = ' class="'.$class.'" title="'.$text.'"';
      }
      else
      {
        $html = ' '.$class.'" title="'.$text;
      }
    }

    return $html;
  }

  /**
   * Creates invisible links to images in order that
   * the popup boxes recognize them
   *
   * @param   array   $rows         An array of image objects to use
   * @param   int     $start        Index of the first image to use
   * @param   int     $end          Index of the last image to use, if null we will use every image from $start to end
   * @param   string  $secondGroup  Name of an optional second group, by specifying a second group all links will be duplicated with that group
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public static function popup(&$rows, $start = 0, $end = null, $secondGroup = null)
  {
    $config   = JoomConfig::getInstance();
    $ambit    = JoomAmbit::getInstance();
    $user     = JFactory::getUser();
    $view     = JRequest::getCmd('view');

    $html = '';

    if( (     $view == 'category'
              // 'jg_detailpic_open' is not numeric if an OpenImage plugin was selected, thus we handle it like > 4
          &&  (!is_numeric($config->get('jg_detailpic_open')) || $config->get('jg_detailpic_open') > 4)
          &&  (   $config->get('jg_showdetailpage') == 1
              ||  ($config->get('jg_showdetailpage') == 0 && $user->get('id'))
              )
        )
      ||
        (     $view == 'detail'
          &&  (   ($config->get('jg_bigpic') == 1 && $user->get('id'))
                || ($config->get('jg_bigpic_unreg') == 1 && !$user->get('id'))
              )
              // 'jg_bigpic_open' is not numeric if an OpenImage plugin was selected, thus we handle it like > 4
          &&  (!is_numeric($config->get('jg_bigpic_open')) || $config->get('jg_bigpic_open') > 4)
        )
      )
    {
      if(is_null($end))
      {
        $rows = array_slice($rows, (int)$start);
      }
      else
      {
        $rows = array_slice($rows, (int)$start, (int)$end);
      }

      $html = '  <div class="jg_displaynone">';

      foreach($rows as $row)
      {
        if(  ($view == 'detail' && is_file($ambit->getImg('orig_path', $row)))
           || $view == 'category'
          )
        {
          if($view == 'detail')
          {
            $type = $config->get('jg_bigpic_open');
          }
          else
          {
            $type = $config->get('jg_detailpic_open');
          }

          // Set the title attribute in a tag with title and/or description of image
          // if a box is activated
          if(!is_numeric($type) || $type > 1)
          {
            $atagtitle = JHtml::_('joomgallery.getTitleforATag', $row);
          }
          else
          {
            $atagtitle = 'title="'.$row->imgtitle.'"';
          }

          $link = self::openImage($type, $row);
          $html .= '
      <a href="'.$link.'" '.$atagtitle.'>'.$row->id.'</a>';

          if($secondGroup)
          {
            $link = self::openImage($type, $row, false, $secondGroup);
            $html .= '
      <a href="'.$link.'" '.$atagtitle.'>'.$row->id.'</a>';
          }
        }
      }
      $html .= '
    </div>';
    }

    return $html;
  }

  /**
   * Returns the title attribute of HTML a tag
   *
   * @param   object  $image  The object which holds the image data
   * @param   boolean $attr   True if title attribute should be returned completely,
   *                          if false only the content is returned, defaults to true
   * @return  string  The title attribute of HTML a Tag
   * @since   2.0
   */
  public static function getTitleforATag($image, $attr = true)
  {
    $config = JoomConfig::getInstance();

    $tagtitle  = '';
    $tagdesc   = '';
    if(    $config->get('jg_show_title_in_popup')
        || $config->get('jg_show_description_in_popup'))
    {
      if($config->get('jg_show_title_in_popup'))
      {
        $tagtitle = $image->imgtitle;
      }

      if(   $config->get('jg_show_description_in_popup')
         && !empty($image->imgtext))
      {
        if($config->get('jg_show_description_in_popup') == 1)
        {
          // Show description without html tag modifications
          $tagdesc = htmlspecialchars($image->imgtext);
        }
        else
        {
          // Strip html tags of description before
          $tagdesc = strip_tags($image->imgtext);
        }
      }

      if(!empty($tagdesc))
      {
        if(!empty($tagtitle))
        {
          $tagtitle .= ' '.$tagdesc;
        }
        else
        {
          $tagtitle = $tagdesc;
        }
      }

      if(!empty($tagtitle) && $attr)
      {
        $tagtitle = 'title="'.$tagtitle.'"';
      }
    }

    return $tagtitle;
  }

  /**
   * Returns the link to a given image, which opens the image in slimbox, for example
   *
   * @param   int         $open   Use of lightbox, javascript window or DHTML container?
   * @param   int/object  $image  The id of the image or an object which holds the image data
   * @param   string      $type   The image type ('thumb', 'img', 'orig'), use 'false' for default value
   * @param   string      $group  Name of a group to group images in the popups
   * @return  string      The link to the image
   * @since   1.0.0
   */
  public static function openImage($open, $image, $type = false, $group = null)
  {
    static $loaded = array();

    $config = JoomConfig::getInstance();
    $ambit  = JoomAmbit::getInstance();
    $user   = JFactory::getUser();

    // No detail view for guests if adjusted like that
    if(!$config->get('jg_showdetailpage') && !$user->get('id'))
    {
      return 'javascript:alert(\''.JText::_('COM_JOOMGALLERY_COMMON_ALERT_NO_DETAILVIEW_FOR_GUESTS', true).'\')';
    }

    if(!is_object($image))
    {
      $image  = $ambit->getImgObject($image);
    }

    if(!$type)
    {
              // 'jg_detailpic_open' is not numeric if an OpenImage plugin was selected, thus we handle it like > 4
      if(     (!is_numeric($config->get('jg_detailpic_open')) || $config->get('jg_detailpic_open') > 4)
          &&  $config->get('jg_lightboxbigpic')
        )
      {
        $type = 'orig';
      }
      else
      {
        if(JRequest::getCmd('view') == 'detail')
        {
          $type = 'orig';
        }
        else
        {
          $type = 'img';
        }
      }
    }

    if(!$group)
    {
      $group = 'joomgallery';
    }

    $img_url  = $ambit->getImg($type.'_url',   $image);
    $img_path = $ambit->getImg($type.'_path',  $image);

    switch($open)
    {
      case '0': // Detail view
        $link = JRoute::_('index.php?view=detail&id='.$image->id);
        break;
      case 1: // New window
        $link = $img_url."\" target=\"_blank";
        break;
      case 2: // JavaScript window
        $imginfo = getimagesize($img_path);
        $link    = "javascript:joom_openjswindow('".$img_url."','".JoomHelper::fixForJS($image->imgtitle)."', '".$imginfo[0]."','".$imginfo[1]."')";

        if(!isset($loaded[2]))
        {
          $doc = JFactory::getDocument();
          $doc->addScript($ambit->getScript('jswindow.js'));
          $script = '    var resizeJsImage = '.$config->get('jg_resize_js_image').';
    var jg_disableclick = '.$config->get('jg_disable_rightclick_original').';';
          $doc->addScriptDeclaration($script);
          $loaded[2] = true;
        }
        break;
      case 3: // DHTML container
        $imginfo = getimagesize($img_path);
        $link    = "javascript:joom_opendhtml('".$img_url."','";

        if($config->get('jg_show_title_in_popup'))
        {
          $link .= JoomHelper::fixForJS($image->imgtitle)."','";
        }
        else
        {
          $link .= "','";
        }
        if($config->get('jg_show_description_in_popup'))
        {
          if($config->get('jg_show_description_in_popup') == 1)
          {
            $link .= JoomHelper::fixForJS($image->imgtext)."','";
          }
          else
          {
            $link .= JoomHelper::fixForJS(strip_tags($image->imgtext))."','";
          }
        }
        else
        {
          $link .= "','";
        }
        $link .= $imginfo[0]."','".$imginfo[1]."','".JUri::root()."')";

        if(!isset($loaded[3]))
        {
          $doc = JFactory::getDocument();
          $doc->addScript($ambit->getScript('dhtml.js'));
          $script = '    var resizeJsImage = '.$config->get('jg_resize_js_image').';
    var jg_padding = '.$config->jg_openjs_padding.';
    var jg_dhtml_border = "'.$config->jg_dhtml_border.'";
    var jg_openjs_background = "'.$config->jg_openjs_background.'";
    var jg_disableclick = '.$config->jg_disable_rightclick_original.';';
          $doc->addScriptDeclaration($script);
          $loaded[3] = true;
        }
        break;
      case 4: // Modalbox
        #$imginfo = getimagesize($img_path);
        $link = $img_url.'" class="modal" rel="'./*{handler: 'iframe', size: {x: ".$imginfo[0].", y: ".$imginfo[1]."}}*/'" title="'.$image->imgtitle;

        if(!isset($loaded[4]))
        {
          JHtml::_('behavior.framework'); // Loads mootools only, if it hasn't already been loaded
          JHTML::_('behavior.modal');
          $loaded[4] = true;
        }
        break;
      case 5: // Thickbox3
        $link = $img_url.'" rel="thickbox.'.$group;

        if(!isset($loaded[5]))
        {
          $doc = JFactory::getDocument();

          JHtml::_('jquery.framework');
          $doc->addScript($ambit->getScript('thickbox3/js/thickbox.js'));
          $doc->addStyleSheet($ambit->getScript('thickbox3/css/thickbox.css'));
          $script = '    var resizeJsImage = '.$config->get('jg_resize_js_image').';
    var joomgallery_image = "'.JText::_('COM_JOOMGALLERY_COMMON_IMAGE', true).'";
    var joomgallery_of = "'.JText::_('COM_JOOMGALLERY_POPUP_OF', true).'";
    var joomgallery_close = "'.JText::_('COM_JOOMGALLERY_POPUP_CLOSE', true).'";
    var joomgallery_prev = "'.JText::_('COM_JOOMGALLERY_POPUP_PREVIOUS', true).'";
    var joomgallery_next = "'.JText::_('COM_JOOMGALLERY_POPUP_NEXT', true).'";
    var joomgallery_press_esc = "'.JText::_('COM_JOOMGALLERY_POPUP_ESC', true).'";
    var tb_pathToImage = "'.$ambit->getScript('thickbox3/images/loadingAnimation.gif').'";';
          $doc->addScriptDeclaration($script);
          $loaded[5] = true;
        }
        break;
      case 6: // Slimbox
        $link = $img_url.'" rel="lightbox['.$group.']';

        if(!isset($loaded[6]))
        {
          $doc = JFactory::getDocument();
          JHtml::_('behavior.framework'); // Loads mootools only, if it hasn't already been loaded
          $doc->addScript($ambit->getScript('slimbox/js/slimbox.js'));
          $doc->addStyleSheet($ambit->getScript('slimbox/css/slimbox.css'));
          $script = '    var resizeJsImage = '.$config->get('jg_resize_js_image').';
    var resizeSpeed = '.$config->get('jg_lightbox_speed').';
    var joomgallery_image = "'.JText::_('COM_JOOMGALLERY_COMMON_IMAGE', true).'";
    var joomgallery_of = "'.JText::_('COM_JOOMGALLERY_POPUP_OF', true).'";';
          $doc->addScriptDeclaration($script);
          $loaded[6] = true;
        }
        break;
      default: // Plugins
        if(!isset($loaded[12]))
        {
          $loaded[12] = JDispatcher::getInstance();
        }
        $link = '';
        $loaded[12]->trigger('onJoomOpenImage', array(&$link, $image, $img_url, $group, $type, $open));
        if(!$link)
        {
          // Fallback to new window
          $link = $img_url."\" target=\"_blank";
        }
        break;
    }

    return $link;
  }

  /**
   * Creates a JavaScript tree with all sub-categories of a category
   *
   * @param   int     $rootcatid  The category ID
   * @param   string  $align      Alignment of the tree
   * @return  void
   * @since   1.5.0
   */
  public static function categoryTree($rootcatid, $align)
  {
    $ambit      = JoomAmbit::getInstance();
    $config     = JoomConfig::getInstance();
    $user       = JFactory::getUser();
    $categories = $ambit->getCategoryStructure(true);

    // Check access rights settings
    $filter_cats        = false;
    $restricted_hint    = false;
    $restricted_cats    = false;
    $root_access        = false;
    if(!$config->get('jg_showrestrictedhint') && !$config->get('jg_showrestrictedcats'))
    {
      $filter_cats = true;
    }
    else
    {
      if($config->get('jg_showrestrictedhint'))
      {
        $restricted_hint = true;
      }
      if($config->get('jg_showrestrictedcats'))
      {
        $restricted_cats = true;
      }
    }

    // Array to hold the relevant sub-category objects
    $subcategories = array();
    // Array to hold the valid parent categories
    $validParentCats = array();
    $validParentCats[$rootcatid] = true;

    // Get all relevant subcategories
    $keys = array_keys($categories);
    $startindex = array_search($rootcatid, $keys);
    if($startindex !== false)
    {
      $stopindex     = count($categories);
      $root_access   = in_array($categories[$rootcatid]->access, $user->getAuthorisedViewLevels()) && !$categories[$rootcatid]->protected;

      for($j = $startindex + 1; $j < $stopindex; $j++)
      {
        $i = $keys[$j];
        $parentcat = $categories[$i]->parent_id;
        if(isset($validParentCats[$parentcat]))
        {
          // Hide empty categories
          $empty = false;
          if($config->get('jg_hideemptycats'))
          {
            $subcatids = JoomHelper::getAllSubCategories($i, true, ($config->get('jg_hideemptycats') == 1), true);
            // If 'jg_hideemptycats' is set to 1 the root category will always be in $subcatids, so check whether there are images in it
            if(   !count($subcatids)
              ||  (count($subcatids) == 1 && $config->get('jg_hideemptycats') == 1 && !$categories[$i]->piccount)
              )
            {
              $empty  = true;
            }
          }

          if(    $categories[$i]->published
             && ($filter_cats == false || (
                                                in_array($categories[$i]->access, $user->getAuthorisedViewLevels())
                                            &&  (($parentcat == $rootcatid && $root_access) || ($parentcat != $rootcatid && $subcategories[$parentcat]->access))
                                          )
                )
             && !$categories[$i]->hidden
             && (!$config->get('jg_hideemptycats') || !$empty)
            )
          {
            $validParentCats[$i]  = true;
            $subcategories[$i]    = clone $categories[$i];
            if(
                (     $parentcat == $rootcatid
                  &&  !$root_access
                )
              ||
                (     $parentcat != $rootcatid
                  &&
                      !$subcategories[$parentcat]->access
                )
              || !in_array($categories[$i]->access, $user->getAuthorisedViewLevels())
              || $categories[$i]->protected
              )
            {
              $subcategories[$i]->access = false;

              if(
                  (     $parentcat == $rootcatid
                    &&  !$root_access
                  )
                ||
                  (     $parentcat != $rootcatid
                    &&
                        !$subcategories[$parentcat]->access
                  )
                )
              {
                $subcategories[$i]->protected = false;
              }
            }
          }
        }
        else
        {
          if($parentcat == 0)
          {
            // Branch has been processed completely
            break;
          }
        }
      }
    }

    // Show the treeview
    $count = count($subcategories);
    if(!$count)
    {
      return;
    }

    // If $align is 'jg_element_txt' we are displaying random thumbnails
    // or the thumbnail alignment is set to 'Use global'. In both cases
    // we have to take 'jg_ctalign' under consideration.
    if($align == 'jg_element_txt')
    {
      switch($config->get('jg_ctalign'))
      {
        case 0:
          // Changing: $align is only 'jg_element_txt' if the thumbnail is aligned left
          // Break omitted intentionally
        case 1:
          // Left
          $align = 'jg_element_txt_l';
          break;
        case 2:
          // Right
          $align = 'jg_element_txt_r';
          break;
        case 3:
          // Break omitted intentionally
        default:
          // Centered
          $align = 'jg_element_txt_c';
          break;
      }
    }

    if($align == 'jg_element_txt_l')
    {
?>
          <div class="jg_treeview_l">
<?php
    }
    elseif($align == 'jg_element_txt_r')
    {
?>
          <div class="jg_treeview_r">
<?php
    }
    else
    {
?>
          <div class="jg_treeview_c">
<?php
    }
?>
            <table>
              <tr>
                <td>
                  <script type="text/javascript" language="javascript">
                  <!--
                  // Create new dTree object
                  var jg_TreeView<?php echo $rootcatid;?> = new jg_dTree( <?php echo "'"."jg_TreeView".$rootcatid."'"; ?>,
                                                                          <?php echo "'".$ambit->getScript('dTree/img/')."'"; ?>);
                  // dTree configuration
                  jg_TreeView<?php echo $rootcatid;?>.config.useCookies = true;
                  jg_TreeView<?php echo $rootcatid;?>.config.inOrder = true;
                  jg_TreeView<?php echo $rootcatid;?>.config.useSelection = false;
                  // Add root node
                  jg_TreeView<?php echo $rootcatid;?>.add( 0, -1, ' ', <?php echo "'".JRoute::_( 'index.php?view=gallery'.$rootcatid)."'"; ?>, false);
                  // Add node to hold all subcategories
                  jg_TreeView<?php echo $rootcatid;?>.add( <?php echo $rootcatid; ?>, 0, <?php echo "'".JText::_('COM_JOOMGALLERY_COMMON_SUBCATEGORIES', true)."(".$count.")"."'";?>,
                                                           <?php echo $root_access ? "'".JRoute::_('index.php?view=category&catid='.$rootcatid)."'" : "''"; ?>,
                                                           <?php echo $root_access ? 'false' :'true'; ?> );
<?php
    foreach($subcategories as $category)
    {
      // Create sub-category name and sub-category link
      if($filter_cats == false || $category->access || $category->protected)
      {
        // If the category is accessible create a link.
        // The link is also created if the category is password-protected, but only if its parent category is accessible.
        // The latter is ensured above by setting property 'protected' respectively.
        if($category->access || $category->protected)
        {
          $cat_name = addslashes(trim($category->name));
          $cat_link = JRoute::_('index.php?view=category&catid='.$category->cid);
        }
        else
        {
          $cat_name = ($restricted_cats == true ? addslashes(trim($category->name)) : JText::_('COM_JOOMGALLERY_COMMON_NO_ACCESS', true));
          $cat_link = '';
        }
      }
      if($restricted_hint == true)
      {
        if(!$category->access)
        {
          if($category->protected)
          {
            $cat_name .= '<span class="jg_rm">'.self::icon('key.png', 'COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY').'</span>';
          }
          else
          {
            $cat_name .= '<span class="jg_rm">'.self::icon('group_key.png', 'COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY').'</span>';
          }
        }
      }
      if($config->get('jg_showcatasnew'))
      {
        $isnew = JoomHelper::checkNewCatg($category->cid);
      }
      else
      {
        $isnew = '';
      }
      $cat_name .= $isnew;

      // Add node
      if($category->parent_id == $rootcatid)
      {
?>
                  jg_TreeView<?php echo $rootcatid;?>.add(<?php echo $category->cid;?>,
                                                          <?php echo $rootcatid;?>,
                                                          <?php echo "'".$cat_name."'";?>,
                                                          <?php echo "'".$cat_link."'"; ?>,
                                                          <?php echo $category->access ? 'false' :'true'; ?>
                                                          );
<?php
      }
      else
      {
?>
                  jg_TreeView<?php echo $rootcatid;?>.add(<?php echo $category->cid;?>,
                                                          <?php echo $category->parent_id;?>,
                                                          <?php echo "'".$cat_name."'";?>,
                                                          <?php echo "'".$cat_link."'"; ?>,
                                                          <?php echo $category->access ? 'false' :'true'; ?>
                                                          );
<?php
      }
    }
?>
                  document.write(jg_TreeView<?php echo $rootcatid;?>);
                  -->
                  </script>
                </td>
              </tr>
            </table>
          </div>
<?php
  }

  /**
   * Creates the HTML output to display the rating of an image
   *
   * @param   object  $image          Image object holding the image data
   * @param   boolean $shortText      In case of text output return text without COM_JOOMGALLERY_COMMON_RATING_VAR
   * @param   string  $ratingclass    CSS class name of rating div in case of displaying stars
   * @param   string  $tooltipclass   CSS tooltip class of rating div in case of displaying stars
   * @return  string  The HTML output
   * @since   1.5.6
   */
  public static function rating($image, $shortText, $ratingclass, $tooltipclass = null)
  {
    $config     = JoomConfig::getInstance();
    $db         = JFactory::getDBO();
    $html       = '';
    $maxvoting  = $config->get('jg_maxvoting');

    // Standard rating output as text
    if($config->get('jg_ratingdisplaytype') == 0)
    {
      $rating = number_format((float) $image->rating, 2, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));
      if($image->imgvotes > 0)
      {
        if($image->imgvotes == 1)
        {
          $html = $rating.' ('.$image->imgvotes.' '.  JText::_('COM_JOOMGALLERY_COMMON_ONE_VOTE') . ')';
        }
        else
        {
          $html = $rating.' ('.$image->imgvotes.' '.  JText::_('COM_JOOMGALLERY_COMMON_VOTES') . ')';
        }
      }
      else
      {
        $html = JText::_('COM_JOOMGALLERY_COMMON_NO_VOTES');
      }
      if(!$shortText)
      {
        $html = JText::sprintf('COM_JOOMGALLERY_COMMON_RATING_VAR', $html);
      }
      // Same as &nbsp; but &#160; also works in XML
      $html .= '&#160;';
    }

    // Rating output with star images
    if($config->get('jg_ratingdisplaytype') == 1)
    {
      $width = 0;
      if($config->get('jg_maxvoting') > 0 && $image->imgvotes > 0)
      {
        $width = (int) ($image->rating / (float) $config->get('jg_maxvoting') * 100.0);
      }

      if(isset($tooltipclass))
      {
        $html .= '<div class="'.$ratingclass.' '.JHTML::_('joomgallery.tip', JText::sprintf('COM_JOOMGALLERY_COMMON_RATING_TIPTEXT_VAR', $image->rating, $image->imgvotes), JText::_('COM_JOOMGALLERY_COMMON_RATING_TIPCAPTION'), false, false, $tooltipclass).'">';
      }
      else
      {
        $html .= '<div class="'.$ratingclass.' '.JHTML::_('joomgallery.tip', JText::sprintf('COM_JOOMGALLERY_COMMON_RATING_TIPTEXT_VAR', $image->rating, $image->imgvotes), JText::_('COM_JOOMGALLERY_COMMON_RATING_TIPCAPTION'), false, false).'">';
      }
      $html .= '  <div style="width:'.$width.'%"></div>';
      $html .= '</div>';
    }

    return $html;
  }

  /**
   * Replace bbcode tags (b/u/i/url/email) with HTML tags
   *
   * @param   string  $text The text to be modified
   * @return  string  The modified text
   * @since   1.0.0
   */
  public static function BBDecode($text)
  {
    static $bbcode_tpl    = array();
    static $patterns      = array();
    static $replacements  = array();

    // First: If there isn't a "[" and a "]" in the message, don't bother.
    if((strpos($text, '[') === false || strpos($text, ']') === false))
    {
      return $text;
    }

    // [b] and [/b] for bolding text.
    $text = str_replace('[b]',  '<b>',  $text);
    $text = str_replace('[/b]', '</b>', $text);

    // [u] and [/u] for underlining text.
    $text = str_replace('[u]',  '<u>',  $text);
    $text = str_replace('[/u]', '</u>', $text);

    // [i] and [/i] for italicizing text.
    $text = str_replace('[i]',  '<i>',  $text);
    $text = str_replace('[/i]', '</i>', $text);

    if(!count($bbcode_tpl))
    {
      // We do URLs in several different ways..
      $bbcode_tpl['url']    = '<span class="bblink"><a href="{URL}" target="_blank">{DESCRIPTION}</a></span>';
      $bbcode_tpl['email']  = '<span class="bblink"><a href="mailto:{EMAIL}">{EMAIL}</a></span>';
      $bbcode_tpl['url1']   = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
      $bbcode_tpl['url1']   = str_replace('{DESCRIPTION}', '\\1\\2', $bbcode_tpl['url1']);
      $bbcode_tpl['url2']   = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
      $bbcode_tpl['url2']   = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);
      $bbcode_tpl['url3']   = str_replace('{URL}', '\\1\\2', $bbcode_tpl['url']);
      $bbcode_tpl['url3']   = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url3']);
      $bbcode_tpl['url4']   = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
      $bbcode_tpl['url4']   = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url4']);
      $bbcode_tpl['email']  = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);

      // [url]xxxx://www.phpbb.com[/url] code..
      $patterns[1]      = '#\[url\]([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si';
      $replacements[1]  = $bbcode_tpl['url1'];
      // [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
      $patterns[2]      = '#\[url\]([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\[/url\]#si';
      $replacements[2]  = $bbcode_tpl['url2'];
      // [url=xxxx://www.phpbb.com]phpBB[/url] code..
      $patterns[3]      = '#\[url=([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si';
      $replacements[3]  = $bbcode_tpl['url3'];
      // [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
      $patterns[4]      = '#\[url=([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+\(\)]+)\](.*?)\[/url\]#si';
      $replacements[4]  = $bbcode_tpl['url4'];
      //[email]user@domain.tld[/email] code..
      $patterns[5]      = '#\[email\]([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si';
      $replacements[5]  = $bbcode_tpl['email'];
    }

    $text = preg_replace($patterns, $replacements, $text);

    return $text;
  }

  /**
   * Displays the credits
   *
   * @return  void
   * @since   1.5.5
   */
  public static function credits()
  {
    $ambit = JoomAmbit::getInstance();
?>
<div class="center">
  <p>
    <br />
    <a href="http://www.joomgallery.net" target="_blank">
      <img src="<?php echo $ambit->getIcon('powered_by.gif'); ?>"  class="jg-poweredby" alt="Powered by JoomGallery" />
    </a>
  </p>
  By:
  <a href="mailto:team@joomgallery.net">
    JoomGallery::ProjectTeam
  </a>
  <br />
  <?php echo 'Version '.$ambit->get('version'); ?>
</div>
<?php
  }

  /**
   * Creates the path to a category which can be displayed
   *
   * @param   int     $catid      The category ID
   * @param   boolean $child      True, if category itself shall also be included, defaults to true
   * @param   string  $separator  The separator
   * @param   boolean $linked     True if elements shall be linked, defaults to false
   * @param   boolean $with_home  True if the home link shall be included, defaults to false
   * @param   boolean $all        True if all categories shall be shown defaults to false
   * @return  string  The HTML output
   * @since   1.5.5
   */
  public static function categoryPath(&$catid, $child = true, $separator = ' &raquo; ', &$linked = false, &$with_home = false, $all = false)
  {
    // Maybe this path was already generated
    static $catPaths = array();
    if(isset($catPaths[$catid]))
    {
      return $catPaths[$catid];
    }

    $path                = '';
    $catid               = intval($catid);
    $user                = JFactory::getUser();
    $ambit               = JoomAmbit::getInstance();
    $allowed_categories  = $ambit->getCategoryStructure();

    // Get category and their parents
    $pathCats = JoomHelper::getAllParentCategories($catid, $child, $all);

    $count = count($pathCats);
    if(!$count)
    {
      return $path;
    }

    // Construct the HTML
    if($count == 1)
    {
      $category = reset($pathCats);

      // Link to category only if category published
      if($linked && isset($allowed_categories[$catid]))
      {
        $path = '<a href="'.JRoute::_('index.php?view=category&catid='.$catid).'" class="jg_pathitem">'.$category->name.'</a>';
      }
      else
      {
        $path = $category->name;
      }
    }
    else
    {
      // Reindex the array with index from 0 to n
      $pathCatsidx = array_values($pathCats);

      // First element
      if($linked && isset($allowed_categories[$pathCatsidx[0]->cid]))
      {
        $path = '<a href="'.JRoute::_('index.php?view=category&catid='.$pathCatsidx[0]->cid).'" class="jg_pathitem">'.$pathCatsidx[0]->name.'</a>';
      }
      else
      {
        $path = $pathCatsidx[0]->name;
      }

      for($i = 1; $i < $count; $i++)
      {
        if($linked && isset($allowed_categories[$pathCatsidx[$i]->cid]))
        {
          $path .= $separator.'<a href="'.JRoute::_('index.php?view=category&catid='.$pathCatsidx[$i]->cid).'" class="jg_pathitem">'.$pathCatsidx[$i]->name.'</a>';
        }
        else
        {
          $path .= $separator.$pathCatsidx[$i]->name;
        }
      }
    }

    if($with_home)
    {
      $home = '<a href="'.JRoute::_('index.php?view=gallery').'" class="jg_pathitem">'.JText::_('COM_JOOMGALLERY_COMMON_HOME').'</a>';
      $path = $home.$separator.$path.' ';
    }

    // Store it for later use
    $catPaths[$catid] = $path;

    return $path;
  }

  /**
   * Creates the HTML output to display a minithumb for an image
   *
   * @param   object  $img            Image object holding the image data
   * @param   string  $class          CSS class name for minithumb styling
   * @param   boolean $linkattribs    Link attributes for creating a link on the minithumb, if false no link will created
   * @param   boolean $showtip        Shows the thumbnail as tip on hoovering above minithumb
   * @return  string  The HTML output
   * @since   1.5.7
   */
  public static function minithumbimg($img, $class = null, $linkattribs = null, $showtip = true)
  {
    jimport('joomla.filesystem.file');

    $ambit    = JoomAmbit::getInstance();
    $config   = JoomConfig::getInstance();
    $html     = '';
    $linked   = $linkattribs ? true : false;

    $thumb = $ambit->getImg('thumb_path', $img);
    if(JFile::exists($thumb))
    {
      $isSite   = JFactory::getApplication()->isSite();
      $imginfo  = getimagesize($thumb);
      $url      = $ambit->getImg('thumb_url', $img);

      if($showtip)
      {
        if($isSite)
        {
          $html .= '<span'.JHtml::_('joomgallery.tip', '<img src="'.$url.'" width="'.$imginfo[0].'" height="'.$imginfo[1].'" alt="'.$img->imgtitle.'" />', null, true, false).'>';
        }
        else
        {
          $html .= '<span class="hasTooltip" title="'.htmlspecialchars('<img src="'.$url.'" width="'.$imginfo[0].'" height="'.$imginfo[1].'" alt="'.$img->imgtitle.'" />', ENT_QUOTES, 'UTF-8').'">';
        }
      }
      if($linked)
      {
        if($isSite)
        {
          // Set the title attribute in a tag with title and/or
          // description of image if a box is activated
          if(!is_numeric($config->get('jg_detailpic_open')) || $config->get('jg_detailpic_open') > 1)
          {
            $atagtitle = JHtml::_('joomgallery.getTitleforATag', $img);
          }
          else
          {
            // Set the imgtitle by default
            $atagtitle = 'title="'.$img->imgtitle.'"';
          }
          $html .= '<a '.$atagtitle.' href="'.JHtml::_('joomgallery.openImage', $config->get('jg_detailpic_open'), $img, false, 'jgminithumbs').'">';
        }
        else
        {
          $html .= '<a '.$linkattribs.'">';
        }
      }
      $html .= '<img src="'.$url.'" alt="'.htmlspecialchars($img->imgtitle, ENT_QUOTES, 'UTF-8').'"';
      if($class !== null)
      {
        $html .= ' class="'.$class.'"';
      }
      $html .= '>';
      if($linked)
      {
        $html .= '</a>';
      }
      if($showtip)
      {
        $html .= '</span>';
      }
    }
    return $html;
  }

  /**
   * Creates the HTML output to display a minithumb for a category
   *
   * @param   object  $cat          Category object holding the category data
   * @param   string  $class        CSS class name for minithumb styling
   * @param   boolean $linkattribs  Link attributes for creating a link on the minithumb, if false no link will created
   * @param   boolean $showtip      Shows the thumbnail as tip on hoovering above minithumb
   * @return  string  The HTML output
   * @since   1.5.7
   */
  public static function minithumbcat($cat, $class = null, $linkattribs = null, $showtip = true)
  {
    $ambit  = JoomAmbit::getInstance();
    $config = JoomConfig::getInstance();
    $html   = '';
    $linked = $linkattribs ? true : false;

    if(isset($cat->thumbnail) && !empty($cat->thumbnail))
    {
      $thumb = $ambit->getImg('thumb_path', $cat->thumbnail, null, $cat->cid);

      jimport('joomla.filesystem.file');
      if(JFile::exists($thumb))
      {
        $isSite   = JFactory::getApplication()->isSite();
        $imginfo  = getimagesize($thumb);
        $url      = $ambit->getImg('thumb_url', $cat->thumbnail, null, $cat->cid);

        // Clean category name
        $catname = str_replace('&nbsp;', '', $cat->name);
        $catname = trim(str_replace('&raquo;', '', $catname));

        if($showtip)
        {
          if($isSite)
          {
            $html .= '<span'.JHtml::_('joomgallery.tip', '<img src="'.$url.'" width="'.$imginfo[0].'" height="'.$imginfo[1].'" alt="'.$cat->name.'" />', null, true, false).'>';
          }
          else
          {
            $html .= '<span class="hasTooltip" title="'.htmlspecialchars('<img src="'.$url.'" width="'.$imginfo[0].'" height="'.$imginfo[1].'" alt="'.$catname.'" />', ENT_QUOTES, 'UTF-8').'">';
          }
        }
        if($linked)
        {
          if($isSite)
          {
            $html .= '<a href="'.JRoute::_('index.php?view=category&catid='.$cat->cid).'">';
          }
          else
          {
            $html .= '<a '.$linkattribs.'">';
          }
        }
        $html .= '<img src="'.$url.'" alt="'.htmlspecialchars($catname, ENT_QUOTES, 'UTF-8').'"';
        if($class !== null)
        {
          $html .= ' class="'.$class.'"';
        }
        $html .= '>';
        if($linked)
        {
          $html .= '</a>';
        }
        if($showtip)
        {
          $html .= '</span>';
        }
      }
    }

    return $html;
  }


  /**
   * Creates the HTML output to display a input box and color picker field
   *
   * @param  $key        string      the identifier of the configuration option, e.g. 'jg_pathimages'
   * @param  $color      string      current color setting of the option
   * @param  $style      string      colorpicker style, either 'hue', 'saturation', 'brightness' or 'wheel'
   * @param  $postion    string      postion of the panel, right, left, top or bottom
   * @return string      The HTML output
   * @since   2.1.0
   */
  public static function colorpicker($key, $color, $style = 'hue', $postion = 'right')
  {
    $color = strtolower($color);
    if(!$color || in_array($color, array('none', 'transparent')))
    {
      $color = 'none';
    }
    elseif($color['0'] != '#')
    {
      $color = '#' . $color;
    }

    $class     = ' class="minicolors"';
    $control   = ' data-control="'.$style.'"';
    $position  = ' data-position="'.$postion.'"';

    JHtml::_('behavior.colorpicker');

    return '<input type="text" name="'.$key.'" id="'.$key.'"'.' value="'
             .htmlspecialchars($color, ENT_COMPAT, 'UTF-8').'"'.$class.$position.$control.'/>';
  }

  /**
   * State buttons for approved/not yet approved/rejected state of images
   *
   * @param   array   $states     Array of state information
   * @param   int     $value      Current state of the image
   * @param   int     $i          Number of the image in the current list
   * @param   string  $prefix     Optional prefix for task name
   * @param   boolean $enabled    Indicates whether the buttons should be active
   * @param   int     $id         ID of the image
   * @param   int     $owner      ID of the owner of the image
   * @param   boolean $translate  Indicates whether the state names should be translated
   * @param   string  $checkbox   Name prefix of the checkboxes in the current image list
   * @return  string  HTML of the state buttons
   * @since   3.1
   */
  public static function approved($states, $value, $i, $prefix = '', $enabled = true, $id = 0, $owner = 0, $translate = true, $checkbox = 'cb')
  {
    $state = JArrayHelper::getValue($states, (int) $value, $states[0]);
    $task = array_key_exists('task', $state) ? $state['task'] : $state[0];
    $text = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
    $active_title = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
    $inactive_title = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
    $tip = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
    $active_class = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
    $inactive_class = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

    if($enabled)
    {
      // Button group is only necessary for 'not yet approved'
      if($value == 0)
      {
        $html[] = '<span class="btn-group">';
      }

      // Normal button for approving is necessary for states 'not yet approved' and 'rejected'
      if($value == 0 || $value == -1)
      {
        $html[] = '<a class="btn btn-micro ' . ($active_class == "publish" || $value == -1 ? 'active' : '') . '" ' . ($tip ? 'rel="tooltip"' : '') . '';
        $html[] = ' href="javascript:void(0);" onclick="return listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix . $task . '\')"';
        $html[] = ' title="' . addslashes(htmlspecialchars($translate ? JText::_($active_title) : $active_title, ENT_COMPAT, 'UTF-8')) . '">';
        $html[] = '<i class="icon-' . $active_class . '">';
        $html[] = '</i>';
        $html[] = '</a>';
      }

      // Special rejecting button is necessary for states 'not yet approved' and 'approved'
      if($value == 0 || $value == 1)
      {
        if($owner)
        {
          $owner = htmlspecialchars(JHtml::_('joomgallery.displayname', $owner), ENT_COMPAT, 'UTF-8');
        }
        else
        {
          $owner = '';
        }

        JHtml::_('bootstrap.modal', 'jg-reject-popup');
        $html[] = '<a class="btn btn-micro '.($active_class == "publish" ? 'active' : '').'" '.($tip ? 'rel="tooltip"' : '').'';
        $html[] = ' href="javascript:void(0);" onclick="joomRejectionWindow(this);" data-owner="'.$owner.'" data-image-id="'.$id.'"';
        $html[] = ' title="'.addslashes(htmlspecialchars(JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE'), ENT_COMPAT, 'UTF-8')).'" data-toggle="modal" data-target="#jg-reject-popup">';

        if($value == 0)
        {
          $html[] = '<i class="icon-minus" style="color:#BD362F;">';
        }
        else
        {
          $html[] = '<i class="icon-'.$active_class.'">';
        }

        $html[] = '</i>';
        $html[] = '</a>';

        if($value == 0)
        {
          $html[] = '</span>';
        }

        $html[] = '<script type="text/javascript">
        function joomRejectionWindow(button)
        {
          var owner = jQuery(button).attr(\'data-owner\');
          var id    = jQuery(button).attr(\'data-image-id\');
          jQuery(\'#jg-reject-image\').attr(\'src\', \''.JRoute::_('index.php?option='._JOOM_OPTION.'&view=image', false).'&format=raw&type=img&cid=\' + id);
          jQuery(\'#jg-reject-cid\').val(id);
          if(owner)
          {
            jQuery(\'#jg-reject-no-owner\').addClass(\'hide\');
            jQuery(\'#jg-reject-owner-name\').html(owner);
            jQuery(\'#jg-reject-owner\').removeClass(\'hide\');
          }
          else
          {
            jQuery(\'#jg-reject-no-owner\').removeClass(\'hide\');
            jQuery(\'#jg-reject-owner\').addClass(\'hide\');
          }
        }
        </script>';
      }
    }
    else
    {
      // Disabled button (usually used if the user doesn't have the permission to change the state)
      $html[] = '<a class="btn btn-micro disabled jgrid" '.($tip ? 'rel="tooltip"' : '');
      $html[] = ' title="'.addslashes(htmlspecialchars($translate ? JText::_($inactive_title) : $inactive_title, ENT_COMPAT, 'UTF-8')).'">';
      $html[] = '<i class="icon-'.$inactive_class.'"></i>';
      $html[] = '</a>';
    }

    return implode($html);
  }
}