<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/upload.php $
// $Id: upload.php 4331 2013-09-08 08:27:42Z erftralle $
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
 * JoomGallery frontend upload model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelUpload extends JoomGalleryModel
{
  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Returns the number of images of the current user
   *
   * @return  int     The number of images of the current user
   * @since   1.5.5
   */
  public function getImageNumber()
  {
    $query = $this->_db->getQuery(true)
          ->select('COUNT(id)')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('owner = '.$this->_user->get('id'));

    $timespan = $this->_config->get('jg_maxuserimage_timespan');
    if($timespan > 0)
    {
      $query->where('imgdate > (UTC_TIMESTAMP() - INTERVAL '. $timespan .' DAY)');
    }

    $this->_db->setQuery($query);

    return $this->_db->loadResult();
  }

  /**
   * Returns the default user upload category
   *
   * @return  int     The default user upload category
   * @since   3.0
   */
  public function getDefaultUserUploadCategory()
  {
    $defaultcat = 0;

    if($this->_config->get('jg_useruploaddefaultcat'))
    {
      $cats = $this->_ambit->getCategoryStructure(true);

      $newestcat   = 0;
      $oldestcat   = PHP_INT_MAX;
      $owncatsonly = $this->_config->get('jg_useruploaddefaultcat') > 2 ? true : false;

      foreach($cats as $cat)
      {
        if(!$owncatsonly || ($owncatsonly && $cat->owner == $this->_user->get('id')))
        {
          if(    (    $this->_user->authorise('joom.upload.inown', _JOOM_OPTION.'.category.'.$cat->cid)
                    && $cat->owner
                    && $cat->owner == $this->_user->get('id')
                 )
              || $this->_user->authorise('joom.upload', _JOOM_OPTION.'.category.'.$cat->cid)
            )
          {
            if($cat->cid > $newestcat)
            {
              $newestcat = $cat->cid;
            }
            if($cat->cid < $oldestcat)
            {
              $oldestcat = $cat->cid;
            }
          }
        }
      }

      switch($this->_config->get('jg_useruploaddefaultcat'))
      {
        case 1:
        case 3:
          if($oldestcat < PHP_INT_MAX)
          {
            $defaultcat = $oldestcat;
          }
          break;
        case 2:
        case 4:
          if($newestcat)
          {
            $defaultcat = $newestcat;
          }
          break;
        default:
          break;
      }
    }

    return $defaultcat;
  }

  /**
   * Returns the redirect URL configured in configuration manager the user should be directed too
   * after an successful image upload.
   *
   * @param  string  $uploadType  Upload type (e.g. single, ajax, java, batch)
   * @param  string  $defaultType Upload view to redirect too if no redirect url is configured, defaults to null
   * @param  boolean $xhtml       Replace & by &amp; for XML compilance.
   * @return string  The redirect URL
   * @since  3.0
   */
  public function getRedirectUrlAfterUpload($uploadType = 'single', $defaultType = null, $xhtml = false)
  {
    $url = '';

    // Set a redirect according to the correspondent setting in configuration manager
    switch($this->_config->get('jg_redirect_after_upload'))
    {
      case 1:
        $url = JRoute::_('index.php?view=upload&tab='.$uploadType, $xhtml);
        break;
      case 2:
        $url = JRoute::_('index.php?view=userpanel', $xhtml);
        break;
      case 3:
        $url = JRoute::_('index.php?view=gallery', $xhtml);
        break;
      default:
        if(!empty($defaultType))
        {
          $url = JRoute::_('index.php?view=upload&tab='.$defaultType, $xhtml);
        }
        break;
    }

    return $url;
  }
}