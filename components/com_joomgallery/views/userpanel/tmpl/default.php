<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder                = $this->escape($this->state->get('list.ordering'));
$listDirn                 = $this->escape($this->state->get('list.direction'));
$saveOrder                = (($listOrder == 'ordering') && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.inuse'));
$display_hidden_asterisk  = false;
$editingforms             = "";

if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&task=images.saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'imageList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, false);
endif;

$sortFields = $this->getSortFields();

echo $this->loadTemplate('header');
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
    <div class="jg_up_head btn-toolbar">
<?php if($this->params->get('show_upload_button')): ?>
      <div class="btn-group">
        <button type="button" class="btn" onclick="javascript:location.href='<?php echo JRoute::_('index.php?view=upload', false); ?>';">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_UPLOAD_NEW_IMAGE'); ?>
        </button>
      </div>
<?php endif;
      if($this->params->get('show_categories_button')): ?>
      <div class="btn-group">
        <button type="button" class="btn" onclick="javascript:location.href='<?php echo JRoute::_('index.php?view=usercategories', false); ?>';">
          <?php echo JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES'); ?>
        </button>
      </div>
<?php endif; ?>
    </div>
    <form action="<?php echo JRoute::_('index.php?view=userpanel'); ?>" method="post" name="adminForm" id="adminForm">
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
        <div class="btn-group pull-right hidden-phone">
          <?php echo JHtml::_('joomselect.categorylist', $this->state->get('filter.category'), 'filter_category', 'onchange="document.adminForm.submit();"', null, '- ', 'filter'); ?>
        </div>
      </div>
      <div class="clearfix"> </div>
      <table class="table table-striped" id="imageList">
        <thead>
          <tr>
            <th width="1%" class="nowrap center hidden-phone jg-visible-hidden-toggle">
              <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'COM_JOOMGALLERY_COMMON_REORDER'); ?>
            </th>
            <th width="1%" class="hidden-phone hidden">
              <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
            </th>
<?php       if($this->_config->get('jg_showminithumbs')): ?>
            <th class="center" width="25"></th>
<?php       endif ?>
            <th class="nowrap">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_IMAGE_NAME', 'imgtitle', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap" width="5%">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_HITS', 'hits', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap" width="7%">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_DOWNLOADS', 'downloads', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap hidden-phone" width="30%">
              <?php echo JHtml::_('grid.sort', 'COM_JOOMGALLERY_COMMON_CATEGORY', 'catid', $listDirn, $listOrder); ?>
            </th>
            <th class="nowrap" width="13%">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_ACTION'); ?>
            </th>
<?php       if(!$this->_config->get('jg_approve')): ?>
            <th class="nowrap" width="5%">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED'); ?>
            </th>
<?php       endif; ?>
<?php       if($this->_config->get('jg_approve')): ?>
            <th class="nowrap center" width="10%" colspan="2">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_STATES'); ?>
            </th>
<?php       endif; ?>
          </tr>
        </thead>
        <tbody>
<?php   $allowed_categories = $this->_ambit->getCategoryStructure();
        foreach($this->items as $i => $item):
          $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$item->id);
          $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$item->id) && $item->owner && $item->owner == $this->_user->get('id');
          $canChange  = $this->_user->authorise('core.edit.state', _JOOM_OPTION.'.image.'.$item->id);
          $canView    =    $item->approved == 1
                        && $item->published
                        && in_array($item->access, $this->_user->getAuthorisedViewLevels())
                        && isset($allowed_categories[$item->catid]);
          ?>
          <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid ?>" id="jg-title-row-<?= $item->id ?>" >
            <td class="order nowrap center hidden-phone jg-visible-hidden-toggle">
            <?php if($canChange) :
              $disableClassName = '';
              $disabledLabel    = '';

              if (!$saveOrder) :
                $disabledLabel    = JText::_('COM_JOOMGALLERY_COMMON_ORDERING_DISABLED');
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
            <td class="center hidden-phone hidden">
              <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
