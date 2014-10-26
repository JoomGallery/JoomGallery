<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/adminconfig.php $
// $Id: adminconfig.php 4076 2013-02-12 10:35:29Z erftralle $
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
 * JoomGallery Admin Configuration Helper
 *
 * Provides some extra handling with all configuration
 * settings of the gallery like saving the configuration, for example.
 *
 * @package JoomGallery
 * @since   1.5.7
 */
class JoomAdminConfig extends JoomConfig
{
  /**
   * Save configuration in database
   *
   * @param   object/array  $newconfig    Holds the new settings to store
   * @param   int/boolean   $id           ID of the config row to update, false if a new row shall be created based on $existing_row for group $group_id
   * @param   int           $existing_row The ID of an existing row which will be used as a base for the new row
   * @param   int           $group_id     The ID of the user group for which the new row will be created
   * @return  boolean       True on successful insert/update of configuration, false otherwise
   * @since   1.5.5
   */
  public function save($newconfig = null, $id = false, $existing_row = 0, $group_id = 0)
  {
    if(!is_null($newconfig))
    {
      $data = (array) $newconfig;
    }
    else
    {
      $data = $this->getProperties();
    }

    $isNew = !$id;

    $config = JTable::getInstance('joomgalleryconfig', 'Table');

    if(!$this->isExtended())
    {
      // Update the currently selected row
      $config->load($this->_id);
      $id = $this->_id;
    }
    else
    {
      // Create a new row based on an existing one
      if($isNew && !$existing_row)
      {
        $existing_row = $this->_id;
      }
      else
      {
        if(!$isNew)
        {
          $existing_row = $id;
        }
      }

      $config->load($existing_row);

      if($isNew)
      {
        $config->group_id = $group_id;
        $config->ordering = $config->getNextOrder();
      }
    }

    // Remove some values which could make the resulting row invalid
    if(isset($data['group_id']))
    {
      unset($data['group_id']);
    }
    if(isset($data['ordering']))
    {
      unset($data['ordering']);
    }

    $config->bind($data);

    $config->id = $id;

    if(!$config->check())
    {
      return false;
    }

    if(!$config->store())
    {
      return false;
    }

    // Publish new config values
    $properties = $config->getProperties();
    foreach($properties as $key => $value)
    {
      $this->$key = $value;
    }

    if(!$this->saveCSS())
    {
      return false;
    }

    return $config->id;
  }

