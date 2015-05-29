<?php defined('_JEXEC') or die('Restricted access'); ?>
  <form action="index.php" method="post" class="form-validate form-horizontal" id="MiniUploadForm" name="MiniUploadForm">
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('catid'); ?>
      <div class="controls">
        <?php echo $this->upload_categories ? $this->upload_categories : $this->upload_form->getInput('catid'); ?>
      </div>
    </div>
    <?php if(!$this->editFilename): ?>
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('generictitle'); ?>
      <div class="controls">
        <?php echo $this->upload_form->getInput('generictitle'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('imgtitle'); ?>
      <div class="controls">
        <?php echo $this->upload_form->getInput('imgtitle'); ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('published'); ?>
      <div class="controls">
        <?php echo $this->upload_form->getInput('published'); ?>
      </div>
    </div>
    <?php if($this->delete_original): ?>
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('original_delete'); ?>
      <div class="controls">
        <?php echo $this->upload_form->getInput('original_delete'); ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="control-group">
      <?php echo $this->upload_form->getLabel('create_special_gif'); ?>
      <div class="controls">
        <?php echo $this->upload_form->getInput('create_special_gif'); ?>
      </div>
    </div>
  </form>
  <div>
    <?php echo $this->upload_form->getInput('ajaxupload'); ?>
  </div>
  <div class="buttons">
    <button id="triggerUpload" class="btn btn-large btn-primary pull-left">
      <i class="icon-upload icon-white"></i>
      <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_UPLOAD'); ?>
    </button>
    <button id="triggerClearUploadList" class="btn btn-info pull-right hidden">
      <i class="icon-list icon-black"></i> <?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_CLEAR_UPLOAD_LIST'); ?>
    </button>
  </div>