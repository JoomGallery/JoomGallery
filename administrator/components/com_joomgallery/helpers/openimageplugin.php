<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/openimageplugin.php $
// $Id: openimageplugin.php 4067 2013-02-07 22:25:32Z chraneco $
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

jimport('joomla.plugin.plugin');

/**
 * JoomGallery helper class for OpenImage plugins
 *
 * Open image plugins can extend this class, so that they only have to set the 'title'
 * property and to implement the simple methods 'init()' and 'getLinkAttributes()'.
 *
 * @package JoomGallery
 * @since   3.0
 */
abstract class JoomOpenImagePlugin extends JPlugin
{
  /**
   * Name of this popup box
   *
   * @var   string
   * @since 3.0
   */
  protected $title;

  /**
   * Initializes the box by adding all necessary JavaScript and CSS files.
   * This is done only once per page load.
   *
   * Please use the document object of Joomla! to add JavaScript and CSS files, e.g.:
   * <code>
   * $doc = JFactory::getDocument();
   * $doc->addStyleSheet(JUri::root().'media/plg_exampleopenimage/css/exampleopenimage.css');
   * $doc->addScript(JUri::root().'media/plg_exampleopenimage/js/exampleopenimage.js');
   * $doc->addScriptDeclaration("    jQuery(document).ready(function(){ExampleOpenImage.init()}");
   * </code>
   *
   * or if using Mootools or jQuery the respective JHtml method:
   * <code>
   * JHtml::_('jquery.framework');
   * JHtml::_('behavior.framework');
   * </code>
   *
   * @return  void
   * @since   3.0
   */
  abstract protected function init();

  /**
   * OnJoomOpenImage method
   *
   * Method is called when an image of JoomGallery shall be opened.
   * It modifies the given link in order to use the respective box for opening the image.
   *
   * @access  public
   * @param   string  $link     The link to modify
   * @param   object  $image    An object holding the image data
   * @param   string  $img_url  The URL to the image which shall be openend
   * @param   string  $group    The name of an image group, most boxes will make an album out of the images of a group
   * @param   string  $type     'orig' for original image, 'img' for detail image or 'thumb' for thumbnail
   * @param   string  $selected If a specific box was selected it will be delivered in this argument
   * @return  void
   * @since   3.0
   */
  public function onJoomOpenImage(&$link, $image = null, $img_url = null, $group = 'joomgallery', $type = 'orig', $selected = null)
  {
    if(!$image)
    {
      // Let JoomGallery know that this plugin is enabled (this is for backwards compatibility only)
      $link = true;

      return;
    }

    if($selected && $selected != $this->title)
    {
      // If an OpenImage plugin was selected but not this one we don't do anything here
      return;
    }

    // Ensure that the necessary assets are loaded once (if necessary)
    static $loaded = false;
    if(!$loaded && !$this->params->get('global_box_existent'))
    {
      $this->init();
      $loaded = true;
    }

    // Get all the attributes for the link tag
    $attribs = array('href' => $img_url);
    $this->getLinkAttributes($attribs, $image, $img_url, $group, $type);

    $href = $img_url;
    if(isset($attribs['href']))
    {
      $href = $attribs['href'];
      unset($attribs['href']);
    }

    if(!count($attribs))
    {
      $link = $href;

      return;
    }

    $attribs = JArrayHelper::toString($attribs);

    // Remove the last quotation marks and create the tag
    $link = $href.'" '.substr($attribs, 0, -1);
  }

  /**
   * This method should set an associative array of attributes for the 'a'-Tag (key/value pairs) which opens the image.
   *
   * <code>
   * $attribs['data-rel']   = 'examplebox';
   * $attribs['data-group'] = $group;
   * </code>
   *
   * The example above will create a link tag like that: <a href="<image URL>"  data-rel="examplebox" group="<image group>" ... >
   *
   * ($attribs is passed by references and should only be filled)
   *
   * By default the attribute 'href' is filled with the URL to the image which shall be opened. You only have to set that
   * attribute if you want to change that (the image URL is passed in the third argument of this method).
   *
   * NOTE!!!: You are not allowed to set the attributes 'title' and 'class' because these are handled internally by JoomGallery.
   *
   * @param   array   $attribs  Associative array of HTML attributes which you have to fill
   * @param   object  $image    An object holding all the relevant data about the image to open
   * @param   string  $img_url  The URL to the image which shall be openend
   * @param   string  $group    The name of an image group, most popup boxes are able to group the images with that
   * @param   string  $type     'orig' for original image, 'img' for detail image or 'thumb' for thumbnail
   * @return  void
   * @since   3.0
   */
  abstract protected function getLinkAttributes(&$attribs, $image, $img_url, $group, $type);

  /**
   * onJoomOpenImageGetName method
   *
   * Method is called if the popup box name is requested used by this plugin
   *
   * @return  string  The name of the popup box used by this plugin
   * @since   3.0
   */
  public function onJoomOpenImageGetName()
  {
    return $this->title;
  }
}