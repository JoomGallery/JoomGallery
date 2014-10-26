<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
echo $this->loadTemplate('header'); ?>
  <div class="jg_topview">
    <div class="well well-small jg-header">
      <?php echo $this->title; ?>&nbsp;
    </div>
<?php $count = count($this->rows);
      $num_rows = ceil($count / $this->_config->get('jg_toplistcols'));
      $index    = 0;
      $this->i  = 0;
      if(!$count): ?>
    <div class="jg_txtrow">
      <div class="jg_row1">
        <?php echo JHTML::_('joomgallery.icon', 'arrow.png', 'arrow'); ?>
        <?php echo JText::_('COM_JOOMGALLERY_TOPLIST_NO_IMAGES'); ?>
      </div>
    </div>
<?php endif;
      for($row_count = 0; $row_count < $num_rows; $row_count++ ): ?>
    <div class="jg_row jg_row<?php $this->i++; echo ($this->i % 2) + 1; ?>">
<?php   for($col_count = 0; ($col_count < $this->_config->get('jg_toplistcols')) && ($index < $count); $col_count++):
          $row = $this->rows[$index]; ?>
      <div class="jg_topelement">
 <?php    if($this->_config->get('jg_imgalign') && $this->_config->get('jg_toplistcols') > 1) : ?>
        <div class="jg_imgalign_top">
<?php     endif; ?>
          <div class="jg_topelem_photo">
            <a <?php echo $row->atagtitle; ?> href="<?php echo $row->link; ?>">
              <img src="<?php echo $row->thumb_src; ?>" class="jg_photo" alt="<?php echo $row->imgtitle; ?>" />
            </a>
          </div>
<?php     if($this->_config->get('jg_imgalign') && $this->_config->get('jg_toplistcols') > 1): ?>
        </div>
<?php     endif; ?>
        <div class="jg_topelem_txt">
          <ul>
            <li>
              <b><?php echo $row->imgtitle; ?></b>
            </li>
            <li>
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_CATEGORY'); ?>
              <a href="<?php echo JRoute::_('index.php?view=category&catid='.$row->catid); ?>">
                <?php echo $row->name; ?>
              </a>
            </li>
<?php     if($this->_config->get('jg_showauthor')): ?>
            <li>
              <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_AUTHOR_VAR', $row->authorowner); ?>
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
              <?php echo JHTML::_('joomgallery.rating', $row, false, 'jg_starrating_top'); ?>
            </li>
<?php     endif;
          if($this->_config->get('jg_showcatcom')): ?>
            <li>
<?php       switch($row->comments)
            {
              case 0: ?>
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_NO_COMMENTS'); ?>
<?php           break;
              case 1: ?>
              <?php echo $row->comments.' '.JText::_('COM_JOOMGALLERY_COMMON_COMMENT'); ?>
<?php           break;
              default: ?>
              <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_COMMENTS_VAR', $row->comments); ?>
<?php           break;
            } ?>
            </li>
<?php     endif;
          $results = $this->_mainframe->triggerEvent('onJoomAfterDisplayThumb', array($row->id));
          echo implode('', $results) ?>
            <li>
<?php     if($this->params->get('show_download_icon') == 1): ?>
              <a href="<?php echo JRoute::_('index.php?option=com_joomgallery&task=download&id='.$row->id); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'download.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?></a>
<?php     endif;
          if($this->params->get('show_download_icon') == -1): ?>
              <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_LOGIN_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'download_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOAD_TIPCAPTION'); ?>
              </span>
<?php     endif;
          if($this->params->get('show_favourites_icon') == 1): ?>
              <a href="<?php echo JRoute::_('index.php?task=favourites.addimage&id='.$row->id.'&toplist='.$this->type); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION'); ?></a>
<?php     endif;
          if($this->params->get('show_favourites_icon') == 2): ?>
              <a href="<?php echo JRoute::_('index.php?task=favourites.addimage&id='.$row->id.'&toplist='.$this->type); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'basket_put.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION'); ?></a>
<?php     endif;
          if($this->params->get('show_favourites_icon') == -1): ?>
              <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'star_gr.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGE_TIPCAPTION'); ?>
              </span>
<?php     endif;
          if($this->params->get('show_favourites_icon') == -2): ?>
              <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'basket_put_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGE_TIPCAPTION'); ?>
              </span>
<?php     endif;
          if($this->params->get('show_report_icon') == 1): ?>
              <a href="<?php echo JRoute::_('index.php?view=report&id='.$row->id.'&toplist='.$this->type.'&tmpl=component'); ?>" class="modal<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?>" rel="{handler:'iframe'}"><!--, size:{x:200,y:100}-->
                <?php echo JHTML::_('joomgallery.icon', 'exclamation.png', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?></a>
<?php     endif;
          if($this->params->get('show_report_icon') == -1): ?>
              <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'exclamation_gr.png', 'COM_JOOMGALLERY_COMMON_REPORT_IMAGE_TIPCAPTION'); ?>
              </span>
<?php     endif;
          if($row->show_edit_icon): ?>
              <a href="<?php echo JRoute::_('index.php?view=edit&id='.$row->id.$this->redirect); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'edit.png', 'COM_JOOMGALLERY_COMMON_EDIT_IMAGE_TIPCAPTION'); ?></a>
<?php     endif;
          if($row->show_delete_icon): ?>
              <a href="javascript:if(confirm('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_SURE_DELETE_SELECTED_ITEM', true); ?>')){ location.href='<?php echo JRoute::_('index.php?task=image.delete&id='.$row->id.$this->redirect, false);?>';}"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'edit_trash.png', 'COM_JOOMGALLERY_COMMON_DELETE_IMAGE_TIPCAPTION'); ?></a>
<?php     endif;
          $results = $this->_mainframe->triggerEvent('onJoomDisplayIcons', array('toplist.image', $row));
          echo implode('', $results) ?>
            </li>
<?php     if($this->type == 'lastcommented' && $this->_config->get('jg_showthiscomment')): ?>
            <li>
<?php       if($this->params->get('delivered_by_plugin')): ?>
              <?php echo $row->comment; ?>
<?php       else: ?>
              <?php echo JText::sprintf('COM_JOOMGALLERY_TOPLIST_WROTE_AT', $row->cmtname, JHTML::_('date', $row->cmtdate, JText::_('DATE_FORMAT_LC1'))); ?>
              <?php echo $row->processed_cmttext; ?>
<?php       endif; ?>
            </li>
<?php     endif; ?>
          </ul>
        </div>
      </div>
<?php     $index++;
        endfor; ?>
      <div class="jg_clearboth"></div>
    </div>
<?php endfor; ?>
  </div>
<?php echo $this->loadTemplate('footer');