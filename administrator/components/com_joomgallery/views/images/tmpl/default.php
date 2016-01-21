<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder                = $this->escape($this->state->get('list.ordering'));
$listDirn                 = $this->escape($this->state->get('list.direction'));
$saveOrder                = $listOrder == 'a.ordering';
$columns                  = 15;
$display_hidden_asterisk  = false;
$approved_states          = array( 1 => array('reject', 'COM_JOOMGALLERY_COMMON_APPROVED', 'COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE', 'COM_JOOMGALLERY_COMMON_APPROVED', true, 'publish', 'publish'),
                                   0 => array('approve', 'COM_JOOMGALLERY_COMMON_NOT_APPROVED', 'COM_JOOMGALLERY_IMGMAN_APPROVE_IMAGE', 'COM_JOOMGALLERY_COMMON_NOT_APPROVED', true, 'unpublish', 'unpublish'),
                                  -1 => array('approve', 'COM_JOOMGALLERY_COMMON_REJECTED', 'COM_JOOMGALLERY_IMGMAN_APPROVE_IMAGE', 'COM_JOOMGALLERY_COMMON_REJECTED', true, 'unpublish', 'unpublish'));
if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&controller=images&task=saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'imageList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, false);
endif;

JFactory::getDocument()->addScriptDeclaration(
  '
  jQuery(document).ready(function() {
    jQuery(\'.js-stools-btn-clear\').click(function() {
      jQuery(\'#filter_owner\').val(\'\');
      ' . ($this->_config->get('jg_ajaxcategoryselection') ? 'jQuery(\'#filter_category\').val(\'\');' : '') . '
    });
  });'
);
?>

<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images');?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty($this->sidebar)): ?>
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
      <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_MSG_NO_IMAGES_FOUND_MATCHING_YOUR_QUERY'); ?>
    </div>
<?php else : ?>
    <table class="table table-striped" id="imageList">
      <thead>
        <tr>
          <th width="1%" class="nowrap center hidden-phone">
            <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
          </th>
          <th width="1%" class="center">
            <?php echo JHtml::_('grid.checkall'); ?>
          </th>
          <th width="7%">
            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
          </th>
          <th class="center hidden-phone" width="25"></th>
          <th class="nowrap">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_TITLE', 'a.imgtitle', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_APPROVED', 'a.approved', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'category_name', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_ACCESS', 'access_level', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_OWNER', 'a.owner', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_TYPE', 'a.owner', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_AUTHOR', 'a.imgauthor', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_DATE', 'a.imgdate', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_IMGMAN_HITS', 'a.hits', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="7%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_DOWNLOADS', 'a.downloads', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="1%" class="nowrap">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_ID', 'a.id', $listDirn, $listOrder); ?>
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
              <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
            <?php endif; ?>
          </td>
          <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
          </td>
          <td>
            <div class="btn-group pull-left">
              <?php echo JHtml::_('jgrid.published', $item->published, $i, '', $canChange); ?>
              <?php echo $this->featured($item->featured, $i, '', $canChange); ?>
            </div>
            <div class="pull-left">
              <?php if($item->published && $item->hidden):
                      echo '<span title="'.JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN').'">'.JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK').'</span>';
                      $display_hidden_asterisk = true;
                    endif; ?>
            </div>
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
          <td colspan="<?php echo $columns; ?>">
          </td>
        </tr>
      </tfoot>
    </table>
<?php endif; ?>
<?php echo $this->pagination->getListFooter(); ?>
<?php if($display_hidden_asterisk): ?>
    <div class = "small pull-left">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
    </div>
<?php endif; ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
    <?php JHTML::_('joomgallery.credits'); ?>
  </div>
</form>
<?php echo $this->loadTemplate('reject');