<?php defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$ordering   = $this->state->get('ordering.array');
$saveOrder  = (($listOrder == 'c.lft' || !$listOrder) && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.published') && !$this->state->get('filter.search'));
if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&controller=categories&task=saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
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
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories');?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty( $this->sidebar)): ?>
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif;?>
    <div id="filter-bar" class="btn-toolbar">
      <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH');?></label>
        <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" />
      </div>
      <div class="btn-group hidden-phone">
        <button class="btn tip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
        <button class="btn tip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
      </div>
      <div class="btn-group pull-right hidden-phone">
        <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
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
      <div class="clearfix"></div>
    </div>

    <table class="table table-striped" id="categoryList">
      <thead>
        <tr>
          <th width="1%" class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'c.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
          </th>
          <th width="1%" class="hidden-phone">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th width="28"></th>
          <th width="1%" class="nowrap center">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_PUBLISHED', 'c.published', $listDirn, $listOrder); ?>
          </th>
          <th>
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'c.name', $listDirn, $listOrder); ?>
          </th>
          <th class="hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_PARENT_CATEGORY', 'c.parent_id', $listDirn, $listOrder); ?>
          </th>
          <th width="10%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_ACCESS', 'access_level', $listDirn, $listOrder); ?>
          </th>
          <th width="5%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_OWNER', 'c.owner', $listDirn, $listOrder); ?>
          </th>
          <th width="5%" class="center hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_TYPE', 'c.owner', $listDirn, $listOrder); ?>
          </th>
          <th width="1%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'c.cid', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 0;
        $disabled = $saveOrder ?  '' : 'disabled="disabled"';
        $display_hidden_asterisk = false;
        $originalOrders = array();
        foreach($this->items as $key => $item):
          $orderkey   = array_search($item->cid, $ordering[$item->parent_id]);
          $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.category.'.$item->cid);
          $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.category.'.$item->cid) && $item->owner == $this->_user->get('id');
          $canChange  = $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.category.'.$item->cid);

          // Get the parents of item for sorting
          if ($item->level > 1)
          {
            $parentsStr = '';
            $_currentParentId = $item->parent_id;
            $parentsStr = ' '.$_currentParentId;
            for($j = 0; $j < $item->level; $j++)
            {
              foreach($ordering as $k => $v)
              {
                $v = implode('-', $v);
                $v = '-'.$v.'-';
                if(strpos($v, '-'.$_currentParentId.'-') !== false)
                {
                  $parentsStr .= ' '.$k;
                  $_currentParentId = $k;
                  break;
                }
              }
            }
          }
          else
          {
            $parentsStr = '';
          }
          ?>
        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id;?>" item-id="<?php echo $item->cid?>" parents="<?php echo $parentsStr; ?>" level="<?php echo $item->level; ?>">
          <td class="order nowrap center hidden-phone">
          <?php if($canChange):
                  $disableClassName = '';
                  $disabledLabel    = '';
                  if(!$saveOrder):
                    $disabledLabel    = JText::_('JORDERINGDISABLED');
                    $disableClassName = 'inactive tip-top';
                  endif; ?>
            <span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
              <i class="icon-menu"></i>
            </span>

          <?php else : ?>
            <span class="sortable-handler inactive">
              <i class="icon-menu"></i>
            </span>
          <?php endif; ?>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
          </td>
          <td class="center hidden-phone">
            <?php echo JHtml::_('grid.id', $i, $item->cid); ?>
          </td>
          <td>
            <?php echo JHTML::_('joomgallery.minithumbcat', $item, 'jg_minithumb', $canEdit || $canEditOwn ? 'href="'.JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories&task=edit&cid='.$item->cid) : null, true); ?>
          </td>
          <td class="center">
            <?php echo JHtml::_('jgrid.published', $item->published, $i, '', $canChange);
                  if($item->published && $item->hidden):
                    echo '<span title="'.JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN').'">'.JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK').'</span>';
                    $display_hidden_asterisk = true;
                  endif; ?>
          </td>
          <td>
            <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1); ?>
<?php if($canEdit || $canEditOwn): ?>
            <a href="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories&task=edit&cid='.$item->cid); ?>">
              <?php echo $this->escape($item->name); ?></a>
<?php else: ?>
            <?php echo $this->escape($item->name); ?>
<?php endif; ?>
            <span class="small" title="<?php /*echo $this->escape($item->path);*/ ?>">
              <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
            </span>
          </td>
          <td class="hidden-phone">
            <?php echo JHtml::_('joomgallery.categorypath', $item->cid, false); ?>
          </td>
          <td class="small hidden-phone">
            <?php echo $this->escape($item->access_level); ?>
          </td>
          <td class="center nowrap hidden-phone">
            <?php echo JHTML::_('joomgallery.displayname', $item->owner); ?>
          </td>
          <td class="center hidden-phone">
            <?php echo JHTML::_('joomgallery.type', $item); ?>
          </td>
          <td class="center hidden-phone">
            <span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
              <?php echo (int) $item->cid; ?></span>
          </td>
        </tr>
      <?php $i++;
            endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="11">
            <?php echo $this->pagination->getListFooter(); ?>
<?php if($display_hidden_asterisk): ?>
            <div align="left">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
            </div>
<?php endif; ?>
          </td>
        </tr>
      </tfoot>
    </table>
    <?php //Load the batch processing form ?>
    <?php echo $this->loadTemplate('batch'); ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
    <?php echo JHtml::_('form.token');
    JHtml::_('joomgallery.credits'); ?>
  </div>
</form>