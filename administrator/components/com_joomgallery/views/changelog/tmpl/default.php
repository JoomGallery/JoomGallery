<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
foreach($this->changelog->release as $release): ?>
  <h4><?php echo $release->attributes()->version?> published <?php echo JHTML::_('date', $release->attributes()->published, JText::_('DATE_FORMAT_LC4')); ?></h4>
<?php
  foreach($release->entries as $entry): ?>
  <table class="table table-condensed">
    <thead>
      <tr>
        <th>
          <?php echo JHTML::_('date', $entry->attributes()->date, JText::_('DATE_FORMAT_LC4')); ?>
        </th>
      </tr>
    </thead>
    <tbody>
<?php
    foreach($entry->logentry as $logentry):
      switch($logentry->attributes()->type):
        case '*':
            $term = 'Security';
            $labelclass = 'label-important';
          break;
        case '#':
            $term = 'BugFix';
            $labelclass = 'label-warning';
            break;
        case '+':
          $term = 'New';
          $labelclass = 'label-success';
          break;
        case '-':
          $term = 'Removed';
          $labelclass = 'label-inverse';
          break;
        case '^':
          $term = 'Change';
          $labelclass = 'label-info';
          break;
        case '!':
        default:
          $term = 'Note';
          $labelclass = '';
          break;
      endswitch;
?>
      <tr>
        <td width="15%">
          <span class="label <?php echo $labelclass; ?>"><?php echo $term; ?></span>
        </td>
        <td>
          <?php echo $logentry; ?>
        </td>
      </tr>
<?php
    endforeach; ?>
    </tbody>
  </table>
<?php
  endforeach; ?>
<?php
endforeach; ?>