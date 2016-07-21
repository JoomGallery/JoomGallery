<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminFormDatabase" method="post">
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormDatabase.task.value = 'optimize';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_DB_OPTIMIZE'); ?></button>
      </div>
      <div class="span10">
          <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_DB_OPTIMIZE_LONG'); ?> 
      </div>
      <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
      <input type="hidden" name="controller" value="maintenance" />
      <input type="hidden" name="task" value="" />
    </div>
  </div>
</form>