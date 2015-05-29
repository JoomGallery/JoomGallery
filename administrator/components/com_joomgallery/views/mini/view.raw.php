<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/mini/view.html.php $
// $Id: view.html.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * HTML View class for the Mini Joom view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewMini extends JoomGalleryView
{
  /**
   * HTML view display method
   *
   * @access  public
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  function display($tpl = null)
  {
    JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR.'/models/forms');

    // Initialise some variables
    $this->page   = 1;
    $this->e_name = $this->_mainframe->getUserStateFromRequest('joom.mini.e_name', 'e_name', 'text', 'string');
    $this->images_fields = array('type', 'position', 'linked', 'linked_type', 'alttext', 'class', 'linkedtext');

    $this->extended     = $this->_mainframe->getUserStateFromRequest('joom.mini.extended', 'extended', 1, 'int');
    $this->upload_catid = $this->_mainframe->input->getInt('upload_category');
    $this->prefix       = $this->_mainframe->getUserStateFromRequest('joom.mini.prefix', 'prefix', 'joom', 'cmd');

    // Decide which tabs have to be displayed
    $this->tabs = array('images' => true);
    if($this->extended > 0)
    {
      $this->tabs = array('images' => true, 'categories' => true, 'upload' => true, 'createcategory' => true);
    }

    if($this->upload_catid)
    {
      $this->tabs = array('upload' => true);
    }

    // Images tab
    if(isset($this->tabs['images']))
    {
      // Also display the options for inserting images into articles
      if($this->extended > 0)
      {
        $plugin = JPluginHelper::getPlugin('content', 'joomplu');
        if(!$this->upload_catid && !count($plugin))
        {
          $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_MINI_MSG_NOT_INSTALLED_OR_ACTIVATED'), 'notice');
          $params = '';
        }
        else
        {
          $params = $plugin->params;
        }

        // Load plugin parameters
        $this->params = new JRegistry();
        $this->params->loadString($params);

        $this->images_form = JForm::getInstance(_JOOM_OPTION.'.mini.images', 'mini.images');

        $fields_default_values = array('type' => 'thumb', 'position' => '', 'linked' => 0, 'linked_type' => 'orig', 'alttext' => '', 'class' => '', 'linkedtext' => '');
        foreach($this->images_fields as $field)
        {
          $this->images_form->setFieldAttribute($field, 'default', $this->params->get('default_'.$field, $fields_default_values[$field]));
        }

        // Hidden images
        $this->_mainframe->setUserState('joom.mini.showhidden', $this->params->get('showhidden'));
      }

      // Pagination
      $this->total = $this->get('TotalImages');

      // Calculation of the number of total pages
      $limit = $this->_mainframe->getUserStateFromRequest('joom.mini.limit', 'limit', 30, 'int');
      if(!$limit)
      {
        $this->totalpages = 1;
      }
      else
      {
        $this->totalpages = floor($this->total / $limit);
        $offcut     = $this->total % $limit;
        if($offcut > 0)
        {
          $this->totalpages++;
        }
      }

      $totalimages = $this->total;
      $this->total = number_format($this->total, 0, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));

      // Get the current page
      $this->page = JRequest::getInt('page', 0);
      if($this->page > $this->totalpages)
      {
        $this->page = $this->totalpages;
      }
      if($this->page < 1)
      {
        $this->page = 1;
      }

      // Limitstart
      $limitstart = ($this->page - 1) * $limit;
      JRequest::setVar('limitstart', $limitstart);

      if($this->total <= $limit)
      {
        $limitstart = 0;
        JRequest::setVar('limitstart', $limitstart);
      }

      JRequest::setVar('limit', $limit);

      require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/pagination.php';
      $onclick = 'javascript:ajaxRequest(\'index.php?option='._JOOM_OPTION.'&view=mini&format=json\', %u); return false;';
      $this->pagination = new JoomPagination($totalimages, $limitstart, $limit, '', null, $onclick);

      $this->images = $this->get('Images');

      $this->catid = $this->_mainframe->getUserStateFromRequest('joom.mini.catid', 'catid', 0, 'int');

      foreach($this->images as $key => $image)
      {
        $image->thumb_src = null;
        $thumb = $this->_ambit->getImg('thumb_path', $image);
        if($image->imgthumbname && is_file($thumb))
        {
          $imginfo              = getimagesize($thumb);
          $image->thumb_src     = $this->_ambit->getImg('thumb_url', $image);
          $image->thumb_width   = $imginfo[0];
          $image->thumb_height  = $imginfo[1];
          $this->image          = $image;
          $overlib              = $this->loadTemplate('overlib');
          $image->overlib       = str_replace(array("\r\n", "\r", "\n"), '', htmlspecialchars($overlib, ENT_QUOTES, 'UTF-8'));
        }

        $this->images[$key]           = $image;
      }

      // Limit Box
      $limits = array();

      // Create the option list
      for($i = 5; $i <= 30; $i += 5)
      {
        $limits[] = JHtml::_('select.option', $i);
      }
      $limits[] = JHtml::_('select.option', '50');
      $limits[] = JHtml::_('select.option', '100');
      $limits[] = JHtml::_('select.option', '0', JText::_('JALL'));

      $url      = 'index.php?option='._JOOM_OPTION.'&view=mini&format=json';
      $this->lists = array();
      $this->lists['limit'] = JHtml::_('select.genericlist',  $limits, 'limit', 'class="inputbox input-mini" size="1" onchange="javascript:ajaxRequest(\''.$url.'\', 0, \'limit=\' + this[this.selectedIndex].value)"', 'value', 'text', $limit);
      $this->lists['image_categories'] = JHtml::_('joomselect.categorylist', $this->catid, 'catid', 'onchange="javascript:ajaxRequest(\''.$url.'\', 0, \'catid=\' + document.id(\'catid\').value)"', null, '- ', 'filter');

      $this->object = $this->_mainframe->getUserStateFromRequest('joom.mini.object', 'object', '', 'cmd');
      $this->search = $this->_mainframe->getUserStateFromRequest('joom.mini.search', 'search', '', 'string');
    }

    // Categories tab
    if(isset($this->tabs['categories']))
    {
      $this->categories_form = JForm::getInstance(_JOOM_OPTION.'.mini.categories', 'mini.categories');

      $this->categories_form->setFieldAttribute('category_catid', 'onchange', str_replace('joom_', $this->prefix.'_', $this->categories_form->getFieldAttribute('category_catid', 'onchange')));
      $categories_fields = array('category_mode', 'category_limit', 'category_columns', 'category_ordering', 'category_linkedtext');
      foreach($categories_fields as $field)
      {
        $this->categories_form->setFieldAttribute($field, 'default', $this->params->get('default_'.$field));
      }
    }

    // Upload tab
    if(isset($this->tabs['upload']))
    {
      $this->upload_form = JForm::getInstance(_JOOM_OPTION.'.mini.upload', 'mini.upload');

      if($this->upload_catid)
      {
        $this->upload_form->setFieldAttribute('catid', 'default', $this->upload_catid);
      }
      else
      {
        $this->upload_form->setFieldAttribute('ajaxupload', 'insert_options', true);
      }

      $this->editFilename     = $this->_mainframe->isSite() ? $this->_config->get('jg_useruseorigfilename') : $this->_config->get('jg_useorigfilename');
      $this->delete_original  = $this->_mainframe->isSite() ? ($this->_config->get('jg_delete_original_user') == 2) : ($this->_config->get('jg_delete_original') == 2);

      JText::script('COM_JOOMGALLERY_MINI_TYPE');
      JText::script('COM_JOOMGALLERY_MINI_POSITION');
      JText::script('COM_JOOMGALLERY_MINI_ALTTEXT');
      JText::script('COM_JOOMGALLERY_COMMON_THUMBNAIL');
      JText::script('COM_JOOMGALLERY_MINI_DETAIL');
      JText::script('COM_JOOMGALLERY_MINI_ORIGINAL');
      JText::script('JNONE');
      JText::script('JGLOBAL_CENTER');
      JText::script('JGLOBAL_LEFT');
      JText::script('JGLOBAL_RIGHT');
    }

    // Create category tab
    if(isset($this->tabs['createcategory']))
    {
      $this->category_form = JForm::getInstance(_JOOM_OPTION.'.mini.category', 'mini.category');

      JText::script('COM_JOOMGALLERY_MINI_PLEASE_ENTER_TEXT');
    }

    // The parameter object is necessary for the even if it
    // doesn't contain any data (this simplifies things)
    if(!isset($this->params))
    {
      $this->params = new JRegistry();
      $this->params->loadString('');
    }

    // Set some default values before possibly modifying the view
    $this->upload_categories  = null;
    $this->parent_categories  = null;
    $this->upload_enabled     = true;
    $this->createcat_enabled  = true;

    // If we are in frontend modify the view by adding possibility to
    // change the category select boxes according to the plugin settings
    if(!$this->upload_catid && $this->_mainframe->isSite())
    {
      $this->modifyView();
    }

    JText::script('JLIB_FORM_FIELD_INVALID');

    // Build the sorted message list
    $messages = $this->_mainframe->getMessageQueue();
    $this->messages = array();
    if(is_array($messages) && !empty($messages))
    {
      foreach($messages as $msg)
      {
        if(isset($msg['type']) && isset($msg['message']))
        {
          $this->messages[$msg['type']][] = $msg['message'];
        }
      }
    }

    parent::display($tpl);
  }

  /**
   * Modifies the view by adding possibility to change the
   * category select boxes according to the plugin settings
   *
   * @return  void
   * @since   3.0
   */
  protected function modifyView()
  {
    // Upload
    // The default is that uploading is enabled and that the complete category select box of JForm is used
    if($this->params->get('upload_enabled'))
    {
      // Check whether only a part of the categories shall be available
      $catids = explode(',', $this->params->get('upload_catids'));
      if($this->params->get('upload_catids') && count($catids))
      {
        $categories = $this->getModel()->getUploadCategories($catids);
        if(!count($categories))
        {
          // If no category is left disable the upload
          $this->upload_enabled     = false;
        }
        else
        {
          // Otherwise set the new category select box
          $this->upload_categories  = JHtml::_('select.genericlist', $categories, 'catid', null, 'cid', 'path', null, 'upload_categories');
        }
      }
    }

    // Create Category
    // The default is that category creation is enabled and that the complete category select box of JForm is used
    if($this->params->get('create_category'))
    {
      // Check whether only a part of the categories shall be available
      $catids = explode(',', $this->params->get('parent_catids'));
      if($this->params->get('parent_catids') && count($catids))
      {
        $categories = $this->getModel()->getParentCategories($catids);
        if(!count($categories))
        {
          // If no category is left disable the category creation
          $this->createcat_enabled  = false;
        }
        else
        {
          // Otherwise set the new category select box
          $this->parent_categories  = JHtml::_('select.genericlist', $categories, 'parent_id', null, 'cid', 'path', null, 'parent_categories');
        }
      }
    }
  }
}
