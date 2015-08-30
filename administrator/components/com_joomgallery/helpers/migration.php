<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/helpers/migration.php $
// $Id: migration.php 4278 2013-05-25 23:58:54Z chraneco $
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
 * Helper class for migration procedures
 *
 * @package JoomGallery
 * @since   1.5.5
 */
abstract class JoomMigration
{
  /**
   * The name of the log file
   *
   * @var   string
   * @since 1.5.5
   */
  protected $logfilename;

  /**
   * JDatabase object
   *
   * @var   object
   * @since 1.5.5
   */
  protected $_db;

  /**
   * JDatabase object for external database
   *
   * @var   object
   * @since 2.0
   */
  protected $_db2;

  /**
   * JApplication object
   *
   * @var   object
   * @since 1.5.5
   */
  protected $_mainframe;

  /**
   * JoomConfig object
   *
   * @var   object
   * @since 1.5.5
   */
  protected $_config;

  /**
   * JoomAmbit object
   *
   * @var   object
   * @since 1.5.5
   */
  protected $_ambit;

  /**
   * Determines whether this script is executed from the command line
   *
   * @var   boolean
   * @since 3.2
   */
  protected $isCli;

  /**
   * The name of the migration
   * (should be unique)
   *
   * @var   string
   * @since 1.5.5
   */
  protected $migration;

  /**
   * The new ID of a category
   * which original ID was 1
   *
   * @var   int
   * @since 2.0
   */
  protected $newCatid;

  /**
   * Determines whether the gallery
   * from which we are migrating uses
   * another database
   *
   * @var   boolean
   * @since 2.0
   */
  protected $otherDatabase = false;

  /**
   * Determines whether images should be copied instead of moved
   *
   * @var   boolean
   * @since 3.1
   */
  protected $copyImages = false;

  /**
   * Determines whether image and category owner IDs should
   * be checked for existence during migration
   *
   * @var   boolean
   * @since 3.1
   */
  protected $checkOwner = false;

