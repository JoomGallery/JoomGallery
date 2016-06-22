<?php defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirn       = $this->escape($this->state->get('list.direction'));
$columns        = 11;
$ordering       = $this->state->get('ordering.array');
$saveOrder      = (($listOrder == 'c.lft' || !$listOrder) && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.published') && !$this->state->get('filter.search') && !$this->state->get('filter.owner'));
$originalOrders = array();

if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&controller=categories&task=saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
endif;

JFactory::getDocument()->addScriptDeclaration(
  '
  jQuery(document).ready(function() {
    jQuery(\'.js-stools-btn-clear\').click(function() {
      jQuery(\'#filter_owner\').val(\'\');
    });
  });'
);
?>

<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories');?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty( $this->sidebar)): ?>
  <div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
  </div>
  <div id="j-main-container" class="span10">
<?php else : ?>
  <div id="j-main-container">
<?php endif;?>
  <?php
    // Search tools bar
    echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
  ?>
<?php if($this->state->get('filter.inuse') && !$this->get('Total')) : ?>
    <div class="alert alert-no-items">
      <?php echo JText::_('COM_JOOMGALLERY_CATMAN_MSG_NO_CATEGORIES_FOUND_MATCHING_YOUR_QUERY'); ?>
    </div>
<?php else : ?>
    <table class="table table-striped" id="categoryList">
      <thead>
        <tr>
          <th width="1%" class="nowrap center hidden-phone">
            <?php echo JHtml::_('searchtools.sort', '', 'c.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
          </th>
          <th width="1%" class="center">
            <?php echo JHtml::_('grid.checkall'); ?>
          </th>
          <th class="center" width="28"></th>
          <th width="1%" class="nowrap center">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_PUBLISHED', 'c.published', $listDirn, $listOrder); ?>
          </th>
          <th>
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'c.name', $listDirn, $listOrder); ?>
          </th>
          <th class="hidden-phone">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_PARENT_CATEGORY', 'c.parent_id', $listDirn, $listOrder); ?>
          </th>
          <th width="10%" class="nowrap hidden-phone">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_ACCESS', 'access_level', $listDirn, $listOrder); ?>
          </th>
          <th width="5%" class="nowrap hidden-phone">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_OWNER', 'c.owner', $listDirn, $listOrder); ?>
          </th>
          <th width="5%" class="center hidden-phone">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_TYPE', 'c.owner', $listDirn, $listOrder); ?>
          </th>
          <th width="1%" class="nowrap hidden-phone">
            <?php echo JHtml::_('searchtools.sort',  'COM_JOOMGALLERY_COMMON_ID', 'c.cid', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 0;
        $display_hidden_asterisk = false;
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
            <?php
            $iconClass = '';
            if (!$canChange)
            {
              $iconClass = ' inactive';
            }
            elseif (!$saveOrder)
            {
              $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
            }
            ?>
            <span class="sortable-handler<?php echo $iconClass ?>">
              <span class="icon-menu"></span>
            </span>
            <?php if ($canChange && $saveOrder) : ?>
              <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" class="width-20 text-area-order " />
            <?php endif; ?>
          </td>
          <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->cid); ?>
          </td>
          <td class="center">
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
          <td class="nowrap hidden-phone">
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
          <td colspan="<?php echo $columns; ?>">
          </td>
        </tr>
      </tfoot>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>
<?php if($display_hidden_asterisk): ?>
    <div class = "small pull-left">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
    </div>
<?php endif; ?>
    <?php //Load the batch processing form ?>
    <?php echo $this->loadTemplate('batch'); ?>
<?php endif;?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
    <?php echo JHtml::_('form.token');
    JHtml::_('joomgallery.credits'); ?>
  </div>
</form>