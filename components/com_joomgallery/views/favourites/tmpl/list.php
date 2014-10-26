<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
if(!count($this->rows)):
  $this->_mainframe->enqueueMessage($this->output('NO_IMAGES'), 'notice');
endif;
echo $this->loadTemplate('header'); ?>
  <div class="well well-small jg-header">
    <?php echo $this->output('HEADING'); ?>
  </div>
  <div class="jg_fav_switchlayout">
    <a href="<?php echo JRoute::_('index.php?task=favourites.switchlayout&layout='.$this->getLayout()); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_FAVOURITES_SWITCH_LAYOUT'); ?>
    </a>
  </div>
  <div class="jg_fav_clearlist">
    <a href="<?php echo JRoute::_('index.php?task=favourites.removeall'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_FAVOURITES_REMOVE_ALL'); ?>
    </a>
  </div>
  <form action="<?php echo JRoute::_('index.php?view=favourites'); ?>" method="post" id="adminForm">
    <table class="table table-striped" id="imageList">
      <thead>
        <tr>
          <th width="1%" class="hidden-phone hidden">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th class="center" width="25"></th>
          <th class="nowrap">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_IMAGE_NAME', 'imgtitle', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap" width="5%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_HITS', 'hits', $listDirn, $listOrder); ?>
          </th>
<?php if($this->_config->get('jg_showdownloads')): ?>
          <th class="nowrap" width="7%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_DOWNLOADS', 'downloads', $listDirn, $listOrder); ?>
          </th>
<?php endif; ?>
          <th class="nowrap hidden-phone" width="30%">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'catid', $listDirn, $listOrder); ?>
          </th>
          <th class="nowrap" width="20%">
            <?php echo JText::_('COM_JOOMGALLERY_COMMON_ACTION'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
<?php foreach($this->rows as $i => $item): ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="center hidden-phone hidden">
            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
          </td>
          <td class="center">
            <?php echo JHtml::_('joomgallery.minithumbimg', $item, 'jg_up_eminithumb', true, true); ?>
          </td>
          <td>
            <a <?php echo $item->atagtitle; ?> href="<?php echo $item->link; ?>">
              <?php echo $item->imgtitle; ?>
            </a>
          </td>
          <td>
            <?php echo $item->hits; ?>
          </td>
<?php   if($this->_config->get('jg_showdownloads')): ?>
          <td>
            <?php echo $item->downloads; ?>
          </td>
<?php   endif; ?>
          <td class="hidden-phone">
            <?php echo JHtml::_('joomgallery.categorypath', $item->catid, true, ' &raquo; ', true, false, true); ?>
          </td>
          <td class="nowrap">
<?php   if($this->params->get('show_download_icon') == 1): ?>
            <div class="pull-left<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?>">
              <a href="<?php echo JRoute::_('index.php?task=download&id='.$item->id); ?>">
                <?php echo JHtml::_('joomgallery.icon', 'download.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?></a>
            </div>
<?php   endif;
        if($this->params->get('show_download_icon') == -1): ?>
            <div class="pull-left<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_LOGIN_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?>">
              <?php echo JHtml::_('joomgallery.icon', 'download_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?>
            </div>
<?php   endif; ?>
            <div class="pull-left<?php echo JHtml::_('joomgallery.tip', $this->output('REMOVE_TIPTEXT'), $this->output('REMOVE_TIPCAPTION'), false, false); ?>">
              <a href="<?php echo JRoute::_('index.php?option=com_joomgallery&task=favourites.removeimage&id='.$item->id); ?>">
                <?php echo JHtml::_('joomgallery.icon', 'basket_remove.png', $this->output('REMOVE_TIPCAPTION'), null, null, false); ?></a>
            </div>
<?php   if($item->show_edit_icon): ?>
            <div class="pull-left<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?>">
              <a href="<?php echo JRoute::_('index.php?view=edit&id='.$item->id.$this->redirect); ?>">
                <?php echo JHtml::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?></a>
            </div>
<?php   else: ?>
            <div class="pull-left">&nbsp;</div>
<?php   endif;
        if($item->show_delete_icon): ?>
            <div class="pull-left<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?>">
              <a href="javascript:if(confirm('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=image.delete&id='.$item->id.$this->redirect, false);?>';}">
                <?php echo JHtml::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?></a>
            </div>
<?php   else: ?>
            <div class="pull-left">&nbsp;</div>
<?php   endif; ?>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <div>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    </div>
  </form>
<?php echo $this->loadTemplate('footer');