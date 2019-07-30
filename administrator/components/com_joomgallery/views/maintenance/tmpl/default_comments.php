<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminFormComments" method="post" onsubmit="return confirm(Joomla.JText._('COM_JOOMGALLERY_MAIMAN_CM_ALERT_RESET_COMMENTS_CONFIRM'))">
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormComments.task.value = 'synchronize';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_SYNCHRONIZE_COMMENTS'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_SYNCHRONIZE_COMMENTS_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormComments.task.value = 'deleteip';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_DELETE_COMMENTS_IP'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_DELETE_COMMENTS_IP_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormComments.task.value = 'reset';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_RESET_COMMENTS'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CM_RESET_COMMENTS_LONG'); ?>
      </div>
    </div>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
    <input type="hidden" name="controller" value="comments" />
    <input type="hidden" name="task" value="" />
  </div>
</form>