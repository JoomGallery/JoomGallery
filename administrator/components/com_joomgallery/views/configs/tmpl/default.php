<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
JHtml::_('formbehavior.chosen', 'select');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$saveOrder  = (($listOrder == 'c.ordering' || !$listOrder) && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.published') && !$this->state->get('filter.search'));
if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&controller=config&task=saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'configList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, true, false);
endif;
$sortFields = $this->getSortFields(); ?>
<form action="index.php" method="post" id="adminForm" name="adminForm">
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
        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH');?></label>
        <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" />
      </div>
      <div class="btn-group hidden-phone">
        <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
        <button class="btn hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
      </div>
      <div class="btn-group pull-right hidden-phone">
        <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
        <?php echo $this->pagination->getLimitBox(); ?>
      </div>
      <div class="btn-group pull-right hidden-phone">
        <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
        <select name="directionTable" id="directionTable" class="input-medium" onchange="var sortTable = document.getElementById('sortTable'); Joomla.tableOrdering(sortTable.options[sortTable.selectedIndex].value, this.options[this.selectedIndex].value, '');">
          <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
          <option value="asc" <?php if(strtolower($listDirn) == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
          <option value="desc" <?php if(strtolower($listDirn) == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
        </select>
      </div>
      <div class="btn-group pull-right">
        <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
        <select name="sortTable" id="sortTable" class="input-medium" onchange="var directionTable = document.getElementById('directionTable'); Joomla.tableOrdering(this.options[this.selectedIndex].value, directionTable.options[directionTable.selectedIndex].value, '');">
          <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
          <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
        </select>
      </div>
      <div class="clearfix"></div>
    </div>

    <table class="table table-striped" id="configList">
      <thead>
        <tr>
          <!--<th width="1%" class="nowrap center hidden-phone">
            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'c.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
          </th>-->
          <th width="1%" class="hidden-phone">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th>
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_CONFIGS_TITLE', 'g.title', $listDirn, $listOrder); ?>
          </th>
          <th>
            <span title="<?php echo JHtml::tooltiptext('COM_JOOMGALLERY_CONFIGS_GROUPS', 'COM_JOOMGALLERY_CONFIGS_GROUPS_TIP'); ?>" class="hasTooltip">
              <?php echo JText::_('COM_JOOMGALLERY_CONFIGS_GROUPS'); ?>
            </span>
          </th>
          <th class="hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_CONFIGS_GROUP', 'g.lft', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'c.ordering', $listDirn, $listOrder); ?>
          </th>
          <th width="1%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="6">
            <?php echo $this->pagination->getListFooter(); ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
<?php $i = 0;
      foreach($this->items as $item): ?>
        <tr class="row<?php echo $i % 2; ?>">
          <!--<td class="order nowrap center hidden-phone">
          <?php if($item->id != 1):
                  $disableClassName = '';
                  $disabledLabel    = '';
                  if(!$saveOrder):
                    $disabledLabel    = JText::_('JORDERINGDISABLED');
                    $disableClassName = 'inactive tip-top';
                  endif; ?>
            <span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
              <i class="icon-menu"></i>
            </span>
            <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" />
          <?php else: ?>
            <span class="inactive tip-top">
              <i class="icon-menu"></i>
            </span>
          <?php endif; ?>
          </td>-->
          <td class="center hidden-phone">
            <?php echo JHTML::_('grid.id', $i, $item->id); ?>
          </td>
          <td class="nowrap">
<?php   if($item->title): ?>
            <a href="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=config&task=edit&id='.$item->id); ?>">
              <?php echo $item->id == 1 ? '<i>'.JText::sprintf('COM_JOOMGALLERY_CONFIGS_DEFAULT_TITLE', $this->escape($item->title)).'</i>' : $this->escape($item->title); ?></a>
<?php   else: ?>
            <?php echo JText::_('COM_JOOMGALLERY_CONFIGS_MISSING'); ?>
<?php   endif; ?>
          </td>
          <td>
<?php   if($item->usergroups && $item->title): ?>
            <ul>
<?php     foreach($item->usergroups as $group): ?>
              <li><?php echo $group; ?></li>
<?php     endforeach; ?>
            </ul>
<?php   else:
          if($item->title): ?>
            <img src="<?php echo $this->_ambit->getIcon('error.png'); ?>" alt="Warning" /> <?php echo JText::_('COM_JOOMGALLERY_CONFIGS_BAD_ORDERING'); ?>
<?php     endif;
        endif; ?>
          </td>
          <td class="hidden-phone">
<?php   if($item->title): ?>
            <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level); ?>
            <?php echo $this->escape($item->title); ?>
<?php   else: ?>
            <img src="<?php echo $this->_ambit->getIcon('error.png'); ?>" alt="Warning" /> <?php echo JText::_('COM_JOOMGALLERY_CONFIGS_MISSING_TEXT'); ?>
<?php   endif; ?>
          </td>
          <td class="center hidden-phone">
<?php if($saveOrder && $item->id != 1): ?>
          <span><?php echo $this->pagination->orderUpIcon($i, $i > 1, 'orderup', 'JLIB_HTML_MOVE_UP', $saveOrder); ?></span>
          <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, $i < count($this->items), 'orderdown', 'JLIB_HTML_MOVE_DOWN', $saveOrder); ?></span>
          <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="text-area-order span4" />
<?php else: ?>
          <input type="hidden" name="order[]" size="5" value="<?php echo $item->ordering;?>" /><?php echo $item->ordering; ?>
<?php endif; ?>
          </td>
          <td class="center hidden-phone">
            <?php echo $item->id; ?>
          </td>
        </tr>
<?php   $i++;
      endforeach; ?>
      </tbody>
    </table>
    <div class="alert alert-block alert-info">
      <h3><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_NOTICES'); ?></h3>
      <ul>
        <li><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INFO_1'); ?></li>
        <li><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INFO_2'); ?></li>
        <li><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INFO_3'); ?></li>
        <li><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INFO_4'); ?></li>
      </ul>
    </div>
<?php if(count($this->items) == 1):
        echo $this->loadTemplate('info'); ?>
    <script type="text/javascript">
      jQuery(window).load(function(){
        jQuery('#jg-info-popup').modal('show');
      });
    </script>
<?php endif; ?>
    <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
    <input type="hidden" name="controller" value="config" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <input type="hidden" name="id" value="" />
    <input type="hidden" name="group_id" value="" />
    <?php echo JHtml::_('form.token'); ?>
    <?php JHtml::_('joomgallery.credits'); ?>
  </div>
</form>
<?php
echo $this->loadTemplate('new');
$layout = new JLayoutFile('joomgallery.config.reset', JPATH_COMPONENT . '/layouts');
echo $layout->render();
?>