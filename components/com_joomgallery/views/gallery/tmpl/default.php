<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
echo $this->loadTemplate('header'); ?>
<?php if($this->_config->get('jg_anchors')): ?>
  <a name="gallery"></a>
<?php endif;
      if($this->params->get('show_count_top')): ?>
  <div class="jg_galcountcats">
<?php   if($this->total == 1): ?>
    <?php echo JText::_('COM_JOOMGALLERY_GALLERY_THERE_IS_ONE_CATEGORY_IN_GALLERY'); ?>
<?php   endif;
        if($this->total > 1): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_GALLERY_THERE_ARE_CATEGORIES_IN_GALLERY', $this->total); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_pagination_top')): ?>
  <div class="pagination">
    <?php echo $this->pagination->getPagesLinks(); ?>
  </div>
<?php endif;
      if($this->_config->get('jg_showallcathead')): ?>
  <div class="well well-small jg-header">
    <?php echo JText::_('COM_JOOMGALLERY_COMMON_CATEGORIES'); ?>
  </div>
<?php endif;
      $this->i  = 0;
      $num_rows = ceil(count($this->rows ) / $this->_config->get('jg_colcat'));
      $index    = 0;
      for($row_count = 0; $row_count < $num_rows; $row_count++):?>
  <div class="jg_row jg_row<?php echo ($this->i % 2) + 1; ?>">
<?php   for($col_count = 0; (($col_count < $this->_config->get('jg_colcat')) && ($index < count($this->rows))); $col_count++):
          $row = $this->rows[$index]; ?>
    <div class="<?php echo $row->gallerycontainer; ?>">
<?php     if($row->thumb_src): ?>
      <div class="jg_imgalign_gal">
        <div class="<?php echo $row->photocontainer; ?>">
          <a title="<?php echo $row->name; ?>" href="<?php echo $row->link ?>">
            <img src="<?php echo $row->thumb_src; ?>" class="jg_photo" alt="<?php echo $row->name; ?>" />
          </a>
        </div>
      </div>
<?php     endif; ?>
      <div class="<?php echo $row->textcontainer; ?>">
        <ul>
          <li>
<?php     if(in_array($row->access, $this->_user->getAuthorisedViewLevels())): ?>
            <a href="<?php echo $row->link; ?>">
              <b><?php echo $this->escape($row->name); ?></b>
            </a>
<?php       if($row->password && $this->_config->get('jg_showrestrictedhint')): ?>
            <span<?php echo JHtml::_('joomgallery.tip', JText::_('COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED_TIPTEXT'), JText::_('COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED'), true); ?>>
              <?php echo JHtml::_('joomgallery.icon', 'key.png', 'COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED'); ?>
            </span>
<?php       endif; ?>
<?php     else: ?>
            <span class="jg_no_access<?php echo JHTML::_('joomgallery.tip', JText::_('COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY'), $this->escape($row->name), false, false); ?>">
              <b><?php echo $this->escape($row->name); ?></b>
              <?php if($this->_config->get('jg_showrestrictedhint')): echo JHtml::_('joomgallery.icon', 'group_key.png', 'COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY'); endif; ?>
            </span>
<?php     endif; ?>
          </li>
<?php     if(in_array($row->access, $this->_user->getAuthorisedViewLevels()) && (!$row->password || in_array($row->cid, $this->_mainframe->getUserState('joom.unlockedCategories', array(0))))):
            if($this->_config->get('jg_showtotalcatimages') || $row->isnew): ?>
          <li>
<?php       if($this->_config->get('jg_showtotalcatimages')): ?>
            <?php echo JText::sprintf($row->picorpics, $row->pictures); ?>
<?php       endif;
            echo $row->isnew; ?>
          </li>
<?php       endif;
            if($this->_config->get('jg_showtotalcathits')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_HITS_VAR', $row->totalhits); ?>
          </li>
<?php       endif;
          endif;
          if($row->description && $this->_config->get('jg_showdescriptioningalleryview')): ?>
          <li>
            <?php echo JHTML::_('joomgallery.text', $row->description); ?>
          </li>
<?php     endif; ?>
          <?php echo $row->event->afterDisplayCatThumb; ?>
<?php     if(isset($row->show_favourites_icon) && $row->show_favourites_icon): ?>
          <li>
<?php       if($row->show_favourites_icon == 1): ?>
            <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$row->cid.'&return=gallery'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION'); ?></a>
<?php       endif;
            if($row->show_favourites_icon == 2): ?>
            <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$row->cid.'&return=gallery'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'basket_put.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION'); ?></a>
<?php       endif;
            if($row->show_favourites_icon == -1): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'star_gr.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION'); ?>
            </span>
<?php       endif;
            if($row->show_favourites_icon == -2): ?>
            <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION', true); ?>>
              <?php echo JHTML::_('joomgallery.icon', 'basket_put_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION'); ?>
            </span>
<?php       endif; ?>
          </li>
<?php     endif;
          if(isset($row->show_upload_icon) && $row->show_upload_icon): ?>
            <li>
              <a href="<?php echo JRoute::_('index.php?view=mini&format=raw&upload_category='.$row->cid); ?>" class="modal<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPTEXT', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?>" rel="{handler: 'iframe', size: {x: 620, y: 550}}">
                <?php echo JHtml::_('joomgallery.icon', 'add.png', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?></a>
            </li>
<?php     endif; ?>
        </ul>
      </div>
<?php     //TODO: config option? -> add possibility of solution without javascript (like it had been before dtree was added)
          if($this->_config->get('jg_showsubsingalleryview')):
            JHTML::_('joomgallery.categorytree', $row->cid, $row->textcontainer);
          endif; ?>
    </div>
<?php     $index++;
        endfor; ?>
    <div class="jg_clearboth"></div>
  </div>
<?php   $this->i++;
      endfor;
      if($this->_config->get('jg_showallcathead')): ?>
  <div class="jg-footer">
    &nbsp;
  </div>
<?php endif;
      if($this->params->get('show_count_bottom')): ?>
  <div class="jg_galcountcats">
<?php   if($this->total == 1): ?>
    <?php echo JText::_('COM_JOOMGALLERY_GALLERY_THERE_IS_ONE_CATEGORY_IN_GALLERY'); ?>
<?php   endif;
        if($this->total > 1): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_GALLERY_THERE_ARE_CATEGORIES_IN_GALLERY', $this->total); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_pagination_bottom')): ?>
  <div class="pagination">
    <?php echo $this->pagination->getPagesLinks(); ?>
  </div>
<?php endif;
echo $this->loadTemplate('footer');