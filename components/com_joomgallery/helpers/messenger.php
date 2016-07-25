<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/components/com_joomgallery/helpers/messenger.php $
// $Id: messenger.php 4250 2013-05-02 16:49:22Z chraneco $
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
 * JoomGallery Messenger
 *
 * Sends all kind of messages in the gallery.
 * If a message is going to be send as a personal message
 * the event 'onJoomBeforeSendMessage' will be triggered afore.
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class JoomMessenger extends JObject
{
  /**
   * Message data array
   *
   * @var array
   */
  protected $_message = array();

  /**
   * Recipients
   *
   * @var array
   */
  protected $_recipients = array();

  /**
   * All available modes
   *
   * @var     array
   */
  protected $_modes = array();

  /**
   * Current mode
   *
   * @var string
   */
  protected $_mode = null;

  /**
   * Current type
   *
   * @var int
   */
  public $type = 0;

  /**
   * Indicates whether the user names or the real
   * names of user should be used in the messages
   *
   * @var   boolean
   * @since 2.1
   */
  protected $realname = true;

  /**
   * Indicates whether only the global sender mail address
   * and name of the system should be used for mails
   *
   * @var   boolean
   * @since 3.1
   */
  protected $globalfrom = false;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.5
   */
  public function __construct()
  {
    parent::__construct();

    $config = JoomConfig::getInstance();

    $this->realname   = $config->get('jg_realname') ? true : false;
    $this->globalfrom = $config->get('jg_msg_global_from') ? true : false;

    // Predefined message send modes
    $this->addMode( array('name'        => 'upload',
                          'recipients'  => explode(',', $config->get('jg_msg_upload_recipients')),
                          'type'        => $config->get('jg_msg_upload_type')
                          )
                  );
    $this->addMode( array('name'        => 'download',
                          'recipients'  => explode(',', $config->get('jg_msg_download_recipients')),
                          'type'        => $config->get('jg_msg_download_type')
                          )
                  );
    $this->addMode( array('name'        => 'zipdownload',
                          'recipients'  => explode(',', $config->get('jg_msg_download_recipients')),
                          'type'        => $config->get('jg_msg_download_type')
                          )
                  );
    $this->addMode( array('name'        => 'comment',
                          'recipients'  => explode(',', $config->get('jg_msg_comment_recipients')),
                          'type'        => $config->get('jg_msg_comment_type')
                          )
                  );
    $this->addMode( array('name'        => 'nametag',
                          'recipients'  => explode(',', $config->get('jg_msg_nametag_recipients')),
                          'type'        => $config->get('jg_msg_nametag_type')
                          )
                  );
    $this->addMode( array('name'        => 'report',
                          'recipients'  => explode(',', $config->get('jg_msg_report_recipients')),
                          'type'        => $config->get('jg_msg_report_type')
                          )
                  );
    $this->_modes['send2friend']['recipients']  = array();
    $this->_modes['send2friend']['type']        = 1;

    $this->_modes['rejectimg']['recipients']    = array();
    $this->_modes['rejectimg']['type']          = $config->get('jg_msg_rejectimg_type');

    $this->_modes['default']    ['recipients']  = array();
    $this->_modes['default']    ['type']        = 2;
  }

  /**
   * Method to send a message
   *
   * <pre>
   * $message = array(
   *                    'recipient' => (int/string) 65 (user ID) || 'localhost@localhost.de (address)
   *                    'from'      => (int/string) 65 (user ID) || 'localhost@localhost.de (address)
   *                    'fromname'  => (string)     'Username'
   *                    'subject'   => (string)     'Subject line'
   *                    'body'      => (string)     'Message'
   *                    'mode'      => (string)     'upload' || 'comment' || 'send2friend' || ...
   *                    'type'      => (int)        0 (global setting according to mode) || 1 (mail) || 2 (msg) || 3 (both)
   *                  );
   * </pre>
   *
   * @param   array   $message  Array which holds the message data
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function send($message)
  {
    if(!$this->_loadMessage($message))
    {
      return false;
    }

    // Send message depending of the selected type
    $result_array = array();
    if($this->type == 1 || $this->type == 3)
    {
      $result_array[] = $this->_sendMail();
    }
    if($this->type == 2 || $this->type == 3)
    {
      $result_array[] = $this->_sendMsg();
    }

    if(in_array(false, $result_array, true))
    {
      return false;
    }

    return true;
  }

  /**
   * Method to add one ore more recipients for the next delivery
   *
   * @param   array   $recipients An array of recipients or a single one as a string
   * @return  void
   * @since   1.5.5
   */
  public function addRecipients($recipients)
  {
    if(is_array($recipients))
    {
      $this->_recipients    = array_merge($this->_recipients, $recipients);
    }
    else
    {
      $this->_recipients[]  = $recipients;
    }
  }

  /**
   * Method to add a message send mode
   *
   * @param   array   $mode Holds the data of the additional mode
   * @return  boolean True on success, false otherwise
   * @since   1.5.5
   */
  public function addMode($mode)
  {
    // Only add mode if sufficient information is given
    if(     !isset($mode['name'])       || !($mode['name'])
        ||  !isset($mode['recipients']) || !is_array($mode['recipients'])
        ||  !isset($mode['type'])       || !is_numeric($mode['type'])
      )
    {
      return false;
    }

    if(   !count($mode['recipients'])
      ||  ($mode['recipients'][0] == '-1' && count($mode['recipients']) == 1)
      )
    {
      // If array is empty or first element of array is '-1' (Default Recipients)
      // we will add all users who have set 'Receive System E-mails' to 'Yes'
      static $recipients;

      if(empty($recipients))
      {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
              ->select('id')
              ->from('#__users')
              ->where('sendEmail = 1');
        $db->setQuery($query);
        $recipients = $db->loadColumn();
      }

      $mode['recipients'] = $recipients;
    }
    else
    {
      // If first element of array is 0, no certain
      // recipient shall get the message by default
      // Please add recipients while preparing the message then
      if($mode['recipients'][0] == 0 && count($mode['recipients']) == 1)
      {
        $mode['recipients'] = array();
      }
    }

    $name = $mode['name'];
    unset($mode['name']);
    $this->_modes[$name] = $mode;

    return true;
  }

  /**
   * Returns the data of a specific message send mode
   *
   * @param   array   $mode The key of the requested mode
   * @return  array   An array that holds the data of the requested mode
   * @since   1.5.5
   */
  public function getModeData($mode = 'default')
  {
    if(!isset($this->_modes[$mode]))
    {
      $mode = 'default';
    }

    return $this->_modes[$mode];
  }

  /**
   * Returns the recipients which are currently selected.
   *
   * Merges the recipients of the currently selected mode and the additional
   * recipients added by the method addRecipients().
   *
   * @return  array   An array of recipients
   * @since   1.5.5
   */
  public function getRecipients()
  {
    return array_unique(array_merge($this->_modes[$this->_mode]['recipients'], $this->_recipients));
  }

  /**
   * Returns the type of a specific mode.
   *
   * @param   string  The mode of which the type is requested
   * @return  array   The requested type, 2 if $mode was false
   * @since   1.5.5
   */
  public function getType($mode = false)
  {
    if($mode)
    {
      $mode = $this->getModeData($mode);
      return $mode['type'];
    }
    else
    {
      return 2;
    }
  }

  /**
   * Checks if sufficent information are given to send a message
   * and prepares the message for getting sent.
   *
   * @param   array     $message  Array which should hold all information about the message.
   * @return  boolean   True if message may be sent, false otherwise.
   * @since   1.5.5
   */
  protected function _loadMessage($message)
  {
    if(     /*!isset($message['from'])
        ||*/  !isset($message['subject'])
        ||  !isset($message['body'])
        /*||  empty($message['from'])*/
        ||  !$message['subject']
        ||  !$message['body']
      )
    {
      JError::raiseNotice(500, 'Unsufficient Information to send message');
      return false;
    }

    if(isset($message['recipient']) && $message['recipient'])
    {
      $this->addRecipients($message['recipient']);
    }

    $this->_message = $message;
    $this->_subject = $this->_message['subject'];
    $this->_text    = $this->_message['body'];

    $this->_loadMode();

    return true;
  }

  /**
   * Loads the message send mode according to the loaded message.
   *
   * @return  void
   * @since   1.5.5
   */
  protected function _loadMode()
  {
    if(isset($this->_message['mode']))
    {
      $this->_mode = $this->_message['mode'];
    }
    else
    {
      $this->_mode = 'default';
    }

    if(!array_key_exists($this->_mode, $this->_modes))
    {
      JError::raiseError(500, 'Unknown JoomGallery send message mode');
    }

    if(isset($this->_message['type']) && $this->_message['type'])
    {
      $this->type = $this->_message['type'];
    }
    else
    {
      $this->type = $this->getType($this->_mode);
    }
  }

  /**
   * Sends a message as an electronic mail.
   *
   * @return  booelean  True on success, JError object otherwise
   * @since   1.5.5
   */
  protected function _sendMail()
  {
    $from = null;
    if(!$this->globalfrom && isset($this->_message['from']))
    {
      if(is_numeric($this->_message['from']))
      {
        $user = JFactory::getUser($this->_message['from']);

        // Ensure that a valid user was selected
        if(is_object($user))
        {
          $from = $user->get('email');
        }
      }
      else
      {
        //if(JMailHelper::isEmailAddress($this->_message['from']))
        //{
          $from = $this->_message['from'];
        //}
      }
    }
    if(!$from)
    {
      $mainframe  = JFactory::getApplication('site');
      $from       = $mainframe->getCfg('mailfrom');
    }

    if($this->globalfrom || !isset($this->_message['fromname']) || !$this->_message['fromname'])
    {
      if(!isset($user) || !is_object($user))
      {
        $mainframe  = JFactory::getApplication('site');
        $fromname   = $mainframe->getCfg('fromname');
      }
      else
      {
        $fromname = $this->realname ? $user->get('name') : $user->get('username');
      }
    }
    else
    {
      $fromname = $this->_message['fromname'];
    }

    $recipients = array();
    foreach($this->getRecipients() as $recipient)
    {
      if(is_numeric($recipient))
      {
        $user = JFactory::getUser($recipient);

        // Ensure that a valid user was selected
        if(is_object($user))
        {
          $recipients[] = $user->get('email');
        }
      }
      else
      {
        $recipients[] = $recipient;
      }
    }

    // Remove duplicate values
    $recipients = array_unique($recipients);

    if(!count($recipients))
    {
      // If there aren't any recipients, there is nothing left to do
      return true;
    }

    $result = false;

    try
    {
      $mailer = JFactory::getMailer();

      $mailer->setSubject($this->_subject);
      $mailer->setBody($this->_text);
      $mailer->isHtml(false);
      $mailer->addBcc($recipients);
      $mailer->setSender(array($from, $fromname));

      $result = $mailer->Send();
    }
    catch(phpmailerException $ex)
    {
      return false;
    }

    if($result !== true)
    {
      return false;
    }

    return true;
  }

  /**
   * Sends a message as a personal message (PM).
   *
   * @return  booelean  True on success, false otherwise
   * @since   1.5.5
   */
  protected function _sendMsg()
  {
    $db = JFactory::getDBO();
    require_once JPATH_ADMINISTRATOR.'/components/com_messages/tables/message.php';

    $no_from = false;
    if(isset($this->_message['from']) && $this->_message['from'] && is_numeric($this->_message['from']))
    {
      $from = $this->_message['from'];
    }
    else
    {
      $no_from = true;

      // Try to get the ID of a super administrator
      $query = $db->getQuery(true)
            ->select('user_id')
            ->from('#__user_usergroup_map')
            ->where('group_id = 8');
      $db->setQuery($query);
      if(!$from = $db->loadResult())
      {
        $query->clear()
              ->select('id')
              ->from('#__users')
              ->order('id ASC');
        $db->setQuery($query);
        $from = $db->loadResult();
      }
    }

    $recipients = array();
    foreach($this->getRecipients() as $recipient)
    {
      if(is_numeric($recipient))
      {
        $recipients[] = $recipient;
      }
    }

    if(!count($recipients))
    {
      // If there aren't any recipients, there is nothing left to do
      return true;
    }

    // Messaging for new items
    JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_messages/models', 'MessagesModel');
    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_messages/tables');

    $result_array = array();
    foreach($recipients as $recipient)
    {
      $message = array( 'from'      => $from,
                        'recipient' => $recipient,
                        'subject'   => $this->_subject,
                        'text'      => $this->_text,
                        'mode'      => $this->_mode,
                        'system'    => $no_from
                      );

      // Send messages only if all plugins allow that
      // A plugin could disallow that if it sends the message via another system, for example.
      $plugins = JDispatcher::getInstance()->trigger('onJoomBeforeSendMessage', array($message));
      if(!in_array(false, $plugins, true))
      {
        $message['user_id_to']    = $recipient;
        $message['user_id_from']  = $from;
        $message['message']       = $this->_text;
        $msg = JModelLegacy::getInstance('Message', 'MessagesModel');
        $result_array[] =  $msg->save($message);
      }
    }

    if(in_array(false, $result_array))
    {
      JError::raiseNotice(500, $msg->getError());
      return false;
    }

    return true;
  }
}