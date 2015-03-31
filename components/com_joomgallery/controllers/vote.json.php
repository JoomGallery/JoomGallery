<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/vote.json.php $
// $Id: vote.json.php 4077 2013-02-12 10:46:13Z erftralle $
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

jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/controller.php';

/**
 * JoomGallery JSON Vote Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerVote extends JoomGalleryController
{
  /**
   * Votes an image via AJAX
   *
   * @return  void
   * @since   2.1
   */
  public function vote()
  {
    require_once JPATH_ADMINISTRATOR.'/components/com_languages/helpers/jsonresponse.php';

    $response = array();

    $model = $this->getModel('vote');

    if(!$model->vote())
    {
      echo new JJsonResponse(new Exception($model->getError()));
    }
    else
    {
      // HTML for updating the rating
      JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html');
      $response['rating'] = JHtml::_('joomgallery.rating', $model->getRating(), true, 'jg_starrating_detail', 'hasHintAjaxVote');

      // Set CSS tooltip class in case of star rating
      $response['tooltipclass'] = '';
      if($this->_config->get('jg_ratingdisplaytype') == 1)
      {
        if($this->_config->get('jg_tooltips') == 2)
        {
          $response['tooltipclass'] = 'jg-tooltip-wrap';
        }
        else
        {
          if($this->_config->get('jg_tooltips') == 1)
          {
            $response['tooltipclass'] = 'default';
          }
        }
      }

      echo new JJsonResponse($response, JText::_('COM_JOOMGALLERY_DETAIL_RATINGS_MSG_YOUR_VOTE_COUNTED'));
    }
  }
}