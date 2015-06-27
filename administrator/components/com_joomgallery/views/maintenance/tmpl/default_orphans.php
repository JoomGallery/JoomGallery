<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
      if($this->current_tab != 'orphans'): ?>
  <div class="alert alert-info alert-block">
    <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_MSG_REFRESH_NECESSARY'); ?>
    <script type="text/javascript">
      $$('.cpanel-panel-joom-maintenance-file-images').addEvent('click', function(){
        document.location.href="index.php?option=<?php echo _JOOM_OPTION; ?>&controller=maintenance&tab=orphans";
      });
    </script>
  </div>
<?php else:
        $listOrder  = $this->escape($this->state->get('list.ordering'));
        $listDirn   = $this->escape($this->state->get('list.direction')); ?>
  <form action="index.php" method="post" id="adminForm" name="adminForm">
    <div id="filter-bar" class="btn-toolbar">
      <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?></label>
        <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" />
      </div>
      <div class="btn-group pull-left hidden-phone">
        <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>"><i class="icon-search"></i></button>
        <button class="btn hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
      </div>
      <?php if($this->checked): ?>
      <div class="btn-group pull-right hidden-phone">
        <label for="limit" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH_LIMIT'); ?></label>
        <?php echo $this->pagination->getLimitBox(); ?>
      </div>
      <?php endif; ?>
      <div class="btn-group pull-right">
        <?php echo $this->lists['orphan_filter']; ?>
      </div>
      <div class="btn-group pull-right">
        <?php echo $this->lists['orphan_proposal']; ?>
      </div>
    </div>
    <div class="clearfix"> </div>

    <table class="table table-striped" id="orphanList">
      <thead>
        <tr>
          <th width="1%">
            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
          </th>
          <th>
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_MAIMAN_OP_FILENAME_AND_PATH', 'a.fullpath', $listDirn, $listOrder); ?>
          </th>
          <th width="10%" class="center hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_TYPE', 'a.type', $listDirn, $listOrder); ?>
          </th>
          <th width="15%" class="center hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_MAIMAN_SUGGESTION', 'a.refid', $listDirn, $listOrder); ?>
          </th>
          <th width="1%" class="nowrap hidden-phone">
            <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_ID', 'a.id', $listDirn, $listOrder); ?>
          </th>
        </tr>
      </thead>
      <tfoot>
<?php   if($this->checked):
          if($n = count($this->items)): ?>
        <tr>
          <td colspan="5">
            <?php echo $this->pagination->getListFooter(); ?>
          </td>
        </tr>
<?php     endif; ?>
        <tr>
          <td colspan="5">
<?php     if(!$n): ?>
            <div class="alert alert-success">
               <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_MSG_NO_ORPHANS_FOUND'); ?>
            </div>
<?php     endif; ?>
            <div>
              <button class="btn btn-large btn-primary" onclick="Joomla.submitform('check');return false;" type="button"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_AGAIN'); ?></button>
            </div>
          </td>
        </tr>
<?php   else: ?>
        <tr>
          <td colspan="5">
            <div class="alert alert-info">
              <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_PLEASE_CHECK'); ?>
            </div>
            <div>
              <button class="btn btn-large btn-primary" onclick="Joomla.submitform('check');return false;" type="button"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CHECK'); ?></button>
            </div>
            <div class="alert">
              <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_CHECK_NOTE'); ?>
            </div>
          </td>
        </tr>
<?php   endif; ?>
      </tfoot>
      <tbody>
<?php   if($this->checked):
          $k = 0;
          for($i = 0; $i < $n; $i++):
            $row = &$this->items[$i]; ?>
        <tr class="row<?php echo $k; ?>">
          <td>
            <?php echo JHtml::_('grid.id', $i, $row->id); ?>
          </td>
          <td>
            <?php echo $row->type == 'unknown' ? $this->warning(JText::_('COM_JOOMGALLERY_MAIMAN_UNKNOWN_FILE_TYPE'), JText::_('COM_JOOMGALLERY_MAIMAN_UNKNOWN_FILE_TYPE_LONG')) : ''; ?>&nbsp;<?php echo $row->fullpath; ?>
          </td>
          <td class="center hidden-phone">
            <?php if($row->type != 'unknown'):
                    echo JText::_('COM_JOOMGALLERY_MAIMAN_TYPE_'.strtoupper($row->type));
                  else:
                    #jimport('joomla.filesystem.file');
                    echo JText::sprintf('COM_JOOMGALLERY_MAIMAN_TYPE_UNKNOWN_VAL'/*, JFile::getExt($row->fullpath)*/);
                  endif; ?>
          </td>
          <td class="center hidden-phone">
<?php       if($row->refid): ?>
            <span title="<?php echo JText::_('COM_JOOMGALLERY_MAIMAN_SHOW_IMAGE_DETAILS'); ?>" class="hasTooltip">
              <a href="index.php?option=<?php echo _JOOM_OPTION; ?>&amp;controller=images&amp;task=edit&amp;cid=<?php echo $row->refid; ?>">
                <?php echo $row->title; ?>
              </a>
            </span>
            <?php echo $this->correct('addorphan', $row->id, JText::_('COM_JOOMGALLERY_MAIMAN_ADD_ORPHAN_TO_IMAGE')); ?>
<?php       else: ?>
            <?php echo $this->cross('COM_JOOMGALLERY_MAIMAN_NO_PROPOSAL'); ?>
            <?php echo $this->correct('deleteorphan', 0, JText::_('COM_JOOMGALLERY_MAIMAN_DELETE_THIS_ORPHAN'), 'javascript:listItemTask(\'cb'.$i.'\', \'deleteorphan\');'); ?>
<?php       endif; ?>
          </td>
          <td class="hidden-phone">
            <?php echo $row->id; ?>
          </td>
        </tr>
<?php       $k = 1 - $k;
          endfor;
        endif; ?>
      </tbody>
    </table>
    <div class="accordion-inner">
      <fieldset class="batch">
        <legend><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_BATCH_JOBS'); ?></legend>
        <?php echo $this->lists['orphan_jobs']; ?>
        <div id="batchjobs"></div>
        <button type="submit" class="btn btn-primary" onclick="if(document.adminForm.job.value == ''){return false;}else{submitbutton(document.adminForm.job.value);}"><?php echo JText::_('COM_JOOMGALLERY_MAIMAN_APPLY'); ?></button>
      </fieldset>
    </div>
    <div>
      <input type="hidden" name="option" value="<?php echo _JOOM_OPTION; ?>" />
      <input type="hidden" name="controller" value="maintenance" />
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="tab" value="orphans" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </div>
  </form>
<?php endif;