  /**
   * Constructor
   *
   * @return  void
   * @since   1.5.0
   */
  public function __construct()
  {
    $this->_mainframe = JFactory::getApplication();
    $this->_db        = JFactory::getDbo();
    $this->_config    = JoomConfig::getInstance();
    $this->_ambit     = JoomAmbit::getInstance();

    $this->logfilename = 'migration.'.$this->migration.'.php';

    require_once JPATH_COMPONENT.'/helpers/refresher.php';

    $this->refresher = new JoomRefresher(array('task' => 'migrate&migration='.$this->migration));

    JLog::addLogger(array('text_file' => $this->logfilename, 'text_entry_format' => '{DATETIME}  {PRIORITY}  {MESSAGE}'), JLog::ALL, array('migration'.$this->migration));

    $this->newCatid = $this->_mainframe->getUserState('joom.migration.internal.new_catid', 1);

    $this->copyImages = $this->getStateFromRequest('copy_images', 'copy_images', $this->copyImages, 'boolean');
    $this->checkOwner = $this->getStateFromRequest('check_owner', 'check_owner', $this->checkOwner, 'boolean');

    $this->isCli      = $this->getStateFromRequest('is_cli', 'is_cli', $this->isCli, 'boolean');

    // Connect to second database if necessary
    $db = $this->getStateFromRequest('db2', 'db', array(), 'array');
    if(JArrayHelper::getValue($db, 'enabled', false, 'boolean'))
    {
      $driver   = JArrayHelper::getValue($db, 'db_type', 'mysqli', 'string');
      $host     = JArrayHelper::getValue($db, 'db_host', 'localhost', 'string');
      $name     = JArrayHelper::getValue($db, 'db_name', '', 'string');
      $user     = JArrayHelper::getValue($db, 'db_user', '', 'string');
      $password = JArrayHelper::getValue($db, 'db_pass', '', 'string');
      $prefix   = $this->getStateFromRequest('prefix', 'prefix', '', 'cmd');
      if(!$prefix)
      {
        $prefix   = JArrayHelper::getValue($db, 'prefix', '', 'cmd');
        $this->setState('prefix', $prefix);
      }

      $options	= array ('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $name, 'prefix' => $prefix);

      $this->_db2 = JDatabaseDriver::getInstance($options);

      $this->otherDatabase = true;
    }
    else
    {
      $this->_db2 = $this->_db;
    }
  }

  /**
   * Opens the log file and puts first comments into it.
   *
   * @return  boolean True on success, false if errors occurred.
   * @since   1.5.0
   */
  public function start()
  {
    $this->_mainframe->setUserState('joom.migration.internal', null);
    $this->writeLogfile('Migration Step started');
    $this->writeLogfile('max. execution time: '.@ini_get('max_execution_time').' seconds');
    $this->writeLogfile('calculated refresh time: '.$this->refresher->getMaxTime().' seconds');
    $this->writeLogfile('*****************************');

    try
    {
      $this->doMigration();
    }
    catch(Exception $e)
    {
      $this->setError($e->getMessage());
    }

    return $this->end();
  }

  /**
   * Continues the migration
   *
   * @return  boolean True on success, false if errors occurred.
   * since    2.0
   */
  public function migrate()
  {
    $this->writeLogfile('*****************************');
    $this->writeLogfile('Migration Step started');

    try
    {
      $this->doMigration();
    }
    catch(Exception $e)
    {
      $this->setError($e->getMessage());
    }

    return $this->end();
  }

  /**
   * Checks the remaining time of the current migration step
   *
   * @return  boolean True: Time remaining for migration, false: No more time left
   * @since   1.5.0
   */
  protected function checkTime()
  {
    return $this->refresher->check();
  }

  /**
   * Make a redirect to continue/end migration
   *
   * @param   string  $task Name of the task to continue with after the refresh
   * @return  void
   * @since   1.5.0
   */
  protected function refresh($task = null)
  {
    $this->_mainframe->setUserState('joom.migration.internal.task', $task);

    if($this->isCli)
    {
      // If executed from the command line it is not necessary to refresh
      return;
    }

    $this->writeLogfile('Refresh to continue the migration');
    $this->refresher->refresh();
  }

  /**
   * Puts last comments into the log file,
   * closes it and sets redirect with report of success.
   *
   * @return  boolean True on success, false if errors occurred.
   * @since   1.5.0
   */
  protected function end()
  {
    $this->writeLogfile('end of migration - exiting');
    $this->writeLogfile('*****************************');

    $msgType = 'message';
    $success = true;
    $errors = $this->_mainframe->getUserState('joom.migration.internal.errors');
    if($errors)
    {
      $this->writeLogfile('Errors recognized: '.$errors);
      $msg      = 'There were '.$errors.' error(s) during migration. Please have a look at the log file.';
      $msgType  = 'error';
      $success = false;
    }
    else
    {
      $msg      = 'Migration successfully ended';
    }

    $this->writeLogfile('Migration ended');

    if(!$this->isCli)
    {
      // Refreshing is only necessary if not executed from the command line
      $this->refresher->refresh(null, 'display', $msg, $msgType);
    }

    return $success;
  }

  /**
   * Writes a line into the logfile
   *
   * @param   string  $line     The line to write into the logfile
   * @param   int     $priority Determines whether the line is an info or an error message
   * @return  void
   * @since   1.5.0
   */
  protected function writeLogfile($line, $priority = JLog::INFO)
  {
    JLog::add($line, $priority, 'migration'.$this->migration);
  }

  /**
   * Increases the error counter and optionally appends an error message
   *
   * @param   string  $msg  An optional error message to write into the logfile
   * @param   boolean $db   True, if a DB-Error occured
   * @return  void
   * @since   1.5.0
   */
  protected function setError($msg = null, $db = false)
  {
    $error_counter = $this->_mainframe->getUserState('joom.migration.internal.errors');
    if(is_null($error_counter))
    {
      $error_counter = 1;
    }
    else
    {
      $error_counter++;
    }

    $this->_mainframe->setUserState('joom.migration.internal.errors', $error_counter);

    if(!is_null($msg))
    {
      if(!$db)
      {
        $this->writeLogfile('Error: '.$msg, JLog::ERROR);
      }
      else
      {
        $replace = array("\r\n", "\r", "\n", '              ');
        $msg = str_replace($replace, ' ', $msg);
        $this->writeLogfile('DB error: '.$msg, JLog::ERROR);
      }
    }
  }

  /**
   * Returns the current task of the migration.
   *
   * Please use this function and @see setTask(string) for managing different steps during the migration
   *
   * @param   string  $default  The default task to return if there isn't any task stored in the session
   * @return  The current task of the migration
   * @since   3.1
   */
  protected function getTask($default = null)
  {
    return $this->_mainframe->getUserState('joom.migration.internal.task', $default);
  }

  /**
   * Sets the current task of the migration in the session.
   *
   * Please use this function and @see getTask() for managing different steps during the migration
   *
   * @param   string  $task The task name to set
   * @return  The previous task if one existed
   * @since   3.1
   */
  protected function setTask($task)
  {
    return $this->_mainframe->setUserState('joom.migration.internal.task', $task);
  }

  /**
   * Returns a custom state stored in the session
   *
   * Please use this function and @see setState(string, mixed) for storing different states across site refreshes
   *
   * @param   string  $key      Name of the state to retrieve
   * @param   mixed   $default  The default state to return if it isn't stored in the session
   * @return  The requested state
   * @since   3.1
   */
  protected function getState($key, $default = null)
  {
    return $this->_mainframe->getUserState('joom.migration.data.'.$this->migration.'.'.$key, $default);
  }

  /**
   * Sets a custom state in the session.
   *
   * Please use this function and @see getState(string, mixed) for storing different states across site refreshes
   *
   * @param   string  $key  Name of the state to set
   * @param   mixed   $task The state to set
   * @return  The previous state if one existed
   * @since   3.1
   */
  protected function setState($key, $state)
  {
    return $this->_mainframe->setUserState('joom.migration.data.'.$this->migration.'.'.$key, $state);
  }

  /**
   * Gets the value of a state, first looking in the request for it
   *
   * Please use this function, @see getState(string, mixed) and @see setState(string, mixed) for storing different states across site refreshes
   *
   * @param   string  $key      The name of the state
   * @param   string  $request  The name of the variable passed in a request
   * @param   string  $default  The default value for the variable if not found
   * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}
   * @return  The requested state
   * @since   3.1
   */
  public function getStateFromRequest($key, $request, $default = null, $type = 'none')
  {
    $cur_state = $this->getState($key, $default);
    $new_state = $this->_mainframe->input->get($request, null, $type);

    // Save the new value only if it was set in this request
    if($new_state !== null)
    {
      $this->setState($key, $new_state);
    }
    else
    {
      $new_state = $cur_state;
    }

    return $new_state;
  }

  /**
   * Renders the form for configuring a migration using an XML file
   * which has the same name than the migration script
   *
   * @return  string  HTML of the rendered form
   * @since   3.1
   */
  public function getForm()
  {
    // Try to load language file of the migration script
    JFactory::getLanguage()->load('com_joomgallery.migrate'.$this->migration);

    // Prepare display data
    $displayData = new stdClass();
    $displayData->url = JRoute::_('index.php?option='._JOOM_OPTION.'&controller=migration&task=check');
    $displayData->migration = $this->migration;
    $displayData->fields = array();
    $displayData->description = '';

    JForm::addFormPath(JPATH_COMPONENT.'/helpers/migration/');
    JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
    JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');

    // Try to load additional form fields
    try
    {
      $form = JForm::getInstance(_JOOM_OPTION.'.migrate.'.$this->migration, 'migrate'.$this->migration);

      // Check for form field with name 'database' which means that fields for
      // the connection to a second database should be displayed
      $form->setFieldAttribute('database', 'id', 'database_'.$this->migration);
      if($databaseField = $form->getField('database'))
      {
        // Render the field now because it adds other form fields to the form
        // which has to happen afore all the fields are retrieved for displaying
        $databaseField->input;
      }

      $data = $this->_mainframe->getUserState('joom.migration.data.'.$this->migration, new stdClass());
      $data->check_owner = (int) $this->checkOwner;
      $data->copy_images = (int) $this->copyImages;
      $data->database = $this->otherDatabase;
      $data->db = array();
      $db = (array) $this->getState('db2', array());
      $data->db['enabled'] = $data->database;
      $data->db['db_type'] = JArrayHelper::getValue($db, 'db_type', 'mysqli', 'string');
      $data->db['db_host'] = JArrayHelper::getValue($db, 'db_host', 'localhost', 'string');
      $data->db['db_user'] = JArrayHelper::getValue($db, 'db_user', '', 'string');
      $data->db['db_pass'] = JArrayHelper::getValue($db, 'db_pass', '', 'string');
      $data->db['db_name'] = JArrayHelper::getValue($db, 'db_name', '', 'string');
      $data->db['prefix'] = $this->getState('prefix');
      $form->bind($data);

      $displayData->fields = $form->getGroup('');
      if(count($fieldsets = $form->getFieldsets()) && isset(reset($fieldsets)->description))
      {
        $displayData->description = JText::_(reset($fieldsets)->description);
      }
    }
    catch(Exception $e)
    {
      // Simply don't add any fields if there is no valid form
    }

    // Render the form
    $layout = new JLayoutFile('joomgallery.migration.form', JPATH_COMPONENT.'/layouts');

    return $layout->render($displayData);
  }

  /**
   * Checks general requirements for migration
   *
   * @param   string  $xml_dile     Path to the XML-File of the required extension
   * @param   string  $min_version  minimal required version, false if no check shall be performed
   * @param   string  $min_version  maximum possible version, false if no check shall be performed
   * @return  string  Message about state or boolean true or false.
   * @since   1.5.0
   */
  protected function checkGeneral($xml_file = null, $min_version = false, $max_version = false)
  {
    // Check extension
    if($xml_file)
    {
      if(!file_exists($xml_file))
      {
        return JText::_('COM_JOOMGALLERY_MIGMAN_EXTENSION_NOT_INSTALLED');
      }
      else
      {
        if($min_version || $max_version)
        {
          $xml = simplexml_load_file($xml_file);

          if(isset($xml->version))
          {
            $version = (string) $xml->version;
            if($min_version)
            {
              $comparision_min = version_compare($version, $min_version, '>=');
            }
            else
            {
              $comparision_min = true;
            }
            if($max_version)
            {
              $comparision_max = version_compare($version, $max_version, '<=');
            }
            else
            {
              $comparision_max = true;
            }
            if(!$comparision_min || !$comparision_max)
            {
              return JText::_('COM_JOOMGALLERY_MIGMAN_WRONG_VERSION');
            }
          }
        }
      }
    }

    // Check whether site is offline
    $sitestatus = $this->_mainframe->getCfg('offline');
    $displayData = new stdClass();
    $displayData->title = JText::_('COM_JOOMGALLERY_MIGMAN_SITESTATUS');
    $displayData->checks = array();
    $displayData->checks[] = array('title' => JText::_('COM_JOOMGALLERY_MIGMAN_SITE_OFFLINE'), 'state' => $sitestatus == 1);

    $layout = new JLayoutFile('joomgallery.migration.checksection', JPATH_COMPONENT.'/layouts');
    echo $layout->render($displayData);

    return $sitestatus == 1;
  }

  /**
   * Checks required directories for migration
   *
   * @param   array   $dirs Array of directories to search for
   * @return  boolean True if all directories are existent, false otherwise
   * @since   1.5.0
   */
  protected function checkDirectories($dirs = array())
  {
    // Add JoomGallery directories
    $joom_dirs  = array($this->_ambit->get('img_path'),
                        $this->_ambit->get('orig_path'),
                        $this->_ambit->get('thumb_path'));
    $dirs = array_merge($dirs, $joom_dirs);
    $ready = true;
    $displayData = new stdClass;
    $displayData->title = JText::_('COM_JOOMGALLERY_MIGMAN_DIRECTORIES');
    $displayData->checks = array();
    foreach($dirs as $dir)
    {
      $directory = array();
      $directory['title'] = $dir;
      $directory['state'] = true;
      if(!is_dir($dir))
      {
        $ready = false;
        $directory['state'] = false;
      }

      $displayData->checks[] = $directory;
    }

    // Check log directory and log file
    $log_dir  = JPath::clean($this->_mainframe->getCfg('log_path'));
    $check    = array('title' => JText::sprintf('COM_JOOMGALLERY_MIGMAN_LOG_DIRECTORY', $log_dir));
    $error    = false;
    $message  = '';
    if(is_dir($log_dir))
    {
      $log_file = JPath::clean($log_dir.'/'.$this->logfilename);
      if(is_file($log_file))
      {
        if(is_writable($log_file))
        {
          $message = JText::sprintf('COM_JOOMGALLERY_MIGMAN_LOG_FILE_IS_WRITABLE', $this->logfilename);
        }
        else
        {
          $error = true;
          $message = JText::sprintf('COM_JOOMGALLERY_MIGMAN_LOG_FILE_IS_NOT_WRITABLE', $this->logfilename);
        }
      }
      else
      {
        if(is_writable($log_dir))
        {
          $message = JText::sprintf('COM_JOOMGALLERY_MIGMAN_LOG_FILE_WILL_BE_CREATED', $this->logfilename);
        }
        else
        {
          $error = true;
          $message = JText::_('COM_JOOMGALLERY_MIGMAN_LOG_FILE_IS_NOT_WRITABLE');
        }
      }
    }
    else
    {
      $error = true;
    }

    if($error && $message)
    {
      $check['title'] .= ' <span style="color:#f30; font-weight:bold;">'.$message.'</span>';
    }
    else
    {
      if(!$error)
      {
        $check['title'] .= ' <span style="color:#080; font-weight:bold;">'.$message.'</span>';
      }
    }

    $check['state'] = !$error;
    $displayData->checks[] = $check;

    $layout = new JLayoutFile('joomgallery.migration.checksection', JPATH_COMPONENT.'/layouts');
    echo $layout->render($displayData);

    return $ready && !$error;
  }

  /**
   * Checks required database tables for migration
   *
   * @param   array   $tables Array of database tables to search for
   * @return  boolean True if all tables are existent, false otherwise
   * @since   1.5.0
   */
  protected function checkTables($tables = array())
  {
    $displayData = new stdClass();
    $displayData->title = JText::_('COM_JOOMGALLERY_MIGMAN_DATABASETABLES');

    $ready = false;

    if(!$this->otherDatabase || is_null($this->_db2))
    {
      $db = $this->_db;
    }
    else
    {
      $db = $this->_db2;
    }

    $displayData->checks = array();
    foreach($tables as $table)
    {
      $check = array();

      try
      {
        $query = $db->getQuery(true)
              ->select('COUNT(*)')
              ->from($table);
        $db->setQuery($query);
        
        $count = $db->loadResult();

        if($count == 0)
        {
          $check['title'] = $table.': <span style="color:#080; font-size:12px; font-weight:bold;">'.JText::_('COM_JOOMGALLERY_MIGMAN_EMPTY').'</span>';
          $check['state'] = true;
        }
        else
        {
          $check['title'] = $table.': <span style="color:#080; font-weight:bold;">'.$count .' '.JText::_('COM_JOOMGALLERY_MIGMAN_ROWS').'</span>';
          $check['state'] = true;
          $ready = true;
        }
      }
      catch(Exception $e)
      {
        $check['title'] = $table.': <span style="color:#f30; font-weight:bold;">'.$db->getErrorMsg().'</span>';
        $check['state'] = false;
      }

      $displayData->checks[] = $check;
    }

    // Check JoomGallery tables
    $tables = array(_JOOM_TABLE_IMAGES,
                    _JOOM_TABLE_CATEGORIES,
                    _JOOM_TABLE_COMMENTS,
                    _JOOM_TABLE_NAMESHIELDS,
                    _JOOM_TABLE_USERS,
                    _JOOM_TABLE_VOTES,
                    _JOOM_TABLE_IMAGE_DETAILS,
                    _JOOM_TABLE_CATEGORY_DETAILS);
    $prefix = $this->_mainframe->getCfg('dbprefix');
    foreach($tables as $table)
    {
      $check = array();
      if($table != _JOOM_TABLE_CATEGORIES)
      {
        $query = $this->_db->getQuery(true)
              ->select('COUNT(*)')
              ->from($this->_db->qn($table));
      }
      else
      {
        $query =$this->_db->getQuery(true)
              ->select('COUNT(*)')
              ->from($this->_db->qn(_JOOM_TABLE_CATEGORIES))
              ->where('cid != 1');
      }
      $this->_db->setQuery($query);
      $count = $this->_db->loadResult();
      if(!is_null($count) && $count == 0)
      {
        $check['title'] = str_replace('#__', $prefix, $table).': <span style="color:#080; font-size:12px; font-weight:bold;">'.JText::_('COM_JOOMGALLERY_MIGMAN_EMPTY').'</span>';
        $check['state'] = true;
      }
      else
      {
        $check['title'] = str_replace('#__', $prefix, $table).': <span style="color:#f30; font-weight:bold;">'.$count .' '.JText::_('COM_JOOMGALLERY_MIGMAN_ROWS').'. ';
        $check['title'] .= JText::_('COM_JOOMGALLERY_MIGMAN_ONLY_IN_NEW_INSTALLATION').'</span> '.JText::_('COM_JOOMGALLERY_MIGMAN_PLEASE_REINSTALL');
        $check['state'] = false;
        $ready = false;
      }

      $displayData->checks[] = $check;
    }

    // Check whether ROOT category exists
    $check = array();
    $query = $this->_db->getQuery(true)
          ->select('COUNT(*)')
          ->from($this->_db->qn(_JOOM_TABLE_CATEGORIES))
          ->where('cid = 1')
          ->where('name = '.$this->_db->q('ROOT'))
          ->where('parent_id = 0');
    $this->_db->setQuery($query);
    if($this->_db->loadResult())
    {
      $check['title'] = JText::_('COM_JOOMGALLERY_MIGMAN_ROOT_CATEGORY_EXISTS');
      $check['state'] = true;
    }
    else
    {
      $check['title'] = '<span style="color:#f30; font-weight:bold;">'.JText::_('COM_JOOMGALLERY_MIGMAN_ROOT_CATEGORY_DOES_NOT_EXIST').'</span> '.JText::_('COM_JOOMGALLERY_MIGMAN_PLEASE_REINSTALL');
      $check['state'] = false;
      $ready = false;
    }

    $displayData->checks[] = $check;

    // Check whether ROOT asset exists
    $check = array();
    $query = $this->_db->getQuery(true)
          ->select('COUNT(*)')
          ->from($this->_db->qn('#__assets'))
          ->where('name = '.$this->_db->q(_JOOM_OPTION))
          ->where('parent_id = 1');
    $this->_db->setQuery($query);
    if($this->_db->loadResult())
    {
      $check['title'] = JText::_('COM_JOOMGALLERY_MIGMAN_ROOT_ASSET_EXISTS');
      $check['state'] = true;
    }
    else
    {
      $check['title'] = '<span style="color:#f30; font-weight:bold;">'.Text::_('COM_JOOMGALLERY_MIGMAN_ROOT_ASSET_DOES_NOT_EXIST').'</span> '.JText::_('COM_JOOMGALLERY_MIGMAN_PLEASE_REINSTALL');
      $check['state'] = false;
      $ready = false;
    }

    $displayData->checks[] = $check;

    $layout = new JLayoutFile('joomgallery.migration.checksection', JPATH_COMPONENT.'/layouts');
    echo $layout->render($displayData);

    return $ready;
  }

  /**
   * Displays message whether migration can be started or not.
   * If yes, the button which starts the migration will be displayed, too.
   *
   * @param   boolean $ready  True, if the migration may be started
   * @return  void
   * @since   1.5.0
   */
  protected function endCheck($ready = false)
  {
    $displayData = new stdClass();
    $displayData->ready = $ready;
    $displayData->url = JRoute::_('index.php?option='._JOOM_OPTION.'&amp;controller=migration');
    $displayData->migration = $this->migration;

    $layout = new JLayoutFile('joomgallery.migration.checkend', JPATH_COMPONENT.'/layouts');
    echo $layout->render($displayData);
  }

  /**
   * Starts all default migration checks.
   *
   * If you want to add additional migration checks
   * you will have to call all check functions above manually.
   * Please don't forget to check whether they return 'true'.
   *
   * @param   array   $dirs         Array of directories to search for
   * @param   array   $tables       Array of database tables to search for
   * @param   string  $xml          Path to the XML-File of the required extension
   * @param   string  $min_version  minimal required version, false if no check shall be performed
   * @param   string  $min_version  maximum possible version, false if no check shall be performed
   * @return  void
   * @since   1.5.0
   */
  public function check($dirs = array(), $tables = array(), $xml = false, $min_version = false, $max_version = false)
  {
    // Check for correct connection to second database if necessary
    if($this->otherDatabase)
    {
      try
      {
        $this->_db2->connect();
      }
      catch(Exception $e)
      {
        $this->_mainframe->redirect('index.php?option='._JOOM_OPTION.'&controller=migration', $e->getMessage(), 'error');
      }
    }

    $layout = new JLayoutFile('joomgallery.migration.checkstart', JPATH_COMPONENT.'/layouts');
    echo $layout->render(null);

    $ready    = array();
    $ready[]  = $this->checkGeneral($xml, $min_version, $max_version);
    if($ready[0] !== true && $ready[0] !== false)
    {
      $this->_mainframe->redirect('index.php?option='._JOOM_OPTION.'&controller=migration', $ready[0], 'notice');
    }
    $ready[]  = $this->checkDirectories($dirs);
    $ready[]  = $this->checkTables($tables);
    $this->endCheck(!in_array(false, $ready));
  }

  /**
   * Main migration function
   *
   * @return  void
   * @since   1.5.0
   */
  abstract protected function doMigration();

  /**
   * Returns the maximum category ID of the extension to migrate from.
   *
   * This is necessary because in JoomGallery there can't be any category
   * with ID 1, so we have to look for a new one.
   *
   * @return  int   The maximum category ID of the extension to migrate from
   * @since   2.0
   */
  abstract protected function getMaxCategoryId();

  /**
   * Creates directories and the database entry for a category
   *
   * @param   object  $cat        Holds information about the new category
   * @param   boolean $checkOwner Determines whether the owner ID shall be checked against the existing users
   * @return  boolean True on success, false otherwise
   * @since   1.5.0
   */
  public function createCategory($cat, $checkOwner = null)
  {
    jimport('joomla.filesystem.file');

    if(is_null($checkOwner))
    {
      $checkOwner = $this->checkOwner;
    }

    // Some checks
    if(!isset($cat->cid))
    {
      $this->setError('Invalid category ID');

      return false;
    }
    if(!isset($cat->name))
    {
      $cat->name = 'no cat name';
    }
    if(!isset($cat->alias))
    {
      // Will be created later on
      $cat->alias = '';
    }
    if(!isset($cat->parent_id) || $cat->parent_id < 0)
    {
      $cat->parent_id = 1;
    }
    else
    {
      // If category with parent category ID 1 comes in we have
      // to set the parent ID to the newly created one because
      // category with ID 1 is the ROOT category in JoomGallery
      if($cat->parent_id == 1)
      {
        $cat->parent_id = $this->newCatid;
      }

      // Main categories are children of the ROOT category
      if($cat->parent_id == 0)
      {
        $cat->parent_id = 1;
      }
    }
    if(!isset($cat->description))
    {
      $cat->description = '';
    }
    if(!isset($cat->ordering))
    {
      $cat->ordering = 0;
    }
    if(!isset($cat->lft))
    {
      $cat->lft = $cat->ordering;
    }
    if(!isset($cat->access))
    {
      $cat->access = $this->_mainframe->getCfg('access');
    }
    if(!isset($cat->published))
    {
      $cat->published = 0;
    }
    if(!isset($cat->hidden))
    {
      $cat->hidden = 0;
    }
    if(!isset($cat->in_hidden))
    {
      $cat->in_hidden = 0;
    }
    if(     !isset($cat->owner)
        ||  !is_numeric($cat->owner)
        ||  $cat->owner < 1
        ||  ($checkOwner && !JUser::getTable()->load($cat->owner))
      )
    {
      $cat->owner = 0;
    }
    if(!isset($cat->password))
    {
      $cat->password = '';
    }
    if(!isset($cat->thumbnail))
    {
      $cat->thumbnail = 0;
    }
    if(!isset($cat->img_position))
    {
      $cat->img_position = 0;
    }
    if(!isset($cat->params))
    {
      $cat->params = '';
    }
    if(!isset($cat->metakey))
    {
      $cat->metakey = '';
    }
    if(!isset($cat->metadesc))
    {
      $cat->metadesc = '';
    }
    if(!isset($cat->exclude_toplists))
    {
      $cat->exclude_toplists = 0;
    }
    if(!isset($cat->exclude_search))
    {
      $cat->exclude_search = 0;
    }

    $catid_changed = false;
    if($cat->cid == 1)
    {
      // Special handling for categories with ID 1 because that's the ROOT category in JoomGallery
      $cat->cid = $this->getMaxCategoryId() + 1;

      // Store the new category ID because we have to use it for the images and categories in this category
      $this->newCatid = $cat->cid;
      $this->_mainframe->setUserState('joom.migration.internal.new_catid', $this->newCatid);

      $this->writeLogfile('New ID '.$cat->cid.' assigned to category '.$cat->name);

      $catid_changed = true;
    }

    // Make the category name safe
    JFilterOutput::objectHTMLSafe($cat->name);

    // If the new category should be assigned as subcategory...
    if($cat->parent_id > 1)
    {
      // Save the category path of parent category in a variable
      $parentpath = JoomHelper::getCatPath($cat->parent_id);
    }
    else
    {
      // Otherwise leave it empty
      $parentpath = '';
    }

    // Creation of category path
    // Cleaning of category title with function JoomFile::fixFilename
    // so special chars are converted and underscore removed
    // affects only the category path
    $newcatname = JoomFile::fixFilename($cat->name);
    // Add an underscore and the category ID
    // affects only the category path
    $newcatname = $newcatname.'_'.$cat->cid;
    // Prepend - if exists - the parent category path
    $cat->catpath = $parentpath.$newcatname;

    // Create the paths of category for originals, pictures, thumbnails
    $cat_originalpath  = JPath::clean($this->_ambit->get('orig_path').$cat->catpath);
    $cat_detailpath    = JPath::clean($this->_ambit->get('img_path').$cat->catpath);
    $cat_thumbnailpath = JPath::clean($this->_ambit->get('thumb_path').$cat->catpath);

    $result   = array();
    if(!JFolder::exists($cat_originalpath))
    {
      $result[] = JFolder::create($cat_originalpath);
      $result[] = JoomFile::copyIndexHtml($cat_originalpath);
    }
    if(!JFolder::exists($cat_detailpath))
    {
      $result[] = JFolder::create($cat_detailpath);
      $result[] = JoomFile::copyIndexHtml($cat_detailpath);
    }
    if(!JFolder::exists($cat_thumbnailpath))
    {
      $result[] = JFolder::create($cat_thumbnailpath);
      $result[] = JoomFile::copyIndexHtml($cat_thumbnailpath);
    }

    // Create database entry
    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_CATEGORIES)
          ->columns('cid, name, alias, parent_id, lft, description, access, published, hidden, in_hidden, password, owner, thumbnail, img_position, catpath, params, metakey, metadesc, exclude_toplists, exclude_search')
          ->values( (int) $cat->cid.','.
                    $this->_db->quote($cat->name).','.
                    $this->_db->quote($cat->alias).','.
                    (int) $cat->parent_id.','.
                    (int) $cat->lft.','.
                    $this->_db->quote($cat->description).','.
                    (int) $cat->access.','.
                    (int) $cat->published.','.
                    (int) $cat->hidden.','.
                    (int) $cat->in_hidden.','.
                    $this->_db->quote($cat->password).','.
                    (int) $cat->owner.','.
                    (int) $cat->thumbnail.','.
                    (int) $cat->img_position.','.
                    $this->_db->quote($cat->catpath).','.
                    $this->_db->quote($cat->params).','.
                    $this->_db->quote($cat->metakey).','.
                    $this->_db->quote($cat->metadesc).','.
                    (int) $cat->exclude_toplists.','.
                    (int) $cat->exclude_toplists
                  );

    $this->_db->setQuery($query);
    $result[] = $this->runQuery();

    // Create asset and alias
    $table = JTable::getInstance('joomgallerycategories', 'Table');
    $table->load($cat->cid);
    if($table->check())
    {
      $result['db'] = $table->store();
      if(!$result['db'])
      {
        $this->setError($table->getError(), true);
      }
    }

    if($catid_changed)
    {
      // Set back category ID in the object because it may be used again later
      $cat->cid = 1;
    }

    if(!in_array(false, $result))
    {
      $this->writeLogfile('Category '.($catid_changed ? $this->newCatid : $cat->cid).' created: '.$cat->name);

      return true;
    }
    else
    {
      $this->writeLogfile(' -> Error creating category '.($catid_changed ? $this->newCatid : $cat->cid).': '.$cat->name);

      return false;
    }
  }

