<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminFormVotes" method="post" onsubmit="return confirm(Joomla.JText._('COM_JOOMGALLERY_MAIMAN_ALERT_RESET_VOTES_CONFIRM'))">
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormVotes.task.value = 'synchronize';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_SYNCHRONIZE_VOTES'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_SYNCHRONIZE_VOTES_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormVotes.task.value = 'reset';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_RESET_VOTES'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_RESET_VOTES_LONG'); ?>
      </div>
    </div>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
    <input type="hidden" name="controller" value="votes" />
    <input type="hidden" name="task" value="" />
  </div>
</form>