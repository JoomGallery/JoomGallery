<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<?php if($this->params->get('show_footer_separator')): ?>
  <div class="jg-footer">
    &nbsp;
  </div>
<?php endif;
      if($this->params->get('show_footer_toplist', 0)): ?>
  <div class="jg_toplist">
    <?php JHTML::_('joomgallery.toplistbar'); ?>
  </div>
<?php endif;
      if($this->params->get('show_rmsm_legend', 0)): ?>
  <div class="jg_rmsm_legend">
    <div class="jg_rm">
      <?php echo JHtml::_('joomgallery.icon', 'group_key.png', 'COM_JOOMGALLERY_COMMON_TIP_YOU_NOT_ACCESS_THIS_CATEGORY'); ?> <?php echo  JText::_('COM_JOOMGALLERY_COMMON_RESTRICTED_CATEGORIES'); ?>
    </div>
  </div>
<?php endif;
      if($this->params->get('show_footer_allpics', 0) OR $this->params->get('show_footer_allhits', 0)): ?>
  <div class="jg_gallerystats">
<?php   if($this->params->get('show_footer_allpics', 0)): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_NUMB_IMAGES_ALL_CATEGORIES', $this->numberofpics); ?>
<?php     if($this->params->get('show_footer_allhits', 0)): ?>
    <br />
<?php     endif;
        endif;
        if($this->params->get('show_footer_allhits', 0)): ?>
    <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_NUMB_HITS_ALL_IMAGES', $this->numberofhits); ?>
<?php   endif; ?>
  </div>
<?php endif;
      if($this->params->get('show_footer_backlink')): ?>
  <div class="jg_back">
    <a href="<?php echo $this->backtarget; ?>">
      <?php echo $this->backtext; ?></a>
  </div>
<?php endif;
      if($this->params->get('show_footer_search', 0)): ?>
  <div class="jg_search">
    <form action="<?php echo JRoute::_('index.php?view=search'); ?>" method="post">
      <input type="text" name="sstring" class="inputbox" onblur="if(this.value=='') this.value='<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH', true) ;?>';" onfocus="if(this.value=='<?php echo  JText::_('COM_JOOMGALLERY_COMMON_SEARCH', true) ;?>') this.value='';" value="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH') ;?>" />
    </form>
  </div>
<?php endif;
      if($this->params->get('show_footer_pathway', 0)): ?>
  <div class="jg_pathway" >
    <a href="<?php echo JRoute::_('index.php') ;?>">
      <?php echo JHTML::_('joomgallery.icon', 'home.png', 'COM_JOOMGALLERY_COMMON_HOME', 'hspace="6" border="0" align="middle"'); ?></a>
    <?php echo $this->pathway; ?>
  </div>
<?php endif;
      if($this->params->get('show_btm_modules', 0)): ?>
  <div class="jg_btmmodules">
<?php foreach($this->modules['btm'] as $module): ?>
    <div class="jg_btmmodule">
      <?php echo $module->rendered; ?>
    </div>
<?php endforeach; ?>
  </div>
<?php endif;
      if($this->params->get('show_credits', 0)): ?>
  <div class="jg_clearboth"></div>
  <div align="center" class="jg_poweredbydiv">
    <a href="https://www.joomgalleryfriends.net" target="_blank">
      <img src="<?php echo $this->_ambit->getIcon('powered_by.gif'); ?>" class="jg_poweredby" alt="Powered by JoomGallery" />
    </a>
  </div>
<?php endif; ?>
</div>