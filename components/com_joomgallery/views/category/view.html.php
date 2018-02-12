<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/category/view.html.php $
// $Id: view.html.php 4250 2013-05-02 16:49:22Z chraneco $
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
 * HTML View class for the category view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewCategory extends JoomGalleryView
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
    $this->params = $this->_mainframe->getParams();

    // Prepare params for header and footer
    JoomHelper::prepareParams($this->params);

    // Load modules at position 'top'
    $this->modules['top'] = JoomHelper::getRenderedModules('top');
    if(count($this->modules['top']))
    {
      $this->params->set('show_top_modules', 1);
    }
    // Load modules at position 'btm'
    $this->modules['btm'] = JoomHelper::getRenderedModules('btm');
    if(count($this->modules['btm']))
    {
      $this->params->set('show_btm_modules', 1);
    }

    // Check whether this is the active menu item. This is a
    // special case in addition to code in constructor of parent class
    // because here we have to check the category ID, too.
    $active = $this->_mainframe->getMenu()->getActive();
    if(!$active || strpos($active->link, '&catid='.$this->_mainframe->input->getInt('catid')) === false)
    {
      // Get the default layout from the configuration
      if($layout = $this->_config->get('jg_alternative_layout'))
      {
        $this->setLayout($layout);
      }
    }

    // Get number of images and hits in gallery
    $numbers            = JoomHelper::getNumberOfImgHits();
    $this->numberofpics = $numbers[0];
    $this->numberofhits = $numbers[1];

    // Categories pagination
    if($this->_config->get('jg_hideemptycats') == 2)
    {
      $this->totalcategories = $this->get('TotalCategoriesWithoutEmpty');
    }
    else
    {
      $this->totalcategories = $this->get('TotalCategories');
    }

    // Calculation of the number of total pages
    $catperpage = $this->_config->get('jg_subperpage');
    if(!$catperpage)
    {
      $catperpage = 10;
    }
    $this->cattotalpages = floor($this->totalcategories / $catperpage);
    $offcut              = $this->totalcategories % $catperpage;
    if($offcut > 0)
    {
      $this->cattotalpages++;
    }

    $total                 = $this->totalcategories;
    $this->totalcategories = number_format($this->totalcategories, 0, ',', '.');
    // Get the current page
    $this->catpage = $this->_mainframe->input->getInt('catpage', 0);
    if($this->catpage > $this->cattotalpages)
    {
      $this->catpage = $this->cattotalpages;
      if($this->catpage <= 0)
      {
        $this->catpage = 1;
      }
    }
    else
    {
      if($this->catpage < 1)
      {
        $this->catpage = 1;
      }
    }

    // Limitstart
    $limitstart = ($this->catpage - 1) * $catperpage;
    $this->_mainframe->input->set('catlimitstart', $limitstart);

    require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/pagination.php';
    $this->catpagination = new JoomPagination($total, $limitstart, $catperpage, 'cat', 'subcategory');

    if($this->cattotalpages > 1 && $this->totalcategories != 0)
    {
      if($this->_config->get('jg_showpagenavsubs') <= 2)
      {
        $this->params->set('show_pagination_cat_top', 1);
      }
      if($this->_config->get('jg_showpagenavsubs') >= 2)
      {
        $this->params->set('show_pagination_cat_bottom', 1);
      }
    }

    // Displaying of category number depends on pagination position
    if($this->_config->get('jg_showsubcatcount'))
    {
      if($this->_config->get('jg_showpagenavsubs') <= 2)
      {
        $this->params->set('show_count_cat_top', 1);
      }
      if($this->_config->get('jg_showpagenavsubs') >= 2)
      {
        $this->params->set('show_count_cat_bottom', 1);
      }
    }

    // Images pagination
    $this->totalimages = $this->get('TotalImages');

    // Calculation of the number of total pages
    $perpage = $this->_config->get('jg_perpage');
    if(!$perpage)
    {
      $perpage = 10;
    }
    $this->totalpages = floor($this->totalimages / $perpage);
    $offcut           = $this->totalimages % $perpage;
    if($offcut > 0)
    {
      $this->totalpages++;
    }

    $total = $this->totalimages;
    $this->totalimages = number_format($this->totalimages, 0, ',', '.');
    // Get the current page
    $this->page = $this->_mainframe->input->getInt('page', 0);
    if($this->page > $this->totalpages)
    {
      $this->page = $this->totalpages;
      if($this->page <= 0)
      {
        $this->page = 1;
      }
    }
    else
    {
      if($this->page < 1)
      {
        $this->page = 1;
      }
    }

    // Limitstart
    $limitstart = ($this->page - 1) * $perpage;

    $this->pagination = new JoomPagination($total, $limitstart, $perpage);

    // 'jg_detailpic_open' is not numeric if an OpenImage plugin was selected, thus we handle it like > 4
    if(     $this->_config->get('jg_lightbox_slide_all')
        &&  (!is_numeric($this->_config->get('jg_detailpic_open')) ||  $this->_config->get('jg_detailpic_open') > 4)
      )
    {
      $this->params->set('show_all_in_popup', 1);
      $this->_mainframe->input->set('limitstart', -1);

      // We need all images of this category
      $images = $this->get('Images');

      $this->popup = array();

      $end    = ($this->page - 1) * $perpage;
      $start  = $this->page * $perpage;
      $this->popup['before']  = JHTML::_('joomgallery.popup', $images, 0, $end);
      $this->popup['after']   = JHTML::_('joomgallery.popup', $images, $start);

      // Now we have to select the images according to the pagination
      $images = array_slice($images, $limitstart, $perpage);
    }
    else
    {
      $this->_mainframe->input->set('limitstart',  $limitstart);
      $this->_mainframe->input->set('limit',       $this->_config->get('jg_perpage'));

      $images = $this->get('Images');
    }

    if($this->totalpages > 1 && $this->totalimages != 0)
    {
      if($this->_config->get('jg_showpagenav') <= 2)
      {
        $this->params->set('show_pagination_img_top', 1);
      }
      if($this->_config->get('jg_showpagenav') >= 2)
      {
        $this->params->set('show_pagination_img_bottom', 1);
      }
    }

    // Displaying of image number depends on pagination position
    if($this->_config->get('jg_showpiccount'))
    {
      if($this->_config->get('jg_showpagenav') <= 2)
      {
        $this->params->set('show_count_img_top', 1);
      }
      if($this->_config->get('jg_showpagenav') >= 2)
      {
        $this->params->set('show_count_img_bottom', 1);
      }
    }

    $cat = $this->get('Category');

    if(isset($cat->protected))
    {
      $this->cat = $cat;
      echo $this->loadTemplate('password');

      return;
    }

    $backlink = array();

    if($cat->parent_id > 1)
    {
      // Sub-category -> parent category
      $this->backtarget = JRoute::_('index.php?view=category&catid='.$cat->parent_id);
      $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_CATEGORY');
    }
    else
    {
      // Category view -> gallery view
      $this->backtarget = JRoute::_('index.php?view=gallery');
      $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');
    }

    // Meta data
    if($cat->metadesc)
    {
      $this->_doc->setDescription($cat->metadesc);
    }
    if($cat->metakey)
    {
      $this->_doc->setMetadata('keywords', $cat->metakey);
    }

    /*if($this->_mainframe->getCfg('MetaAuthor') == '1' && $cat->author)
    {
      $this->_doc->setMetaData('author', $cat->author);
    }*/

    // Breadcrumbs
    if($this->_config->get('jg_completebreadcrumbs') || $this->_config->get('jg_showpathway'))
    {
      $parents  = JoomHelper::getAllParentCategories($cat->cid);
    }

    $menus = $this->_mainframe->getMenu();
    $menu  = $menus->getActive();
    if($menu && array_key_exists('view',$menu->query) && $this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs  = $this->_mainframe->getPathway();
      switch($menu->query['view'])
      {
        case '':
        case 'gallery':
          foreach($parents as $parent)
          {
            $breadcrumbs->addItem($parent->name, 'index.php?view=category&catid='.$parent->cid);
          }

          $breadcrumbs->addItem($cat->name);
          break;
        case 'category':
          $skip = true;
          foreach($parents as $key => $parent)
          {
            if($skip)
            {
              if($key == $menu->query['catid'])
              {
                $skip = false;
              }
            }
            else
            {
              $breadcrumbs->addItem($parent->name, 'index.php?view=category&catid='.$parent->cid);
            }
          }

          if(!$skip)
          {
            $breadcrumbs->addItem($cat->name);
          }
          break;
        default:
          break;
      }
    }

    /*if($this->_config->get('jg_completebreadcrumbs'))
    {
      $breadcrumbs = &$this->_mainframe->getPathway();

      foreach($parents as $parent)
      {
        $breadcrumbs->addItem($parent->name, 'index.php?view=category&catid='.$parent->cid);
      }

      $breadcrumbs->addItem($cat->name);
    }*/

    // JoomGallery Pathway
    $this->pathway = '';
    if($this->_config->get('jg_showpathway'))
    {
      $this->pathway = '<a href="'.JRoute::_('index.php?view=gallery').'" class="jg_pathitem">'.JText::_('COM_JOOMGALLERY_COMMON_HOME').'</a> &raquo; ';

      foreach($parents as $parent)
      {
        $this->pathway .= '<a href="'.JRoute::_('index.php?view=category&catid='.$parent->cid).'" class="jg_pathitem">'.$parent->name.'</a> &raquo; ';
      }

      $this->pathway .= $cat->name;
    }

    // Page title
    if($this->_config->get('jg_pagetitle_cat'))
    {
      $pagetitle = JoomHelper::createPagetitle( $this->_config->get('jg_pagetitle_cat'),
                                                $cat->name,
                                                '',
                                                $this->params->get('page_title') ? $this->params->get('page_title') : JText::_('COM_JOOMGALLERY_COMMON_GALLERY')
                                              );
      $this->_doc->setTitle($pagetitle);
    }

    // RSS feed
    if($this->_config->get('jg_category_rss'))
    {
      $link = '&format=feed&limitstart=';
      $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
      $this->_doc->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
      $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
      $this->_doc->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

      if($this->_config->get('jg_category_rss_icon'))
      {
        $this->params->set('show_feed_icon', 1);
        $this->params->set('feed_url', JRoute::_($link.'&type='.$this->_config->get('jg_category_rss_icon')));
      }
    }

    // Favourites icon for categories
    if($this->_config->get('jg_allimagesofcategory') && $this->_config->get('jg_favourites'))
    {
      if(   $this->_user->get('id')
         || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
        )
      {
        if(    $this->_config->get('jg_usefavouritesforzip')
           || ($this->_config->get('jg_usefavouritesforpubliczip') && !$this->_user->get('id'))
          )
        {
          $this->params->set('show_headerfavourites_icon', 2);
        }
        else
        {
          $this->params->set('show_headerfavourites_icon', 1);
        }
      }
      else
      {
        if(($this->_config->get('jg_favouritesshownotauth') == 1))
        {
          if($this->_config->get('jg_usefavouritesforzip'))
          {
            $this->params->set('show_headerfavourites_icon', -2);
          }
          else
          {
            $this->params->set('show_headerfavourites_icon', -1);
          }
        }
      }
    }

    // Icon for quick upload
    if(     $this->_config->get('jg_uploadiconcategory')
        &&  (   $this->_user->authorise('joom.upload', _JOOM_OPTION.'.category.'.$cat->cid)
            ||  $cat->owner && $cat->owner == $this->_user->get('id') && $this->_user->authorise('joom.upload.inown', _JOOM_OPTION.'.category.'.$cat->cid)
            )
      )
    {
      $this->params->set('show_upload_icon', 1);
      JHtml::_('behavior.modal');
    }

    // Get all sub-categories of the current category
    if($this->_config->get('jg_hideemptycats') == 2)
    {
      // If the third alternative for hiding empty categories
      // is chosen ('Also those which contain empty sub-categories'),
      // we need additional code to exclude these categories.
      // (For the second alternative only the query in the model is modified.)
      $categories = $this->get('CategoriesWithoutEmpty');
    }
    else
    {
      $categories = $this->get('Categories');
    }

    foreach($categories as $key => $category)
    {
      $categories[$key]->isnew = '';
      if($this->_config->get('jg_showcatasnew'))
      {
        // Check if an image in this category or in sub-categories is marked with 'new'
        $categories[$key]->isnew = JoomHelper::checkNewCatg($categories[$key]->cid);
      }

      // Count the images in category and sub-categories
      $imgshits = JoomHelper::getNumberOfImgHits($categories[$key]->cid);
      $categories[$key]->pictures = $imgshits[0];
      if($categories[$key]->pictures == '1')
      {
        $categories[$key]->picorpics = 'COM_JOOMGALLERY_GALLERY_ONE_IMAGE';
      }
      else
      {
        $categories[$key]->picorpics = 'COM_JOOMGALLERY_GALLERY_IMAGES';
      }

      // Count the hits of all images in category and sub-categories
      $categories[$key]->totalhits = $imgshits[1];

      $category->thumb_src = null;
      $category->gallerycontainer = 'jg_subcatelem_cat';
      $category->photocontainer   = 'jg_subcatelem_photo';
      $category->textcontainer    = 'jg_subcatelem_txt';
      if($this->_config->get('jg_showsubthumbs') > 0 && in_array($categories[$key]->access, $this->_user->getAuthorisedViewLevels()))
      {
        if(     $this->_config->get('jg_showsubthumbs') == 2
            ||  (     $this->_config->get('jg_showsubthumbs') == 3
                  &&  (     !$categories[$key]->thumbnail
                        ||  !isset($categories[$key]->id)
                      )
                )
          )
        {
          // Random choice of category/thumbnail
          switch($this->_config->get('jg_showrandomsubthumb'))
          {
            // Only from current category
            case 1:
              $random_catid = $category->cid;
              break;
            // Only from sub-categories
            case 2:
              // Get array of all sub-categories without the current category
              // Only with images
              $allsubcats = JoomHelper::getAllSubCategories($category->cid, false);
              if(count($allsubcats))
              {
                $random_catid = $allsubcats[mt_rand(0, count($allsubcats) - 1)];
              }
              else
              {
                $random_catid = 0;
              }
              break;
            // From both
            case 3:
              // Get array of all sub-categories including the current category
              // Only with images
              $allsubcats = JoomHelper::getAllSubCategories($category->cid, true);
              if(count($allsubcats))
              {
                $random_catid = $allsubcats[mt_rand(0, count($allsubcats)-1)];
              }
              else
              {
                $random_catid = 0;
              }
              break;
            default:
              $random_catid = 0;
              break;
          }

          // Random image, only if there are $randomcat(s)
          if(    $this->_config->get('jg_showrandomsubthumb') == 1
             || ($this->_config->get('jg_showrandomsubthumb') >= 2 && $random_catid != 0)
            )
          {
            $model  = $this->getModel();

            if($row = $model->getRandomImage($category->cid, $random_catid))
            {
              $cropx    = null;
              $cropy    = null;
              $croppos  = null;
              if($this->_config->get('jg_dyncrop'))
              {
                $cropx    = $this->_config->get('jg_dyncropwidth');
                $cropy    = $this->_config->get('jg_dyncropheight');
                $croppos  = $this->_config->get('jg_dyncropposition');
              }
              $categories[$key]->thumb_src = $this->_ambit->getImg('thumb_url', $row, null, 0, true, $cropx, $cropy, $croppos);
              // Store the image id for later skipping the category view
              if(isset($row->catid) && $categories[$key]->cid == $row->catid)
              {
                $categories[$key]->dtlimgid = $row->id;
              }
            }
          }
        }
        else
        {
          // Check if there's a category thumbnail selected
          // 'isset' checks whether it is a valid image which we are allowed to display
          if($categories[$key]->thumbnail && isset($categories[$key]->id))
          {
            $cropx    = null;
            $cropy    = null;
            $croppos  = null;
            if($this->_config->get('jg_dyncrop'))
            {
              $cropx    = $this->_config->get('jg_dyncropwidth');
              $cropy    = $this->_config->get('jg_dyncropheight');
              $croppos  = $this->_config->get('jg_dyncropposition');
            }
            $categories[$key]->thumb_src = $this->_ambit->getImg('thumb_url', $categories[$key], null, 0, true, $cropx, $cropy, $croppos);

            if(!$categories[$key]->imghidden && isset($categories[$key]->catid) && $categories[$key]->cid == $categories[$key]->catid)
            {
              // Store the image id for later skipping the category view
              $categories[$key]->dtlimgid = $categories[$key]->id;
            }

            // Own choice of alignment
            switch($categories[$key]->img_position)
            {
              // Left
              case 0:
                $categories[$key]->photocontainer = 'jg_subcatelem_photo_l';
                $categories[$key]->textcontainer  = 'jg_subcatelem_txt_l';
                break;
              // Right
              case 1:
                $categories[$key]->gallerycontainer = 'jg_subcatelem_cat_r';
                $categories[$key]->photocontainer   = 'jg_subcatelem_photo_r';
                $categories[$key]->textcontainer    = 'jg_subcatelem_txt_r';
                break;
              // Centered
              case 2:
                $categories[$key]->photocontainer = 'jg_subcatelem_photo_c';
                $categories[$key]->textcontainer  = 'jg_subcatelem_txt_c';
                break;
              default:
                // Use global settings: The default classes are used
                if($this->_config->get('jg_subcatthumbalign') != 1)
                {
                  $gallerycontainer = 'jg_subcatelem_cat_r';
                }
                break;
            }
          }
        }
      }

      // Set the href url for the <a>-Tag, dependent on setting in
      // jg_skipcatview
      // link to category view or directly to detail view if the category
      // doesn't contain other categories
      if($this->_config->get('jg_skipcatview'))
      {
        // Get subcategories from category
        $allsubcats = JoomHelper::getAllSubCategories($categories[$key]->cid, false);

        // Link to category view if there are any viewable subcategories
        // otherwise to detail view
        if(count($allsubcats))
        {
          $categories[$key]->link = JRoute::_('index.php?view=category&catid='.$category->cid);
        }
        else
        {
          // Try to set link to detail view
          // get link with the help of model if
          // 1) view of thumb in configuration deactivated
          // 2) view of thumb activated but no thumb setted for category
          if(    $this->_config->get('jg_showsubthumbs') == 0
              || ($this->_config->get('jg_showsubthumbs') != 0
                 && !isset($categories[$key]->dtlimgid)
             )
            )
          {
            // Get the model
            $categoryModel = $this->getModel();

            // Get the id of image
            $image = $categoryModel->getImageCat($categories[$key]->cid);

            // Set the id of image for the link to detail view
            if(isset($image))
            {
              $categories[$key]->dtlimgid = $image;
            }
          }
          // Check the id of image setted before
          if(isset($categories[$key]->dtlimgid))
          {
            // Set link to detail view
            $categories[$key]->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_detailpic_open'), $categories[$key]->dtlimgid);

            // If category view is skipped we display the favourites icon for adding all images at the thumbnail.
            // Calculations for that have already been done for the favourites icon in the header
            $categories[$key]->show_favourites_icon = $this->params->get('show_headerfavourites_icon');
          }
          else
          {
            $categories[$key]->link = JRoute::_('index.php?view=category&catid='.$category->cid);
          }
        }
      }
      else
      {
        // Set link to category view if no skipping
        $categories[$key]->link = JRoute::_('index.php?view=category&catid='.$category->cid);
      }

      // show only intro-text in subcategories
      $categories[$key]->description = JoomHelper::getIntrotext($categories[$key]->description);

      // Icon for quick upload at sub-category thumbnail
      if(     $this->_config->get('jg_uploadiconsubcat')
          &&  (   $this->_user->authorise('joom.upload', _JOOM_OPTION.'.category.'.$category->cid)
              ||  $category->owner && $category->owner == $this->_user->get('id') && $this->_user->authorise('joom.upload.inown', _JOOM_OPTION.'.category.'.$category->cid)
              )
        )
      {
        $categories[$key]->show_upload_icon = true;
        JHtml::_('behavior.modal');
      }

      $categories[$key]->event  = new stdClass();

      // Additional HTML added by plugins
      $results  = $this->_mainframe->triggerEvent('onJoomAfterDisplayCatThumb', array($category->cid));
      $categories[$key]->event->afterDisplayCatThumb  = trim(implode('', $results));

      /*// Additional icons added by plugins
      $results  = $this->_mainframe->triggerEvent('onJoomDisplayIcons', array('category.category', $category));
      $categories[$key]->event->icons                 = trim(implode('', $results));*/
    }

    // Download icon
    if($this->_config->get('jg_download') && $this->_config->get('jg_showcategorydownload'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_download_unreg'))
      {
        $this->params->set('show_download_icon', 1);
      }
      else
      {
        if($this->_config->get('jg_download_hint'))
        {
          $this->params->set('show_download_icon', -1);
        }
      }
    }

    // Favourites icon
    if(!$this->params->get('disable_global_info') && $this->_config->get('jg_favourites') && $this->_config->get('jg_showcategoryfavourite'))
    {
      if(   $this->_user->get('id')
         || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
        )
      {
        if(    $this->_config->get('jg_usefavouritesforzip')
           || ($this->_config->get('jg_usefavouritesforpubliczip') && !$this->_user->get('id'))
          )
        {
          $this->params->set('show_favourites_icon', 2);
        }
        else
        {
          $this->params->set('show_favourites_icon', 1);
        }
      }
      else
      {
        if(($this->_config->get('jg_favouritesshownotauth') == 1))
        {
          if($this->_config->get('jg_usefavouritesforzip'))
          {
            $this->params->set('show_favourites_icon', -2);
          }
          else
          {
            $this->params->set('show_favourites_icon', -1);
          }
        }
      }
    }

    // Report icon
    if($this->_config->get('jg_report_images') && $this->_config->get('jg_category_report_images'))
    {
      if($this->_user->get('id') || $this->_config->get('jg_report_unreg'))
      {
        $this->params->set('show_report_icon', 1);

        JHTML::_('behavior.modal');
      }
      else
      {
        if($this->_config->get('jg_report_hint'))
        {
          $this->params->set('show_report_icon', -1);
        }
      }
    }

    // Display Full text of category
    $cat->description = JoomHelper::getFulltext($cat->description);

    foreach($images as $key => $image)
    {
      $cropx    = null;
      $cropy    = null;
      $croppos  = null;
      if($this->_config->get('jg_dyncrop'))
      {
        $cropx    = $this->_config->get('jg_dyncropwidth');
        $cropy    = $this->_config->get('jg_dyncropheight');
        $croppos  = $this->_config->get('jg_dyncropposition');
        $images[$key]->imgwh = 'width="'.$cropx.'" height="'.$cropy.'"';
      }
      else
      {
        // Get dimensions for width and height attribute in img tag
        $imgwh  = getimagesize($this->_ambit->getImg('thumb_path', $image));
        $images[$key]->imgwh = $imgwh[3];
      }
      $images[$key]->thumb_src = $this->_ambit->getImg('thumb_url', $image, null, 0, true, $cropx, $cropy, $croppos);

      if($this->_config->get('jg_showpicasnew'))
      {
        $images[$key]->isnew = JoomHelper::checkNew($image->imgdate, $this->_config->get('jg_daysnew'));
      }

      $images[$key]->link = JHTML::_('joomgallery.openimage', $this->_config->get('jg_detailpic_open'), $image);

      if($this->_config->get('jg_showauthor'))
      {
        if($image->imgauthor)
        {
          $images[$key]->authorowner = $image->imgauthor;
        }
        else
        {
          if($this->_config->get('jg_showowner'))
          {
            $images[$key]->authorowner = JHTML::_('joomgallery.displayname', $image->owner);
          }
          else
          {
            $images[$key]->authorowner = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
          }
        }
      }

      // Show editor links for that image
      $images[$key]->show_edit_icon   = false;
      $images[$key]->show_delete_icon = false;
      if(   $this->_config->get('jg_showcategoryeditorlinks') == 1
         && $this->_config->get('jg_userspace') == 1
        )
      {
        if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$images[$key]->id)
            ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$images[$key]->id)
                &&  $images[$key]->owner
                &&  $images[$key]->owner == $this->_user->get('id')
                )
            )
        )
        {
          $images[$key]->show_edit_icon = true;
        }

        if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$images[$key]->id))
        {
          $images[$key]->show_delete_icon = true;
        }
      }

      // Set the title attribute in a tag with title and/or description of image
      // if a box is activated
      if(!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 1)
      {
        $images[$key]->atagtitle =  JHTML::_('joomgallery.getTitleforATag', $images[$key]);
      }
      else
      {
        // Set the imgtitle by default
        $images[$key]->atagtitle = 'title="'.$images[$key]->imgtitle.'"';
      }

      // show only intro-text of images
      $images[$key]->imgtext = JoomHelper::getIntrotext($images[$key]->imgtext);

      $images[$key]->event  = new stdClass();

      // Additional HTML added by plugins
      $results  = $this->_mainframe->triggerEvent('onJoomAfterDisplayThumb', array($image->id));
      $images[$key]->event->afterDisplayThumb = trim(implode('', $results));

      // Additional icons added by plugins
      $results  = $this->_mainframe->triggerEvent('onJoomDisplayIcons', array('category.image', $image));
      $images[$key]->event->icons             = trim(implode('', $results));

      // Check if there are any elements beside the image to be shown
      // if not deactivate the output of corresponding html tags in template
      // to avoid empty div/ul/li
      if(    !$this->_config->get('jg_showtitle')
          && !$this->_config->get('jg_showpicasnew')
          && !$this->_config->get('jg_showhits')
          && !$this->_config->get('jg_showdownloads')
          && !$this->_config->get('jg_showauthor')
          && !$this->_config->get('jg_showcatcom')
          && !$this->_config->get('jg_showcatrate')
          && (   !$this->_config->get('jg_showcatdescription')
               || (    $this->_config->get('jg_showcatdescription')
                    && !$images[$key]->imgtext
                  )
             )
          && !$this->params->get('show_download_icon')
          && !$this->params->get('show_favourites_icon')
          && !$this->params->get('show_report_icon')
          && (   !$this->_config->get('jg_showcategoryeditorlinks')
               || (    $this->_config->get('jg_showcategoryeditorlinks')
                    && !$images[$key]->show_delete_icon
                    && !$images[$key]->show_edit_icon
                  )
             )
          && !$images[$key]->event->afterDisplayThumb
          && !$images[$key]->event->icons
        )
      {
        $images[$key]->show_elems = false;
      }
      else
      {
        $images[$key]->show_elems = true;
      }
    }

    if($this->_config->get('jg_usercatorder') && count($images))
    {
      $this->order_by   = $this->_mainframe->getUserStateFromRequest('joom.category.images.orderby', 'orderby');
      $this->order_dir  = $this->_mainframe->getUserStateFromRequest('joom.category.images.orderdir', 'orderdir');

      // If subcategory navigation active insert current subcategory startpage
      if($this->catpage > 1)
      {
        $this->sort_url = JRoute::_('index.php?view=category&catid='.$cat->cid.'&catpage='.$this->catpage).JHTML::_('joomgallery.anchor', 'category');
      }
      else
      {
        $this->sort_url = JRoute::_('index.php?view=category&catid='.$cat->cid).JHTML::_('joomgallery.anchor', 'category');
      }
    }

    // Set redirect url used in editor links to redirect back to favourites view after edit/delete
    $this->redirect = '&redirect='.base64_encode(JFactory::getURI()->toString());

    $this->category   = &$cat;
    $this->images     = &$images;
    $this->categories = &$categories;

    parent::display($tpl);
  }
}