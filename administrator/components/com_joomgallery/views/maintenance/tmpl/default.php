<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip'); ?>
<?php if(!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
  <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
  <div class="alert alert-info">
    <?php echo JText::_('COM_JOOMGALLERY_MAIMAN_INTRO'); ?>
  </div>
<?php
echo JHtml::_('tabs.start', 'maintenance-pane', array('startOffset' => $this->startOffset));
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_IMAGES').$this->information['images'], 'cpanel-panel-joom-maintenance-db-images');
echo $this->loadTemplate('dbimages');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_CATEGORIES').$this->information['categories'], 'cpanel-panel-joom-maintenance-db-categories');
echo $this->loadTemplate('categories');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_ORPHANS').$this->information['orphans'], 'cpanel-panel-joom-maintenance-file-images');
echo $this->loadTemplate('orphans');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_ORPHANED_FOLDERS').$this->information['folders'], 'cpanel-panel-joom-maintenance-file-folders');
echo $this->loadTemplate('folders');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_COMMAN_COMMENTS_MANAGER'), 'cpanel-panel-joom-maintenance-comments');
echo $this->loadTemplate('comments');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_FAVOURITES_MANAGER'), 'cpanel-panel-joom-maintenance-favourites');
echo $this->loadTemplate('favourites');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_NAMETAGS'), 'cpanel-panel-joom-maintenance-nametags');
echo $this->loadTemplate('nametags');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_VOTES_MANAGER'), 'cpanel-panel-joom-maintenance-votes');
echo $this->loadTemplate('votes');
echo JHtml::_('tabs.panel', JText::_('COM_JOOMGALLERY_MAIMAN_TAB_DATABASE'), 'cpanel-panel-joom-maintenance-database');
echo $this->loadTemplate('database');
echo JHtml::_('tabs.end');
JHtml::_('joomgallery.credits'); ?>
</div>