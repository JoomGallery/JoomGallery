<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/controllers/categories.json.php $
// $Id: categories.json.php 4331 2013-09-08 08:27:42Z erftralle $
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
 * JoomGallery Categories JSON Controller
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomGalleryControllerCategories extends JControllerLegacy
{
  /**
   * Outputs a result set of allowed categories for a certain action in JSON format
   *
   * @return  void
   * @since   2.1
   */
  public function getCategories()
  {
    require_once JPATH_ADMINISTRATOR.'/components/com_languages/helpers/jsonresponse.php';

    $model = $this->getModel('categories');

    $action     = $this->input->getCmd('action');
    $filter     = $this->input->getInt('filter');
    $search     = $this->input->getString('searchstring');
    $limitstart = $this->input->getInt('more');
    $current    = $this->input->getInt('current');

    echo new JJsonResponse($model->getAllowedCategories($action, $filter, $search, $limitstart, $current));
  }

  /**
   * Method to save the submitted ordering values for records via AJAX.
   *
   * @return  void
   * @since   3.0
   */
  public function saveOrder()
  {
    require_once JPATH_ADMINISTRATOR.'/components/com_languages/helpers/jsonresponse.php';

    JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

    // Get the arrays from the request
    $pks            = $this->input->post->get('cid',	null, 'array');
    $order          = $this->input->post->get('order',	null, 'array');
    $originalOrder  = explode(',', $this->input->getString('original_order_values'));

    // Make sure something has changed
    if($order !== $originalOrder)
    {
      // Create and load the categories table object
      $table = JTable::getInstance('joomgallerycategories', 'Table');

      if($table->saveorder($pks, $order))
      {
        echo new JJsonResponse();
      }
    }
  }

  /**
   * Unlocks a password protected category
   *
   * @return  void
   * @since   3.1
   */
  public function unlock()
  {
    require_once JPATH_ADMINISTRATOR.'/components/com_languages/helpers/jsonresponse.php';

    $model = $this->getModel('category');

    try
    {
      $model->unlock($input->getInt('catid'), $this->input->getString('password'));

      echo new JJsonResponse();
    }
    catch(Exception $e)
    {
      echo new JJsonResponse($e);
    }
  }
}