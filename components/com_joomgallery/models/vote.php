<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/models/vote.php $
// $Id: vote.php 4077 2013-02-12 10:46:13Z erftralle $
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
 * JoomGallery Votes model
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomGalleryModelVote extends JoomGalleryModel
{
  /**
   * The ID of the image the vote belongs to
   *
   * @var int
   */
  protected $_id;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    $id = JRequest::getInt('id');
    $this->setId($id);
  }

  /**
   * Method to set the image I
   *
   * @param   int   $id The image ID
   * @since   1.5.5
   */
  public function setId($id)
  {
    // Set new image ID if valid
    if(!$id)
    {
      $this->_mainframe->redirect(JRoute::_('index.php?view=gallery', false), JText::_('COM_JOOMGALLERY_COMMON_NO_IMAGE_SPECIFIED'), 'notice');
    }
    $this->_id  = $id;
  }

  /**
   * Method to get the image ID
   *
   * @return  int   The image ID
   * @since   1.5.5
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * Method to vote an image
   *
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function vote()
  {
    // Check for hacking attempt
    $categories = $this->_ambit->getCategoryStructure();
    $query = $this->_db->getQuery(true)
          ->select('a.owner')
          ->from(_JOOM_TABLE_IMAGES.' AS a')
          ->leftJoin(_JOOM_TABLE_CATEGORIES.' AS c ON c.cid = a.catid')
          ->where('a.published  = 1')
          ->where('a.approved   = 1')
          ->where('a.id         = '.$this->_id)
          ->where('a.access     IN ('.implode(',', $this->_user->getAuthorisedViewLevels()).')')
          ->where('c.cid        IN ('.implode(',', array_keys($categories)).')');
    $this->_db->setQuery($query);
    $owner = $this->_db->loadResult();
    if(is_null($owner) || ($this->_config->get('jg_votingonlyreg') && !$this->_user->get('id')))
    {
      $this->setError('Stop Hacking attempt!');

      return false;
    }

    // No votes from image owner allowed
    if($this->_config->get('jg_votingonlyreg') && $this->_user->get('id') == $owner)
    {
      $this->setError(JText::_('COM_JOOMGALLERY_DETAIL_RATING_NOT_ON_OWN_IMAGES'));

      return false;
    }

    $vote = JRequest::getInt('imgvote');

    // Check if vote was manipulated with modifying the HTML code
    if($vote < 1 || $vote > $this->_config->get('jg_maxvoting'))
    {
      $this->setError('Stop Hacking attempt!');

      return false;
    }

      // Get voted or not
    if($this->_config->get('jg_votingonlyreg'))
    {
      // Check whether the user already voted on that image
      $query->clear()
            ->select('COUNT(*)')
            ->from(_JOOM_TABLE_VOTES)
            ->where('userid  = '.$this->_user->get('id'))
            ->where('picid   = '.$this->_id);
      $this->_db->setQuery($query);

      // Vote or enqueue notice
      if($this->_db->loadResult())
      {
        $this->setError(JText::_('COM_JOOMGALLERY_DETAIL_RATINGS_MSG_YOUR_VOTE_NOT_COUNTED'));

        return false;
      }
    }
    else
    {
      if($this->_config->get('jg_votingonlyonce'))
      {
        // Check whether there was already a vote for that image
        // from the same IP address during the last 24 hours
        $query->clear()
              ->select('COUNT(*)')
              ->from(_JOOM_TABLE_VOTES)
              ->where('userip  = '.$this->_db->q($_SERVER['REMOTE_ADDR']))
              ->where('picid   = '.$this->_id)
              ->where('datevoted > DATE_SUB(NOW(), INTERVAL 24 HOUR)');
        $this->_db->setQuery($query);

        // Vote or enqueue notice
        if($this->_db->loadResult())
        {
          $this->setError(JText::_('COM_JOOMGALLERY_DETAIL_RATINGS_MSG_YOUR_VOTE_NOT_COUNTED'));

          return false;
        }
      }
    }

    // Get old values from database
    $query->clear()
          ->select('imgvotes, imgvotesum')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('id = '.$this->_id);
    $this->_db->setQuery($query);
    $row = $this->_db->loadObject();

    // Recalculate with the new vote
    $row->imgvotes++;
    $row->imgvotesum = $row->imgvotesum + $vote;

    // Trigger event 'onJoomBeforeVote'
    $plugins  = $this->_mainframe->triggerEvent('onJoomBeforeVote', array(&$row, $vote));
    if(in_array(false, $plugins, true))
    {
      return false;
    }

    // Save new values
    $query->clear()
          ->update(_JOOM_TABLE_IMAGES)
          ->set('imgvotes   = '.$row->imgvotes)
          ->set('imgvotesum = '.$row->imgvotesum)
          ->where('id = '.$this->_id);
    $this->_db->setQuery($query);
    if(!$this->_db->query())
    {
      $this->setError($this->_db->getErrorMsg());

      return false;
    }

    // Store log of vote
    $row  = $this->getTable('joomgalleryvotes');
    $date = JFactory::getDate();

    $row->picid     = $this->_id;
    $row->userid    = $this->_user->get('id');
    $row->userip    = $_SERVER['REMOTE_ADDR'];
    $row->datevoted = $date->toSQL();
    $row->vote      = $vote;

    if(!$row->store())
    {
      $this->setError($row->getError());

      return false;
    }

    $this->_mainframe->triggerEvent('onJoomAfterVote', array($row, $vote));

    return true;
  }

  /**
   * Get new rating for the image voted to refresh detail view
   *
   * @return  object  Holds the image rating data
   * @since   2.1
   */
  public function getRating()
  {
    $query = $this->_db->getQuery(true)
          ->select('imgvotes, imgvotesum, '.JoomHelper::getSQLRatingClause().' AS rating')
          ->from(_JOOM_TABLE_IMAGES)
          ->where('id = '.$this->_id);
    $this->_db->setQuery($query);

    return $this->_db->loadObject();
  }
}