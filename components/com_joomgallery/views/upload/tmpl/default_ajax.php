<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="form-horizontal">
  <div class="control-group">
    <div class="control-label">
    </div>
    <div class="controls">
      <div id="triggerClearUploadList" class="btn btn-info pull-right hidden">
        <i class="icon-list icon-black"></i> <?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_CLEAR_UPLOAD_LIST'); ?>
      </div>
      <?php echo $this->ajax_form->getInput('ajaxupload'); ?>
    </div>
  </div>
</div>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="AjaxUploadForm" id="AjaxUploadForm" enctype="multipart/form-data" class="form-validate form-horizontal" onsubmit="">
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('catid'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('catid'); ?>
    </div>
  </div>
      <?php if(!$this->_config->get('jg_useruseorigfilename')): ?>
  <div class="control-group">
    <?php echo $this->ajax_form->getLabel('generictitle'); ?>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('generictitle'); ?>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('imgtitle'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('imgtitle'); ?>
    </div>
  </div>
      <?php endif;
            if(!$this->_config->get('jg_useruseorigfilename') && $this->_config->get('jg_useruploadnumber')): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('filecounter'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('filecounter'); ?>
    </div>
  </div>
      <?php endif; ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('imgtext'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('imgtext'); ?>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('imgauthor'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('imgauthor'); ?>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('owner'); ?>
    </div>
    <div class="controls">
      <div class="jg-uploader"><?php echo JHtml::_('joomgallery.displayname', $this->_user->get('id'), 'upload'); ?></div>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('published'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('published'); ?>
    </div>
  </div>
    <?php /*
      <?php echo $this->ajax_form->getLabel('access'); ?>
      <?php echo $this->ajax_form->getInput('access'); ?>
          */ ?>
      <?php if($this->_config->get('jg_delete_original_user') == 2): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('original_delete'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('original_delete'); ?>
    </div>
  </div>
      <?php endif;
            if($this->_config->get('jg_special_gif_upload') == 1): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('create_special_gif'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('create_special_gif'); ?>
    </div>
  </div>
      <?php endif;
            if($this->_config->get('jg_redirect_after_upload')): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->ajax_form->getLabel('debug'); ?>
    </div>
    <div class="controls">
      <?php echo $this->ajax_form->getInput('debug'); ?>
    </div>
  </div>
      <?php endif; ?>
  <div class="control-group">
    <div class="control-label">
      <label for="button"></label>
    </div>
    <div class="controls">
      <div id="triggerUpload" class="btn btn-primary">
        <i class="icon-upload icon-white"></i> <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_UPLOAD'); ?>
      </div>
      <button type="button" class="btn" onclick="javascript:location.href='<?php echo JRoute::_('index.php?view=userpanel', false); ?>';return false;"><i class="icon-cancel"></i> <?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL'); ?></button>
    </div>
  </div>
</form>