<?php       if($this->_config->get('jg_showminithumbs')): ?>
            <td class="center">
              <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_up_eminithumb', $canView, true); ?>
            </td>
<?php       endif ?>
            <td>
<?php       if($canView):
              $link = JHTML::_('joomgallery.openImage', $this->_config->get('jg_detailpic_open'), $item);
?>
              <a <?php echo $item->atagtitle; ?> href="<?php echo $link; ?>">
<?php       endif; ?>
              <span class="jg-image-title"><?php echo $item->imgtitle; ?></span>
<?php       if($canView): ?>
              </a>
<?php       endif; ?>
            </td>
            <td>
              <?php echo $item->hits; ?>
            </td>
            <td>
              <?php echo $item->downloads; ?>
            </td>
            <td class="hidden-phone">
              <?php echo JHtml::_('joomgallery.categorypath', $item->catid, true, ' &raquo; ', true, false, true); ?>
            </td>
            <td class="nowrap">
<?php       if($item->show_edit_icon): ?>
              <div class="pull-left jg-show-editing-units<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_QUICK_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_QUICK_EDIT_IMAGE_TIPCAPTION'); ?>">
                <a href="#" data-id="<?php echo $item->id; ?>"<?php echo $listOrder == 'ordering' ? ' class="jg-icon-disabled"' : ''; ?>>
                  <?php echo JHTML::_('joomgallery.icon', 'lightning.png', 'COM_JOOMGALLERY_COMMON_QUICK_EDIT_IMAGE_TIPCAPTION'); ?></a>
              </div>
              <div class="pull-left jg-show-editing-units hide<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_CLOSE_QUICK_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_CLOSE_QUICK_EDIT_IMAGE_TIPCAPTION'); ?>">
                <a href="#">
                  <?php echo JHTML::_('joomgallery.icon', 'lightning_delete.png', 'COM_JOOMGALLERY_COMMON_CLOSE_QUICK_EDIT_IMAGE_TIPCAPTION'); ?></a>
              </div>
              <div class="pull-left<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?>">
                <a href="<?php echo JRoute::_('index.php?view=edit&id='.$item->id.$this->slimitstart); ?>">
                  <?php echo JHTML::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?></a>
              </div>
<?php       endif;
            if($item->show_delete_icon): ?>
              <div class="pull-left<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?>">
                <a href="javascript:if(confirm('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=image.delete&id='.$item->id.$this->slimitstart, false);?>';}">
                  <?php echo JHTML::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE'); ?></a>
              </div>
<?php       endif; ?>
            </td>
            <td class="nowrap center">
<?php       $p_img    = 'cross';
            if($item->published):
              $p_img = 'tick';
            endif; ?>
<?php       if($canChange):
              $p_title  = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_IMAGE_TIPCAPTION');
              $p_text   = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_IMAGE_TIPTEXT');
              if($item->published):
                $p_title = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_IMAGE_TIPCAPTION');
                $p_text  = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_IMAGE_TIPTEXT');
              endif; ?>
              <a href="<?php echo JRoute::_('index.php?task=image.publish&id='.$item->id.$this->slimitstart); ?>"<?php echo JHTML::_('joomgallery.tip', $p_text, $p_title, true, false); ?>>
                <?php echo JHTML::_('joomgallery.icon', $p_img.'.png', $p_img, null, null, false); ?></a>
<?php       else:
              $p_title  = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISHED');
              if($item->published):
                $p_title = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED');
              endif; ?>
              <div class="<?php echo JHTML::_('joomgallery.tip', '', $p_title); ?>">
                <?php echo JHTML::_('joomgallery.icon', $p_img.'.png', $p_img, null, null, false); ?>
              </div>
<?php       endif;
            if($item->published && $item->hidden):
              $h_title = JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK');
              $h_text  = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN');
              echo '<span'.JHTML::_('joomgallery.tip', $h_text, $h_title, true, false).'>'.JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK').'</span>';
              $display_hidden_asterisk = true;
            endif; ?>
            </td>
<?php     if($this->_config->get('jg_approve')): ?>
            <td class="nowrap center">
