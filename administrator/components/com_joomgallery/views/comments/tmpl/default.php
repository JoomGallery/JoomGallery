<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$columns          = 10;
$approved_states  = array(1 => array('reject', 'COM_JOOMGALLERY_COMMON_APPROVED', 'COM_JOOMGALLERY_COMMAN_REJECT_COMMENT', 'COM_JOOMGALLERY_COMMON_APPROVED', false, 'publish', 'publish'),
                          0 => array('approve', 'COM_JOOMGALLERY_COMMON_REJECTED', 'COM_JOOMGALLERY_COMMAN_APPROVE_COMMENT', 'COM_JOOMGALLERY_COMMON_REJECTED', false, 'unpublish', 'unpublish'));
?>
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=comments');?>" method="post" name="adminForm" id="adminForm">
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
      <?php echo JText::_('COM_JOOMGALLERY_COMMAN_MSG_NO_COMMENTS_FOUND_MATCHING_YOUR_QUERY'); ?>
    </div>
<?php else : ?>
    <table class="table table-striped" id="imageList">
      <thead>
        <tr>
          <th width="1%" class="center">
            <?php echo JHtml::_('grid.checkall'); ?>
          </th>
          <th class="center" width="25"></th>
          <th class="nowrap" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_AUTHOR', 'user', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMAN_TEXT', 'c.cmttext', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_PUBLISHED', 'c.published', $listDirn, $listOrder); ?>
          </th>
          <th class="center" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_APPROVED', 'c.approved', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_IMAGE', 'i.imgtitle', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMAN_IP', 'c.cmtip', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="10%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_DATE', 'c.cmtdate', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap hidden-phone" width="5%">
            <?php echo JHtml::_('searchtools.sort', 'COM_JOOMGALLERY_COMMON_ID', 'c.cmtid', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tbody>
<?php foreach($this->items as $i => $item):
          $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$item->cmtpic);
          $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$item->cmtpic) && $item->owner == $this->_user->get('id'); ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item->cmtid); ?>
          </td>
          <td class="center">
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
          <td colspan="<?php echo $columns; ?>">
          </td>
        </tr>
      </tfoot>
    </table>
<?php endif; ?>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
    <?php JHTML::_('joomgallery.credits'); ?>
  </div>
</form>