  /**
   * Runs a query set afore and handles the errors
   *
   * @param   string  The database method to use
   * @return  mixed   The result of the query
   * @since   2.0
   */
  protected function runQuery($method = '', $db = null)
  {
    if(!$method)
    {
      $method = 'query';
    }

    if(is_null($db))
    {
      $db = $this->_db;
    }

    try
    {
      $result = $db->$method();
    }
    catch(Exception $e)
    {
      $this->setError($e->getMessage(), true);

      $result = null;
    }

    return $result;
  }

  /**
   * Prepares a table for being able to iterate through its data sets
   *
   * For categories it is important that always the parent categories have to be migrated
   * afore their sub-categories. So please pass the parameters $table and $parent_name
   * if you are migrating categories.
   *
   * @param   object  $query          A query object holding the query to use
   * @param   string  $table          Name of the database table to prepare
   * @param   string  $parent_name    Name of the column which holds the parent id of each category
   * @param   array   $first_parents  Array of category IDs which can be migrated first (main categories)
   * @return  void
   * @since   2.0
   */
  protected function prepareTable($query, $table = null, $parent_name = null, $first_parents = array(0))
  {
    $this->parent_name = $parent_name;

    if(is_null($this->_mainframe->getUserState('joom.migration.internal.counter')))
    {
      $this->_mainframe->setUserState('joom.migration.internal.counter', 0);
    }

    if($table && $parent_name)
    {
      $parent_cats = $this->_mainframe->getUserState('joom.migration.internal.parent_cats');
      if(is_null($parent_cats))
      {
        if(!$this->otherDatabase || is_null($this->_db2))
        {
          $db = $this->_db;
        }
        else
        {
          $db = $this->_db2;
        }

        // Check whether 'joom_migrated' exists from previously failed migrations
        $checkQuery = $db->getQuery(true)
              ->select('COLUMN_NAME')
              ->from('information_schema.COLUMNS')
              ->where('TABLE_NAME = '.$db->q($table))
              ->where('COLUMN_NAME = '.$db->q('joom_migrated'));
        $db->setQuery($checkQuery);
        if($this->runQuery('loadResult', $db))
        {
          $db->setQuery('ALTER TABLE '.$table.' DROP '.$db->qn('joom_migrated'));
          $this->runQuery('', $db);
        }

        // Add column 'joom_migrated' to be able to keep track of already migrated entries
        $db->setQuery('ALTER TABLE '.$table.' ADD '.$db->qn('joom_migrated').' INT(1) NOT NULL default 0');
        if(!$this->runQuery('', $db))
        {
          // If this fails we want to abort the whole migration
          throw new RuntimeException('Could not add \'joom_migrated\' column which is essential for the migration.');
        }

        $this->_mainframe->setUserState('joom.migration.internal.parent_cats', $first_parents);
      }
    }

    $this->query = $query;
  }

