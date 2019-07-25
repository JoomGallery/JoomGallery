<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminFormNametags" method="post" onsubmit="return confirm(Joomla.JText._('COM_JOOMGALLERY_MAIMAN_NT_ALERT_RESET_NAMETAGS_CONFIRM'))">
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormNametags.task.value = 'synchronize';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_SYNCHRONIZE'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_SYNCHRONIZE_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormNametags.task.value = 'deleteip';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_DELETE_NAMETAGS_IP'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_DELETE_NAMETAGS_IP_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormNametags.task.value = 'reset';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_RESET'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_NT_RESET_LONG'); ?>
      </div>
    </div>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
    <input type="hidden" name="controller" value="nametags" />
    <input type="hidden" name="task" value="" />
  </div>
</form>