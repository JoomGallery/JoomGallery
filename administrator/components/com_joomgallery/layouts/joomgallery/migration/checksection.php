<?php defined('_JEXEC') or die; ?>
  <tr>
    <td colspan="3">
      <h4><?php echo $displayData->title; ?></h4>
    </td>
  </tr>
<?php foreach($displayData->checks as $check): ?>
  <tr>
    <td width="80%"><?php echo $check['title']; ?></td>
    <td width="10%" class="center">
      <?php echo $check['state'] ? JHTML::_('jgrid.published', true, 0, '', false) : '&nbsp;'; ?>
    </td>
    <td class="center">
      <?php echo !$check['state'] ? JHTML::_('jgrid.published', false, 0, '', false) : '&nbsp;'; ?>
    </td>
  </tr>
<?php endforeach;