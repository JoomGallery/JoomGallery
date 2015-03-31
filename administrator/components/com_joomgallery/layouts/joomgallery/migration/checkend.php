<?php defined('_JEXEC') or die; ?>
  <tr>
    <td colspan="3">
      <hr />
<?php if($displayData->ready): ?>
      <div style="text-align:center;color:#080;padding:1em 0;font:bold 1.2em Verdana;">
        <?php echo JText::_('COM_JOOMGALLERY_MIGMAN_TRUE'); ?></div>
      <div style="text-align:center;"><?php echo JText::_('COM_JOOMGALLERY_MIGMAN_TRUE_LONG'); ?></div>
<?php else: ?>
      <div style="text-align:center;color:#f30;padding:1em 0;font:bold 1.2em Verdana;">
        <?php echo JText::_('COM_JOOMGALLERY_MIGMAN_FALSE'); ?></div>
      <div style="text-align:center;"><?php echo JText::_('COM_JOOMGALLERY_MIGMAN_FALSE_LONG'); ?></div>
<?php endif; ?>
      <hr />
    </td>
  </tr>
<?php if($displayData->ready): ?>
  <tr>
    <th colspan="3" style="text-align:center;">
      <form action="<?php echo $displayData->url; ?>" method="post">
        <div>
          <input type="hidden" name="migration" value="<?php echo $displayData->migration; ?>">
          <input type="hidden" name="task" value="start">
          <button class="btn btn-large btn-primary btn-block"><?php echo JText::_('COM_JOOMGALLERY_MIGMAN_START'); ?></button>
        </div>
      </form>
      <hr />
    </th>
  </tr>
<?php endif; ?>
</table>