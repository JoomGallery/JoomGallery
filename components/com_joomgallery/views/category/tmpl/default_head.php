<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
  <div class="jg_category">
<?php if($this->_config->get('jg_showcathead')): ?>
    <div class="well well-small jg-header">
<?php if($this->params->get('show_feed_icon')): ?>
      <div class="jg_feed_icon">
        <a href="<?php echo $this->params->get('feed_url'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_CATEGORY_FEED_TIPTEXT', 'COM_JOOMGALLERY_CATEGORY_FEED_TIPCAPTION', true); ?>>
          <?php echo JHtml::_('joomgallery.icon', 'feed.png', 'COM_JOOMGALLERY_CATEGORY_FEED_TIPCAPTION'); ?>
        </a>
      </div>
<?php $this->params->set('show_feed_icon', 0);
      endif;
      if($this->params->get('show_headerfavourites_icon')): ?>
      <div class="jg_headerfavourites_icon">
<?php   if($this->params->get('show_headerfavourites_icon') == 1): ?>
        <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$this->category->cid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION', true); ?>>
          <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION'); ?></a>
<?php   endif;
        if($this->params->get('show_headerfavourites_icon') == 2): ?>
        <a href="<?php echo JRoute::_('index.php?task=favourites.addimages&catid='.$this->category->cid); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION', true); ?>>
          <?php echo JHTML::_('joomgallery.icon', 'basket_put.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION'); ?></a>
<?php   endif;
        if($this->params->get('show_headerfavourites_icon') == -1): ?>
        <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION', true); ?>>
          <?php echo JHTML::_('joomgallery.icon', 'star_gr.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_ADD_IMAGES_TIPCAPTION'); ?>
        </span>
<?php   endif;
        if($this->params->get('show_headerfavourites_icon') == -2): ?>
        <span<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION', true); ?>>
          <?php echo JHTML::_('joomgallery.icon', 'basket_put_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_ADD_IMAGES_TIPCAPTION'); ?>
        </span>
<?php   endif; ?>
      </div>
<?php endif;
      if($this->params->get('show_upload_icon')): ?>
      <div class="jg_upload_icon">
        <a href="<?php echo JRoute::_('index.php?view=mini&format=raw&upload_category='.$this->category->cid); ?>" class="modal<?php echo JHtml::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPTEXT', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?>" rel="{handler: 'iframe', size: {x: 620, y: 550}}">
          <?php echo JHtml::_('joomgallery.icon', 'add.png', 'COM_JOOMGALLERY_COMMON_UPLOAD_ICON_TIPCAPTION'); ?>
        </a>
      </div>
<?php $this->params->set('show_upload_icon', 0);
      endif; ?>
      <?php echo $this->category->name; ?>
    </div>
<?php endif;
      if($this->_config->get('jg_showcatdescriptionincat') == 1): ?>
    <div class="jg_catdescr">
      <?php echo JHTML::_('joomgallery.text', $this->category->description); ?>
    </div>
<?php endif;
      if($this->_config->get('jg_usercatorder')): ?>
    <div class="jg_catorderlist">
      <form action="<?php echo $this->sort_url;?>" method="post">
          <?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY'); ?>
        <select title="<?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY'); ?>" name="orderby" onchange="this.form.submit()" class="inputbox">
          <option value="default"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_DEFAULT'); ?></option>
<?php   if(strpos($this->_config->get('jg_usercatorderlist'), 'date') !== false): ?>
          <option <?php if($this->order_by == 'date') echo 'selected="selected"'; ?> value="date"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_DATE'); ?></option>
<?php   endif;
        if(strpos($this->_config->get('jg_usercatorderlist'), 'user') !== false): ?>
          <option <?php if($this->order_by == 'user') echo 'selected="selected"'; ?> value="user"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_AUTHOR'); ?></option>
<?php   endif;
        if(strpos($this->_config->get('jg_usercatorderlist'), 'title') !== false): ?>
          <option <?php if($this->order_by == 'title') echo 'selected="selected"'; ?> value="title"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_TITLE'); ?></option>
<?php   endif;
        if(strpos($this->_config->get('jg_usercatorderlist'), 'hits') !== false): ?>
          <option <?php if($this->order_by == 'hits') echo 'selected="selected"'; ?> value="hits"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_HITS'); ?></option>
<?php   endif;
        if(strpos($this->_config->get('jg_usercatorderlist'), 'rating') !== false): ?>
          <option <?php if($this->order_by == 'rating') echo 'selected="selected"'; ?> value="rating"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_RATING'); ?></option>
<?php   endif; ?>
        </select>
<?php   $disabled = '';
        if($this->order_by != 'title' && $this->order_by != 'hits' && $this->order_by != 'date' && $this->order_by != 'user' && $this->order_by != 'rating'):
          $disabled = ' disabled="disabled"';
        endif; ?>
        <select<?php echo $disabled; ?> title="orderdir" name="orderdir" onchange="this.form.submit()" class="inputbox">
          <option <?php if ($this->order_dir == 'asc') echo 'selected="selected"' ?> value="asc"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_ASC'); ?></option>
          <option <?php if ($this->order_dir == 'desc') echo 'selected="selected"' ?> value="desc"><?php echo JText::_('COM_JOOMGALLERY_CATEGORY_OPTION_USER_ORDERBY_DESC'); ?></option>
        </select>
      </form>
    </div>
<?php endif; ?>
  </div>
