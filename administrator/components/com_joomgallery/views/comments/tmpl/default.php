<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$approved_states  = array(1 => array('reject', 'COM_JOOMGALLERY_COMMON_APPROVED', 'COM_JOOMGALLERY_COMMAN_REJECT_COMMENT', 'COM_JOOMGALLERY_COMMON_APPROVED', false, 'publish', 'publish'),
                          0 => array('approve', 'COM_JOOMGALLERY_COMMON_REJECTED', 'COM_JOOMGALLERY_COMMAN_APPROVE_COMMENT', 'COM_JOOMGALLERY_COMMON_REJECTED', false, 'unpublish', 'unpublish'));
$sortFields       = $this->getSortFields();
?>
<script type="text/javascript">
  Joomla.orderTable = function() {
    table = document.getElementById("sortTable");
    direction = document.getElementById("directionTable");
    order = table.options[table.selectedIndex].value;
    if (order != '<?php echo $listOrder; ?>') {
      dirn = 'asc';
    } else {
      dirn = direction.options[direction.selectedIndex].value;
    }
    Joomla.tableOrdering(order, dirn, '');
  }
</script>
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=comments');?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty($this->sidebar)): ?>
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif;?>
    <div id="filter-bar" class="btn-toolbar">
      <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?></label>
        <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" />
      </div>
      <div class="btn-group pull-left hidden-phone">
        <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>"><i class="icon-search"></i></button>
        <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
      </div>
      <div class="btn-group pull-right hidden-phone">
        <label for="limit" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH_LIMIT'); ?></label>
        <?php echo $this->pagination->getLimitBox(); ?>
      </div>
      <div class="btn-group pull-right hidden-phone">
        <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
        <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
          <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
          <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
          <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
        </select>
      </div>
      <div class="btn-group pull-right">
        <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
        <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
          <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
          <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
        </select>
      </div>
    </div>
    <div class="clearfix"> </div>

    <table class="table table-striped" id="imageList">
      <thead>
        <tr>
          <th width="1%" class="hidden-phone">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th class="center hidden-phone" width="25"></th>
          <th class="nowrap" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_AUTHOR', 'user', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMAN_TEXT', 'c.cmttext', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_PUBLISHED', 'c.published', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_APPROVED', 'c.approved', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_IMAGE', 'i.imgtitle', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMAN_IP', 'c.cmtip', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_DATE', 'c.cmtdate', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_ID', 'c.cmtid', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tbody>
<?php foreach($this->items as $i => $item):
          $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$item->cmtpic);
          $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$item->cmtpic) && $item->owner == $this->_user->get('id'); ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->cmtid); ?>
          </td>
          <td class="center hidden-phone">
            <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', $canEdit || $canEditOwn ? 'href="index.php?option='._JOOM_OPTION.'&amp;controller=images&amp;task=edit&amp;cid='.$item->cmtpic : null, true); ?>
          </td>
          <td class="nowrap">
            <?php echo $item->cmtname; ?>
          </td>
          <td>
            <?php echo $item->cmttext; ?>
          </td>
          <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i); ?>
          </td>
          <td class="center">
            <?php echo JHTML::_('jgrid.state', $approved_states, $item->approved, $i); ?>
          </td>
          <td class="hidden-phone" width="10%">
<?php   if($canEdit || $canEditOwn): ?>
            <a href="index.php?option=<?php echo _JOOM_OPTION; ?>&amp;controller=images&amp;task=edit&amp;cid=<?php echo $item->cmtpic; ?>">
              <?php echo $this->escape($item->imgtitle); ?>
            </a>
<?php   else: ?>
            <?php echo $this->escape($item->imgtitle); ?>
<?php   endif; ?>
          </td>
          <td class="nowrap hidden-phone">
            <?php echo $item->cmtip; ?>
          </td>
          <td class="small hidden-phone nowrap" width="10%">
            <?php echo JHtml::_('date', $item->cmtdate, JText::_('DATE_FORMAT_LC4')); ?>
          </td>
          <td class="hidden-phone">
            <?php echo $item->cmtid; ?>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="10">
            <?php echo $this->pagination->getListFooter(); ?>
          </td>
        </tr>
      </tfoot>
    </table>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>
    <?php JHTML::_('joomgallery.credits'); ?>
  </div>
</form>