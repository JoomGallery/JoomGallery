<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder                = $this->escape($this->state->get('list.ordering'));
$listDirn                 = $this->escape($this->state->get('list.direction'));
$saveOrder                = $listOrder == 'a.ordering';
$display_hidden_asterisk  = false;
$approved_states          = array( 1 => array('reject', 'COM_JOOMGALLERY_COMMON_APPROVED', 'COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE', 'COM_JOOMGALLERY_COMMON_APPROVED', true, 'publish', 'publish'),
                                   0 => array('approve', 'COM_JOOMGALLERY_COMMON_NOT_APPROVED', 'COM_JOOMGALLERY_IMGMAN_APPROVE_IMAGE', 'COM_JOOMGALLERY_COMMON_NOT_APPROVED', true, 'unpublish', 'unpublish'),
                                  -1 => array('approve', 'COM_JOOMGALLERY_COMMON_REJECTED', 'COM_JOOMGALLERY_IMGMAN_APPROVE_IMAGE', 'COM_JOOMGALLERY_COMMON_REJECTED', true, 'unpublish', 'unpublish'));
if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&controller=images&task=saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'imageList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, false);
endif;

$sortFields = $this->getSortFields();
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
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images');?>" method="post" name="adminForm" id="adminForm">
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
          <th width="1%" class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
          </th>
          <th width="1%" class="hidden-phone">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th class="center hidden-phone" width="25"></th>
          <th class="nowrap">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_TITLE', 'a.imgtitle', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_PUBLISHED', 'a.published', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_APPROVED', 'a.approved', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'category_name', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_ACCESS', 'access_level', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_OWNER', 'a.owner', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_TYPE', 'a.owner', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_AUTHOR', 'a.imgauthor', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_DATE', 'a.imgdate', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_IMGMAN_HITS', 'a.hits', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_DOWNLOADS', 'a.downloads', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="1%" class="nowrap">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_ID', 'a.id', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tbody>
<?php foreach($this->items as $i => $item):
        $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$item->id);
        $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$item->id) && $item->owner == $this->_user->get('id');
        $canChange  = $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$item->id); ?>
        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid ?>">
          <td class="order nowrap center hidden-phone">
          <?php if($canChange) :
            $disableClassName = '';
            $disabledLabel    = '';

            if (!$saveOrder) :
              $disabledLabel    = JText::_('JORDERINGDISABLED');
              $disableClassName = 'inactive tip-top';
            endif; ?>
            <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
              <i class="icon-menu"></i>
            </span>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
          <?php else : ?>
            <span class="sortable-handler inactive" >
              <i class="icon-menu"></i>
            </span>
          <?php endif; ?>
          </td>
          <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
          </td>
          <td class="center hidden-phone">
            <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', $canEdit || $canEditOwn ? 'href="'.JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&task=edit&cid='.$item->id) : null, true); ?>
          </td>
          <td>
<?php if($canEdit || $canEditOwn): ?>
              <a href="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&task=edit&cid='.$item->id);?>">
                <?php echo $this->escape($item->imgtitle); ?></a>
<?php else: ?>
              <?php echo $this->escape($item->imgtitle); ?>
<?php endif; ?>
            <span class="small">
              <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
            </span>
          </td>
          <td class="center">
            <?php echo JHTML::_('jgrid.published', $item->published, $i, '', $canChange);
                  if($item->published && $item->hidden):
                    echo '<span title="'.JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN').'">'.JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK').'</span>';
                    $display_hidden_asterisk = true;
                  endif; ?>
          </td>
          <td class="center">
            <?php echo JHTML::_('joomgallery.approved', $approved_states, $item->approved, $i, '', $canChange, $item->id, $item->owner); ?>
          </td>
          <td class="small hidden-phone">
            <?php echo $this->escape($item->category_name); ?>
          </td>
          <td class="small hidden-phone">
            <?php echo $this->escape($item->access_level); ?>
          </td>
          <td class="small hidden-phone nowrap">
            <?php echo JHTML::_('joomgallery.displayname', $item->owner); ?>
          </td>
          <td class="center hidden-phone">
            <?php echo JHTML::_('joomgallery.type', $item); ?>
          </td>
          <td class="small hidden-phone">
            <?php echo $item->imgauthor; ?>
          </td>
          <td class="small hidden-phone nowrap">
            <?php echo JHTML::_('date', $item->imgdate, JText::_('DATE_FORMAT_LC4')); ?>
          </td>
          <td class="hidden-phone">
            <?php echo $item->hits; ?>
          </td>
          <td class="hidden-phone">
            <?php echo $item->downloads; ?>
          </td>
          <td class="hidden-phone">
            <?php echo $item->id; ?>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="15">
            <?php echo $this->pagination->getListFooter(); ?>
<?php if($display_hidden_asterisk): ?>
            <div class = "small pull-left">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
            </div>
<?php endif; ?>
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
<?php echo $this->loadTemplate('reject');