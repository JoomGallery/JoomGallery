<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="PopupNewModalLabel" aria-hidden="true" id="jg-new-popup">
	<script type="text/javascript">
		function createConfigRow()
		{
			document.id('adminForm').id.value       = document.id('formNew').base.value;
			document.id('adminForm').group_id.value = document.id('usergroup').value;
			Joomla.submitform('edit', document.id('adminForm'));
		}
	</script>
  <form id="formNew" method="post" action="index.php">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3 id="PopupNewModalLabel"><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_NEW_HEADING'); ?></h3>
    </div>
    <div class="modal-body">
      <p><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_SELECT_USER_GROUP'); ?></p>
      <p><?php echo count($this->usergroups) ? JHtml::_('select.genericlist', $this->usergroups, 'usergroup') : JText::_('COM_JOOMGALLERY_CONFIGS_NO_MORE_USER_GROUPS'); ?></p>
      <p><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_SELECT_CONFIG_ROW'); ?></p>
      <p><?php echo JHtml::_('select.genericlist', $this->allitems, 'base', null, 'id', 'title'); ?></p>
    </div>
    <div class="modal-footer">
      <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
<?php if(count($this->usergroups)): ?>
      <button class="btn btn-primary" onclick="createConfigRow();return false;" type="button"><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_OK'); ?></button>
<?php endif; ?>
    </div>
  </form>
</div>