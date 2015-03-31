<?php defined('_JEXEC') or die; ?>
  <tr>
    <td width="50%">
      <h4><?php echo JText::_('FILES_JOOMGALLERY_MIGRATION_'.strtoupper($displayData->migration).'_MIGRATIONCHECK'); ?></h4>
    </td>
    <td>
      <form action="<?php echo $displayData->url; ?>" method="post" class="form-horizontal form-validate">
        <?php if($displayData->description): ?>
        <div class="alert alert-info"><?php echo $displayData->description; ?></div>
        <?php endif;
              foreach($displayData->fields as $field):
                if(!$field->hidden): ?>
        <div class="control-group">
          <div class="control-label">
            <?php echo $field->label; ?>
          </div>
          <div class="controls">
            <?php echo $field->input; ?>
          </div>
        </div>
        <?php else: ?>
        <div><?php echo $field->input; ?></div>
        <?php   endif;
              endforeach;
              if(count($displayData->fields)): ?>
        <div class="control-group">
          <div class="controls">
            <button id="button-<?php echo $displayData->migration; ?>" class="btn btn-primary">
              <?php echo JText::_('COM_JOOMGALLERY_MIGMAN_CHECK'); ?>
            </button>
          </div>
          <input type="hidden" name="migration" value="<?php echo $displayData->migration; ?>" />
        </div>
        <?php else: ?>
        <div class="center">
          <button id="button-<?php echo $displayData->migration; ?>" class="btn btn-primary">
            <?php echo JText::_('COM_JOOMGALLERY_MIGMAN_CHECK'); ?>
          </button>
          <input type="hidden" name="migration" value="<?php echo $displayData->migration; ?>" />
        </div>
        <?php endif; ?>
      </form>
    </td>
  </tr>