  /**
   * Returns the next data object of the query specified with method 'prepareTable'.
   *
   * 'prepareTable' has to be called first once.
   *
   * @return  object  The next data object
   * @since   2.0
   */
  protected function getNextObject()
  {
    if(!$this->otherDatabase || is_null($this->_db2))
    {
      $db = $this->_db;
    }
    else
    {
      $db = $this->_db2;
    }

    if($this->parent_name)
    {
      $parent_cats = $this->_mainframe->getUserState('joom.migration.internal.parent_cats');
      $this->query->clear('where')
                  ->where($this->parent_name.' IN ('.implode(',', $parent_cats).')')
                  ->where('joom_migrated = 0');
    }

    $counter = $this->_mainframe->getUserState('joom.migration.internal.counter');

    $db->setQuery($this->query, $counter, 1);

    if(!$this->parent_name)
    {
      $counter++;
      $this->_mainframe->setUserState('joom.migration.internal.counter', $counter);
    }

    return $this->runQuery('loadObject', $db);
  }

  /**
   * Marks a table row as migrated.
   *
   * This is important for migrating categories
   * @see method 'prepareTable'
   *
   * @param   int     $catid  ID of the data set which has been migrated
   * @param   string  $key    Primary key name of the table $table
   * @param   string  $table  Name of the database table the row is in
   * @return  void
   * @since   2.0
   */
  protected function markAsMigrated($catid, $key, $table)
  {
    if(!$this->otherDatabase || is_null($this->_db2))
    {
      $db = $this->_db;
    }
    else
    {
      $db = $this->_db2;
    }

    $parent_cats = $this->_mainframe->getUserState('joom.migration.internal.parent_cats');
    $parent_cats[] = (int) $catid;
    $parent_cats = array_unique($parent_cats);
    $this->_mainframe->setUserState('joom.migration.internal.parent_cats', $parent_cats);

    $query = $db->getQuery(true)
          ->update($table)
          ->set('joom_migrated = 1')
          ->where($key.' = '.(int) $catid);
    $db->setQuery($query);
    $this->runQuery('', $db);
  }

