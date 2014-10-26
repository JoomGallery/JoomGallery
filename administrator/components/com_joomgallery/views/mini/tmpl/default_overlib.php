<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="jg_overlib">
  <div><!-- # style="float:left;"> -->
    <img src="<?php echo $this->image->thumb_src; ?>" />
  </div>
  <div>
    <div class="jg_title">
      <?php echo $this->escape($this->image->imgtitle); ?>
    </div>
<?php if(!$this->catid): ?>
    <div class="jg_catname">
      <?php echo $this->escape($this->image->name); ?>
    </div>
<?php endif; ?>
  </div>
</div>