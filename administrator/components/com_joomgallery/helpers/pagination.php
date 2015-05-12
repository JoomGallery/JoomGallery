<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/pagination.php $
// $Id: pagination.php 4076 2013-02-12 10:35:29Z erftralle $
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

jimport('joomla.html.pagination');

/**
 * Pagination Class.  Provides a common interface for content pagination
 * for JoomGallery (extends the pagination class of Joomla!)
 *
 * @package JoomGallery
 * @since   2.0
 */
class JoomPagination extends JPagination
{
  /**
   * Holds the anchor for URLs if it is enabled
   *
   * @var   string
   * @since 2.0
   */
  protected $anchortag;

  /**
   * Holds the 'onlick' attribute if there is one given
   *
   * @var   string
   * @since 2.0
   */
  protected $onclick;

  /**
   * Internal cache for the data object
   *
   * @var   object
   * @since 2.0
   */
  protected $store;

  /**
   * Constructor
   *
   * @param   int     $total      The total number of items
   * @param   int     $limitstart The offset of the item to start at
   * @param   int     $limit      The number of items to display per page
   * @param   string  $prefix     The prefix used for request variables
   * @param   string  $anchortag  The anchor to use for the URLs
   * @param   string  $onclick    The contents of an optional 'onlick' attribute
   * @return  void
   * @since   2.0
   */
  public function __construct($total, $limitstart, $limit, $prefix = '', $anchortag = 'category', $onclick = null)
  {
    parent::__construct($total, $limitstart, $limit, $prefix);

    $this->anchortag = '';
    if($anchortag)
    {
      $this->anchortag = JHtml::_('joomgallery.anchor', $anchortag);
    }

    if($onclick)
    {
      $this->onclick = '" onclick="'.$onclick;
    }
  }

