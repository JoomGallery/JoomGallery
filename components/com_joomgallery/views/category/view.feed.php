<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/views/category/view.feed.php $
// $Id: view.feed.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Category Feed View class
 *
 * @package JoomGallery
 * @since   1.5.7
 */
class JoomGalleryViewCategory extends JoomGalleryView
{
  /**
   * Outputs an RSS feed which a configured number of last added images
   * of the current category and its sub-categories
   *
   * @access  public
   * @return  void
   * @since   1.5.7
   */
  function display()
  {
    $params     = & $this->_mainframe->getParams();
    $feedEmail  = ($this->_mainframe->getCfg('feed_email')) ? $this->_mainframe->getCfg('feed_email') : 'author';
    $siteEmail  = $this->_mainframe->getCfg('mailfrom');

    // Get the images data from the model
    JRequest::setVar('limit', $this->_config->get('jg_category_rss'));
    $category  = & $this->get('Category');
    $rows     = & $this->get('AllImages');

    $this->_doc->link = JRoute::_('index.php?view=category&catid='.$category->cid);

    foreach($rows as $row)
    {
      // Strip HTML from feed item title
      $title = $this->escape($row->imgtitle);
      $title = html_entity_decode($title);

      // URL link to image
      if(is_numeric($this->_config->get('jg_detailpic_open')) && $this->_config->get('jg_detailpic_open') == 0)
      {
        $link = JRoute::_('index.php?view=detail&id='.$row->id);
      }
      else
      {
        $link = $this->_ambit->getImg('img_url', $row);
      }

      // Strip HTML from feed item description text
      $description  = $this->escape(strip_tags($row->imgtext));
      $author      = $row->imgauthor ? $row->imgauthor : $row->owner;

      // Load individual item creator class
      $item = new JFeedItem();
      $item->title        = $title;
      $item->link         = $link;
      $item->description  = $description;
      $item->date         = $row->imgdate;
      $item->category     = $row->catid;
      $item->author       = $author;
      if($feedEmail == 'site')
      {
        $item->authorEmail = $siteEmail;
      }
      else
      {
        if($userid = $row->owner)
        {
          $user = JFactory::getUser($userid);
          $item->authorEmail = $user->get('email');
        }
      }

      // Load item info into RSS array
      $this->_doc->addItem($item);
    }
  }
}