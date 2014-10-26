<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/category/view.raw.php $
// $Id: view.raw.php 4139 2013-03-14 22:21:57Z chraneco $
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
 * Raw View class for displaying images in Cooliris
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryViewCategory extends JoomGalleryView
{
  /**
   * Raw view display method
   *
   * @access  public
   * @return  void
   * @since   1.5.5
   */
  function display($tpl = null)
  {
    $catid = JRequest::getInt('catid');

    $this->_doc->setMimeEncoding('text/xml');

    // Images pagination
    $totalimages = $this->get('TotalImages');

    // Calculation of the number of total pages
    $perpage = $this->_config->get('jg_perpage');
    if(!$perpage)
    {
      $perpage = 10;
    }
    $totalpages = floor($totalimages / $perpage);
    $offcut     = $totalimages % $perpage;
    if($offcut > 0)
    {
      $totalpages++;
    }

    $totalimages = number_format($totalimages, 0, ',', '.');
    // Get the current page
    $page = JRequest::getInt('page', 0);
    if($page > $totalpages)
    {
      $page = $totalpages;
      if($page <= 0)
      {
        $page = 1;
      }
    }
    else
    {
      if($page < 1)
      {
        $page = 1;
      }
    }

    $limitstart = ($page - 1) * $perpage;
    JRequest::setVar('limitstart', $limitstart);
    JRequest::setVar('limit', $perpage);

    $images = $this->get('Images');

    $rss = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>'.$this->_mainframe->getCfg('sitename').'</title>
    <link>'.JURI::root().'</link>
    <description><![CDATA['.JText::_('COM_JOOMGALLERY_COMMON_GALLERY').']]></description>';

    if($totalpages == 1)
    {
      $rss .= '
    <atom:link rel="self" href="'.JRoute::_('index.php?option=com_joomgallery&amp;view=category&amp;catid='.$catid.'&amp;format=raw').'" />';
    }
    else
    {
      $rss .= '
    <atom:link rel="self" href="'.JRoute::_('index.php?option=com_joomgallery&amp;view=category&amp;catid='.$catid.'&amp;page='.$page.'&amp;format=raw').'" />';
    }

    // Only if showing more than one site analyze prev-next links and output them
    if($totalpages > 1)
    {
      // Prev link only if currentpage > 1
      if($page > 1)
      {
        $prevpage = $page - 1;
        $rss     .= '
    <atom:link rel="previous" href="'.JRoute::_('index.php?option=com_joomgallery&amp;view=category&amp;catid='.$catid.'&amp;page='.$prevpage.'&amp;format=raw').'" />';
      }

      // Next link only if last site hasn't already been reached
      if($page < $totalpages)
      {
        $nextpage = $page + 1;
        $rss     .= '
    <atom:link rel="next" href="'.JRoute::_('index.php?option=com_joomgallery&amp;view=category&amp;catid='.$catid.'&amp;page='.$nextpage.'&amp;format=raw').'" />';
      }
    }

    JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html');

    foreach($images as $image)
    {
      $title  = trim($this->escape($image->imgtitle));
      $text   = trim($this->escape($image->imgtext));
      $link   = JRoute::_('index.php?view=detail&id='.$image->id);
      $name   = $image->imgauthor;

      $link_big = 1;

      // Check if original image exists, otherwise use detail image
      /*if(file_exists($this->_ambit->getImg('orig_path', $image)))
      {
        $func = 'detail';
      }*/

      $contenturl = JHTML::_('joomgallery.openimage', $link_big, $image);
      $contenturl = str_replace('" target="_blank', '', $contenturl);

      $rss .= '
    <item>
      <title>'.$title.'</title>
      <link>'.$link.'</link>
      <media:thumbnail url="'.$this->_ambit->getImg('thumb_url', $image).'" />
      <media:content url="'.$contenturl.'" />
      <media:description><![CDATA['.$text.']]></media:description>
      <guid isPermaLink="false">'.JURI::root().'gallery-'.$image->id.'</guid>
    </item>';
    }

    $rss .= '
  </channel>
</rss>';

    echo $rss;

    #parent::display();
  }
}