  /**
   * Save joom_settings.css according to the configuration settings
   *
   * @return  boolean   True on success, false otherwise
   * @since   1.5.5
   */
  public function saveCSS()
  {
    $icon_url = '../images/';

    // Common settings
    // Bottomed alignment of images
    $jg_common_imgalign = '';
    // Gallery view
    if($this->jg_imgalign > 0 && $this->jg_colcat > 1)
    {
      $jg_common_imgalign .= ".jg_imgalign_gal{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_photo_container, .jg_photo_container_c, .jg_photo_container_l, .jg_photo_container_r{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}\n";
      $jg_common_imgalign .= ".jg_element_txt{\n";
      $jg_common_imgalign .= "  margin-top: 10px;\n";
      $jg_common_imgalign .= "}\n";
    }

    // Category view subcategories
    if($this->jg_imgalign > 0 && $this->jg_colsubcat > 1)
    {
      $jg_common_imgalign .= ".jg_imgalign_catsubs{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_subcatelem_photo, .jg_subcatelem_photo_c, .jg_subcatelem_photo_l, .jg_subcatelem_photo_r{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}\n";
    }

    // Category view images
    if($this->jg_imgalign > 0 && $this->jg_colnumb > 1)
    {
      $jg_common_imgalign .= ".jg_imgalign_catimgs{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_catelem_photo_align{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}\n";
    }

    // Toplists/Favourites view
    if($this->jg_imgalign > 0 && $this->jg_toplistcols > 1)
    {
      $jg_common_imgalign .= ".jg_imgalign_top{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_topelem_photo{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_imgalign_fav{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_favelem_photo{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}\n";
    }

    // Search view
    if($this->jg_imgalign > 0 && $this->jg_searchcols > 1)
    {
      $jg_common_imgalign .= ".jg_imgalign_search{\n";
      $jg_common_imgalign .= "  height:".$this->jg_imgalign."px;\n";
      $jg_common_imgalign .= "  position:relative;\n";
      $jg_common_imgalign .= "}\n";

      $jg_common_imgalign .= ".jg_searchelem_photo{\n";
      $jg_common_imgalign .= "  bottom:0;\n";
      $jg_common_imgalign .= "  position:absolute;\n";
      $jg_common_imgalign .= "  width:100%;\n";
      $jg_common_imgalign .= "}";
    }

    // Calculation of colum widths
    // Gallery view
    $colwidth_gal = floor(99 / $this->jg_colcat);
    // Category view
    $colwidth_cat = floor(99 / $this->jg_colnumb);
    // Sub-category view
    $colwidth_subcat = floor(99 / $this->jg_colsubcat);

    // Alignment of container for text and image
    // if ct_align=0, alternating alignments
    // jg_element_gal
    $jg_gal_container    = "";

    // jg_photo_container
    $jg_gal_elemimg      = "";

    // jg_element_txt
    $jg_gal_elemtxt      = "";
    $jg_gal_elemtxt_subs = "";

    // Gallery view
    // User defined alignment for category thumb
    if($this->jg_showcatthumb == 2)
    {
      if($this->jg_colcat == 1)
      {
        $jg_gal_container = "  text-align:left !important;\n";
      }
      else
      {
        $jg_gal_container = "  float:left;\n";
      }
    }
    // Activated random view of thumbs or no thumbs or override
    // Alignment on one columned view not with float, instead text-align
    //if($this->jg_showcatthumb == 1 || $this->jg_showcatthumb == 0 || $this->jg_showcatthumb == 3)
    //{
      switch($this->jg_ctalign)
      {
        case 1:
          // Left aligned
          // One column -> text-align
          if($this->jg_colcat == 1)
          {
            $jg_gal_container    = "  text-align:left !important;\n";
            $jg_gal_elemtxt      = "  text-align:left !important; \n";
            $jg_gal_elemtxt_subs = "  text-align:left !important; \n";
          }
          else
          {
            $jg_gal_container    = "  float:left;\n";
            $jg_gal_elemtxt      = "  float:left;\n";
            $jg_gal_elemtxt_subs = "  float:left;\n";
          }
          break;
        case 2:
          // Right aligned
          // One column -> text-align
          if($this->jg_colcat == 1 || $this->jg_catperpage == 1)
          {
            $jg_gal_container = "  text-align:right !important;\n";
          }
          else
          {
            $jg_gal_container = "  float:right;\n";
          }
          $jg_gal_elemtxt      = "  text-align:right !important;\n";
          $jg_gal_elemtxt_subs = "  float:right;\n  text-align:right !important;";
          break;
        case 3:
          // Centered
          if ($this->jg_colcat == 1 || $this->jg_catperpage == 1)
          {
            $jg_gal_container = "  text-align:center;\n";
          }
          else
          {
            $jg_gal_container = "  float:left;\n";
          }
          $jg_gal_elemtxt      = "  text-align:center !important;\n";
          $jg_gal_elemtxt_subs = "  text-align:center !important;\n";
          break;

        default:
          // =0 alternating, classes with *_r implied right placement
          // in joomgallery.css
          $jg_gal_container    = "  float:left;\n";
          $jg_gal_elemtxt      = "  text-align:left !important;\n";
          $jg_gal_elemtxt_subs = "  text-align:left !important;\n";
          break;
      }

      // Alignment of thumb
      // Only with activated random view
      switch($this->jg_ctalign)
      {
        case 1:
          // Left aligned
          $jg_gal_elemimg = "  float:left;\n";
          break;
        case 2:
          // Right aligned
          $jg_gal_elemimg = "  text-align:right !important;\n";
          break;
        case 3:
          // Centered
          $jg_gal_elemimg = "  text-align:center !important;\n";
          break;
        default:
          // Alternating
          $jg_gal_elemimg = "  float:left;\n";
          break;
      }
    //}

    // Category view
    switch($this->jg_catthumbalign)
    {
      case 1:
        // Left aligned
        if($this->jg_colnumb == 1)
        {
          $cat_container = "  text-align:left;";
          $cat_photo     = "  text-align:left;";
        }
        else
        {
          $cat_container = "  float:left;";
          $cat_photo     = "  float:left;";
        }
        $cat_txt       = "  text-align:left !important;";
        break;
      case 2:
        // Right aligned
        if($this->jg_colnumb == 1)
        {
          $cat_container = "  text-align:right !important;";
          $cat_photo     = "  display:block;\n  text-align:right !important;";
        }
        else
        {
          if($this->jg_imgalign == 0)
          {
            $cat_container = "  float:right;\n  text-align:right !important;\n";
            $cat_photo     = "  display:block;\n  text-align:right !important;";
          }
          else
          {
            $cat_container = "  float:right;\n";
            $cat_photo     = "  text-align:right !important;";
          }
        }
        $cat_txt       = "  text-align:right !important;";
        break;
      case 3:
        // Centered
        if($this->jg_colnumb == 1)
        {
          $cat_container = "  text-align:center !important;";
          $cat_txt       = "  text-align:center !important;";
          $cat_photo     = "  display:block;\n  text-align:center !important;";
        }
        else
        {
          if($this->jg_imgalign == 0)
          {
            $cat_container = "  float:left;\n  text-align:center !important;\n";
            $cat_photo     = "  display:block;\n  text-align:center !important;";
          }
          else
          {
            $cat_container = "  float:left;\n";
            $cat_photo     = "  text-align:center !important;";
          }
          $cat_txt       = "  text-align:center !important;";
        }
        break;
    }

    // Sub-category view
    // User defined alignment for subcategory thumb
    if($this->jg_showsubthumbs == 1)
    {
      if($this->jg_colsubcat == 1)
      {
        $subcat_container = "  text-align:left !important;\n";
      }
      else
      {
        $subcat_container = "  float:left;\n";
      }
    }
    // Activated random view of thumbs or no thumbs or override
    //if($this->jg_showsubthumbs == 2 || $this->jg_showsubthumbs == 0 || $this->jg_showsubthumbs == 3)
    //{
      switch($this->jg_subcatthumbalign)
      {
        case 1:
          // Left aligned
          if($this->jg_colsubcat == 1)
          {
            $subcat_container = "  text-align:left !important;";
            $subcat_photo     = "  float:left;";
            $subcat_txt       = "  text-align:left !important;";
          }
          else
          {
            $subcat_container = "  float:left;";
            $subcat_photo     = "  float:left;";
            $subcat_txt       = "  text-align:left !important;";
          }
          break;
        case 2:
          // Right aligned
          if($this->jg_colsubcat == 1)
          {
            $subcat_container = "  text-align:right !important;";
            $subcat_photo     = "  text-align:right !important;";
            $subcat_txt       = "  text-align:right !important;";
          }
          else
          {
            $subcat_container = "  float:right !important;";
            $subcat_photo     = "  text-align:right !important;";
            $subcat_txt       = "  text-align:right !important;";
          }
          break;
        case 3:
          // Centered
          if($this->jg_colsubcat == 1)
          {
            $subcat_container = "  text-align:center !important;";
            $subcat_photo     = "  text-align:center !important;";
            $subcat_txt       = "  display:block;\n  text-align:center !important;";
          }
          else
          {
            $subcat_container = "  float:left;\n  text-align:center !important;";
            $subcat_photo     = "  text-align:center !important;\n";
            $subcat_txt       = "  clear:both;\n  text-align:center !important;";
          }
          break;
      }
    //}

    // Toplist view
    $colwidth_top = floor (99 / $this->jg_toplistcols);

    $top_container = '';
    $top_txt       = '';

    // Only if activated
    if($this->jg_showtoplist != 0 || $this->jg_favourites != 0)
    {
      switch($this->jg_topthumbalign)
      {
        case 1:
          // Image left aligned
          if($this->jg_toplistcols == 1)
          {
            $top_container = "";
            $top_photo = "  width:49%;\n  float:left;";

            switch($this->jg_toptextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $top_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $top_txt = "  text-align: right !important;";
                break;
              case 3:
                // Centered
                $top_txt = "  text-align: center !important;";
                break;
            }
            $top_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            // Image and text left aligned in multi columned view
            $top_container = "  float:left;\n  height:100%;";
            $top_photo     = "";
            $top_txt       = "  text-align:left !important;";
          }
          break;

        case 2:
          // Image right aligned
          if($this->jg_toplistcols == 1)
          {
            $top_container="";
            $top_photo="  width:49%;\n  float:left;\n  text-align:right !important;";

            switch($this->jg_toptextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $top_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $top_txt = "  text-align: right !important;";
                break;
              case 3:
                // Centered
                $top_txt = "  text-align: center !important;";
                break;
            }
            $top_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            // Image and text right aligned in multi columned view
            $top_container = "  float:left;\n  height:100%;\n  text-align:right !important;";
            $top_photo     = "  text-align:right !important;";
            $top_txt       = "  text-align:right !important;";
          }
          break;

        case 3:
          // Image centered
          if($this->jg_toplistcols == 1)
          {
            $top_container = "";
            $top_photo = "  width:49%;\n  float:left;\n  text-align:center !important;";

            switch($this->jg_toptextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $top_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $top_txt = "  text-align:right !important;";
                break;
              case 3:
                // Centered
                $top_txt = "  text-align:center !important;";
                break;
            }
            $top_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            // Image and text centered in multi columned view
            $top_container = "  float:left;\n  height:100%;\n  text-align:center !important;";
            $top_photo     = "  text-align:center !important";
            $top_txt       = "  text-align:center !important;";
          }
          break;
      }
    }

    // Detail view
    if($this->jg_minis != 0 && $this->jg_minisprop == 2 )
    {
      $minidimensions  = "height:".$this->jg_miniHeight."px";
    }
    else
    {
      if($this->jg_minisprop == 1 )
      {
        $minidimensions  = "width:".$this->jg_miniWidth."px";
      }
      else
      {
        $minidimensions  = "width:".$this->jg_miniWidth."px;\n";
        $minidimensions .= "height:".$this->jg_miniHeight."px;\n";
      }
    }

    // Search view
    $colwidth_search = floor (99 / $this->jg_searchcols);

    // Only if activated
    if($this->jg_search != 0)
    {
      switch($this->jg_searchthumbalign)
      {
        case 1:
          // Image left aligned
          if($this->jg_searchcols == 1)
          {
            $search_container = "";
            $search_photo     = "  width:49%;\n  float:left;";

            switch($this->jg_searchtextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $search_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $search_txt = "  text-align: right !important;";
                break;
              case 3:
                // Centered
                $search_txt = "  text-align: center !important;";
                break;
            }
            $search_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            $search_container = "  float:left;\n  height:100%;";
            $search_photo     = "  text-align:left !important;";
            $search_txt       = "  text-align:left !important;";
          }
          break;
        case 2:
          // Image right aligned
          if($this->jg_searchcols == 1)
          {
            $search_container = "";
            $search_photo     = "  width:49%;\n  float:left;\n  text-align:right !important;";

            switch($this->jg_searchtextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $search_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $search_txt = "  text-align: right !important;";
                break;
              case 3:
                // Centered
                $search_txt = "  text-align: center !important;";
                break;
            }
            $search_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            $search_container = "  float:left;\n  height:100%;\n  text-align:right !important;";
            $search_photo     = "  text-align: right !important;";
            $search_txt       = "  text-align: right !important;";
          }
          break;
        case 3:
          // Image centered
          if($this->jg_searchcols == 1)
          {
            $search_container = "";
            $search_photo = "  width:49%;\n  float:left;\n  text-align:center !important;";

            switch($this->jg_searchtextalign)
            {
              // Alignment of text
              case 1:
                // Left aligned
                $search_txt = "  text-align:left !important;";
                break;
              case 2:
                // Right aligned
                $search_txt = "  text-align: right !important;";
                break;
              case 3:
                // Centered
                $search_txt = "  text-align: center !important;";
                break;
            }
            $search_txt .= "\n  width:49%;\n  float:left;";
          }
          else
          {
            $search_container = "  float:left;\n  height:100%;\n  text-align:center !important;";
            $search_photo     = "  text-align: center !important;";
            $search_txt       = "  text-align: center !important;";
          }
          break;
      }
    }

    // Composing and output of CSS

    $css_settings = "
/* Joomgallery CSS
CSS Styles generated by settings in the Joomgallery backend.
DO NOT EDIT - this file will be overwritten every time the config is saved.
Adjust your styles in joom_local.css instead.

CSS Styles, die ueber die Speicherung der Konfiguration im Backend erzeugt werden.
BITTE NICHT VERAENDERN - diese Datei wird  mit dem naechsten Speichern ueberschrieben.
Bitte nehmen Sie Aenderungen in der Datei joom_local.css in diesem
Verzeichnis vor. Sie koennen sie neu erstellen oder die schon vorhandene
joom_local.css.README umbenennen und anpassen
*/\n\n";

    if(!empty($jg_common_imgalign))
    {
      $css_settings .= "/* Common settings */\n";
      $css_settings .= "/* Vertical alignment of images to bottom */\n";
      $css_settings .= $jg_common_imgalign;
      $css_settings .= "\n\n";
    }

    // Gallery view
    $css_settings .= "/* Gallery view */\n";

    // Container with eventually picture and categorytext
    $css_settings .= ".jg_element_gal, .jg_element_gal_r {\n";
    $css_settings .= $jg_gal_container;
    $css_settings .= "  width:".$colwidth_gal."%;\n";
    $css_settings .= "}\n";

    // Text
    $css_settings .= ".jg_element_txt {\n";
    $css_settings .= $jg_gal_elemtxt;
    $css_settings .= "}\n";

    // Text sub-categories
    $css_settings .= ".jg_element_txt_subs {\n";
    $css_settings .= $jg_gal_elemtxt_subs;
    $css_settings .= "  font-size: 0.9em;\n";
    $css_settings .= "}\n";

    // Image if activated
    if(($this->jg_showcatthumb == 1 || $this->jg_showcatthumb == 3 || $this->jg_showcatthumb == 2) && !empty($jg_gal_elemimg))
    {
      $css_settings .= ".jg_photo_container {\n";
      $css_settings .= $jg_gal_elemimg;
      $css_settings .= "}\n";
    }

    // Category view
    $css_settings .= "\n/* Category view */\n";
    $css_settings .= ".jg_element_cat {\n";
    $css_settings .= "  width:".$colwidth_cat."%;\n";
    $css_settings .= $cat_container."\n";
    $css_settings .= "}\n";
    $css_settings .= ".jg_catelem_cat a{\n";
    $css_settings .= "  height:".$this->jg_thumbheight."px;\n";
    $css_settings .= "}\n";
    $css_settings .= ".jg_catelem_photo {\n";
    $css_settings .= $cat_photo."\n";
    $css_settings .= "}\n";
    $css_settings .= ".jg_catelem_txt {\n";
    $css_settings .= $cat_txt."\n";
    $css_settings .= "}\n";
    if($this->jg_ratingdisplaytype == 1)
    {
      // Rating with star graphic
      $css_settings .= ".jg_starrating_cat {\n";
      $css_settings .= "  width:".(int)($this->jg_maxvoting * 16)."px;\n";
      $css_settings .= "  background: url(".$icon_url."star_gr.png) 0 0 repeat-x;\n";
      switch($this->jg_catthumbalign)
      {
        case 2:
          $css_settings .= "  margin-left: auto;\n";
          break;
        case 3:
          $css_settings .= "  margin: 0 auto;\n";
          break;
        default:
          break;
      }
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_cat div {\n";
      $css_settings .= "  height:16px;\n";
      $css_settings .= "  background: url(".$icon_url."star_orange.png) 0 0 repeat-x;\n";
      $css_settings .= "  margin-left: 0;\n";
      $css_settings .= "  margin-right: auto;\n";
      $css_settings .= "}\n";
    }

    // Sub-category view
    $css_settings .= "\n/* Subcategory view */\n";
    $css_settings .= ".jg_subcatelem_cat, .jg_subcatelem_cat_r{\n";
    $css_settings .= "  width:".$colwidth_subcat."%;\n";
    $css_settings .= $subcat_container."\n";
    $css_settings .= "}\n";
    $css_settings .= ".jg_subcatelem_cat a{\n";
    $css_settings .= "  height:".$this->jg_thumbheight."px;\n";
    $css_settings .= "}\n";
    if(isset($subcat_photo))
    {
      $css_settings .= ".jg_subcatelem_photo {\n";
      $css_settings .= $subcat_photo."\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_subcatelem_txt {\n";
      $css_settings .= $subcat_txt."\n";
      $css_settings .= "}\n";
    }

    // Detail view
    $css_settings .= "\n/* Detail view */\n";
    // Motiongallery only if activated
    if($this->jg_minis != 0)
    {
      $css_settings .= ".jg_minipic {\n";
      $css_settings .= "  ".$minidimensions.";\n";
      $css_settings .= "}\n";

      $css_settings .= "#motioncontainer {\n";
      $css_settings .= "  width:".$this->jg_motionminiWidth."px; /* Set to gallery width, in px or percentage */\n";
      $css_settings .= "  height:".$this->jg_motionminiHeight."px;/* Set to gallery height */\n";
      $css_settings .= "}\n";
    }
    if($this->jg_ratingdisplaytype == 1)
    {
      // Rating with star graphic
      $css_settings .= ".jg_starrating_detail {\n";
      $css_settings .= "  width:".(int)($this->jg_maxvoting * 16)."px;\n";
      $css_settings .= "  background: url(".$icon_url."star_gr.png) 0 0 repeat-x;\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_detail div {\n";
      $css_settings .= "  height:16px;\n";
      $css_settings .= "  background: url(".$icon_url."star_orange.png) 0 0 repeat-x;\n";
      $css_settings .= "}\n";
      // Rating bar
      $css_settings .= ".jg_starrating_bar,\n";
      $css_settings .= ".jg_starrating_bar div:hover,\n";
      $css_settings .= ".jg_starrating_bar div:active,\n";
      $css_settings .= ".jg_starrating_bar div:focus,\n";
      $css_settings .= ".jg_starrating_bar .jg_current-rating {\n";
      $css_settings .= "  background: url(".$icon_url."star_rating.png) left -1000px repeat-x;\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_bar {\n";
      $css_settings .= "  position:relative;\n";
      $css_settings .= "  width:".(int)($this->jg_maxvoting * 24)."px;\n";
      $css_settings .= "  height:24px;\n";
      $css_settings .= "  overflow:hidden;\n";
      $css_settings .= "  list-style:none;\n";
      $css_settings .= "  margin:0px auto !important;\n";
      $css_settings .= "  padding:0 !important;\n";
      $css_settings .= "  background-position:left top;\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_bar li {\n";
      $css_settings .= "  display:inline;\n";
      $css_settings .= "  padding:0 !important;\n";
      $css_settings .= "  margin:0 !important;\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_bar div,\n";
      $css_settings .= ".jg_starrating_bar .jg_current-rating {\n";
      $css_settings .= "  position:absolute;\n";
      $css_settings .= "  top:0;\n";
      $css_settings .= "  left:0;\n";
      $css_settings .= "  text-indent:-1000em;\n";
      $css_settings .= "  height:24px;\n";
      $css_settings .= "  line-height:24px;\n";
      $css_settings .= "  outline:none;\n";
      $css_settings .= "  overflow:hidden;\n";
      $css_settings .= "  border: none;\n";
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_bar div:hover,\n";
      $css_settings .= ".jg_starrating_bar div:active,\n";
      $css_settings .= ".jg_starrating_bar div:focus {\n";
      $css_settings .= "  background-position:left bottom;\n";
      $css_settings .= "}\n";
      for($i=0; $i<$this->jg_maxvoting; $i++)
      {
        $css_settings .= ".jg_starrating_bar div.jg_star_".($i + 1)." {\n";
        $css_settings .= "  width:".(int)(100.0 / (float)$this->jg_maxvoting * (float)($i + 1))."%;\n";
        $css_settings .= "  z-index:".(($this->jg_maxvoting + 1) - $i).";\n";
        $css_settings .= "  cursor:pointer;\n";
        $css_settings .= "  display:inline;\n";
        $css_settings .= "}\n";
      }
      $css_settings .= ".jg_starrating_bar .jg_current-rating {\n";
      $css_settings .= "  z-index:1;\n";
      $css_settings .= "  background-position:left center;\n";
      $css_settings .= "}\n";
    }

    // Name tags only if activated
    if($this->jg_nameshields != 0)
    {
      $css_settings .=".nameshield {\n";
      $css_settings .="  line-height:".$this->jg_nameshields_height."px;\n";
      $css_settings .="}\n";
    }

    // Toplist view (special) and favourites
    if($this->jg_showtoplist != 0 || $this->jg_favourites != 0)
    {
      $css_settings .= "\n/* Special view - Toplists*/\n";
      $css_settings .= ".jg_topelement, .jg_favelement {\n";
      $css_settings .= "  width:".$colwidth_top."%;\n";
      $css_settings .= "  height:auto;\n";
      $css_settings .= $top_container."\n";
      $css_settings .= "}\n";

      if(!empty($top_photo))
      {
        $css_settings .= ".jg_topelem_photo, .jg_favelem_photo {\n";
        $css_settings .= $top_photo."\n";
        $css_settings .= "}\n";
      }
      $css_settings .= ".jg_topelem_txt, .jg_favelem_txt {\n";
      $css_settings .= $top_txt."\n";
      $css_settings .= "}\n";
    }

    if($this->jg_ratingdisplaytype == 1)
    {
      // Rating with star graphic
      $css_settings .= ".jg_starrating_fav, .jg_starrating_top  {\n";
      $css_settings .= "  width:".(int)($this->jg_maxvoting * 16)."px;\n";
      $css_settings .= "  background: url(".$icon_url."star_gr.png) 0 0 repeat-x;\n";
      $setting = (($this->jg_toplistcols == 1) ? $this->jg_toptextalign : $this->jg_topthumbalign);
      switch($setting)
      {
        case 2:
          $css_settings .= "  margin-left: auto;\n";
          break;
        case 3:
          $css_settings .= "  margin: 0 auto;\n";
          break;
        default:
          break;
      }
      $css_settings .= "}\n";
      $css_settings .= ".jg_starrating_fav div, .jg_starrating_top div {\n";
      $css_settings .= "  height:16px;\n";
      $css_settings .= "  background: url(".$icon_url."star_orange.png) 0 0 repeat-x;\n";
      $css_settings .= "  margin-left: 0;\n";
      $css_settings .= "  margin-right: auto;\n";
      $css_settings .= "}\n";
    }

    // Search view
    if($this->jg_search != 0)
    {
      $css_settings .= "\n/* Search view*/\n";
      $css_settings .= ".jg_searchelement {\n";
      $css_settings .= "  width:".$colwidth_search."%;\n";
      $css_settings .= "  height:auto;\n";
      if(!empty($search_container))
      {
        $css_settings .= $search_container."\n";
      }
      $css_settings .= "}\n";

      if(!empty($search_photo))
      {
        $css_settings .= ".jg_searchelem_photo {\n";
        $css_settings .= $search_photo."\n";
        $css_settings .= "}\n";
      }

      $css_settings .= ".jg_searchelem_txt {\n";
      $css_settings .= $search_txt."\n";
      $css_settings .= "}\n";

      if($this->jg_ratingdisplaytype == 1)
      {
        // Rating with star graphic
        $css_settings .= ".jg_starrating_search  {\n";
        $css_settings .= "  width:".(int)($this->jg_maxvoting * 16)."px;\n";
        $css_settings .= "  background: url(".$icon_url."star_gr.png) 0 0 repeat-x;\n";
        $setting = (($this->jg_searchcols == 1) ? $this->jg_searchtextalign : $this->jg_searchthumbalign);
        switch($setting)
        {
          case 2:
            $css_settings .= "  margin-left: auto;\n";
            break;
          case 3:
            $css_settings .= "  margin: 0 auto;\n";
            break;
          default:
            break;
        }
        $css_settings .= "}\n";
        $css_settings .= ".jg_starrating_search div {\n";
        $css_settings .= "  height:16px;\n";
        $css_settings .= "  background: url(".$icon_url."star_orange.png) 0 0 repeat-x;\n";
        $css_settings .= "  margin-left: 0;\n";
        $css_settings .= "  margin-right: auto;\n";
        $css_settings .= "}\n";
      }
    }

    // Save the file
    jimport('joomla.filesystem.file');
    $css_settings_file = JPATH_ROOT.'/media/joomgallery/css/'.$this->getStyleSheetName($this->id);
    if(!JFile::write($css_settings_file, $css_settings))
    {
      return false;
    }

    return true;
  }

  /**
   * Deletes a specific configuration row and corresponding CSS file
   *
   * @param   int     $id The Id of the row to delete
   * @return  boolean True on success, false otherwise
   * @since   2.0
   */
  public function delete($id)
  {
    if($id == 1)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_CONFIGS_DEFAULT_ROW_NOT_DELETABLE'));

      return false;
    }

    $config = JTable::getInstance('joomgalleryconfig', 'Table');

    if(!$config->delete($id))
    {
      $this->setError($config->getError());

      return false;
    }

    jimport('joomla.filesystem.file');
    $css_settings_file = JPATH_ROOT.'/media/joomgallery/css/'.$this->getStyleSheetName($id);
    if(!JFile::delete($css_settings_file))
    {
      return false;
    }

    return true;
  }
}