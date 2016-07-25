<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/views/help/view.html.php $
// $Id: view.html.php 4416 2014-10-26 02:04:29Z chraneco $
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
 * HTML View class for the help view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewHelp extends JoomGalleryView
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
    JToolBarHelper::title(JText::_('COM_JOOMGALLERY_HLPIFO_HELP_MANAGER'), 'info');

    $languages = array( 'de-DE-formal'    => array( 'translator'    => 'JoomGallery::ProjectTeam de-DE (formal)',
                                                    'downloadlink'  => 'http://www.joomgallery.net/downloads/joomgallery-fuer-joomla-3/sprachdateien/die-deutschen-formellen-sprachdateien.html',
                                                    'flag'          => 'de.png',
                                                    'type'          => 'formal'),
                        'de-DE-informal'  => array( 'translator'    => 'JoomGallery::ProjectTeam de-DE (informal)',
                                                    'downloadlink'  => 'http://www.joomgallery.net/downloads/joomgallery-fuer-joomla-3/sprachdateien/die-deutschen-informellen-sprachdateien.html',
                                                    'flag'          => 'de.png'),
                        'ar-AA'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Arabic ar-AA',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-arabic-unitag-language-files.html',
                                                    'flag'          => 'sy.png'),
                        'bs-BA'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Bosnian (Bosnia and Herzegovina) bs-BA',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-bosnian-language-files.html',
                                                    'flag'          => 'ba.png'),
                        'bg-BG'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Bulgarian (Bulgaria) bg-BG',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-bulgarian-language-files.html',
                                                    'flag'          => 'bg.png'),
                        'zh-CN'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Chinese (China) zh-CN',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-chinese-simplified-language-files.html',
                                                    'flag'          => 'cn.png'),
                        'zh-TW'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Chinese (Taiwan) zh-TW',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-chinese-traditional-language-files.html',
                                                    'flag'          => 'cn.png'),
                        'hr-HR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Croatian (Croatia) hr-HR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-croatian-language-files.html',
                                                    'flag'          => 'hr.png'),
                        'cs-CZ'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Czech (Czech Republic) cs-CZ',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-czech-language-files.html',
                                                    'flag'          => 'cz.png'),
                        'da-DK'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Danish (Denmark) da-DK',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-danish-language-files.html',
                                                    'flag'          => 'dk.png'),
                        'nl-NL'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Dutch (Netherlands) nl-NL',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-dutch-language-files.html',
                                                    'flag'          => 'nl.png'),
                        'fi-FI'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Finnish (Finland) fi-FI',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-finnish-language-files.html',
                                                    'flag'          => 'fi.png'),
                        'fr-FR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::French (France) fr-FR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-french-language-files.html',
                                                    'flag'          => 'fr.png'),
                        'el-GR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Greek (Greece) el-GR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-greek-language-files.html',
                                                    'flag'          => 'gr.png'),
                        'hu-HU-formal'    => array( 'translator'    => 'JoomGallery::TranslationTeam::Hungarian (Hungary) hu-HU (formal)',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-hungarian-formal-language-files.html',
                                                    'flag'          => 'hu.png'),
                        'hu-HU-informal'  => array( 'translator'    => 'JoomGallery::TranslationTeam::Hungarian (Hungary) hu-HU (informal)',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-hungarian-informal-language-files.html',
                                                    'flag'          => 'hu.png'),
                        'it-IT'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Italian (Italy) it-IT',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-italian-language-files.html',
                                                    'flag'          => 'it.png'),
                        'ja-JP'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Japanese (Japan) ja-JP',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-japanese-language-files.html',
                                                    'flag'          => 'jp.png'),
                        'lt-LT'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Lithuanian (Lithuania) lt-LT',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-lithunian-language-files.html',
                                                    'flag'          => 'lt.png'),
                        'lv-LV'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Latvian (Latvia) lv-LV',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-latvian-language-files.html',
                                                    'flag'          => 'lv.png'),
                        'nb-NO'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Norwegian Bokmål (Norway) nb-NO',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-norwegian-language-files.html',
                                                    'flag'          => 'no.png'),
                        'fa-IR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Persian (Iran) fa-IR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-persian-language-files.html',
                                                    'flag'          => 'ir.png'),
                        'pl-PL'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Polish (Poland) pl-PL',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-polish-language-files.html',
                                                    'flag'          => 'pl.png'),
                        'pt-BR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Portuguese (Brazil) pt-BR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-portuguese-brazilian-language-files.html',
                                                    'flag'          => 'br.png'),
                        'pt-PT'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Portuguese (Portugal) pt-PT',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-portuguese-language-files.html',
                                                    'flag'          => 'pt.png'),
                        'ru-RU'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Russian (Russia) ru-RU',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-russian-language-files.html',
                                                    'flag'          => 'ru.png'),
                        'sr-RS'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Serbian (Serbia) sr-RS',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-serbian-cyrillic-language-files.html',
                                                    'flag'          => 'rs.png'),
                        'sr-YU'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Serbian sr-YU',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-serbian-language-files.html',
                                                    'flag'          => 'rs.png'),
                        'sk-SK'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Slovak (Slovakia) sk-SK',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-slovak-language-files.html',
                                                    'flag'          => 'sk.png'),
                        'sl-SI'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Slovenian (Slovenia) sl-SI',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-slovenian-language-files.html',
                                                    'flag'          => 'si.png'),
                        'es-ES'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Spanish (Spain) es-ES',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-spanish-language-files.html',
                                                    'flag'          => 'es.png'),
                        'sv-SE'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Swedish (Sweden) sv-SE',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-swedish-language-files.html',
                                                    'flag'          => 'se.png'),
                        'tr-TR'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Turkish (Turkey) tr-TR',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-turkish-language-files.html',
                                                    'flag'          => 'tr.png'),
                        'uk-UA'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Ukrainian (Ukraine) uk-UA',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-ukrainian-language-files.html',
                                                    'flag'          => 'ua.png'),
                        'vi-VN'           => array( 'translator'    => 'JoomGallery::TranslationTeam::Vietnamese (Viet Nam) vi-VN',
                                                    'downloadlink'  => 'http://www.en.joomgallery.net/downloads/joomgallery-for-joomla-3/languages/the-vietnamese-language-files.html',
                                                    'flag'          => 'vn.png')
                      );

    $credits  = array(array('title'   => 'Joomla!',
                            'author'  => '',
                            'link'    => 'http://www.joomla.org'),
                      array('title'   => 'jQuery Thumbnail Scroller - Detail view',
                            'author'  => 'Manos Malihutsakis',
                            'link'    => 'http://manos.malihu.gr/jquery-thumbnail-scroller'),
                      array('title'   => 'Slimbox (modified) - Detail and Category view',
                            'author'  => 'Christophe Beyls',
                            'link'    => 'http://www.digitalia.be/software/slimbox'),
                      array('title'   => 'Thickbox3.1 (modified) - Detail and Category view',
                            'author'  => 'Cody Lindley',
                            'link'    => 'http://www.codylindley.com'),
                      array('title'   => 'pngbehavior.htc (PNG in IE6)',
                            'author'  => 'Erik Arvidsson',
                            'link'    => 'http://webfx.eae.net'),
                      array('title'   => 'ImageMagick',
                            'author'  => 'ImageMagick Studio LLC',
                            'link'    => 'http://www.imagemagick.org/script/index.php'),
                      array('title'   => 'Jupload - Java Applet for uploading',
                            'author'  => 'Etienne Gauthier',
                            'link'    => 'http://jupload.sourceforge.net/'),
                      array('title'   => 'Watermark (modified)',
                            'author'  => 'Michael Mueller',
                            'link'    => 'http://www.php4u.net'),
                      array('title'   => 'fastimagecopyresampled (fast conversion of pictures in GD)',
                            'author'  => 'Tim Eckel',
                            'link'    => 'http://de.php.net/manual/en/function.imagecopyresampled.php#77679'),
                      array('title'   => 'Wonderful Icons',
                            'author'  => 'Mark James',
                            'link'    => 'http://www.famfamfam.com'),
                      array('title'   => 'Smoothgallery (modified) slideshow in detail view',
                            'author'  => 'Jonathan Schemoul',
                            'link'    => 'http://smoothgallery.jondesign.net'),
                      array('title'   => 'Resize Image with Different Aspect Ratio - resizing thumbnails',
                            'author'  => 'Nash',
                            'link'    => 'http://nashruddin.com/Resize_Image_to_Different_Aspect_Ratio_on_the_fly'),
                      array('title'   => 'Weighted rating according to Thomas Bayes',
                            'author'  => 'Michael Jašek',
                            'link'    => 'http://www.buntesuppe.de/blog/123/bayessche-bewertung'),
                      array('title'   => 'Fine Uploader',
                            'author'  => 'Ray Nicholus, Andrew Valums',
                            'link'    => 'http://fineuploader.com')
                     );

    $params = JComponentHelper::getParams('com_joomgallery');
    if($this->_config->get('jg_checkupdate') && extension_loaded('curl'))
    {
      $params->set('autoinstall_possible', 1);
    }

    $this->languages  = $languages;
    $this->credits    = $credits;
    $this->params     = $params;

    $this->sidebar = JHtmlSidebar::render();

    parent::display($tpl);
  }
}
