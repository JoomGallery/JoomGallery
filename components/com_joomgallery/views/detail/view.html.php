<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/detail/view.html.php $
// $Id: view.html.php 4404 2014-06-26 21:23:58Z chraneco $
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
 * HTML View class for the detail view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewDetail extends JoomGalleryView
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
    if(!$this->_user->get('id') && !$this->_config->get('jg_showdetailpage'))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false),
                                  JText::_('COM_JOOMGALLERY_COMMON_MSG_NOT_ALLOWED_VIEW_IMAGE'), 'notice');
    }

    if((!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') > 0) && $this->_config->get('jg_disabledetailpage'))
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false),
                                  JText::_('COM_JOOMGALLERY_DETAIL_MSG_NOT_ALLOWED_VIEW_DEFAULT_DETAIL_VIEW'), 'notice');
    }

    $this->images     = $this->get('Images');
    $this->image      = $this->get('Image');
    $this->slideshow  = $this->_mainframe->input->getInt('slideshow');
    $this->params     = $this->_mainframe->getParams();

    // Breadcrumbs
    if(     $this->_config->get('jg_completebreadcrumbs')
        ||  $this->_config->get('jg_showpathway')
        ||  $this->_config->get('jg_pagetitle_detail')
      )
    {
      $parents  = JoomHelper::getAllParentCategories($this->image->catid, true);
    }

    $menus = $this->_mainframe->getMenu();
    $menu  = $menus->getActive();
    if($menu && isset($menu->query['view'])
       && $menu->query['view'] != 'detail'
       && $this->_config->get('jg_completebreadcrumbs'))
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

          $breadcrumbs->addItem($this->image->imgtitle);
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
            $breadcrumbs->addItem($this->image->imgtitle);
          }
          break;
      }
    }

    // JoomGallery Pathway
    $this->pathway = null;
    if($this->_config->get('jg_showpathway'))
    {
      $this->pathway = '<a href="'.JRoute::_('index.php?view=gallery').'" class="jg_pathitem">'.JText::_('COM_JOOMGALLERY_COMMON_HOME').'</a> &raquo; ';

      foreach($parents as $parent)
      {
        $this->pathway .= '<a href="'.JRoute::_('index.php?view=category&catid='.$parent->cid).'" class="jg_pathitem">'.$parent->name.'</a> &raquo; ';
      }

      $this->pathway .= $this->image->imgtitle;
    }

    // Page Title
    if($this->_config->get('jg_pagetitle_detail'))
    {
      $pagetitle  = JoomHelper::createPagetitle($this->_config->get('jg_pagetitle_detail'),
                                                $parents[$this->image->catid]->name,
                                                $this->image->imgtitle,
                                                $this->params->get('page_title') ? $this->params->get('page_title') : JText::_('COM_JOOMGALLERY_COMMON_GALLERY')
                                              );
      $this->_doc->setTitle($pagetitle);
    }

    // Header and footer
    JoomHelper::prepareParams($this->params);

    // Generate the backlink
    if($this->_config->get('jg_skipcatview'))
    {
      $allsubcats = JoomHelper::getAllSubCategories($this->image->catid, false);
      // Link to category view if it include subcategories
      if(count($allsubcats))
      {
        $this->backtarget = JRoute::_('index.php?view=category&catid='.$this->image->catid);
        $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_CATEGORY');
      }
      else
      {
        // Get the parents including category itself if not read before
        if(!isset($parents))
        {
          $parents  = JoomHelper::getAllParentCategories($this->image->catid, true);
        }
        if(count($parents) == 1)
        {
          // Link to gallery view
          $this->backtarget = JRoute::_('index.php?view=gallery');
          $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_GALLERY');
        }
        else
        {
          // Link to parent of category
          $this->backtarget = JRoute::_('index.php?view=category&catid='.$parents[$this->image->catid]->parent_id);
          $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_CATEGORY');
        }
      }
    }
    else
    {
      $this->backtarget = JRoute::_('index.php?view=category&catid='.$this->image->catid);
      $this->backtext   = JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_CATEGORY');
    }

    // Get number of images and hits in gallery
    $numbers            = JoomHelper::getNumberOfImgHits();
    $this->numberofpics = $numbers[0];
    $this->numberofhits = $numbers[1];

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
    // Load modules at position 'detailbtm'
    $this->modules['detailbtm'] = JoomHelper::getRenderedModules('detailbtm');
    if(count($this->modules['detailbtm']))
    {
      $this->params->set('show_detailbtm_modules', 1);
    }

    // Check whether this is the active menu item. This is a
    // special case in addition to code in constructor of parent class
    // because here we have to check the image ID, too.
    $active = $this->_mainframe->getMenu()->getActive();
    if(!$active || strpos($active->link, '&id='.$this->_mainframe->input->getInt('id')) === false)
    {
      // Get the default layout from the configuration
      if($layout = $this->_config->get('jg_alternative_layout'))
      {
        $this->setLayout($layout);
      }
    }

    // Meta data
    if($this->image->metadesc)
    {
      $this->_doc->setDescription($this->image->metadesc);
    }
    elseif($this->image->catmetadesc)
    {
      $this->_doc->setDescription($this->image->catmetadesc);
    }
    if($this->image->metakey)
    {
      $this->_doc->setMetadata('keywords', $this->image->metakey);
    }
    elseif($this->image->catmetakey)
    {
      $this->_doc->setMetadata('keywords', $this->image->catmetakey);
    }
    if($this->_mainframe->getCfg('MetaAuthor') == '1' && $this->image->author && strcmp(JText::_('COM_JOOMGALLERY_COMMON_NO_DATA'), $this->image->author) != 0)
    {
      $this->_doc->setMetaData('author', $this->image->author);
    }

    // Show fulltext of description
    $this->image->imgtext = JoomHelper::getFulltext($this->image->imgtext);

    // Set the title attribute in a tag with title and/or description of image
    // if a box is activated
    if(    (!is_numeric($this->_config->get('jg_bigpic_open')) || $this->_config->get('jg_bigpic_open') > 1)
        && !$this->slideshow
      )
    {
      $this->image->atagtitle =  JHTML::_('joomgallery.getTitleforATag', $this->image);
    }
    else
    {
      // Set the imgtitle by default
      $this->image->atagtitle = 'title="'.$this->image->imgtitle.'"';
    }

    // Accordion
    if($this->_config->get('jg_showdetailaccordion'))
    {
      $this->toggler = 'class="joomgallery-toggler"';
      $this->slider  = 'class="joomgallery-slider"';
      JHtml::_('behavior.framework', true);
      $accordionscript= 'window.addEvent(\'domready\', function(){
        new Fx.Accordion
        (
          $$(\'h4.joomgallery-toggler\'),
          $$(\'div.joomgallery-slider\'),
          {
            onActive: function(toggler, i)
            {
              toggler.addClass(\'joomgallery-toggler-down\');
              toggler.removeClass(\'joomgallery-toggler\');
            },
            onBackground: function(toggler, i)
            {
              toggler.addClass(\'joomgallery-toggler\');
              toggler.removeClass(\'joomgallery-toggler-down\');
            },
            duration         : '.$this->_config->get('jg_accordionduration').',
            display          : '.($this->_config->get('jg_accordiondisplay')-1) .',
            initialDisplayFx : '.$this->_config->get('jg_accordioninitialeffect').',
            opacity          : '.$this->_config->get('jg_accordionopacity').',
            alwaysHide       : '.$this->_config->get('jg_accordionalwayshide').'
           });
        });';
      $this->_doc->addScriptDeclaration($accordionscript);
    }
    else
    {
      $this->toggler = '';
      $this->slider  = '';
    }

    // Linked
    if( (     ($this->_config->get('jg_bigpic') == 1 && $this->_user->get('id'))
          ||  ($this->_config->get('jg_bigpic_unreg') == 1 && !$this->_user->get('id'))
        )
        && !$this->slideshow
        && $this->image->bigger_orig
        &&
        (     !$this->_config->get('jg_nameshields')
          || (!$this->_config->get('jg_show_nameshields_unreg') && !$this->_user->get('id'))
        )
      )
    {
      $this->params->set('image_linked', 1);
    }

    // Original size
    if(     $this->image->orig_exists
        &&  $this->_config->get('jg_showoriginalfilesize')
        && !$this->slideshow
      )
    {
      $this->params->set('show_original_size', 1);
    }

    // Pagination
    if(isset($this->images[$this->image->position-1]) && !$this->slideshow)
    {
      $this->params->set('show_previous_link', 1);
      $this->pagination['previous']['link'] = JRoute::_('index.php?view=detail&id='.$this->images[$this->image->position-1]->id).JHTML::_('joomgallery.anchor');
      if($this->_config->get('jg_showdetailnumberofpics'))
      {
        $this->params->set('show_previous_text', 1);
        $this->pagination['previous']['text'] = JText::sprintf('COM_JOOMGALLERY_DETAIL_IMG_IMAGE_OF_IMAGES', $this->image->position, count($this->images));
      }
    }
    if(isset($this->images[$this->image->position+1]) && !$this->slideshow)
    {
      $this->params->set('show_next_link', 1);
      $this->pagination['next']['link'] = JRoute::_('index.php?view=detail&id='.$this->images[$this->image->position+1]->id).JHTML::_('joomgallery.anchor');
      if($this->_config->get('jg_showdetailnumberofpics'))
      {
        $this->params->set('show_next_text', 1);
        $this->pagination['next']['text'] = JText::sprintf('COM_JOOMGALLERY_DETAIL_IMG_IMAGE_OF_IMAGES', $this->image->position+2, count($this->images));
      }
    }

    // Nametags
    if(     !$this->slideshow
        &&  ( ($this->_config->get('jg_nameshields') && $this->_user->get('id'))
          ||  ($this->_config->get('jg_nameshields_unreg') && !$this->_user->get('id'))
            )
      )
    {
      $this->nametags = $this->get('Nametags');

      if($this->_user->get('id') || $this->nametags)
      {
        $this->params->set('show_nametags', 1);
      }

      $already_tagged = false;
      foreach($this->nametags as $this->nametag)
      {
        if($this->nametag->nuserid == $this->_user->get('id'))
        {
          $already_tagged = true;
          break;
        }
      }

      if(     $this->_config->get('jg_nameshields')
          &&  $this->_user->get('id')
          && !$this->slideshow
          && (!$already_tagged || $this->_config->get('jg_nameshields_others'))
        )
      {
        $this->params->set('show_movable_nametag', 1);

        $length                   = strlen($this->_user->get('username')) * $this->_config->get('jg_nameshields_width');
        $this->nametag            = array();
        $this->nametag['length']  = $length;
        $this->nametag['name']    = $this->_user->get('username');
        $this->nametag['link']    = JRoute::_('index.php?task=nametags.save');

        JHtml::_('behavior.framework');
        if($this->_config->get('jg_nameshields_others'))
        {
          JHTML::_('behavior.modal');
        }
      }
    }

    $script = '';

    // Slideshow
    if($this->_config->get('jg_slideshow'))
    {
      $this->params->set('slideshow_enabled', 1);

      if($this->slideshow)
      {
        JHtml::_('behavior.framework', true);
        $this->_doc->addStyleSheet($this->_ambit->getScript('smoothgallery/css/jd.gallery.css'));
        $this->_doc->addScript($this->_ambit->getScript('smoothgallery/scripts/jd.gallery.js'));

        // No include if standard effects 'fade/crossfade/fadebg' chosen

        switch ($this->_config->get('jg_slideshow_transition'))
        {
          case 0:
            $transition = 'fade';
            break;
          case 1:
            $transition = 'fadeslideleft';
            $this->_doc->addScript($this->_ambit->getScript('smoothgallery/scripts/jd.gallery.transitions.js'));
            break;
          case 2:
            $transition = 'crossfade';
            break;
          case 3:
            $transition = 'continuoushorizontal';
            $this->_doc->addScript($this->_ambit->getScript('smoothgallery/scripts/jd.gallery.transitions.js'));
            break;
          case 4:
            $transition = 'continuousvertical';
            $this->_doc->addScript($this->_ambit->getScript('smoothgallery/scripts/jd.gallery.transitions.js'));
            break;
          case 5:
            $transition = 'fadebg';
            break;
          default:
            $transition = 'fade';
            break;
        }

        // The slideshow needs an array of objects
        $script .= 'var photo = new Array();
                  function joom_createphotoobject(image,thumbnail,linkTitle,link,title,description,number,date,hits,downloads,rating,filesizedtl,filesizeorg,author,detaillink) {
                    this.image = image;
                    this.thumbnail = thumbnail;
                    this.linkTitle = linkTitle;
                    this.link =link;
                    this.title = title;
                    this.description = description;
                    this.transition="'.$transition.'";
                    this.number=number;
                    this.date=date,
                    this.hits=hits,
                    this.downloads=downloads,
                    this.rating=rating,
                    this.filesizedtl=filesizedtl,
                    this.filesizeorg=filesizeorg,
                    this.author=author,
                    this.detaillink=detaillink
                  }';
        $number      = 0;
        $maxwidth    = 0;
        $maxheight   = 0;
        $imgstartidx = 0;
        foreach($this->images as $row)
        {
          // Description
          if($row->imgtext != '')
          {
            $description = JoomHelper::fixForJS(JoomHelper::getFulltext($row->imgtext));
          }
          else
          {
            $description = '&nbsp;';
          }
          // Date
          if($row->imgdate != '')
          {
            $date = JHTML::_('date', $row->imgdate, JText::_('DATE_FORMAT_LC1'));
          }
          else
          {
            $date = '';
          }
          // Rating
          $rating = addslashes(JHTML::_('joomgallery.rating', $row, true, 'jg_starrating_detail'));
          // File size of detail image
          if($this->_config->get('jg_showdetailfilesize'))
          {
            $filesizedtlhw        = @filesize($this->_ambit->getImg('img_path', $row));
            $filesizedtlhw        = number_format($filesizedtlhw/1024, 2, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));
            list($width, $height) = @getimagesize($this->_ambit->getImg('img_path', $row));

            $filesizedtl = JText::sprintf('COM_JOOMGALLERY_COMMON_IMG_HW',
                                           $filesizedtlhw,
                                           $width,
                                           $height
                                         );
          }
          else
          {
            $filesizedtl  = '&nbsp;';
          }
          // File size of original image
          if($this->_config->get('jg_showoriginalfilesize'))
          {
            $filesizeorghw        = @filesize($this->_ambit->getImg('orig_path', $row));
            $filesizeorghw        = number_format($filesizeorghw/1024, 2, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR'));
            list($width, $height) = @getimagesize($this->_ambit->getImg('orig_path', $row));

            $filesizeorg = JText::sprintf('COM_JOOMGALLERY_COMMON_IMG_HW',
                                           $filesizeorghw,
                                           $width,
                                           $height
                                         );
          }
          else
          {
            $filesizeorg  = '&nbsp;';
          }

          // Author-owner
          if($this->_config->get('jg_showdetailauthor'))
          {
            if($row->imgauthor)
            {
              $author = $row->imgauthor;
            }
            else
            {
              if($this->_config->get('jg_showowner'))
              {
                $author = JHTML::_('joomgallery.displayname', $row->imgowner, 'detail');
              }
              else
              {
                $author = JText::_('COM_JOOMGALLERY_COMMON_NO_DATA');
              }
            }
          }
          else
          {
            $author = '';
          }

          if ($this->_config->get('jg_slideshow_maxdimauto'))
          {
            // Get dimensions of image for calculating the max. width/height
            // of all images
            $dimensions = getimagesize($this->_ambit->getImg('img_path', $row));
            if($dimensions[0] > $maxwidth)
            {
              $maxwidth   = $dimensions[0];
            }
            if($dimensions[1] > $maxheight)
            {
              $maxheight  = $dimensions[1];
            }
          }

          $script .= '
            photo['.$number.'] = new joom_createphotoobject(
            "'.str_replace('&amp;', '&', $this->_ambit->getImg('img_url', $row)).'",//image
            "'.$this->_ambit->getImg('thumb_url', $row).'",//thumbnail
            "'.JoomHelper::fixForJS($row->imgtitle).'",//linkTitle
            "'.str_replace('&amp;', '&', $this->_ambit->getImg('img_url', $row)).'",//link
            "'.JoomHelper::fixForJS($row->imgtitle).'",//title
            "'.$description.'",
            '.$number.',
            "'.$date.'",
            "'.$row->hits.'",
            "'.$row->downloads.'",
            "'.$rating.'",
            "'.$filesizedtl.'",
            "'.$filesizeorg.'",
            "'.str_replace(array("\r\n", "\r", "\n"), '', addcslashes($author, '"')).'",
            "'.JHTML::_('joomgallery.openimage', 0, $row).'"
          );';
          // set start image index for slideshow
          if($row->id == $this->image->id)
          {
            $imgstartidx = $number;
          }
          $number++;
        }
        if (!$this->_config->get('jg_slideshow_maxdimauto'))
        {
          $maxwidth   =$this->_config->get('jg_slideshow_width');
          $maxheight  =$this->_config->get('jg_slideshow_heigth');
        }
        $script .= 'var joom_slideshow=null;
                    function startGallery() {
                        joom_slideshow = new gallery($(\'jg_dtl_photo\'), {
                        timed: true,
                        delay: '.$this->_config->get('jg_slideshow_timer').',
                        fadeDuration: '.$this->_config->get('jg_slideshow_transtime').',
                        showArrows: '.$this->_config->get('jg_slideshow_arrows').',
                        showCarousel: '.$this->_config->get('jg_slideshow_carousel').',
                        textShowCarousel: \''.JText::_('COM_JOOMGALLERY_DETAIL_SLIDESHOW_IMAGES', true).'\',
                        showInfopane: '.$this->_config->get('jg_slideshow_infopane').',
                        embedLinks: false,
                        manualData:photo,
                        preloader:false,
                        populateData:false,
                        maxWidth:'.$maxwidth.',
                        maxHeight:'.$maxheight.',
                        imgstartidx:'.$imgstartidx.',
                        repeat: '.$this->_config->get('jg_slideshow_repeat').',
                        repeattxt: \''.JText::_('COM_JOOMGALLERY_DETAIL_SLIDESHOW_REPEAT', true).'\'
                     });
                   }
                   window.addEvent(\'domready\', startGallery);
                   function joom_stopslideshow() {
                     var url = photo[joom_slideshow.getCurrentIter()].detaillink + \''.JHTML::_('joomgallery.anchor').'\';
                     location.href = url.replace(/\&amp;/g,\'&\');
                   }
        ';
      }
      else
      {
        $script .= "function joom_startslideshow() {\n"
                .  "  document.jg_slideshow_form.submit();\n"
                .  "}\n";
      }
    }

    // Rightclick / Cursor navigation
    if($this->_config->get('jg_disable_rightclick_detail'))
    {
      $script .= '
    var jg_photo_hover = 0;
    document.oncontextmenu = function() {
      if(jg_photo_hover==1) {
        return false;
      } else {
        return true;
      }
    }
    function joom_hover() {
      jg_photo_hover = (jg_photo_hover==1) ? 0 : 1;
    }';

    }

    if($this->_config->get('jg_cursor_navigation') == 1)
    {
      $script .= 'document.onkeydown = joom_cursorchange;';
    }

    if($script)
    {
      $this->_doc->addScriptDeclaration($script);
    }

    // MotionGallery
    if($this->_config->get('jg_minis') && $this->_config->get('jg_motionminis') == 2)
    {
      JHtml::_('jquery.framework');
      $this->_doc->addStyleSheet($this->_ambit->getScript('motiongallery/css/jquery.mThumbnailScroller.css'));
      $this->_doc->addScript($this->_ambit->getScript('motiongallery/js/jquery.mThumbnailScroller'.(JFactory::getConfig()->get('debug') ? '' : '.min').'.js'));
    }

    // Icons
    if(!$this->slideshow)
    {
      // Zoom
      if($this->image->bigger_orig)
      {
        if(    ($this->_config->get('jg_bigpic') == 1 && $this->_user->get('id'))
            || ($this->_config->get('jg_bigpic_unreg') == 1 && !$this->_user->get('id'))
          )
        {
          $this->params->set('show_zoom_icon', 1);
        }
        else
        {
          if($this->_config->get('jg_bigpic') == 1 && !$this->_user->get('id'))
          {
            $this->params->set('show_zoom_icon', -1);
          }
        }
      }

      // Download icon
      if(   $this->_config->get('jg_download')
        &&  $this->_config->get('jg_showdetaildownload')
        &&  ($this->image->orig_exists || $this->_config->get('jg_downloadfile') != 1)
        )
      {
        if($this->_user->get('id') || $this->_config->get('jg_download_unreg'))
        {
          $this->params->set('show_download_icon', 1);
          $this->params->set('download_link', JRoute::_('index.php?task=download&id='.$this->image->id));
        }
        else
        {
          if($this->_config->get('jg_download_hint'))
          {
            $this->params->set('show_download_icon', -1);
          }
        }
      }

      // Nametags
      if($this->_config->get('jg_nameshields') && $this->_user->get('id'))
      {
        if(!$this->_config->get('jg_nameshields_others'))
        {
          if(!$already_tagged)
          {
            $this->params->set('show_nametag_icon', 1);
          }
          else
          {
            $this->params->set('show_nametag_icon', 2);
            $this->params->set('nametag_link', JRoute::_('index.php?task=nametags.remove&id='.$this->image->id, false));
          }
        }
        else
        {
          $this->params->set('show_nametag_icon', 3);
        }
      }
      else
      {
        if(    $this->_config->get('jg_nameshields')
            && !$this->_user->get('id')
            && $this->_config->get('jg_show_nameshields_unreg')
          )
        {
          $this->params->set('show_nametag_icon', -1);
        }
      }

      // Favourites
      if(!$this->params->get('disable_global_info') && $this->_config->get('jg_favourites'))
      {
        if(   $this->_user->get('id')
           || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
          )
        {
          $this->params->set('favourites_link', JRoute::_('index.php?task=favourites.addimage&id='.$this->image->id));
          if(     $this->_config->get('jg_usefavouritesforzip') == 1
              || ($this->_config->get('jg_usefavouritesforpubliczip') == 1 && !$this->_user->get('id'))
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
          if($this->_config->get('jg_favouritesshownotauth') == 1)
          {
            if($this->_config->get('jg_usefavouritesforzip') == 1)
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

      // Report
      if($this->_config->get('jg_report_images') && $this->_config->get('jg_detail_report_images'))
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

      // Show editor links for that image
      if(   $this->_config->get('jg_showdetaileditorlinks') == 1
         && $this->_config->get('jg_userspace') == 1
        )
      {
        if( (   $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$this->image->id)
            ||  (   $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$this->image->id)
                &&  $this->image->imgowner
                &&  $this->image->imgowner == $this->_user->get('id')
                )
            )
        )
        {
          $this->params->set('show_edit_icon', 1);
        }

        if($this->_user->authorise('core.delete', _JOOM_OPTION.'.image.'.$this->image->id))
        {
          $this->params->set('show_delete_icon', 1);
        }
      }
    }

    $this->extra = '';
    if($this->_config->get('jg_disable_rightclick_detail') == 1)
    {
      $this->extra = 'onmouseover="javascript:joom_hover();" onmouseout="javascript:joom_hover();"';
    }

    $this->event = new stdClass();

    if(!$this->slideshow)
    {
      if($this->_config->get('jg_lightbox_slide_all'))
      {
        $this->params->set('show_all_in_popup', 1);

        $this->popup = array();

        $this->popup['before']  = JHTML::_('joomgallery.popup', $this->images, 0, $this->image->position, $this->params->get('image_linked') ? 'joomgalleryIcon' : null);
        $this->popup['after']   = JHTML::_('joomgallery.popup', $this->images, $this->image->position + 1, null, $this->params->get('image_linked') ? 'joomgalleryIcon' : null);
      }

      // Pane
      // Load modules at position 'detailpane'
      $this->modules['detailpane'] = JoomHelper::getRenderedModules('detailpane');
      if(count($this->modules['detailpane']))
      {
        $this->params->set('show_detailpane_modules', 1);
      }

      // Exif data
      if(    $this->_config->get('jg_showexifdata')
          && $this->image->orig_exists
          && extension_loaded('exif')
          && function_exists('exif_read_data')
        )
      {
        $exifdata = $this->get('Exifdata');
        if($exifdata)
        {
          $this->params->set('show_exifdata', 1);
          $this->exifdata = &$exifdata;
        }
      }

      // GeoTagging data
      if(    $this->_config->get('jg_showgeotagging')
          && $this->image->orig_exists
          && extension_loaded('exif')
          && function_exists('exif_read_data')
        )
      {
        $mapdata_array = $this->get('Mapdata');
        if($mapdata_array)
        {
          $mapdata = '';

          if(isset($mapdata_array['N']))
          {
            $mapdata .= $mapdata_array['N'];
          }
          else
          {
            if(isset($mapdata_array['S']))
            {
              $mapdata .= '-'.$mapdata_array['S'];
            }
          }

          $mapdata .= ', ';

          if(isset($mapdata_array['E']))
          {
            $mapdata .= $mapdata_array['E'];
          }
          else
          {
            if(isset($mapdata_array['W']))
            {
              $mapdata .= '-'.$mapdata_array['W'];
            }
          }

          if($mapdata)
          {
            $this->params->set('show_map', 1);
            $this->mapdata = $mapdata;

            $apikey = $this->_config->get('jg_geotaggingkey');
            $this->_doc->addScript('http'.(JUri::getInstance()->isSSL() ? 's' : '').'://maps.google.com/maps/api/js?sensor=false'.(!empty($apikey) ? '&amp;key='.$apikey : ''));

            JText::script('COM_JOOMGALLERY_DETAIL_MAPS_BROWSER_IS_INCOMPATIBLE');
          }
        }
      }

      // IPTC data
      if(    $this->_config->get('jg_showiptcdata')
          && $this->image->orig_exists
        )
      {
        $iptcdata = $this->get('Iptcdata');
        if($iptcdata)
        {
          $this->params->set('show_iptcdata', 1);
          $this->iptcdata = $iptcdata;
        }
      }

      // Rating
      if($this->_config->get('jg_showrating'))
      {
        if($this->_config->get('jg_votingonlyreg') && !$this->_user->get('id'))
        {
          // Set voting_area to 3 to show only the message in template
          $this->params->set('show_voting_area', 3);
          $this->params->set('voting_message', JText::_('COM_JOOMGALLERY_DETAIL_LOGIN_FIRST'));
        }
        else
        {
          if($this->_config->get('jg_votingonlyreg') && $this->image->owner == $this->_user->get('id'))
          {
            // Set voting_area to 3 to show only the message in template
            $this->params->set('show_voting_area', 3);
            $this->params->set('voting_message', JText::_('COM_JOOMGALLERY_DETAIL_RATING_NOT_ON_OWN_IMAGES'));
          }
          else
          {
            // Set to 1 will show the voting area
            JHtml::_('behavior.framework');
            $this->params->set('show_voting_area', 1);
            $this->params->set('ajaxvoting', $this->_config->get('jg_ajaxrating'));
            if($this->_config->get('jg_ratingdisplaytype') == 0)
            {
              // Set to 0 will show textual voting bar with radio buttons
              $this->params->set('voting_display_type', 0);

              $selected       = floor($this->_config->get('jg_maxvoting') / 2) + 1;
              $this->voting   = '';

              $options = array();
              for($i = 1; $i <= $this->_config->get('jg_maxvoting'); $i++)
              {
                $options[] = JHTML::_('select.option', $i);
                // Delete options text manually, because it defaults to the value in JHTML::_('select.option'... ) if left empty
                $options[$i - 1]->text = '';
              }
              $this->voting .= JHTML::_('select.radiolist', $options, 'imgvote', null, 'value', 'text', $selected);

              $this->maxvoting = $i - 1;
            }
            else if($this->_config->get('jg_ratingdisplaytype') == 1)
            {
              // Set to 1 will show graphical voting bar with stars
              $this->params->set('voting_display_type', 1);

              $this->maxvoting = $this->_config->get('jg_maxvoting');
            }
          }
        }
      }

      if($this->_config->get('jg_bbcodelink'))
      {
        $current_uri    = JURI::getInstance(JURI::base());
        $current_host   = $current_uri->getHost();
        $current_scheme = $current_uri->getScheme();

        $this->params->set('show_bbcode', 1);

        if(    $this->_config->get('jg_bbcodelink') == 1
            || $this->_config->get('jg_bbcodelink') == 3
          )
        {
          // Ensure that the correct host and path is prepended
          $uri = JFactory::getUri($this->image->img_src);
          $uri->setScheme($current_scheme);
          $uri->setHost($current_host);
          $this->params->set('bbcode_img', str_replace('&', '&amp;', $uri->toString()));
        }

        if(    $this->_config->get('jg_bbcodelink') == 2
            || $this->_config->get('jg_bbcodelink') == 3
          )
        {
          $url = JRoute::_('index.php?view=detail&id='.$this->image->id).JHTML::_('joomgallery.anchor');

          // Ensure that the correct host and path is prepended
          $uri = JFactory::getUri($url);
          $uri->setScheme($current_scheme);
          $uri->setHost($current_host);
          $this->params->set('bbcode_url', str_replace('&', '&amp;', $uri->toString()));
        }
      }

      if($this->_config->get('jg_showcomment'))
      {
        $this->params->set('show_comments_block', 1);

        // Check whether user is allowed to comment
        if(      $this->_config->get('jg_anoncomment')
            || (!$this->_config->get('jg_anoncomment') && $this->_user->get('id'))
          )
        {
          $this->params->set('commenting_allowed', 1);

          $plugins          = $this->_mainframe->triggerEvent('onJoomGetCaptcha');
          $this->event->captchas  = implode('', $plugins);

          $this->_doc->addScriptDeclaration('    var jg_use_code = '.$this->params->get('use_easycaptcha', 0).';');

          if($this->_config->get('jg_bbcodesupport'))
          {
            $this->params->set('bbcode_status', JText::_('COM_JOOMGALLERY_DETAIL_BBCODE_ON'));
          }
          else
          {
            $this->params->set('bbcode_status', JText::_('COM_JOOMGALLERY_DETAIL_BBCODE_OFF'));
          }

          if($this->_config->get('jg_smiliesupport'))
          {
            $this->params->set('smiley_support', 1);
            $this->smileys = JoomHelper::getSmileys();
          }

          JText::script('COM_JOOMGALLERY_DETAIL_SENDTOFRIEND_ALERT_ENTER_NAME_EMAIL');
          JText::script('COM_JOOMGALLERY_DETAIL_COMMENTS_ALERT_ENTER_COMMENT');
          JText::script('COM_JOOMGALLERY_DETAIL_COMMENTS_ALERT_ENTER_CODE');
        }

        // Check whether user is allowed to read comments
        if(     $this->_user->get('username')
           || (!$this->_user->get('username') && $this->_config->get('jg_showcommentsunreg') == 0)
          )
        {
          $this->comments = $this->get('Comments');

          if(!$this->comments)
          {
            $this->params->set('no_comments_message', JText::_('COM_JOOMGALLERY_DETAIL_COMMENTS_NOT_EXISTING'));
            if($this->params->get('commenting_allowed'))
            {
              $this->params->set('no_comments_message2', JText::_('COM_JOOMGALLERY_DETAIL_COMMENTS_WRITE_FIRST'));
            }
          }
          else
          {
            $this->params->set('show_comments', 1);

            // Manager logged?
            if(    $this->_user->authorise('core.manage', _JOOM_OPTION))
            {
              $this->params->set('manager_logged', 1);
            }

            foreach($this->comments as $key => $comment)
            {
              // Display author name or notice that the author is a guest
              if($comment->userid)
              {
                $this->comments[$key]->author = JHTML::_('joomgallery.displayname', $comment->userid, 'comment');
              }
              else
              {
                if($this->_config->get('jg_namedanoncomment'))
                {
                  if($comment->cmtname != JText::_('COM_JOOMGALLERY_COMMON_GUEST'))
                  {
                    $this->comments[$key]->author = JText::sprintf('COM_JOOMGALLERY_DETAIL_COMMENTS_GUEST_NAME', $comment->cmtname);
                  }
                  else
                  {
                    $this->comments[$key]->author = $comment->cmtname;
                  }
                }
                else
                {
                  $this->comments[$key]->author = JText::_('COM_JOOMGALLERY_COMMON_GUEST');
                }
              }

              // Process comment text
              $text     = $comment->cmttext;
              $text     = JoomHelper::processText($text);
              if($this->_config->get('jg_bbcodesupport'))
              {
                $text = JHTML::_('joomgallery.bbdecode', $text);
              }
              if($this->_config->get('jg_smiliesupport'))
              {
                $smileys = JoomHelper::getSmileys();
                foreach($smileys as $i => $sm)
                {
                  $text = str_replace($i, '<img src="'.$sm.'" border="0" alt="'.$i.'" title="'.$i.'" />', $text);
                }
              }
              $this->comments[$key]->text = $text;
            }
          }
        }
        else
        {
          $this->params->set('no_comments_message', JText::_('COM_JOOMGALLERY_DETAIL_COMMENTS_NOT_FOR_UNREG'));
        }
      }

      if($this->_config->get('jg_send2friend'))
      {
        $this->params->set('show_send2friend_block', 1);

        if($this->_user->get('id'))
        {
          $this->params->set('show_send2friend_form', 1);
        }
        else
        {
          $this->params->set('send2friend_message', JText::_('COM_JOOMGALLERY_DETAIL_LOGIN_FIRST'));
        }
      }
    }

    $icons                     = $this->_mainframe->triggerEvent('onJoomDisplayIcons', array('detail.image', $this->image));
    $this->event->icons        = implode('', $icons);
    $afterDisplay              = $this->_mainframe->triggerEvent('onJoomAfterDisplayDetailImage', array($this->image));
    $this->event->afterDisplay = implode('', $afterDisplay);

    // Set redirect url used in editor links to redirect back to favourites view after edit/delete
    $this->redirect = '&redirect='.base64_encode(JFactory::getURI()->toString());

    $this->_doc->addScript($this->_ambit->getScript('detail.js'));

    parent::display($tpl);
  }
}