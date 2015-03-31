<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
echo $this->loadTemplate('header'); ?>
  <h3 class="well well-small jg-header">
    <?php echo JText::_('COM_JOOMGALLERY_COMMON_UPLOAD_NEW_IMAGE'); ?>
  </h3>
<?php if($this->_config->get('jg_newpiccopyright') && !$this->get('AdminLogged')): ?>
  <div class="alert">
    <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_COPYRIGHT'); ?>
  </div>
<?php endif;
      if($this->_config->get('jg_newpicnote') && !$this->get('AdminLogged')): ?>
  <div class="alert alert-info center">
    <h4><?php echo JText::_('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_NOTE'); ?></h4>
    <small>
      <?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_MAXSIZE', number_format($this->_config->get('jg_maxfilesize')/* / 1024*/, 0, JText::_('COM_JOOMGALLERY_COMMON_DECIMAL_SEPARATOR'), JText::_('COM_JOOMGALLERY_COMMON_THOUSANDS_SEPARATOR')));
        if($this->_user->get('id')): ?><br />
      <?php $timespan = $this->_config->get('jg_maxuserimage_timespan'); ?>
      <?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_MAXCOUNT', $this->_config->get('jg_maxuserimage'), $timespan > 0 ? JText::plural('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_MAXCOUNT_TIMESPAN', $timespan) : ''); ?><br />
      <?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_YOURCOUNT', $this->count); ?><br />
      <?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_NEW_IMAGE_REMAINDER', $this->remainder);
        endif; ?>
    </small>
  </div>
<?php endif;

      if(count($this->uploads)):
        $browser = new JBrowser();
        if($browser->isMobile() || count($this->uploads) == 1):
          echo $this->loadTemplate(key($this->uploads));
        else: ?>
  <ul class="nav nav-tabs">
<?php     foreach($this->uploads as $key => $upload): ?>
    <li<?php echo $upload['active'] ? ' class="active"' : ''; ?>>
      <a href="#jg-upload-<?php echo $key; ?>" data-toggle="tab"><?php echo JText::_($upload['title']);?></a>
    </li>
<?php     endforeach; ?>
  </ul>
  <div class="tab-content">
<?php     foreach($this->uploads as $key => $upload): ?>
    <div class="tab-pane<?php echo $upload['active'] ? ' active' : ''; ?>" id="jg-upload-<?php echo $key; ?>">
<?php       echo $this->loadTemplate($key); ?>
    </div>
<?php     endforeach; ?>
  </div>
<?php   endif;
      endif;
echo $this->loadTemplate('footer');