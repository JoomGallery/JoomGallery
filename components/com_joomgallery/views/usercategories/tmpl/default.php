<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
echo $this->loadTemplate('header');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder                = $this->escape($this->state->get('list.ordering'));
$listDirn                 = $this->escape($this->state->get('list.direction'));
$display_hidden_asterisk  = false;
$saveOrder                = (($listOrder == 'c.lft') && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.inuse'));

if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&task=categories.saveorder&format=json';
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
  <div class="jg_userpanelview">
<?php if($this->params->get('show_categories_notice')): ?>
    <div class="center alert alert-info">
      <small>
        <strong><?php echo JText::_('COM_JOOMGALLERY_USERCATEGORIES_NEW_CATEGORY_NOTE'); ?></strong><br />
        <?php echo JText::sprintf('COM_JOOMGALLERY_USERCATEGORIES_NEW_CATEGORY_MAXCOUNT', $this->_config->get('jg_maxusercat')); ?><br />
        <?php echo JText::sprintf('COM_JOOMGALLERY_USERCATEGORIES_NEW_CATEGORY_YOURCOUNT', $this->categoryNumber); ?><br />
        <?php echo JText::sprintf('COM_JOOMGALLERY_USERCATEGORIES_NEW_CATEGORY_REMAINDER', ($this->_config->get('jg_maxusercat') - $this->categoryNumber)); ?><br />
      </small>
    </div>
<?php endif; ?>
    <div class="jg_up_head btn-toolbar">
<?php if($this->params->get('show_category_button')): ?>
      <div class="btn-group">
        <button type="button" class="btn" onclick="javascript:location.href='<?php echo JRoute::_('index.php?view=editcategory'.$this->slimitstart, false); ?>';">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_NEW_CATEGORY'); ?>
        </button>
      </div>
<?php endif; ?>
    </div>
    <form action="<?php echo JRoute::_('index.php?view=usercategories'); ?>" method="post" name="adminForm" id="adminForm">
      <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
          <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_FILTER_SEARCH'); ?></label>
          <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_JOOMGALLERY_COMMON_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_FILTER_SEARCH'); ?>" />
        </div>
        <div class="btn-group pull-left hidden-phone">
          <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_FILTER_SEARCH'); ?>"><i class="icon-search"></i></button>
          <button class="btn hasTooltip" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
        </div>
        <div class="btn-group pull-right hidden-phone">
          <label for="limit" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH_LIMIT'); ?></label>
          <?php echo $this->pagination->getLimitBox(); ?>
        </div>
        <div class="btn-group pull-right hidden-phone">
          <label for="directionTable" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDER_DIRECTION');?></label>
          <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
            <option value=""><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDER_DIRECTION');?></option>
            <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDERING_ASC');?></option>
            <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDERING_DESC');?></option>
          </select>
        </div>
        <div class="btn-group pull-right">
          <label for="sortTable" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SORT_BY_ORDERING');?></label>
          <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
            <option value=""><?php echo JText::_('COM_JOOMGALLERY_COMMON_SORT_BY_ORDERING');?></option>
            <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
          </select>
        </div>
      </div>
      <div class="clearfix"> </div>
      <div class="btn-toolbar">
        <div class="btn-group pull-left hidden-phone">
          <?php echo $this->lists['filter_state']; ?>
        </div>
      </div>
      <div class="clearfix"> </div>
      <table class="table table-striped" id="categoryList">
        <thead>
          <tr>
            <th width="1%" class="nowrap center hidden-phone">
              <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'COM_JOOMGALLERY_COMMON_REORDER'); ?>
            </th>
            <th width="1%" class="hidden-phone hidden">
              <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
            </th>
<?php       if($this->_config->get('jg_showminithumbs')): ?>
            <th class="center" width="25"></th>
<?php       endif ?>
            <th class="nowrap">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'c.name', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap center" width="5%">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_USERCATEGORIES_IMAGES', 'images', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap hidden-phone" width="30%">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_PARENT_CATEGORY', 'c.parent_id', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap" width="10%">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_ACTION'); ?>
            </th>
            <th class="nowrap center" width="5%">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED'); ?>
            </th>
          </tr>
        </thead>
        <tbody>
