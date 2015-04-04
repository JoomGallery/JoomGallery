<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$jinput = JFactory::getApplication()->input;

if ($jinput->get('ajaxeditimg', '', 'STRING') == "edit")
{
  try
  {
    $db               = JFactory::getDBO();
    $user             = JFactory::getUser();
    $user_id          = $user->get('id');
    $ImgDataToUpdate  = json_decode($jinput->post->get('jsonData', '', 'RAW'));

    if ($db->query() and is_object($ImgDataToUpdate))
    {
      foreach ($ImgDataToUpdate as $key => $val)
      {
        $query = "UPDATE #__joomgallery SET
              imgtitle		= '".strip_tags($val->imgtitle)."',
              imgauthor   = '".strip_tags($val->imgauthor)."',
              metadesc		= '".strip_tags($val->metadesc)."',
              imgtext     = '".htmlspecialchars($val->imgtext)."'
              WHERE id		= '$key'
              AND access IN (" . implode(',', $user->getAuthorisedViewLevels()) . ")
              AND owner   = '$user_id'";
        $db->setQuery($query);
        $db->query();
      }
      $result = true;
    }
    else
    {
      $result = JText::_('COM_JOOMGALLERY_COMMON_DATACHANGED_ERROR');
    }

    echo new JResponseJson($result);
  }
  catch(Exception $e)
  {
    echo new JResponseJson(JText::_('COM_JOOMGALLERY_COMMON_DATACHANGED_ERROR'));
  }
	exit;
}
else
{

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder                = $this->escape($this->state->get('list.ordering'));
$listDirn                 = $this->escape($this->state->get('list.direction'));
$saveOrder                = (($listOrder == 'ordering') && (strtoupper($listDirn) == 'ASC' || !$listDirn) && !$this->state->get('filter.inuse'));
$display_hidden_asterisk  = false;
$name_editor              = JFactory::getConfig()->get( 'editor' );

if($saveOrder):
  $saveOrderingUrl = 'index.php?option='._JOOM_OPTION.'&task=images.saveorder&format=json';
  JHtml::_('sortablelist.sortable', 'imageList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, false);
endif;

$sortFields = $this->getSortFields();

echo $this->loadTemplate('header');
?>
  <script type="text/javascript">
		jQuery(function ($) {
      function save(){
        $(".image_data_row.changed").each(function () {
          var image_title = $(this).find("input[name='imgtitle']").val();
          $(this).find('.image_title').html(image_title);
          $(this).removeClass("changed");
        });
      }

      $(".blinker-btn .edit_image").click(function () {
				var imagedata = {};

				$(".image_data_row.changed").each(function () {

					var id = $(this).attr('id');

          <?php
          switch ($name_editor) {
            case 'tinymce':
            case 'jce':
              echo 'var FRAM = document.getElementById("imgtext_"+id+"_ifr");';
              echo 'var imgtext = FRAM.contentDocument.body.innerHTML;';
              break;
            default:
              echo 'var imgtext = $(this).find("#imgtext_" + id).val();';
          }
          ?>

					imagedata[id] = {
						"imgtitle"  : $(this).find('#imgtitle_' + id).val(),
						"imgauthor" : $(this).find('#imgauthor_' + id).val(),
						"metadesc"  : $(this).find('#metadesc_' + id).val(),
						"imgtext"   : imgtext
					};
				});

				$.ajax({
					url: '<?= JURI::current() ?>',
					type: "POST",
					data: {
						ajaxeditimg: "edit",
						jsonData: JSON.stringify(imagedata)
					},
					success: function (msg) {
            var data = jQuery.parseJSON(msg).data;
						if (data === true) {
							$('.blinker-msg').css('display', 'block').delay(1000).fadeOut(2000);
              save();
						}	else {
              alert(data);
						}
					},
					response: 'text',
					dataType: 'text'
				});
			});

      $(window).load(function () {
        $(".image_data_row input").keyup(function() {
          $(this).closest(".image_data_row").addClass("changed");
        });

        <?php
        switch ($name_editor) {
          case 'tinymce':
          case 'jce':
            echo '$(".image_data_row").each(function () {
                    var id = $(this).attr("id");
                    $(this).find("iframe").contents().keyup(function() {
                      $("#"+id).addClass("changed");
                    });
                  });';
            break;
          default: 
            echo '$(".image_data_row textarea").keyup(function() {
                    $(this).closest(".image_data_row").addClass("changed");
                  });';
        }
        ?>
      });
		});
	</script>
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
  <style type="text/css">.toggle-editor{display:none;}</style>

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
          <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_FILTER_SEARCH'); ?>"><i class="icon-search"></i></button>
          <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
        </div>
        <div class="btn-group pull-right hidden-phone">
          <label for="limit" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH_LIMIT'); ?></label>
          <?php echo $this->pagination->getLimitBox(); ?>
        </div>
        <div class="btn-group pull-right hidden-phone">
          <label for="directionTable" class="element-invisible"><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDERING');?></label>
          <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
            <option value=""><?php echo JText::_('COM_JOOMGALLERY_COMMON_ORDERING');?></option>
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
          <?php echo JHtml::_('joomselect.categorylist', $this->state->get('filter.category'), 'filter_category', 'onchange="document.id(\'adminForm\').submit()"', null, '- ', 'filter'); ?>
        </div>
        <div class="clearfix"> </div>
      </div>

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
          <div class="row<?= $i % 2; ?> image_data_row" id="<?= $item->id; ?>" sortable-group-id="<?= $item->catid ?>">

					<div class="title">
						<?php
						if ($canView)
						{
							$link = JHTML::_('joomgallery.openImage', $this->_config->get('jg_detailpic_open'), $item);
							echo '<a class="image_title" ' . $item->atagtitle . ' href="' . $link . '">' . $item->imgtitle . '</a>';
						}
						?>
					</div>
          <div class="image_data">
            <span class="image_hits">
              <?= JText::_('COM_JOOMGALLERY_COMMON_HITS')?>: <?= $item->hits; ?>
            </span>
            <span class="image_category">
              <?= JText::_('COM_JOOMGALLERY_COMMON_CATEGORY')?>: <?= JHtml::_('joomgallery.categorypath', $item->catid, true, ' &raquo; ', true, false, true); ?>
            </span>
          </div>

					<div class="left_block">
						<div>
							<?= JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', $canView, true); ?>
						</div>

						<div class="nowrap">
							<?php if ($item->show_edit_icon): ?>
								<div class="pull-left<?= JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?>">
									<a href="<?= JRoute::_('index.php?view=edit&id=' . $item->id . $this->slimitstart); ?>">
										<?= JHTML::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_CATEGORY_TIPCAPTION'); ?>
									</a>
								</div>
								<?php
							endif;
							if ($item->show_delete_icon):
								?>
								<div class="pull-left<?= JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?>">
									<a href="javascript:if(confirm('<?= JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=image.delete&id=' . $item->id . $this->slimitstart, false); ?>';}">
										<?= JHTML::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE'); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>

						<div class="nowrap center pull-left">
							<?php
							$p_img = 'cross';
							if ($item->published):
								$p_img = 'tick';
							endif;
							?>
							<?php
							if ($canChange):
								$p_title = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_IMAGE_TIPCAPTION');
								$p_text = JText::_('COM_JOOMGALLERY_COMMON_PUBLISH_IMAGE_TIPTEXT');
								if ($item->published):
									$p_title = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_IMAGE_TIPCAPTION');
									$p_text = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISH_IMAGE_TIPTEXT');
								endif;
								?>
								<a href="<?= JRoute::_('index.php?task=image.publish&id=' . $item->id . $this->slimitstart); ?>"<?= JHTML::_('joomgallery.tip', $p_text, $p_title, true, false); ?>>
									<?= JHTML::_('joomgallery.icon', $p_img . '.png', $p_img, null, null, false); ?></a>
								<?php
							else:
								$p_title = JText::_('COM_JOOMGALLERY_COMMON_UNPUBLISHED');
								if ($item->published):
									$p_title = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED');
								endif;
								?>
								<div class="<?= JHTML::_('joomgallery.tip', '', $p_title); ?> ">
									<?= JHTML::_('joomgallery.icon', $p_img . '.png', $p_img, null, null, false); ?>
								</div>
							<?php
							endif;
							if ($item->published && $item->hidden):
								$h_title = JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK');
								$h_text = JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN');
								echo '<span' . JHTML::_('joomgallery.tip', $h_text, $h_title, true, false) . '>' . JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK') . '</span>';
								$display_hidden_asterisk = true;
							endif;
							?>
						</div>
					</div>

					<div class="right_block">
						<input type="hidden" value="<?= $item->id; ?>" name="id" />
						<input type="hidden" value="1" name="ajax" />
						<div class="input_block">
							<label for="imgtitle_<?= $item->id; ?>"><?= JText::_('COM_JOOMGALLERY_COMMON_IMAGE_NAME'); ?></label>
							<input type="text" value="<?= $item->imgtitle; ?>" name="imgtitle" id="imgtitle_<?= $item->id; ?>" />
						</div>
						<div class="input_block">
							<label for="imgauthor_<?= $item->id; ?>"><?= JText::_('COM_JOOMGALLERY_DETAIL_AUTHOR'); ?>:</label>
							<input type="text" value="<?= $item->imgauthor; ?>" name="imgauthor" id="imgauthor_<?= $item->id; ?>" />
						</div>
						<div class="input_block">
							<label for="metadesc_<?= $item->id; ?>"><?= JText::_('COM_JOOMGALLERY_COMMON_METADESC'); ?>:</label>
							<input type="text" value="<?= $item->metadesc; ?>" name="metadesc" id="metadesc_<?= $item->id; ?>" />
						</div>
						<div class="input_block">
							<label for="imgtext_<?= $item->id; ?>"><?= JText::_('COM_JOOMGALLERY_COMMON_DESCRIPTION'); ?>:</label>
              <?php
                switch ($name_editor) {
                  case 'tinymce':
                    $editor =& JFactory::getEditor('tinymce');
                    $params = array( 'mode'=> "0" );
                    echo $editor->display('imgtext_'.$item->id, $item->imgtext, '65%', '168', '5', '5', false, null, null, null, $params);
                    break;
                  case 'jce':
                    $editor =& JFactory::getEditor('jce');
                    echo $editor->display('imgtext_'.$item->id, $item->imgtext, '65%', '168', '5', '5', false);
                    break;
                  default:
                    $editor =& JFactory::getEditor('none');
                    echo $editor->display('imgtext_'.$item->id, $item->imgtext, '65%', '168', '5', '5', false);
                }
              ?>
						</div>
					</div>

					<div class="center hidden-phone hidden">
						<?php //echo JHtml::_('grid.id', $i, $item->id);  ?>
					</div>


					<?php if ($this->_config->get('jg_approve')): ?>
						<div class="nowrap center">
							<?php
							$a_img = 'cross';
							$a_title = 'COM_JOOMGALLERY_COMMON_REJECTED';
							if ($item->approved == 1):
								$a_img = 'tick';
								$a_title = 'COM_JOOMGALLERY_COMMON_APPROVED';
							endif;
							?>
							<div class="<?= JHTML::_('joomgallery.tip', '', $a_title); ?>">
								<?= JHTML::_('joomgallery.icon', $a_img . '.png', $a_img, null, null, false); ?>
							</div>
						</div>
					<?php endif ?>
				</div>
			<?php endforeach; //end foreach($this->items as $i => $item)   ?>

      <div>
<?php         if($this->pagination->get('pagesTotal') > 1): ?>
                <?php echo $this->pagination->getListFooter();
              endif;
              if($display_hidden_asterisk): ?>
              <div class = "small pull-right">
                <?php echo JText::_('COM_JOOMGALLERY_COMMON_HIDDEN_ASTERISK'); ?> <?php echo JText::_('COM_JOOMGALLERY_COMMON_PUBLISHED_BUT_HIDDEN'); ?>
              </div>
<?php         endif; ?>
      </div>

      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
      <?php echo JHtml::_('form.token'); ?>
    </form>
  </div>

  <div class="blinker-msg">
		<p><?= JText::_('COM_JOOMGALLERY_COMMON_DATACHANGED_SUCCESS'); ?></p>
	</div>
	<div class="blinker-btn btn-toolbar">
    <div class="btn-group">
      <button type="button" class="btn btn-primary edit_image">
        <i class="icon-ok"></i><?= JText::_('COM_JOOMGALLERY_MINI_SAVE'); ?>
      </button>
    </div>
	</div>
<?php echo $this->loadTemplate('footer');
} // End if (JRequest::getVar('ajax', '', 'POST')) else