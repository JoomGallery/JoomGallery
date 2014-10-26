<?php defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
?>
<form class="form-horizontal" action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images'); ?>" method="post" id="adminForm" name="adminForm" >
  <div class="control-group">
    <div class="control-label">
      <label for="catid">
        <strong><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_MOVE_IMAGE_TO_CATEGORY'); ?></strong>
      </label>
    </div>
    <div class="controls">
      <?php echo $this->lists['cats']; ?>
    </div>
  </div>
  <table class="table table-striped">
    <thead>
      <tr>
        <th class="nowrap" colspan="3">
          <strong><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_IMAGES_TO_MOVE'); ?></strong>
        </th>
      </tr>
      <tr>
        <th width="25" class="center hidden-phone"></th>
        <th width="40%" class="nowrap">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_TITLE'); ?>
        </th>
        <th class="nowrap">
          <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_PREVIOUS_CATEGORY'); ?>
        </th>
      </tr>
    </thead>
    <tbody>
<?php foreach($this->items as $i => $item): ?>
      <tr class="row<?php echo $i % 2; ?>">
        <td class="center hidden-phone">
          <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', null, false); ?>
        </td>
        <td>
          <?php echo $item->imgtitle; ?>
          <input type="hidden" name="cid[]" value="<?php echo $item->id; ?>" />
        </td>
        <td>
          <?php echo JHtml::_('joomgallery.categorypath', $item->catid); ?>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
  <div>
    <input type="hidden" name="task" value="savemove" />
    <input type="hidden" name="boxchecked" value="1" />
  </div>
</form>
<?php JHTML::_('joomgallery.credits');