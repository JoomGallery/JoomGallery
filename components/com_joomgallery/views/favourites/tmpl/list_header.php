<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="gallery">
<?php if($this->params->get('show_page_heading', 0)): ?>
  <h2>
    <?php echo $this->escape($this->params->get('page_heading')); ?>
  </h2>
<?php endif;
if($this->params->get('show_top_modules', 0)): ?>
  <div class="jg_topmodules">
    <?php foreach($this->modules['top'] as $module): ?>
      <div class="jg_topmodule">
        <?php echo $module->rendered; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif;
if($this->params->get('show_header_pathway', 0)): ?>
  <div class="jg_pathway" >
    <a href="<?php echo JRoute::_('index.php') ;?>">
      <?php echo JHTML::_('joomgallery.icon', 'home.png', 'COM_JOOMGALLERY_COMMON_HOME', 'hspace="6" border="0" align="middle"'); ?></a>
    <?php echo $this->pathway; ?>
  </div>
<?php endif;
if($this->params->get('show_header_search', 0)): ?>
  <div class="jg_search">
    <form action="<?php echo JRoute::_('index.php?view=search'); ?>" method="post">
      <input title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" type="text" name="sstring" class="inputbox" onblur="if(this.value=='') this.value='<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH', true); ?>';" onfocus="if(this.value=='<?php echo  JText::_('COM_JOOMGALLERY_COMMON_SEARCH', true); ?>') this.value='';" value="<?php echo JText::_('COM_JOOMGALLERY_COMMON_SEARCH'); ?>" />
    </form>
  </div>
<?php endif;
if($this->params->get('show_header_backlink')): ?>
  <div class="jg_back">
    <a href="<?php echo $this->backtarget; ?>">
      <?php echo $this->backtext; ?></a>
  </div>
<?php endif;
if($this->params->get('show_mygal')): ?>
  <div class="jg_mygal">
    <a href="<?php echo JRoute::_('index.php?option=com_joomgallery&view=userpanel') ;?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL') ;?>
    </a>
  </div>
<?php endif;
if($this->params->get('show_mygal_no_access')): ?>
  <div class="jg_mygal">
    <span class="jg_no_access<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_MSG_YOU_ARE_NOT_LOGGED', 'COM_JOOMGALLERY_COMMON_USER_PANEL'); ?>">
      <?php echo JText::_('COM_JOOMGALLERY_COMMON_USER_PANEL'); ?>
    </span>
  </div>
<?php endif;
if($this->params->get('show_favourites', 0)): ?>
  <div class="jg_my_favourites">
    <?php switch($this->params->get('show_favourites', 1)):
      case 1: ?>
        <a href="<?php echo JRoute::_('index.php?option=com_joomgallery&view=favourites'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_DOWNLOAD_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY', true); ?>><?php echo JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY'); ?>
          <?php echo JHTML::_('joomgallery.icon', 'basket.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY'); ?>
        </a>
        <?php break;
      case 2: ?>
        <a href="<?php echo JRoute::_('index.php?option=com_joomgallery&view=favourites'); ?>"<?php echo JHTML::_('joomgallery.tip', $this->params->get('favourites_tooltip_text'), JText::_('COM_JOOMGALLERY_COMMON_FAVOURITES_MY'), true, false); ?>><?php echo JText::_('COM_JOOMGALLERY_COMMON_FAVOURITES_MY'); ?>
          <?php echo JHTML::_('joomgallery.icon', 'star.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_MY'); ?>
        </a>
        <?php break;
      case 3: ?>
        <span class="jg_no_access<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_DOWNLOAD_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY'); ?>"><?php echo JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY'); ?>
          <?php echo JHTML::_('joomgallery.icon', 'basket_gr.png', 'COM_JOOMGALLERY_COMMON_DOWNLOADZIP_MY'); ?>
    </span>
        <?php break;
      case 4: ?>
        <span class="jg_no_access<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_COMMON_FAVOURITES_DOWNLOAD_NOT_ALLOWED_TIPTEXT', 'COM_JOOMGALLERY_COMMON_FAVOURITES_MY'); ?>"><?php echo JText::_('COM_JOOMGALLERY_COMMON_FAVOURITES_MY'); ?>
          <?php echo JHTML::_('joomgallery.icon', 'star_gr.png', 'COM_JOOMGALLERY_COMMON_FAVOURITES_MY'); ?>
    </span>
        <?php break;
      case 5: ?>
        <a href="<?php echo JRoute::_('index.php?task=favourites.createzip'); ?>"<?php echo JHTML::_('joomgallery.tip', 'COM_JOOMGALLERY_DOWNLOADZIP_CREATE_TIPTEXT', 'COM_JOOMGALLERY_DOWNLOADZIP_DOWNLOAD', true); ?>><?php echo JText::_('COM_JOOMGALLERY_DOWNLOADZIP_DOWNLOAD'); ?>
          <?php echo JHTML::_('joomgallery.icon', 'package_go.png', 'COM_JOOMGALLERY_DOWNLOADZIP_DOWNLOAD'); ?>
        </a>
        <?php break;
      default:
        break;
    endswitch; ?>
  </div>
<?php endif;
if($this->params->get('show_header_allpics', 0) || $this->params->get('show_header_allhits', 0)): ?>
  <div class="jg_gallerystats">
    <?php   if($this->params->get('show_header_allpics', 0)): ?>
      <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_NUMB_IMAGES_ALL_CATEGORIES', $this->numberofpics); ?>
      <?php     if($this->params->get('show_header_allhits', 0)): ?>
        <br />
      <?php     endif;
    endif;
    if($this->params->get('show_header_allhits', 0)): ?>
      <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_NUMB_HITS_ALL_IMAGES', $this->numberofhits); ?>
    <?php   endif; ?>
  </div>
<?php endif;
if($this->params->get('show_header_toplist', 0)): ?>
  <div class="jg_toplist">
    <?php JHTML::_('joomgallery.toplistbar');?>
  </div>
<?php endif; ?>