<?php   $i = 0;
        $disabled           = $saveOrder ?  '' : 'disabled="disabled"';
        $originalOrders     = array();
        $allowed_categories = $this->_ambit->getCategoryStructure();
        foreach($this->items as $i => $item):
          $orderkey   = array_search($item->cid, $this->ordering[$item->parent_id]);
          $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.category.'.$item->cid);
          $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.category.'.$item->cid) && $item->owner && $item->owner == $this->_user->get('id');
          $canChange  = $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.category.'.$item->cid);
          $canDelete  = $this->_user->authorise('core.delete', _JOOM_OPTION.'.category.'.$item->cid);
          $canView    = isset($allowed_categories[$item->cid]);

          // Get the parents of item for sorting
          if ($item->level > 1)
          {
            $parentsStr = '';
            $_currentParentId = $item->parent_id;
            $parentsStr = ' '.$_currentParentId;
            for($j = 0; $j < $item->level; $j++)
            {
              foreach($this->ordering as $k => $v)
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
          } ?>
          <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id;?>" item-id="<?php echo $item->cid?>" parents="<?php echo $parentsStr; ?>" level="<?php echo $item->level; ?>">
            <td class="order nowrap center hidden-phone">
<?php       if($canChange):
              $disableClassName = '';
              $disabledLabel    = '';
              if(!$saveOrder):
                $disabledLabel    = JText::_('COM_JOOMGALLERY_COMMON_ORDERING_DISABLED');
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
            <td class="center hidden-phone hidden">
              <?php echo JHtml::_('grid.id', $i, $item->cid); ?>
            </td>
<?php       if($this->_config->get('jg_showminithumbs')): ?>
            <td class="center">
<?php         if($item->thumbnail && isset($item->id)):
                echo JHTML::_('joomgallery.minithumbcat', $item, 'jg_up_eminithumb', $canView, true);
              endif; ?>
            </td>
<?php       endif ?>
            <td>
            <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level - 1); ?>
<?php       if($canView): ?>
              <a href="<?php echo JRoute::_('index.php?view=category&catid='.$item->cid); ?>">
<?php       endif;
               echo $item->name;
            if($canView): ?>
              </a>
<?php       endif; ?>
            </td>
            <td class="center">
              <?php echo $item->images; ?>
            </td>
            <td class="hidden-phone">
<?php       if($item->parent_id == 1): ?>
              <?php echo '-'; ?>
<?php       else: ?>
              <?php echo JHtml::_('joomgallery.categorypath', $item->parent_id, true, ' &raquo; ', true, false, true); ?>
<?php       endif; ?>
            </td>
            <td class="nowrap">
<?php       if($canEdit || $canEditOwn): ?>
              <div class="pull-left<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_CATEGORY_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_CATEGORY_TIPCAPTION'); ?>">
                <a href="<?php echo JRoute::_('index.php?view=editcategory&catid='.$item->cid.$this->slimitstart); ?>">
                  <?php echo JHTML::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_CATEGORY_TIPCAPTION'); ?></a>
              </div>
<?php       endif;
            if($canDelete && !$item->children && !$item->images): ?>
              <div class="pull-left<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_CATEGORY_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_CATEGORY_TIPCAPTION'); ?>">
                <a href="javascript:if (confirm('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=category.delete&catid='.$item->cid.$this->slimitstart, false); ?>';}">
                  <?php echo JHTML::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE'); ?></a>
              </div>
<?php       endif; ?>
            </td>
            <td class=center>
<?php         $p_img    = 'cross';
              if($item->published):
                $p_img = 'tick';
              endif;
              if($canChange):
                $p_title  = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_CATEGORY_TIPCAPTION');
                $p_text   = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_CATEGORY_TIPTEXT');
                if($item->published):
                  $p_title = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_CATEGORY_TIPCAPTION');
                  $p_text  = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_CATEGORY_TIPTEXT');
                endif; ?>
              <a href="<?php echo JRoute::_('index.php?task=category.publish&catid='.$item->cid.$this->slimitstart); ?>"<?php echo JHTML::_('joomgallery.tip', $p_text, $p_title, true, false); ?>>
                <?php echo JHTML::_('joomgallery.icon', $p_img.'.png', $p_img); ?></a>
<?php         else:
                $p_title  = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISHED');
                if($item->published):
                  $p_title = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED');
                endif; ?>
              <div class="<?php echo JHTML::_('joomgallery.tip', '', $p_title); ?>">
                <?php echo JHTML::_('joomgallery.icon', $p_img.'.png', $p_img, null, null, false); ?>
              </div>
<?php         endif;
              if($item->published && $item->hidden):
                $h_title = JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK');
                $h_text  = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN');
                echo '<span'.JHTML::_('joomgallery.tip', $h_text, $h_title, true, false).'>'.JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK').'</span>';
                $display_hidden_asterisk = true;
              endif; ?>
            </td>
          </tr>
<?php   endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="8">
<?php         if($this->pagination->get('pagesTotal') > 1): ?>
                <?php echo $this->pagination->getListFooter();
              endif;
              if($display_hidden_asterisk): ?>
              <div class = "small pull-right">
                <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
              </div>
<?php         endif; ?>
            </td>
          </tr>
        </tfoot>
      </table>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
      <input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </form>
    <div class="jg_up_head btn-toolbar">
      <div class="btn-group">
        <button type="button" class="btn" name="button" onclick ="javascript:location.href='<?php echo JRoute::_('index.php?view=userpanel', false); ?>';">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_BACK_TO_USER_PANEL'); ?>
        </button>
      </div>
    </div>
  </div>
<?php echo $this->loadTemplate('footer');