  /**
   * Resets a table which was prepared for iteration with method 'prepareTable'
   *
   * This method must be called after the iteration has been finished
   *
   * @see method 'prepareTable'
   *
   * @param   string  $table  Name of the table to reset
   * @return  void
   * @since   2.0
   */
  protected function resetTable($table = '')
  {
    if(!$this->otherDatabase || is_null($this->_db2))
    {
      $db = $this->_db;
    }
    else
    {
      $db = $this->_db2;
    }

    if($this->parent_name && $table)
    {
      $db->setQuery('ALTER TABLE '.$table.' DROP '.$db->qn('joom_migrated'));
      $this->runQuery('', $db);
    }

    $this->query = null;
    $this->parent_name = null;
    $this->_mainframe->setUserState('joom.migration.internal.parent_cats', null);
    $this->_mainframe->setUserState('joom.migration.internal.counter', null);
  }

  /**
   * Rebuilds the nested set tree
   *
   * This function has to be called after migrating all categories
   * unless during migration the nested set tree wasn't already created
   *
   * @return  void
   * @since   2.0
   */
  protected function rebuild()
  {
    // Refresh page once before rebuilding category tree
    // in order to have as much time for it as possible.
    // Refreshing is only necessary if not executed from the command line.
    if(!$this->isCli && !$this->_mainframe->getUserState('joom.migration.internal.refreshedForRebuild'))
    {
      $this->_mainframe->setUserState('joom.migration.internal.refreshedForRebuild', true);

      $this->writeLogfile('Refresh afore rebuilding category tree');
      $this->_mainframe->setUserState('joom.migration.internal.task', 'rebuild');
      $this->refresher->refresh();
    }

    $table = JTable::getInstance('joomgallerycategories', 'Table');

    $this->writeLogfile('Build the nested set tree');
    if($table->rebuild())
    {
      $this->writeLogfile('Nested set tree successfully built');
    }
    else
    {
      $this->writeLogfile(' -> Error building the nested set tree');
      if($error = $table->getError())
      {
        $this->setError($error);
      }
    }
  }

