<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="alert alert-info">
    <h4><?php echo JText::_('COM_JOOMGALLERY_COMMON_IMPORTANT_NOTICE'); ?></h4>
    <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_JUPLOAD_NOTE'); ?>
</div>
<form action="<?php echo JRoute::_('index.php?type=java'); ?>" method="post" name="JavaUploadForm" id="JavaUploadForm" enctype="multipart/form-data" class="form-validate form-horizontal" onsubmit="if(this.task.value == 'upload.upload' && !document.formvalidator.isValid(document.id('JavaUploadForm'))){alert('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true); ?>');return false;}">
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('catid'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('catid'); ?>
    </div>
  </div>
      <?php if(!$this->_config->get('jg_useruseorigfilename')): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('imgtitle'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('imgtitle'); ?>
    </div>
  </div>
      <?php endif; ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('imgtext'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('imgtext'); ?>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('imgauthor'); ?>
    </div>
    <div class="controls">
      <div class="jg-uploader"><?php echo JHtml::_('joomgallery.displayname', $this->_user->get('id'), 'upload'); ?></div>
    </div>
  </div>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('published'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('published'); ?>
    </div>
  </div>
      <?php /*
      <?php echo $this->applet_form->getLabel('access'); ?>
      <?php echo $this->applet_form->getInput('access'); ?>
        */ ?>
      <?php if($this->_config->get('jg_delete_original_user') == 2): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('original_delete'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('original_delete'); ?>
    </div>
  </div>
      <?php endif;
            if($this->_config->get('jg_special_gif_upload') == 1): ?>
  <div class="control-group">
    <div class="control-label">
      <?php echo $this->applet_form->getLabel('create_special_gif'); ?>
    </div>
    <div class="controls">
      <?php echo $this->applet_form->getInput('create_special_gif'); ?>
    </div>
  </div>
      <?php endif; ?>
  <div class="control-group">
    <?php echo $this->applet_form->getInput('applet'); ?>
    <input type="hidden" name="task" value="upload.upload" />
  </div>
  <!--
  <div class="control-group">
    <div class="control-label">
      <label for="button"></label>
    </div>
    <div class="controls">
      <button id="button" type="button" onclick="if(!document.formvalidator.isValid(document.id('JavaUploadForm'))){alert('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true); ?>');return false;}document.JUpload.startUpload();"><i class="icon-button"></i> <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_UPLOAD'); ?></button>
    </div>
  </div>-->
</form>