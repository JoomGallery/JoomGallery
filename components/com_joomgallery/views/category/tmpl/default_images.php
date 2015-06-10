<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
      if($this->_config->get('jg_anchors')): ?>
  <a name="category"></a>
<?php endif;
      if($this->params->get('show_count_img_top')): ?>
  <div class="jg_catcountimg">
<?php   if($this->totalimages == 1): ?>
    <?php echo JText::_('COM_JOOMGALLERY_CATEGORY_THERE_IS_ONE_IMAGE_IN_CATEGORY'); ?>
<?php   endif;
        if($this->totalimages > 1): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_CATEGORY_THERE_ARE_IMAGES_IN_CATEGORY', $this->totalimages); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_pagination_img_top')): ?>
  <div class="pagination">
    <?php echo $this->pagination->getPagesLinks(); ?>
  </div>
<?php endif;
      if($this->params->get('show_all_in_popup')):
        echo $this->popup['before'];
      endif;
      $count_pics = count($this->images);
      $column     = $this->_config->get('jg_colnumb');
      $num_rows   = ceil($count_pics / $column);
      $index      = 0;
      $this->i    = 0;
      for($row_count = 0; $row_count < $num_rows; $row_count++): ?>
  <div class="jg_row jg_row<?php $this->i++; echo ($this->i % 2) + 1; ?>">
<?php   for($col_count = 0; ($col_count < $column) && ($index < $count_pics); $col_count++):
          $row = $this->images[$index]; ?>
    <div class="jg_element_cat">
      <div class="jg_imgalign_catimgs">
        <a <?php echo $row->atagtitle; ?> href="<?php echo $row->link; ?>" class="jg_catelem_photo jg_catelem_photo_align">
          <img src="<?php echo $row->thumb_src; ?>" class="jg_photo" <?php echo $row->imgwh; ?> alt="<?php echo $row->imgtitle; ?>" /></a>
      </div>
<?php if($row->show_elems): ?>
      <div class="jg_catelem_txt">
        <ul>
<?php     if($this->_config->get('jg_showtitle') || ($this->_config->get('jg_showpicasnew') && $row->isnew)): ?>
          <li>
<?php       if($this->_config->get('jg_showtitle')): ?>
            <b><?php echo $row->imgtitle; ?></b>
<?php       endif;
            if($this->_config->get('jg_showpicasnew')): ?>
            <?php echo $row->isnew; ?>
<?php       endif; ?>
          </li>
<?php     endif;
          if($this->_config->get('jg_showauthor')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_AUTHOR_VAR', $row->authorowner);?>
          </li>
<?php     endif;
          if($this->_config->get('jg_showhits')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_HITS_VAR', $row->hits); ?>
          </li>
<?php     endif;
          if($this->_config->get('jg_showdownloads')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_DOWNLOADS_VAR', $row->downloads); ?>
          </li>
<?php     endif;
          if($this->_config->get('jg_showcatrate')): ?>
          <li>
            <?php echo JHTML::_('joomgallery.rating', $row, false, 'jg_starrating_cat'); ?>
          </li>
<?php     endif;
          if($this->_config->get('jg_showcatcom')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_COMMENTS_VAR', $row->comments); ?>
          </li>
<?php     endif;
          if($row->imgtext && $this->_config->get('jg_showcatdescription')): ?>
          <li>
            <?php echo JHTML::_('joomgallery.text', JText::sprintf('COM_JOOMGALLERY_COMMON_DESCRIPTION_VAR', $row->imgtext)); ?>
          </li>
<?php     endif; ?>
          <?php echo $row->event->afterDisplayThumb; ?>
<?php     if($this->params->get('show_download_icon') || $this->params->get('show_favourites_icon') || $this->params->get('show_report_icon') || $row->show_edit_icon || $row->show_delete_icon || $row->event->icons): ?>
          <li>
<?php       if($this->params->get('show_download_icon') == 1): ?>
            <a href="<?php echo JRoute::_('index.php?task=download&id='.$row->id); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'download.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?></a>
<?php       endif;
            if($this->params->get('show_download_icon') == -1): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_LOGIN_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'download_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?>
            </span>
<?php       endif;
            if($this->params->get('show_favourites_icon') == 1): ?>
            <a href="<?php echo JRoute::_('index.php?task=favourites.addimage&id='.$row->id.'&catid='.$row->catid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION'); ?></a>
<?php       endif;
            if($this->params->get('show_favourites_icon') == 2): ?>
            <a href="<?php echo JRoute::_('index.php?task=favourites.addimage&id='.$row->id.'&catid='.$row->catid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'basket_put.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION'); ?></a>
<?php       endif;
            if($this->params->get('show_favourites_icon') == -1): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'star_gr.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION'); ?>
            </span>
<?php       endif;
            if($this->params->get('show_favourites_icon') == -2): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'basket_put_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION'); ?>
            </span>
<?php       endif;
            if($this->params->get('show_report_icon') == 1): ?>
            <a href="<?php echo JRoute::_('index.php?view=report&id='.$row->id.'&catid='.$row->catid.'&tmpl=component'); ?>" class="modal<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?>" rel="{handler:'iframe'}"><!--, size:{x:200,y:100}-->
              <?php echo JHTML::_('joomgallery.icon', 'exclamation.png', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?></a>
      <?php endif;
            if($this->params->get('show_report_icon') == -1): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'exclamation_gr.png', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?>
            </span>
<?php       endif;
            if($row->show_edit_icon): ?>
            <a href="<?php echo JRoute::_('index.php?view=edit&id='.$row->id.$this->redirect); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?></a>
<?php       endif;
            if($row->show_delete_icon): ?>
            <a href="javascript:if(confirm('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=image.delete&id='.$row->id.$this->redirect, false);?>';}"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?></a>
<?php       endif; ?>
            <?php echo $row->event->icons; ?>
          </li>
<?php     endif; ?>
        </ul>
      </div>
<?php endif; ?>
    </div>
<?php     $index++;
        endfor; ?>
    <div class="jg_clearboth"></div>
  </div>
<?php endfor;
      if($this->params->get('show_all_in_popup')):
        echo $this->popup['after'];
      endif;
      if($this->_config->get('jg_showcathead')): ?>
  <div class="jg-footer">
    &nbsp;
  </div>
<?php endif;
      if($this->params->get('show_count_img_bottom')): ?>
  <div class="jg_catcountimg">
<?php   if($this->totalimages == 1): ?>
    <?php echo JText::_('COM_JOOMGALLERY_CATEGORY_THERE_IS_ONE_IMAGE_IN_CATEGORY'); ?>
<?php   endif;
        if($this->totalimages > 1): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_CATEGORY_THERE_ARE_IMAGES_IN_CATEGORY', $this->totalimages); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_pagination_img_bottom')): ?>
  <div class="pagination">
    <?php echo $this->pagination->getPagesLinks(); ?>
  </div>
<?php endif;