  /**
   * Creates images from the original one or moves the existing ones
   * into the folders of their category.
   *
   * Required parameters are the first two ($row and $origimage) with $row being the data object with image
   * information and $origimage being the path and filename of the image to migrate. $origimage will be the one
   * stored in the original images directory of JoomGallery.
   * You can also specify $detailimage and $thumbnail which will store them in the respective folders. If you
   * don't specify them they will be created from the original image.
   *
   * [jimport('joomla.filesystem.file') has to be called afore]
   *
   * @param   object  $row          Holds information about the new image
   * @param   string  $origimage    The original image
   * @param   string  $detailimage  The detail image
   * @param   string  $thumbnail    The thumbnail
   * @param   boolean $newfilename  True if a new file name shall be generated for the files
   * @param   boolean $copy         True if the image shall be copied into the new directory, not moved
   * @param   boolean $checkOwner   Determines whether the owner ID shall be checked against the existing users
   * @return  boolean True on success, false otherwise
   * @since   1.5.0
   */
  public function moveAndResizeImage($row, $origimage, $detailimage = null, $thumbnail = null, $newfilename = true, $copy = null, $checkOwner = null)
  {
    if(is_null($copy))
    {
      $copy = $this->copyImages;
    }

    if(is_null($checkOwner))
    {
      $checkOwner = $this->checkOwner;
    }

    // Some checks
    if(!isset($row->id) || $row->id < 1)
    {
      $this->setError('Invalid image ID');

      return false;
    }
    if(!isset($row->imgfilename))
    {
      $this->setError('Image file name wasn\'t found.');

      return false;
    }
    if(!isset($row->catid) || $row->catid < 1)
    {
      $this->setError('Invalid category ID');

      return false;
    }
    else
    {
      // If image with category ID 1 comes in we have to set
      // the category ID to the newly created one because
      // category with ID 1 is the ROOT category in JoomGallery
      if($row->catid == 1)
      {
        $row->catid = $this->newCatid;
      }
    }
    if(!isset($row->catpath))
    {
      $row->catpath = JoomHelper::getCatpath($row->catid);
      if(!$row->catpath)
      {
        $this->setError('Category with ID '.$row->catid.' does not exist for image with ID '.$row->id.'. Image cannot be migrated.');

        return false;
      }
    }
    if(!isset($row->imgtitle))
    {
      $row->imgtitle = str_replace(JFile::getExt($row->imgfilename), '', $row->imgfilename);
    }
    if(!isset($row->alias))
    {
      // Will be created later on
      $row->alias = '';
    }
    if(!isset($row->imgauthor))
    {
      $row->imgauthor = '';
    }
    if(!isset($row->imgtext))
    {
      $row->imgtext = '';
    }
    if(!isset($row->imgdate) || is_numeric($row->imgdate))
    {
      $date = JFactory::getDate();
      $row->imgdate = $date->toSQL();
    }
    if(!isset($row->hits))
    {
      $row->hits = 0;
    }
    if(!isset($row->downloads))
    {
      $row->downloads = 0;
    }
    if(!isset($row->imgvotes))
    {
      $row->imgvotes = 0;
    }
    if(!isset($row->imgvotesum))
    {
      $row->imgvotesum = 0;
    }
    if(!isset($row->access) || $row->access < 1)
    {
      $row->access = $this->_mainframe->getCfg('access');
    }
    if(!isset($row->published))
    {
      $row->published = 0;
    }
    if(!isset($row->hidden))
    {
      $row->hidden = 0;
    }
    if(!isset($row->imgthumbname))
    {
      $row->imgthumbname = $row->imgfilename;
    }
    if(!isset($row->checked_out))
    {
      $row->checked_out = 0;
    }
    if(     !isset($row->owner)
        ||  !is_numeric($row->owner)
        ||  $row->owner < 1
        ||  ($checkOwner && !JUser::getTable()->load($row->owner))
      )
    {
      $row->owner = 0;
    }
    if(!isset($row->approved))
    {
      $row->approved = 1;
    }
    if(!isset($row->useruploaded))
    {
      $row->useruploaded = 0;
    }
    if(!isset($row->ordering))
    {
      $row->ordering = 0;
    }
    if(!isset($row->params))
    {
      $row->params = '';
    }
    if(!isset($row->metakey))
    {
      $row->metakey = '';
    }
    if(!isset($row->metadesc))
    {
      $row->metadesc = '';
    }

    // Check whether one of the images to migrate already exist in the destination directory
    $orig_exists  = false;
    $img_exists   = false;
    $thumb_exists = false;
    $neworigimage   = $this->_ambit->getImg('orig_path', $row);
    $newdetailimage = $this->_ambit->getImg('img_path', $row);
    $newthumbnail   = $this->_ambit->getImg('thumb_path', $row);
    if(JFile::exists($neworigimage))
    {
      $orig_exists = true;
    }
    if(JFile::exists($newdetailimage))
    {
      $img_exists = true;
    }
    if(JFile::exists($newthumbnail))
    {
      $thumb_exists = true;
    }

    // Generate a new file name if requested or if a file with the current name already exists
    if($newfilename || $orig_exists || $img_exists || $thumb_exists)
    {
      $row->imgfilename   = $this->genFilename($row->imgtitle, $origimage, $row->catid);
      $row->imgthumbname  = $row->imgfilename;
    }

    $result = array();

    // Copy or move original image into the folder of the original images
    if(!$orig_exists)
    {
      // If it doesn't already exists with another name try to copy or move from source directory
      if(!JFile::exists($origimage))
      {
        $this->setError('Original image not found: '.$origimage);

        return false;
      }

      $neworigimage = $this->_ambit->getImg('orig_path', $row);
      if($copy)
      {
        $result['orig'] = JFile::copy(JPath::clean($origimage),
                                      JPath::clean($neworigimage));
        if(!$result['orig'])
        {
          $this->setError('Could not copy original image from '.$origimage.' to '.$neworigimage);

          return false;
        }
      }
      else
      {
        $result['orig'] = JFile::move(JPath::clean($origimage),
                                      JPath::clean($neworigimage));
        if(!$result['orig'])
        {
          $this->setError('Could not move original image from '.$origimage.' to '.$neworigimage);

          return false;
        }
      }
    }
    else
    {
      // If it already exists with another name copy it to a file with the new name
      if(!JFile::copy($neworigimage, $this->_ambit->getImg('orig_path', $row)))
      {
        $this->setError('Could not copy original image from '.$neworigimage.' to '.$this->_ambit->getImg('orig_path', $row));

        return false;
      }

      // Populate the new original file name and path because it will be
      // necessary for deleting it of deleting original images is configured
      $neworigimage = $this->_ambit->getImg('orig_path', $row);
    }

    if(!$img_exists)
    {
      // If it doesn't already exists with another name try to copy or move from source directory or create a new one
      $newdetailimage = $this->_ambit->getImg('img_path', $row);
      if(is_null($detailimage) || !JFile::exists($detailimage))
      {
        // Create new detail image
        $debugoutput = '';
        $result['detail'] = JoomFile::resizeImage($debugoutput,
                                                  $neworigimage,
                                                  $newdetailimage,
                                                  false,
                                                  $this->_config->get('jg_maxwidth'),
                                                  false,
                                                  $this->_config->get('jg_thumbcreation'),
                                                  $this->_config->get('jg_thumbquality'),
                                                  true,
                                                  0
                                                  );
        if(!$result['detail'])
        {
          $this->setError('Could not create detail image '.$newdetailimage);
        }
      }
      else
      {
        // Copy or move existing detail image
        if($copy)
        {
          $result['detail'] = JFile::copy(JPath::clean($detailimage),
                                          JPath::clean($newdetailimage));
          if(!$result['detail'])
          {
            $this->setError('Could not copy detail image from '.$detailimage.' to '.$newdetailimage);
          }
        }
        else
        {
          $result['detail'] = JFile::move(JPath::clean($detailimage),
                                          JPath::clean($newdetailimage));
          if(!$result['detail'])
          {
            $this->setError('Could not move detail image from '.$detailimage.' to '.$newdetailimage);
          }
        }
      }
    }
    else
    {
      // If it already exists with another name copy it to a file with the new name
      $result['detail'] = JFile::copy($newdetailimage, $this->_ambit->getImg('img_path', $row));
      if(!$result['detail'])
      {
        $this->setError('Could not copy detail image from '.$newdetailimage.' to '.$this->_ambit->getImg('img_path', $row));
      }
    }

    if(!$thumb_exists)
    {
      // If it doesn't already exists with another name try to copy or move from source directory or create a new one
      $newthumbnail = $this->_ambit->getImg('thumb_path', $row);
      if(is_null($thumbnail) || !JFile::exists($thumbnail))
      {
        // Create new thumbnail
        $debugoutput = '';
        $result['thumb'] = JoomFile::resizeImage( $debugoutput,
                                                  $neworigimage,
                                                  $newthumbnail,
                                                  $this->_config->get('jg_useforresizedirection'),
                                                  $this->_config->get('jg_thumbwidth'),
                                                  $this->_config->get('jg_thumbheight'),
                                                  $this->_config->get('jg_thumbcreation'),
                                                  $this->_config->get('jg_thumbquality'),
                                                  false,
                                                  $this->_config->get('jg_cropposition')
                                                );
        if(!$result['thumb'])
        {
          $this->setError('Could not create thumbnail '.$newthumbnail);
        }
      }
      else
      {
        // Copy or move existing thumbnail
        if($copy)
        {
          $result['thumb'] = JFile::copy(JPath::clean($thumbnail),
                                         JPath::clean($newthumbnail));
          if(!$result['thumb'])
          {
            $this->setError('Could not copy thumbnail from '.$thumbnail.' to '.$newthumbnail);
          }
        }
        else
        {
          $result['thumb'] = JFile::move(JPath::clean($thumbnail),
                                         JPath::clean($newthumbnail));
          if(!$result['thumb'])
          {
            $this->setError('Could not move thumbnail from '.$thumbnail.' to '.$newthumbnail);
          }
        }
      }
    }
    else
    {
      // If it already exists with another name copy it to a file with the new name
      $result['thumb'] = JFile::copy($newthumbnail, $this->_ambit->getImg('thumb_path', $row));
      if(!$result['thumb'])
      {
        $this->setError('Could not copy thumbnail from '.$newthumbnail.' to '.$this->_ambit->getImg('thumb_path', $row));
      }
    }

    // Delete original image if configured in JoomGallery
    if($this->_config->get('jg_delete_original') == 1)
    {
      $result['delete_orig'] = JFile::delete($neworigimage);
      if(!$result['delete_orig'])
      {
        $this->setError('Could not delete original image '.$neworigimage);
      }
    }

    // Create database entry
    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_IMAGES)
          ->columns('id, catid, imgtitle, alias, imgauthor, imgtext, imgdate, hits, downloads, imgvotes, imgvotesum, access, published, hidden, imgfilename, imgthumbname, checked_out, owner, approved, useruploaded, ordering, params, metakey, metadesc')
          ->values( (int) $row->id.','.
                    (int) $row->catid.','.
                    $this->_db->quote($row->imgtitle).','.
                    $this->_db->quote($row->alias).','.
                    $this->_db->quote($row->imgauthor).','.
                    $this->_db->quote($row->imgtext).','.
                    $this->_db->quote($row->imgdate).','.
                    (int) $row->hits.','.
                    (int) $row->downloads.','.
                    (int) $row->imgvotes.','.
                    (int) $row->imgvotesum.','.
                    (int) $row->access.','.
                    (int) $row->published.','.
                    (int) $row->hidden.','.
                    $this->_db->quote($row->imgfilename).','.
                    $this->_db->quote($row->imgthumbname).','.
                    (int) $row->checked_out.','.
                    (int) $row->owner.','.
                    (int) $row->approved.','.
                    (int) $row->useruploaded.','.
                    (int) $row->ordering.','.
                    $this->_db->quote($row->params).','.
                    $this->_db->quote($row->metakey).','.
                    $this->_db->quote($row->metadesc)
                  );
    $this->_db->setQuery($query);
    $result[] = $this->runQuery();

