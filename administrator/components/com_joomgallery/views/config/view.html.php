<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/config/view.html.php $
// $Id: view.html.php 4361 2014-02-24 18:03:18Z erftralle $
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
 * HTML View class for the configuration view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewConfig extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  public function display($tpl = null)
  {
    // Load language files of frontend for Exif and IPTC data
    $language = JFactory::getLanguage();
    $language->load(_JOOM_OPTION.'.exif', JPATH_SITE);
    $language->load(_JOOM_OPTION.'.iptc', JPATH_SITE);

    $display = true;
    if($this->_config->isExtended())
    {
      $config_id = JRequest::getInt('id');

      // Overwrite config object with specified one
      $this->_config = JoomConfig::getInstance($config_id);

      if(JRequest::getInt('group_id') || ($config_id && $config_id != 1))
      {
        $display = false;
      }
    }

    // Check the installation of GD
    $gdver = $this->get('GDVersion');
    // Returns version, 0 if not installed, or -1 if appears
    // to be installed but not verified
    if($gdver > 0)
    {
      $gdmsg = JText::sprintf('COM_JOOMGALLERY_CONFIG_GS_IP_GDLIB_INSTALLED', $gdver);
    }
    else
    {
      if($gdver == -1)
      {
        $gdmsg = JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_GDLIB_NO_VERSION');
      }
      else
      {
        $gdmsg = JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_GDLIB_NOT_INSTALLED') .
                '<a href="http://www.php.net/gd" target="_blank">http://www.php.net/gd</a>'
                . JText::_('COM_JOOMGALLERY_GD_MORE_INFO');
      }
    }
    // Check the installation of ImageMagick
    // first check if exec() has been diabled in php.ini
    if($this->get('DisabledExec'))
    {
      $immsg = JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_IMAGIC_EXEC_DISABLED');
    }
    else
    {
      $imver = $this->get('IMVersion');
      // Returns version, 0 if not installed or path not properly configured
      if($imver)
      {
        $immsg = JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_IMAGIC_INSTALLED') .  $imver;
        // Add the information that IM was detected automatically if path is empty
        if(!$this->_config->get('jg_impath'))
        {
          $immsg .= JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_IMAGIC_INSTALLED_AUTO') ;
        }
      }
      else
      {
        $immsg = JText::_('COM_JOOMGALLERY_CONFIG_GS_IP_IMAGIC_NOT_INSTALLED');
      }
    }

    // Check the installation of Exif
    $exifmsg = '';
    if(!extension_loaded('exif'))
    {
      $exifmsg    = '<div style="color:#f00;font-weight:bold; text-align:center;">[' . JText::_('COM_JOOMGALLERY_CONFIG_DV_ED_NOT_INSTALLED') . ' ' . JText::_('COM_JOOMGALLERY_CONFIG_DV_ED_NO_OPTIONS') . ']</div>';
    }
    else
    {
      $exifmsg    = '<div style="color:#080; text-align:center;">[' . JText::_('COM_JOOMGALLERY_CONFIG_DV_ED_INSTALLED') . ']</div>';
      if(!function_exists('exif_read_data'))
      {
        $exifmsg = '<div style="color:#f00;font-weight:bold; text-align:center;">[' . JText::_('COM_JOOMGALLERY_CONFIG_DV_ED_INSTALLED_BUT') . ' ' . JText::_('COM_JOOMGALLERY_CONFIG_DV_ED_NO_OPTIONS') . ']</div>';
      }
    }

    // Check pathes and watermark file
    $writeable   = '<span style="color:#080;">'
      . JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_DIRECTORY_WRITEABLE') .
      '</span>';
    $unwriteable = '<span style="color:#f00;">'
      . JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_DIRECTORY_UNWRITEABLE') .
      '</span>';

    if(is_writeable($this->getPath('img')))
    {
      $write_pathimages = $writeable;
    }
    else
    {
      $write_pathimages = $unwriteable;
    }
    if(is_writeable($this->getPath('orig')))
    {
      $write_pathoriginalimages = $writeable;
    }
    else
    {
      $write_pathoriginalimages = $unwriteable;
    }
    if(is_writeable($this->getPath('thumb')))
    {
      $write_paththumbs = $writeable;
    }
    else
    {
      $write_paththumbs = $unwriteable;
    }
    if(is_writeable($this->getPath('ftp')))
    {
      $write_pathftpupload = $writeable;
    }
    else
    {
      $write_pathftpupload = $unwriteable;
    }
    if(is_writeable($this->getPath('temp')))
    {
      $write_pathtemp = $writeable;
    }
    else
    {
      $write_pathtemp = $unwriteable;
    }
    if(is_writeable($this->getPath('wtm')))
    {
      $write_pathwm = $writeable;
    }
    else
    {
      $write_pathwm = $unwriteable;
    }
    if(is_file($this->getPath('wtm').'/'.$this->_config->get('jg_wmfile')))
    {
      $wmfilemsg = '<span style="color:#080;">'
        . JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_FILE_EXIST') .
        '</span>';
    }
    else
    {
      $wmfilemsg = '<span style="color:#f00;">'
        . JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_FILE_NOT_EXIST') .
        '</span>';
    }

    // Check whether CSS file (joom_settings.css) is writeable
    if(is_writeable(JPATH_ROOT.'/media/joomgallery/css/'.$this->_config->getStyleSheetName()))
    {
      $cssfilemsg = '<div style="color:#080; text-align:center;">['.JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_CSS_CONFIGURATION_WRITEABLE').']</div>';
    }
    else
    {
      $cssfilemsg = '<div style="color:#f00;font-weight:bold; text-align:center;">['.JText::_('COM_JOOMGALLERY_CONFIG_GS_PD_CSS_CONFIGURATION_NOT_WRITEABLE').' '.JText::_('COM_JOOMGALLERY_COMMON_CHECK_PERMISSIONS').']</div>';
    }

    // Exif
    require_once JPATH_COMPONENT.'/includes/exifarray.php';

    $ifdotags   = explode(',', $this->_config->get('jg_ifdotags'));
    $subifdtags = explode(',', $this->_config->get('jg_subifdtags'));
    $gpstags    = explode(',', $this->_config->get('jg_gpstags'));

    $exif_definitions = array(
      1 => array ('TAG' => 'IFD0', 'JG' => $ifdotags, 'NAME' => 'jg_ifdotags[]', 'HEAD' => JText::_('COM_JOOMGALLERY_IFD0TAGS')),
      2 => array ('TAG' => 'EXIF', 'JG' => $subifdtags, 'NAME' => 'jg_subifdtags[]', 'HEAD' => JText::_('COM_JOOMGALLERY_SUBIFDTAGS')),
      3 => array ('TAG' => 'GPS',  'JG' => $gpstags,  'NAME' => 'jg_gpstags[]',  'HEAD' => JText::_('COM_JOOMGALLERY_GPSTAGS'))
    );

    // IPTC
    require_once JPATH_COMPONENT.'/includes/iptcarray.php';

    $iptctags   = explode(',', $this->_config->get('jg_iptctags'));

    $iptc_definitions = array(
    1 => array ('TAG' => 'IPTC', 'JG' => $iptctags, 'NAME' => 'jg_iptctags[]', 'HEAD' => JText::_('COM_JOOMGALLERY_IPTCTAGS')),
    );

    // Include javascript for form validation, cleaning and submitting
    $this->_doc->addScript($this->_ambit->getScript('config.js'));

    JText::script('COM_JOOMGALLERY_CONFIG_GS_PD_ALERT_THUMBNAIL_PATH_SUPPORT');

    $this->assignRef('display',                   $display);
    $this->assignRef('cssfilemsg',                $cssfilemsg);
    $this->assignRef('exifmsg',                   $exifmsg);
    $this->assignRef('gdmsg',                     $gdmsg);
    $this->assignRef('immsg',                     $immsg);
    $this->assignRef('write_pathimages',          $write_pathimages);
    $this->assignRef('write_pathoriginalimages',  $write_pathoriginalimages);
    $this->assignRef('write_paththumbs',          $write_paththumbs);
    $this->assignRef('write_pathftpupload',       $write_pathftpupload);
    $this->assignRef('write_pathtemp',            $write_pathtemp);
    $this->assignRef('write_pathwm',              $write_pathwm);
    $this->assignRef('wmfilemsg',                 $wmfilemsg);
    $this->assignRef('exif_definitions',          $exif_definitions);
    $this->assignRef('exif_config_array',         $exif_config_array);
    $this->assignRef('iptc_definitions',          $iptc_definitions);
    $this->assignRef('iptc_config_array',         $iptc_config_array);

    $this->addToolbar();

    if(!$this->_mainframe->input->getBool('hidemainmenu'))
    {
      $this->sidebar = JHtmlSidebar::render();
    }

    parent::display();
  }

  function addToolbar()
  {
    $title = JText::_('COM_JOOMGALLERY_CONFIG_CONFIGURATION_MANAGER');
    if($this->_config->isExtended())
    {
      $config_title = $this->get('ConfigTitle');
      if(JRequest::getInt('id') == 1)
      {
        $config_title = JText::sprintf('COM_JOOMGALLERY_CONFIGS_DEFAULT_TITLE', $config_title);
      }

      $title .= ' :: '.JText::sprintf('COM_JOOMGALLERY_CONFIG_EDIT_TITLE', $config_title);
    }

    JToolBarHelper::title($title, 'equalizer');
    JToolbarHelper::apply('apply');
    JToolbarHelper::save('save');
    if($this->_config->isExtended())
    {
      JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
    }
  }

  function getPath($type)
  {
    switch($type)
    {
      case 'thumb':
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_paththumbs'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_paththumbs'));
        }
        break;
      case 'img':
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_pathimages'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_pathimages'));
        }
        break;
      case 'orig':
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_pathoriginalimages'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_pathoriginalimages'));
        }
        break;
      case 'ftp':
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_pathftpupload'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_pathftpupload'));
        }
        break;
      case 'temp':
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_pathtemp'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_pathtemp'));
        }
        break;
      default:
        $path = JPath::clean(JPATH_ROOT.'/'.$this->_config->get('jg_wmpath'));
        if(!JFolder::exists($path))
        {
          $path = JPath::clean($this->_config->get('jg_wmpath'));
        }
        break;
    }

    return $path;
  }

  /**
   * Method to get the field input for a component layout field.
   *
   * @param   string  $selected The currently selected layout
   * @return  string  The field input
   * @since   3.0
   */
  protected function getComponentLayouts($selected = null)
  {
    $lang = JFactory::getLanguage();

    // Get the database object and a new query object
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Build the query
    $query->select('e.element, e.name');
    $query->from('#__extensions as e');
    $query->where('e.client_id = 0');
    $query->where('e.type = ' . $db->q('template'));
    $query->where('e.enabled = 1');

    // Set the query and load the templates.
    $db->setQuery($query);
    $templates = $db->loadObjectList('element');

    // Build the search paths for component layouts.
    $component_path = JPath::clean(JPATH_COMPONENT_SITE.'/views/gallery/tmpl');

    // Prepare array of component layouts
    $component_layouts = array();

    // Prepare the grouped list
    $groups = array();

    // Add the layout options from the component path.
    if(is_dir($component_path) && ($component_layouts = JFolder::files($component_path, '^[^_]*\.xml$', false, true)))
    {
      // Create the group for the component
      $groups['_'] = array();
      $groups['_']['id'] = 'jg_alternative_layout__';
      $groups['_']['text'] = JText::sprintf('JOPTION_FROM_COMPONENT');
      $groups['_']['items'] = array();

      foreach($component_layouts as $i => $file)
      {
        // Attempt to load the XML file.
        if(!$xml = simplexml_load_file($file))
        {
          unset($component_layouts[$i]);

          continue;
        }

        // Get the help data from the XML file if present.
        if(!$menu = $xml->xpath('layout[1]'))
        {
          unset($component_layouts[$i]);

          continue;
        }

        $menu = $menu[0];

        // Add an option to the component group
        $value = basename($file, '.xml');
        $component_layouts[$i] = $value;
        if($lang->hasKey('COM_JOOMGALLERY_LAYOUT_'.strtoupper($value)))
        {
          $text = JText::_('COM_JOOMGALLERY_LAYOUT_'.strtoupper($value));
        }
        else
        {
          $text = $value;
        }
        $groups['_']['items'][] = JHtml::_('select.option', '_:' . $value, $text);
      }
    }

    // Loop on all templates
    if($templates)
    {
      foreach($templates as $template)
      {
        // Load language file
        $lang->load('tpl_' . $template->element . '.sys', JPATH_ROOT, null, false, false)
          || $lang->load('tpl_' . $template->element . '.sys',JPATH_ROOT . '/templates/' . $template->element, null, false, false)
          || $lang->load('tpl_' . $template->element . '.sys', JPATH_ROOT, $lang->getDefault(), false, false)
          || $lang->load(
          'tpl_' . $template->element . '.sys', JPATH_ROOT . '/templates/' . $template->element, $lang->getDefault(), false, false
        );

        $template_path = JPath::clean(JPATH_ROOT . '/templates/' . $template->element . '/html/' . _JOOM_OPTION . '/gallery');

        // Add the layout options from the template path.
        if(is_dir($template_path) && ($files = JFolder::files($template_path, '^[^_]*\.php$', false, true)))
        {
          // Files with corresponding XML files are alternate menu items, not alternate layout files
          // so we need to exclude these files from the list.
          $xml_files = JFolder::files($template_path, '^[^_]*\.xml$', false, true);
          for($j = 0, $count = count($xml_files); $j < $count; $j++)
          {
            $xml_files[$j] = basename($xml_files[$j], '.xml');
          }
          foreach($files as $i => $file)
          {
            // Remove layout files that exist in the component folder or that have XML files
            if ((in_array(basename($file, '.php'), $component_layouts))
              || (in_array(basename($file, '.php'), $xml_files)))
            {
              unset($files[$i]);
            }
          }
          if(count($files))
          {
            // Create the group for the template
            $groups[$template->name] = array();
            $groups[$template->name]['id'] = $this->id . '_' . $template->element;
            $groups[$template->name]['text'] = JText::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
            $groups[$template->name]['items'] = array();

            foreach($files as $file)
            {
              // Add an option to the template group
              $value = basename($file, '.php');
              $text = $lang->hasKey($key = strtoupper('TPL_' . $template->name . '_COM_JOOMGALLERY_GALLERY_LAYOUT_' . strtoupper($value))) ? JText::_($key) : $value;
              $groups[$template->name]['items'][] = JHtml::_('select.option', $template->element . ':' . $value, $text);
            }
          }
        }
      }
    }

    // Prepare HTML code
    $html = array();

    // Add a grouped list
    $html[] = JHtml::_(
      'select.groupedlist', $groups, 'jg_alternative_layout',
      array('id' => 'jg_alternative_layout', 'group.id' => 'id', 'list.attr' => null, 'list.select' => $selected)
    );

    return implode($html);
  }
}