  /**
   * Create and return the pagination data object
   *
   * @return  object  Pagination data object
   * @since   2.0
   */
  protected function _buildDataObject()
  {
    // Check whether the data object was already created
    if(!empty($this->store))
    {
      return $this->store;
    }

    // Initialise variables
    $data         = new stdClass;
    $currentPage  = $this->get('pages.current');
    $pageCount    = $this->get('pages.total');

    // Build the additional URL parameters string
    $params = '';
    if(!empty($this->additionalUrlParams))
    {
      foreach($this->additionalUrlParams as $key => $value)
      {
        $params .= '&'.$key.'='.$value;
      }
    }

    $data->all = new JPaginationObject(JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);
    if(!$this->viewall)
    {
      $data->all->base  = '0';
      $data->all->link  = JRoute::_($params.'&'.$this->prefix.'limitstart=');
    }

    // Set the start and previous data objects
    $data->start    = new JPaginationObject(JText::_('JLIB_HTML_START'), $this->prefix);
    $data->previous = new JPaginationObject(JText::_('JPREV'), $this->prefix);

    if($currentPage > 1)
    {
      $previous = $currentPage - 1;

      $data->start->base    = 1;
      $data->start->link    = JRoute::_($params.'&'.$this->prefix.'page=1').$this->anchortag.sprintf($this->onclick, 1);
      $data->previous->base = $previous;
      $data->previous->link = JRoute::_($params.'&'.$this->prefix.'page='.$previous).$this->anchortag.sprintf($this->onclick, $previous);
    }

    // Set the next and end data objects
    $data->next = new JPaginationObject(JText::_('JNEXT'), $this->prefix);
    $data->end  = new JPaginationObject(JText::_('JLIB_HTML_END'), $this->prefix);

    if($currentPage < $pageCount)
    {
      $next = $currentPage + 1;

      $data->next->base = $next;
      $data->next->link = JRoute::_($params.'&'.$this->prefix.'page='.$next).$this->anchortag.sprintf($this->onclick, $next);
      $data->end->base  = $pageCount;
      $data->end->link  = JRoute::_($params.'&'.$this->prefix.'page='.$pageCount).$this->anchortag.sprintf($this->onclick, $pageCount);
    }

    $data->pages = array();

    $workPage       = 2;
    $placeHolderKey = $pageCount;

    // Variable for current page found and assembled
    $currItemfound = false;

    // Work on left edge
    if($currentPage == 1)
    {
      $currItemfound = true;
      $data->pages[1] = new JPaginationObject(1, $this->prefix);
      $data->pages[1]->base = null;
      $data->pages[1]->link = JRoute::_($params.'&'.$this->prefix.'page=1').$this->anchortag.sprintf($this->onclick, 1);
      $data->pages[2] = new JPaginationObject(2, $this->prefix);
      $data->pages[2]->base = 2;
      $data->pages[2]->link = JRoute::_($params.'&'.$this->prefix.'page=2').$this->anchortag.sprintf($this->onclick, 2);
    }
    else
    {
      // Current page not 1
      $data->pages[1] = new JPaginationObject(1, $this->prefix);
      $data->pages[1]->base = 1;
      $data->pages[1]->link = JRoute::_($params.'&'.$this->prefix.'page=1').$this->anchortag.sprintf($this->onclick, 1);

      if($currentPage == 2)
      {
        $currItemfound = true;
        $data->pages[2] = new JPaginationObject(2, $this->prefix);
        $data->pages[2]->base = null;
        $data->pages[2]->link = JRoute::_($params.'&'.$this->prefix.'page=2').$this->anchortag.sprintf($this->onclick, 2);
      }
      else
      {
        $data->pages[2] = new JPaginationObject(2, $this->prefix);
        $data->pages[2]->base = 2;
        $data->pages[2]->link = JRoute::_($params.'&'.$this->prefix.'page=2').$this->anchortag.sprintf($this->onclick, 2);
      }
    }

    // Range left from current page to 1 not assembled yet
    if(!$currItemfound)
    {
      // Construct pages left to current page
      // according to difference to left implement jumps
      // If difference to current page too low, output them exactly
      if($currentPage - $workPage < 6)
      {
        $workPage++;
        for($i = $workPage; $i < $currentPage; $i++)
        {
          $data->pages[$i] = new JPaginationObject($i, $this->prefix);
          $data->pages[$i]->base = $i;
          $data->pages[$i]->link = JRoute::_($params.'&'.$this->prefix.'page='.$i).$this->anchortag.sprintf($this->onclick, $i);
          $workPage++;
        }
      }
      else
      {
        // Otherwise output of remaining links evt. in steps
        // and in addition output of 2 left neighbours
        // completion of range at position 3 to (current page - 3)
        $endRange = $currentPage - 3;
        $jump = ceil(($endRange - 5) / 4);
        if($jump == 0)
        {
          $jump = 1;
        }

        $workPage = $workPage + $jump;
        for($i = 1; $i < 4; $i++)
        {
          if($jump != 1)
          {
            $placeHolderKey++;
            $data->pages[$placeHolderKey] = new JPaginationObject(JText::_('COM_JOOMGALLERY_COMMON_PAGENAVIGATION_ELLIPSIS'), $this->prefix);
            $data->pages[$placeHolderKey]->base = null;
          }

          $data->pages[$workPage] = new JPaginationObject($workPage, $this->prefix);
          $data->pages[$workPage]->base = $workPage;
          $data->pages[$workPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$workPage).$this->anchortag.sprintf($this->onclick, $workPage);
          $workPage = $workPage + $jump;
        }

        if($workPage != ($currentPage - 2))
        {
          $placeHolderKey++;
          $data->pages[$placeHolderKey] = new JPaginationObject(JText::_('COM_JOOMGALLERY_COMMON_PAGENAVIGATION_ELLIPSIS'), $this->prefix);
          $data->pages[$placeHolderKey]->base = null;
        }
        // Output of 2 pages left beside current page
        $data->pages[$currentPage - 2] = new JPaginationObject($currentPage - 2, $this->prefix);
        $data->pages[$currentPage - 2]->base = $currentPage - 2;
        $data->pages[$currentPage - 2]->link = JRoute::_($params.'&'.$this->prefix.'page='.($currentPage - 2)).$this->anchortag.sprintf($this->onclick, $currentPage - 2);
        $data->pages[$currentPage - 1] = new JPaginationObject($currentPage - 1, $this->prefix);
        $data->pages[$currentPage - 1]->base = $currentPage - 1;
        $data->pages[$currentPage - 1]->link = JRoute::_($params.'&'.$this->prefix.'page='.($currentPage - 1)).$this->anchortag.sprintf($this->onclick, $currentPage - 1);
      }

      // Current page
      $data->pages[$currentPage] = new JPaginationObject($currentPage, $this->prefix);
      $data->pages[$currentPage]->base = null;
      $data->pages[$currentPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$currentPage).$this->anchortag.sprintf($this->onclick, $currentPage);
      $currItemfound = true;
      $workPage = $currentPage;
    }

    // Current page found, right beside construct 2 pages
    // max to end
    if($pageCount - $workPage < 3)
    {
      $endRangecount = $pageCount - $workPage;
    }
    else
    {
      $endRangecount = 2;
    }

    $workPage++;
    for($i = 1; $i <= $endRangecount; $i++)
    {
      $data->pages[$workPage] = new JPaginationObject($workPage, $this->prefix);
      $data->pages[$workPage]->base = $workPage;
      $data->pages[$workPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$workPage).$this->anchortag.sprintf($this->onclick, $workPage);
      $workPage++;
    }

    if($workPage == $pageCount)
    {
      $data->pages[$workPage] = new JPaginationObject($workPage, $this->prefix);
      $data->pages[$workPage]->base = $workPage;
      $data->pages[$workPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$workPage).$this->anchortag.sprintf($this->onclick, $workPage);

      $this->store = $data;

      return $data;
    }

    // All ready
    if($workPage > $pageCount)
    {
      $this->store = $data;

      return $data;
    }

    // If only 3 pages to end remain
    if($workPage < $pageCount && ($pageCount - $workPage) < 7)
    {
      for($i = $workPage; $i <= $pageCount; $i++)
      {
        $data->pages[$workPage] = new JPaginationObject($workPage, $this->prefix);
        $data->pages[$workPage]->base = $workPage;
        $data->pages[$workPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$workPage).$this->anchortag.sprintf($this->onclick, $workPage);
        $workPage++;
      }
    }
    else
    {
      // Output of remaining pages in steps
      // and in addition output of last page and the neighbour left
      // Complete the range (current page + 3) to (last page - 3)
      $startRange = $workPage;
      $endRange   = $pageCount - 3;
      $jump       = ceil(($endRange - $startRange) / 4);
      $workPage   = $workPage + $jump;
      for($i = 1; $i < 4; $i++)
      {
        if($jump != 1)
        {
          $placeHolderKey++;
          $data->pages[$placeHolderKey] = new JPaginationObject(JText::_('COM_JOOMGALLERY_COMMON_PAGENAVIGATION_ELLIPSIS'), $this->prefix);
          $data->pages[$placeHolderKey]->base = null;
        }

        $data->pages[$workPage] = new JPaginationObject($workPage, $this->prefix);
        $data->pages[$workPage]->base = $workPage;
        $data->pages[$workPage]->link = JRoute::_($params.'&'.$this->prefix.'page='.$workPage).$this->anchortag.sprintf($this->onclick, $workPage);
        $workPage  = $workPage + $jump;
      }

      $placeHolderKey++;
      $data->pages[$placeHolderKey] = new JPaginationObject(JText::_('COM_JOOMGALLERY_COMMON_PAGENAVIGATION_ELLIPSIS'), $this->prefix);
      $data->pages[$placeHolderKey]->base = null;

      // Output of penultimate and last
      $data->pages[$pageCount - 1] = new JPaginationObject($pageCount - 1, $this->prefix);
      $data->pages[$pageCount - 1]->base = $pageCount - 1;
      $data->pages[$pageCount - 1]->link = JRoute::_($params.'&'.$this->prefix.'page='.($pageCount - 1)).$this->anchortag.sprintf($this->onclick, $pageCount - 1);
      $data->pages[$pageCount] = new JPaginationObject($pageCount, $this->prefix);
      $data->pages[$pageCount]->base = $pageCount;
      $data->pages[$pageCount]->link = JRoute::_($params.'&'.$this->prefix.'page='.$pageCount).$this->anchortag.sprintf($this->onclick, $pageCount);
    }

    $this->store = $data;

    return $data;
  }
}