    // Create asset and alias
    $table = JTable::getInstance('joomgalleryimages', 'Table');
    $table->load($row->id);
    if($table->check())
    {
      $result['db'] = $table->store();
      if(!$result['db'])
      {
        $this->setError($table->getError(), true);
      }
    }

    if(!in_array(false, $result))
    {
      $this->writeLogfile('Image successfully migrated: ' . $row->id . ' Title: ' . $row->imgtitle);

      return true;
    }
    else
    {
      $this->writeLogfile('-> Error migrating image: ' . $row->id . ' Title: ' . $row->imgtitle);

      return false;
    }
  }

  /**
   * Creates a comment
   *
   * @param   object  $row  Holds the comment data
   * @return  boolean True on success, false otherwise
   * @since   1.5.0
   */
  public function createComment($row)
  {
    // Some checks
    if(!isset($row->cmtpic) || !$row->cmtpic)
    {
      $this->setError('Invalid image ID for comment');

      return false;
    }
    if(!isset($row->cmttext) || $row->cmttext == '')
    {
      $this->setError('Comment hasn\'t any text');

      return false;
    }
    if(!isset($row->cmtid))
    {
      $row->cmtid = 0;
    }
    if(!isset($row->cmtip))
    {
      $row->cmtip = '127.0.0.1';
    }
    if(!isset($row->userid))
    {
      $row->userid = 0;
    }
    if(!isset($row->cmtname))
    {
      $row->cmtname = '';
    }
    if(!isset($row->cmtdate) || is_numeric($row->cmtdate))
    {
      $date = JFactory::getDate();
      $row->cmtdate = $date->toSQL();
    }
    if(!isset($row->published))
    {
      $row->published = 0;
    }
    if(!isset($row->approved))
    {
      $row->approved = 1;
    }

    // Create database entry
    $values = array((int) $row->cmtpic,
                    $this->_db->quote($row->cmtip),
                    (int) $row->userid,
                    $this->_db->quote($row->cmtname),
                    $this->_db->quote($row->cmttext),
                    $this->_db->quote($row->cmtdate),
                    (int) $row->published,
                    (int) $row->approved
                    );

    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_COMMENTS);

    if($row->cmtid)
    {
      $query->columns('cmtid');
      array_unshift($values, $row->cmtid);
    }

    $query->columns('cmtpic, cmtip, userid, cmtname, cmttext, cmtdate, published, approved')
          ->values(implode(',', $values));
    $this->_db->setQuery($query);
    if($this->runQuery())
    {
      $this->writeLogfile('Comment with ID '.$row->cmtid.' successfully stored');

      return true;
    }

    return false;
  }

  /**
   * Creates a name tag
   *
   * @param   object  $row  Holds the name tag data
   * @return  boolean True on success, false otherwise
   * @since   1.5.0
   */
  public function createNametag($row)
  {
    // Some checks
    if(!isset($row->npicid) || !$row->npicid)
    {
      $this->setError('Invalid image ID for name tag');

      return false;
    }
    if(!isset($row->nxvalue) || $row->nxvalue < 0)
    {
      $this->setError('Invalid x value for name tag');

      return false;
    }
    if(!isset($row->nyvalue) || $row->nyvalue < 0)
    {
      $this->setError('Invalid y value for name tag');

      return false;
    }
    if(!isset($row->nid))
    {
      $row->nid = 0;
    }
    if(!isset($row->nuserid))
    {
      $row->nuserid = 0;
    }
    if(!isset($row->by))
    {
      $row->by = 0;
    }
    if(!isset($row->nuserip))
    {
      $row->cmtip = '127.0.0.1';
    }
    if(!isset($row->ndate) || is_numeric($row->ndate))
    {
      $date = JFactory::getDate();
      $row->ndate = $date->toSQL();
    }
    if(!isset($row->nzindex))
    {
      $row->nzindex = 500;
    }

    // Create database entry
    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_NAMESHIELDS);

    $values = array((int) $row->npicid,
                    (int) $row->nuserid,
                    (int) $row->nxvalue,
                    (int) $row->nyvalue,
                    (int) $row->by,
                    $this->_db->quote($row->nuserip),
                    $this->_db->quote($row->ndate),
                    (int) $row->nzindex
                    );
    if($row->nid)
    {
      $query->columns('nid');
      array_unshift($values, $row->nid);
    }

    $query->columns('npicid, nuserid, nxvalue, nyvalue, '.$this->_db->quoteName('by').', nuserip, ndate, nzindex')
          ->values(implode(',', $values));
    $this->_db->setQuery($query);
    if($this->runQuery())
    {
      $this->writeLogfile('Name tag with ID '.$row->nid.' successfully stored');

      return true;
    }

    return false;
  }

  /**
   * Creates a user record
   *
   * @param   object  $row  Holds the user data
   * @return  boolean True on success, false otherwise
   * @since   3.1
   */
  public function createUser($row)
  {
    // Some checks
    if(!isset($row->uuserid) || !$row->uuserid)
    {
      $this->setError('Invalid user ID for user record');

      return false;
    }
    if(!isset($row->uid))
    {
      $row->uid = 0;
    }
    if(!isset($row->piclist))
    {
      $row->piclist = '';
    }
    if(!isset($row->layout))
    {
      $row->layout = 0;
    }
    if(!isset($row->time))
    {
      $row->time = JFactory::getDate()->toSql();
    }
    if(!isset($row->zipname) || !$row->zipname)
    {
      $row->zipname = '';
    }

    // Create database entry
    $values = array((int) $row->uuserid,
                    $this->_db->quote($row->piclist),
                    (int) $row->layout,
                    $this->_db->quote($row->time),
                    $this->_db->quote($row->zipname)
                    );

    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_USERS);

    if((int) $row->uid)
    {
      $query->columns('uid');
      array_unshift($values, (int) $row->uid);
    }

    $query->columns('uuserid, piclist, layout, time, zipname')
          ->values(implode(',', $values));
    $this->_db->setQuery($query);
    if($this->runQuery())
    {
      $this->writeLogfile('User record with ID '.((int) $row->uid).' successfully stored');

      return true;
    }

    return false;
  }

  /**
   * Creates a vote record
   *
   * @param   object  $row  Holds the vote data
   * @return  boolean True on success, false otherwise
   * @since   3.1
   */
  public function createVote($row)
  {
    // Some checks
    if(!isset($row->picid) || !$row->picid)
    {
      $this->setError('Invalid image ID for vote record');

      return false;
    }
    if(!isset($row->voteid))
    {
      $row->voteid = 0;
    }
    if(!isset($row->userid))
    {
      $row->userid = 0;
    }
    if(!isset($row->userip))
    {
      $row->userip = '127.0.0.1';
    }
    if(!isset($row->datevoted) || is_numeric($row->datevoted))
    {
      $date = JFactory::getDate();
      $row->datevoted = $date->toSQL();
    }
    if(!isset($row->vote) || !$row->vote)
    {
      $row->vote = 0;
    }

    // Create database entry
    $values = array((int) $row->picid,
                    (int) $row->userid,
                    $this->_db->quote($row->userip),
                    $this->_db->quote($row->datevoted),
                    (int) $row->vote
                    );

    $query = $this->_db->getQuery(true)
          ->insert(_JOOM_TABLE_VOTES);

    if($row->voteid)
    {
      $query->columns('voteid');
      array_unshift($values, $row->voteid);
    }

    $query->columns('picid, userid, userip, datevoted, vote')
          ->values(implode(',', $values));
    $this->_db->setQuery($query);
    if($this->runQuery())
    {
      $this->writeLogfile('Vote record with ID '.$row->voteid.' successfully stored');

      return true;
    }

    return false;
  }

  /**
   * Generates a new filename
   * e.g. <Name/gen. Title>_<Date>_<Random Number>.<Extension>
   *
   * @param   string    $title  The title of the image
   * @param   string    $file   Path and file name of the old image file
   * @param   int       $catid  ID of the category into which the image will be stored
   * @param   string    $tag    File extension e.g. 'jpg'
   * @return  string    The generated filename
   * @since   2.0
   */
  protected function genFilename($title, $file, $catid)
  {
    $date = date('Ymd');

    $tag = strtolower(JFile::getExt($file));

    $filename = JoomFile::fixFilename($title);

    // Remove filetag = $tag incl '.'
    $filename = substr($filename, 0, strlen($filename)-strlen($tag)-1);

    do
    {
      mt_srand();
      $randomnumber = mt_rand(1000000000, 2099999999);

      // New filename
      $newfilename = $filename.'_'.$date.'_'.$randomnumber.'.'.$tag;
    }
    while(    JFile::exists($this->_ambit->getImg('orig_path', $newfilename, null, $catid))
           || JFile::exists($this->_ambit->getImg('img_path', $newfilename, null, $catid))
           || JFile::exists($this->_ambit->getImg('thumb_path', $newfilename, null, $catid))
         );

    return $newfilename;
  }
}