<?php       $a_img = 'cross';
            $a_title = 'COM_JOOMGALLERY_COMMON_REJECTED';
            if($item->approved == 1):
              $a_img = 'tick';
              $a_title = 'COM_JOOMGALLERY_COMMON_APPROVED';
            endif; ?>
              <div class="<?php echo JHTML::_('joomgallery.tip', '', $a_title); ?>">
                <?php echo JHTML::_('joomgallery.icon', $a_img.'.png', $a_img, null, null, false); ?>
              </div>
            </td>
<?php     endif?>
          </tr>
          <tr data-id="<?php echo $item->id; ?>" class="row<?php echo $i % 2; ?> jg-quick-edit-row hide">
            <td colspan="9">
              <div class="row-fluid">
                <div class="span3">
                  <?php echo JHtml::_('joomgallery.minithumbimg', $item, 'img-polaroid img-rounded', $canView, false); ?>
                </div>
                <div class="span9">
                  <div class="row-fluid">
                    <div class="span3">
                      <label for="imgtitle_<?php echo $item->id; ?>"><?php echo JText::_('COM_JOOMGALLERY_COMMON_IMAGE_NAME'); ?><span class="">&nbsp;*</span></label>
                    </div>
                    <div class="span9">
                      <input type="text" value="<?php echo $item->imgtitle; ?>" name="imgtitle" id="imgtitle_<?php echo $item->id; ?>" class="span12 required" required="required" />
                    </div>
                  </div>
                  <div class="row-fluid">
                    <div class="span3">
                      <label for="imgauthor_<?php echo $item->id; ?>"><?php echo JText::_('COM_JOOMGALLERY_DETAIL_AUTHOR'); ?></label>
                    </div>
                    <div class="span9">
                      <input type="text" value="<?php echo $item->imgauthor; ?>" name="imgauthor" id="imgauthor_<?php echo $item->id; ?>" class="span12" />
                    </div>
                  </div>
                  <?php if($this->_config->get('jg_edit_metadata')): ?>
                  <div class="row-fluid">
                    <div class="span3">
                      <label for="metadesc_<?php echo $item->id; ?>"><?php echo JText::_('COM_JOOMGALLERY_USERPANEL_METADESC'); ?></label>
                    </div>
                    <div class="span9">
                      <input type="text" value="<?php echo $item->metadesc; ?>" name="metadesc" id="metadesc_<?php echo $item->id; ?>" class="span12" />
                    </div>
                  </div>
                  <?php endif; ?>
                  <div class="row-fluid">
                    <label for="imgtext_<?php echo $item->id; ?>"><?php echo JText::_('COM_JOOMGALLERY_COMMON_DESCRIPTION'); ?></label>
                    <div class="jg-editor-wrapper">
                      <?php echo $this->editor->display('imgtext_'.$item->id, $item->imgtext, '100%', '168', '5', '5', false, null, null, null, array('mode'=> '0')); ?>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
<?php   endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="9">
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
      <?php echo JHtml::_('form.token'); ?>
    </form>
  </div>
	<div id="jg-quick-edit-btn-bar">
    <button type="button" class="btn btn-primary jg-save" disabled="disabled">
      <span class="icon-ok"></span> <?php echo JText::_('COM_JOOMGALLERY_MINI_SAVE'); ?>
    </button>
    <button type="button" class="btn btn-default jg-cancel">
      <span class="icon-edit"></span> <?php echo JText::_('COM_JOOMGALLERY_USERPANEL_HIDE_EDITING_UNITS'); ?>
    </button>
	</div>
  <script type="text/javascript">
    jQuery(window).load(function () {
        jQuery.QuickEditingData({
            url: '<?php echo JRoute::_('index.php?task=userpanel.quickedit&format=json'); ?>',
            getContentCallback: function(editor) {
              return eval("<?php echo preg_replace(array('/\r|\n/', '/(^|[^\\\\])"/'), array('', '$1\"'), $this->editor->getContent('editor-placeholder')); ?>".replace('editor-placeholder', editor));
            }
        });
    });
  </script>
<?php echo $this->loadTemplate('footer');