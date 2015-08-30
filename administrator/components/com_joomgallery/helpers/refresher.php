<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/refresher.php $
// $Id: refresher.php 4290 2013-05-29 00:02:48Z chraneco $
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
 * Helper class which handles refreshes afore script timeouts
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomRefresher extends JObject
{
  /**
   * Unix timestamp of start time
   *
   * @var int
   */
  protected $_starttime;

  /**
   * Maximum time for execution in seconds
   *
   * @var int
   */
  protected $_maxtime;

  /**
   * JApplication object
   *
   * @var object
   */
  protected $_mainframe;

  /**
   * Controller which will be included
   * into the URL for redirection by default
   *
   * @var string
   */
  protected $_controller;

  /**
   * Task which will be included into
   * the URL for redirection by default
   *
   * @var string
   */
  protected $_task;

  /**
   * Determines whether a message should
   * be displayed for each redirect by default
   *
   * @var boolean
   */
  protected $_msg;

  /**
   * Holds the number of remaining things to do
   *
   * @var int
   */
  protected $_remaining;

  /**
   * Holds the total number of things to do
   *
   * @var int
   */
  protected $_total;

  /**
   * Determines whether the progress bar should be displayed
   *
   * @var boolean
   */
  protected $_showprogress;

  /**
   * Holds the name or a short description of the current task
   *
   * @var string
   */
  protected $_name;

  /**
   * Constructor
   *
   * @param   array $params An array with optional parameters
   * @return  void
   * @since   1.5.5
   */
  public function __construct($params = array())
  {
    $this->_mainframe = JFactory::getApplication('administrator');

    if(isset($params['controller']))
    {
      $this->_controller    = $params['controller'];
    }
    else
    {
      $this->_controller    = JRequest::getCmd('controller');
    }

    if(isset($params['task']))
    {
      $this->_task          = $params['task'];
    }
    else
    {
      $this->_task          = JRequest::getCmd('task');
    }

    if(isset($params['msg']))
    {
      $this->_msg           = $params['msg'];
    }
    else
    {
      $this->_msg           = false;
    }

    if(isset($params['remaining']))
    {
      $this->_remaining     = $params['remaining'];

      if(isset($params['start']) && $params['start'])
      {
        $this->_total       = $params['remaining'];
        $this->_mainframe->setUserState('joom.refresher.total', $this->_total);
      }
      else
      {
        $this->_total       = $this->_mainframe->getUserState('joom.refresher.total');
      }

      $this->_showprogress  = $this->_total ? true : false;
    }

    if(isset($params['name']) && $params['name'])
    {
      $this->_name          = $params['name'];
    }

    $this->init();
  }

  /**
   * Initializes the refresher by storing current time
   *
   * @return  void
   * @since   1.5.5
   */
  public function init()
  {
    // Check the maximum execution time of the script
    $max_execution_time = @ini_get('max_execution_time');

    // Set secure setting of the real execution time
    // Maximum time for the script will be set to 20 seconds
    // (max_exection_time = 0 means no limit)
    if($max_execution_time < 25 && $max_execution_time != 0)
    {
      $this->_maxtime = (int) $max_execution_time * 0.8;
    }
    else
    {
      $this->_maxtime = 20;
    }

    $this->_starttime = time();
  }

  /**
   * Returns the calculated maximum execution time
   *
   * @return  int The calculated maximum execution time
   * @since   3.2
   */
  public function getMaxTime()
  {
    return $this->_maxtime;
  }

  /**
   * Resets the progressbar or the name of the current task
   *
   * @param   int     $remaining  Number of remaining steps
   * @param   boolean $start      Determines whether $remaining holds the total number of steps
   * @param   string  $name       Name of the task to display
   * @return  void
   * @since   1.5.6
   */
  public function reset($remaining = null, $start = null, $name = null)
  {
    if(!is_null($remaining))
    {
      $this->_remaining     = $remaining;

      if(!is_null($start) && $start)
      {
        $this->_total       = $remaining;
        $this->_mainframe->setUserState('joom.refresher.total', $this->_total);
      }
      else
      {
        $this->_total       = $this->_mainframe->getUserState('joom.refresher.total');
      }

      $this->_showprogress  = $this->_total ? true : false;
    }
    else
    {
      $this->_showprogress  = false;
    }

    if(!is_null($name) && $name)
    {
      $this->_name          = $name;
    }
  }

  /**
   * Checks the remaining time
   *
   * @return  boolean True: Time remains, false: No more time left
   * @since   1.5.5
   */
  public function check()
  {
    $timeleft = -(time() - $this->_starttime - $this->_maxtime);
    if($timeleft > 3)
    {
      return true;
    }

    return false;
  }

  /**
   * Make a redirect
   *
   * @param   int     $remaining  Number of remaining steps
   * @param   string  $task       The task which will be called after the redirect
   * @param   string  $msg        An optional message which will be enqueued (this is currently disabled)
   * @param   string  $type       Type of the message (one of 'message', 'notice', 'error')
   * @param   string  $controller The controller which will be called after the redirect
   * @return  void
   * @since   1.5.5
   */
  public function refresh($remaining = null, $task = null, $msg = null, $type = null, $controller = null)
  {
    if($remaining)
    {
      $this->_remaining = $remaining;
    }

    if($this->_msg && is_null($task))
    {
      $this->_mainframe->enqueueMessage(JText::_('COM_JOOMGALLERY_COMMON_REDIRECT'));
    }
    if(!$task)
    {
      $task = $this->_task;
    }
    if(!$controller)
    {
      $controller = $this->_controller;
    }

    if($msg)
    {
      $this->_mainframe->enqueueMessage($msg, $type);
    }

    if(!$this->_msg || $msg)
    {
      // Persist messages if they exist
      $messages = $this->_mainframe->getMessageQueue();
      if(count($messages))
      {
        $session = JFactory::getSession();
        $session->set('application.queue', $messages);
      }
    }

    $url = 'index.php?option='._JOOM_OPTION.'&controller='.$controller.'&task='.$task;
    $onclick = 'document.location.href=\''.$url.'\';';

    $buffer = '';
    if($buffer = ob_get_contents())
    {
      ob_end_clean();
    }

    echo '<?xml version="1.0" encoding="utf-8"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <!--<link rel="stylesheet" href="<?php echo JoomAmbit::getInstance()->getStyleSheet('admin.joomgallery.css'); ?>" type="text/css" />
    <link href="templates/<?php echo $this->_mainframe->getTemplate(); ?>/css/template.css" rel="stylesheet" type="text/css" />-->
    <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap-extended.css" rel="stylesheet" type="text/css" />
    <?php if(JFactory::getLanguage()->isRTL()): ?>
    <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap-rtl.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>
    <style type="text/css">
      body{
        margin-top:30px;
      }
<?php if($this->_showprogress): ?>
      .bar{
        width:<?php echo floor((($this->_total - $this->_remaining) / $this->_total) * 100); ?>% !important;
      }
<?php endif; ?>
    </style>
    <title><?php echo JText::_('COM_JOOMGALLERY_COMMON_REFRESHER_IN_PROGRESS'); ?></title>
  </head>
  <body>
    <div class="row-fluid">
      <div class="offset2 span8 well">
        <div class="alert alert-info">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_REFRESHER_PLEASE_WAIT'); ?>
<?php if($this->_name): ?>
          <br />
          <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_REFRESHER_CURRENT_TASK', '<span style="color:green;">'.$this->_name.'</span>'); ?>
<?php endif; ?>
        </div>
        <p class="center"><img src="<?php echo JUri::root(); ?>media/system/images/modal/spinner.gif" alt="Spinner" width="16" height="16" /></p>
<?php if($this->_showprogress): ?>
        <div class="progress progress-striped active" title="<?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_REFRESHER_PROGRESSBAR', $this->_maxtime); ?>">
          <div class="bar"></div>
        </div>
        <div class="small muted center"><?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_REFRESHER_PROGRESSBAR', $this->_maxtime); ?></div>
<?php endif;
      if($this->_msg):
        $doc = JFactory::getDocument();
        $renderer = $doc->loadRenderer('message');
        echo $renderer->render('');
      endif; ?>
      </div>
    </div>
    <div><?php echo $buffer; ?></div>
    <script type="text/javascript"><?php echo $onclick; ?></script>
  </body>
</html><?php
    $this->_mainframe->close();
  }
}