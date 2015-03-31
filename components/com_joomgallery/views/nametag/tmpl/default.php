<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="minigallery center">
  <h3>
    <?php echo JText::_('COM_JOOMGALLERY_SELECT_NAMETAG'); ?>
  </h3>
  <form action="index.php" name="selectnametagform" method="post">
    <div class="control-group">
      <div class="controls">
        <input type="submit" value="<?php echo JText::_('COM_JOOMGALLERY_DETAIL_NAMETAGS_SELECT_MYSELF'); ?>" class="btn" onclick="window.parent.selectnametag(<?php echo $this->_user->get('id'); ?>, '<?php echo $this->_user->get($this->_config->get('jg_realname') ? 'name' : 'username'); ?>');return false;" />
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <?php echo JHtml::_('joomselect.users', 'selectnametaglist', '', true, array(), 'window.parent.selectnametag(this.value, this[this.selectedIndex].text);', false); ?>
      </div>
    </div>
  </form>
</div>