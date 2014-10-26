<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
      if($this->_config->get('jg_anchors')): ?>
  <a name="subcategory"></a>
<?php endif;
      if($this->params->get('show_count_cat_top')): ?>
  <div class="jg_catcountsubcats">
<?php   if($this->totalcategories == 1): ?>
    <?php echo JText::_('COM_JOOMGALLERY_CATEGORY_THERE_IS_ONE_SUBCATEGORY_IN_CATEGORY'); ?>
<?php   endif;
        if($this->totalcategories > 1): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_CATEGORY_THERE_ARE_SUBCATEGORIES_IN_CATEGORY', $this->totalcategories); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_pagination_cat_top')): ?>
  <div class="pagination">
    <?php echo $this->catpagination->getPagesLinks(); ?>
  </div>
<?php endif; ?>
  <div class="jg_subcat">
<?php if($this->_config->get('jg_showsubcathead')): ?>
    <div class="well well-small jg-header">
<?php if($this->params->get('show_feed_icon')): ?>
      <div class="jg_feed_icon">
        <a href="<?php echo $this->params->get('feed_url'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_CATEGORY_FEED_SUBCATEGORIES_TIPTEXT', 'COM_JOOMGALLERY_CATEGORY_FEED_TIPCAPTION', true); ?>>
          <?php echo JHtml::_('joomgallery.icon', 'feed.png', 'COM_JOOMGALLERY_CATEGORY_FEED_TIPCAPTION'); ?>
        </a>
      </div>
<?php $this->params->set('show_feed_icon', 0);
      endif;
      if($this->params->get('show_upload_icon')): ?>
      <div class="jg_upload_icon">
        <a href="<?php echo JRoute::_('index.php?view=mini&format=raw&upload_category='.$this->category->cid); ?>" class="modal<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPTEXT', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?>" rel="{handler: 'iframe', size: {x: 620, y: 550}}">
          <?php echo JHtml::_('joomgallery.icon', 'add.png', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?>
        </a>
      </div>
<?php $this->params->set('show_upload_icon', 0);
      endif; ?>
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_SUBCATEGORIES'); ?>
    </div>
<?php endif;
      $cat_count = count($this->categories);
      $num_rows  = ceil($cat_count / $this->_config->get('jg_colsubcat'));
      $index     = 0;
      $this->i   = 0;
      for($row_count = 0; $row_count < $num_rows; $row_count++): ?>
    <div class="jg_row jg_row<?php $this->i++; echo ($this->i % 2) + 1; ?>">
<?php   for($col_count = 0; ($col_count < $this->_config->get('jg_colsubcat')) && ($index < $cat_count); $col_count++):
          $row = $this->categories[$index]; ?>
      <div class="<?php echo $row->gallerycontainer; ?>">
<?php
          if($this->_config->get('jg_showsubthumbs')): ?>
<?php       if($row->thumb_src): ?>
        <div class="jg_imgalign_catsubs">
          <div class="<?php echo $row->photocontainer; ?>">
            <a title="<?php echo $row->name; ?>" href="<?php echo $row->link; ?>">
              <img src="<?php echo $row->thumb_src; ?>" hspace="4" vspace="0" class="jg_photo" alt="<?php echo $row->name; ?>" />
            </a>
<?php       endif;
          endif;
          if($this->_config->get('jg_showsubthumbs') && $row->thumb_src):?>
          </div>
        </div>
<?php     endif; ?>
        <div class="<?php echo $row->textcontainer; ?>">
          <ul>
            <li>
              <?php echo JHTML::_('joomgallery.icon', 'arrow.png', 'arrow'); ?>
<?php     if(in_array($row->access, $this->_user->getAuthorisedViewLevels())): ?>
              <a href="<?php echo $row->link; ?>">
                <?php echo $this->escape($row->name); ?></a>
<?php       if($row->password && $this->_config->get('jg_showrestrictedhint')): ?>
              <span<?php echo JHtml::_('joomgallery.tip', JText::_('COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED_TIPTEXT'), JText::_('COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED'), true); ?>>
                <?php echo JHtml::_('joomgallery.icon', 'key.png', 'COM_JOOMGALLERY_COMMON_CATEGORY_PASSWORD_PROTECTED'); ?>
              </span>
<?php       endif; ?>
<?php     else: ?>
              <span class="jg_no_access<?php echo JHTML::_('joomgallery.tip', JText::_('COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY'), $this->escape($row->name), false, false); ?>">
                <?php echo $this->escape($row->name); ?>
                <?php if($this->_config->get('jg_showrestrictedhint')): echo JHtml::_('joomgallery.icon', 'group_key.png', 'COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY'); endif; ?>
              </span>
<?php     endif; ?>
            </li>
<?php     if(in_array($row->access, $this->_user->getAuthorisedViewLevels()) && (!$row->password || in_array($row->cid, $this->_mainframe->getUserState('joom.unlockedCategories', array(0))))):
            if($this->_config->get('jg_showtotalsubcatimages') || $row->isnew): ?>
          <li>
<?php       if($this->_config->get('jg_showtotalsubcatimages')): ?>
            <?php echo JText::sprintf($row->picorpics, $row->pictures); ?>
<?php       endif;
            echo $row->isnew; ?>
          </li>
<?php       endif;
            if($this->_config->get('jg_showtotalsubcathits')): ?>
          <li>
            <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_HITS_VAR', $row->totalhits); ?>
          </li>
<?php       endif;
          endif;
          if($row->description && $this->_config->get('jg_showdescriptionincategoryview')): ?>
            <li>
              <?php echo JHTML::_('joomgallery.text', $row->description); ?>
            </li>
<?php     endif; ?>
            <?php echo $row->event->afterDisplayCatThumb; ?>
<?php     if(isset($row->show_favourites_icon) && $row->show_favourites_icon): ?>
            <li>
<?php       if($row->show_favourites_icon == 1): ?>
              <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$row->cid.'&return='.$this->category->cid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION', true); ?>>
                <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION'); ?></a>
<?php       endif;
            if($row->show_favourites_icon == 2): ?>
              <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$row->cid.'&return='.$this->category->cid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION', true); ?>>
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
      </div>
<?php     $index++;
        endfor; ?>
      <div class="jg_clearboth"></div>
    </div>
<?php endfor;
      if($this->params->get('show_count_cat_bottom')): ?>
    <div class="jg_catcountsubcats">
<?php   if($this->totalcategories == 1): ?>
      <?php echo JText::_('COM_JOOMGALLERY_CATEGORY_THERE_IS_ONE_SUBCATEGORY_IN_CATEGORY'); ?>
<?php   endif;
        if($this->totalcategories > 1): ?>
      <?php echo JText::sprintf('COM_JOOMGALLERY_CATEGORY_THERE_ARE_SUBCATEGORIES_IN_CATEGORY', $this->totalcategories); ?>
<?php   endif; ?>
    </div>
<?php endif;
      if($this->params->get('show_pagination_cat_bottom')): ?>
    <div class="pagination">
      <?php echo $this->catpagination->getPagesLinks(); ?>
    </div>
<?php endif; ?>
  </div>