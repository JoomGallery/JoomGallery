<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminFormFavourites" method="post" onsubmit="return confirm(Joomla.JText._('COM_JOOMGALLERY_MAIMAN_FV_ALERT_RESET_FAVOURITES_CONFIRM'))">
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormFavourites.task.value = 'synchronize';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_FV_SYNCHRONIZE_FAVOURITES'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_FV_SYNCHRONIZE_FAVOURITES_LONG'); ?>
      </div>
    </div>
  </div>
  <div class="well">
    <div class="row-fluid">
      <div class="span2 center">
        <button type="submit" class="btn" onclick="document.adminFormFavourites.task.value = 'reset';"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_FV_RESET_FAVOURITES'); ?></button>
      </div>
      <div class="span10">
        <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_FV_RESET_FAVOURITES_LONG'); ?>
      </div>
    </div>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
    <input type="hidden" name="controller" value="favourites" />
    <input type="hidden" name="task" value="" />
  </div>
</form>