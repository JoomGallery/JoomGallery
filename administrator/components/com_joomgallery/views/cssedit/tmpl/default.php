<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<form action="index.php" name="adminForm" id="adminForm" method="post">
<?php if(!empty($this->sidebar)): ?>
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif;?>
    <div class="row-fluid">
      <div class="alert alert-info">
        <?php echo ($this->edit)? JText::_('COM_JOOMGALLERY_CSSMAN_EDIT_CSS_EXPLANATION') : JText::_('COM_JOOMGALLERY_CSSMAN_NEW_CSS_EXPLANATION'); ?>
      </div>
    </div>
    <div class="row-fluid">
      <strong><?php echo $this->file ?></strong><br />
      <textarea cols="110" rows="25" name="csscontent" class="inputbox span12"><?php echo $this->content ?></textarea>
    </div>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>">
    <input type="hidden" name="controller" value="cssedit" />
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="1" />
    <?php JHtml::_('joomgallery.credits'); ?>
  </div>
</form>