<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="form-horizontal">
  <div class="control-group">
    <div class="control-label">
    </div>
    <div class="controls">
      <div id="triggerClearUploadList" class="btn btn-info pull-right hidden">
        <i class="icon-list icon-black"></i> <?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_CLEAR_UPLOAD_LIST'); ?>
      </div>
      <div id="fine-uploader"></div>
      <script type="text/javascript">
        jQuery(document).ready(function() {
          var uploader = new qq.FineUploader({
            element: jQuery('#fine-uploader')[0],
            request: {
              endpoint: 'index.php?option=<?php echo _JOOM_OPTION; ?>&task=ajaxupload.upload&format=raw',
              paramsInBody: true
            },
            chunking: {
              enabled: true,
              partSize: <?php echo $this->chunkSize; ?>
            },
            autoUpload: false,
            display: {
              fileSizeOnSubmit: true
            },
            text: {
              failUpload: '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_UPLOAD_FAILED', true); ?>',
              formatProgress: '{percent}% ' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_OF', true); ?>' +'  {total_size}',
              waitingForResponse: '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_PROCESSING', true); ?>'
            },
            failedUploadTextDisplay: {
              mode: 'custom'
            },
            dragAndDrop: {
              extraDropzones: []
            },
            fileTemplate: 'qq-template',
            classes: {
                success: 'alert-success',
                fail: 'alert-error',
                debugText: 'qq-upload-debug-text'
            },
            validation: {
              allowedExtensions: ['jpg', 'jpeg', 'jpe', 'gif', 'png'],
              acceptFiles: 'image/*',
              sizeLimit: <?php echo $this->fileSizeLimit; ?>
            },
            messages: {
              typeError: '{file}: ' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_WRONG_EXTENSION', true); ?>',
              sizeError: '{file}: ' + '<?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_OUTPUT_MAX_ALLOWED_FILESIZE', $this->fileSizeLimit, array(true)) ?>',
              fileNameError: '{file}: ' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_WRONG_FILENAME', true); ?>',
              fileNameDouble: '{file}: ' + '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_ALERT_FILENAME_DOUBLE', true); ?>',
              minSizeError: '{file}: ' + '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_ALERT_FILE_TOO_SMALL', true); ?>' + ' {minSizeLimit}.',
              emptyError: '{file} : '  + '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_ALERT_FILE_EMPTY', true); ?>',
              noFilesError: '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_ALERT_NO_FILES', true); ?>',
              onLeave: '<?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_ALERT_ON_LEAVE', true); ?>'
            },
            debug: true,
            maxConnections: 1,
            disableCancelForFormUploads: true,
            callbacks: {
              onComplete: function(id, fileName, responseJSON) {
                if(responseJSON.debug_output) {
                  var item = this.getItemByFileId(id);
                  var element = item.getElementsByClassName("qq-upload-debug-text-selector")[0];
                  element.innerHTML = responseJSON.debug_output;
                }
                if(this.requestParams.hasOwnProperty("filecounter")) {
                  this.requestParams.filecounter =  this.requestParams.filecounter + 1;
                  this.setParams(this.requestParams);
                }
                if(jQuery('#ajax_generictitle').length > 0) {
                  if(!jQuery('#ajax_generictitle').prop('checked')) {
                    jQuery('#ajax_imgtitleid-' + id).attr('readonly', 'true');
                  }
                }
                if(responseJSON.success) {
                	uploader.fileCount--;
                	var ajax_redirect = '<?php echo $this->ajax_redirect; ?>';
                	if(uploader.fileCount == 0 && ajax_redirect != '') {
                    // redirect only if all file upload were successfull
                    location.href = ajax_redirect;
                	}
                }
              },
              onValidate: function(fileData) {
                if(!jg_filenamewithjs) {
                  var searchwrongchars = /[^a-zA-Z0-9_-]/;
                  if(searchwrongchars.test(fileData.name)) {
                    this._itemError('fileNameError', fileData.name);
                    return false;
                  }
                }
                for (var i = 0; i < this._storedIds.length; i++) {
                  var fileName = this.getName(this._storedIds[i]);
                  if(fileName && fileName == fileData.name) {
                    this._itemError('fileNameDouble', fileData.name);
                    return false;
                  }
                }
              },
              onSubmitted: function(id, fileName) {
                if(jQuery('#ajax_generictitle').length > 0) {
                  if(!jQuery('#ajax_generictitle').prop('checked')) {
                    var fileItemContainer = this.getItemByFileId(id);
                    jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="ajax_imgtitleid-' + id +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
                    jQuery('#ajax_imgtitleid-' + id).change(function() {
                      if(jQuery(this).val().trim() != '') {
                        jQuery(this).removeClass('invalid').attr('aria-invalid', 'false');
                      }
                    });
                  }
                }
              },
              onUpload: function(id, fileName) {
                if(jQuery('#ajax_generictitle').length > 0) {
                  if(!jQuery('#ajax_generictitle').prop('checked')) {
                    this.requestParams.imgtitle = jQuery('#ajax_imgtitleid-' + id).val();
                    this.setParams(this.requestParams);
                  }
                }
              }
            }
          });
          jQuery('#triggerClearUploadList').click(function() {
            uploader.reset();
            jQuery('#triggerClearUploadList').addClass('hidden');
          });
          jQuery('#triggerUpload').click(function() {
            if(uploader._storedIds.length == 0) {
              alert('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_ONE_IMAGE', true); ?>');
              return false;
            }
            var form = document.getElementById('AjaxUploadForm');
            if(!document.formvalidator.isValid(form)) {
              var msg = new Array();
              msg.push('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>');
              if(form.imgtitle && form.imgtitle.hasClass('invalid')) {
                  msg.push('<?php echo JText::_("COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE", true);?>');
              }
              if(form.catid.hasClass('invalid')) {
                msg.push('<?php echo JText::_("COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_CATEGORY", true);?>');
              }
              alert(msg.join('\n'));
              return false;
            }
            if(jQuery('#ajax_generictitle').length > 0) {
              if(!jQuery('#ajax_generictitle').prop('checked')) {
                var valid = true;
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  if(jQuery('#ajax_imgtitleid-' + uploader._storedIds[i]).val().trim() == '') {
                    valid = false;
                    jQuery('#ajax_imgtitleid-' + uploader._storedIds[i]).addClass('invalid').attr('aria-invalid', 'true');
                  }
                }
                if(!valid) {
                  alert('<?php echo JText::_("COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE", true);?>');
                  return valid;
                }
              }
            }
            // Prepare request parameters
            uploader.requestParams = new Object();
            uploader.requestParams.catid = jQuery('#ajax_catid').val();
            if(jQuery('#ajax_imgtitle').length > 0) {
              if(jQuery('#ajax_generictitle').prop('checked')) {
                uploader.requestParams.imgtitle = jQuery('#ajax_imgtitle').val();
              }
            }
            if(jQuery('#ajax_filecounter').length > 0) {
              var filecounter = parseInt(jQuery('#ajax_filecounter').val());
              if(!isNaN(filecounter)) {
                uploader.requestParams.filecounter = filecounter;
              }
            }
            uploader.requestParams.imgtext = jQuery('#ajax_imgtext').val();
            uploader.requestParams.published = jQuery('#ajax_published').val();
            if(jQuery('#ajax_original_delete').length > 0) {
              uploader.requestParams.original_delete = jQuery('#ajax_original_delete').prop('checked') ? 1 : 0;
            }
            uploader.requestParams.create_special_gif = jQuery('#ajax_create_special_gif').prop('checked') ? 1 : 0;
            uploader.requestParams.debug = jQuery('#ajax_debug').prop('checked') ? 1 : 0;
            uploader.setParams(uploader.requestParams);
            uploader.fileCount = uploader._storedIds.length;
            uploader.uploadStoredFiles();
            jQuery('#triggerClearUploadList').removeClass('hidden');
          });
          if(jQuery('#ajax_generictitle').length > 0) {
            jQuery('#ajax_generictitle').change(function() {
              if(jQuery(this).prop('checked')) {
                jQuery('#ajax_imgtitle').attr('aria-required', 'true').attr('required', 'required');
                jQuery('#ajax_imgtitle-lbl').attr('aria-invalid', 'false');
                jQuery('#ajax_imgtitle').parent().parent().show(750);
                if(jQuery('#ajax_filecounter').length > 0 ) {
                  jQuery('#ajax_filecounter').val('1');
                  jQuery('#ajax_filecounter').parent().parent().show(750);
                }
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  jQuery('#ajax_imgtitleid-' + uploader._storedIds[i]).remove();
                }
              }
              else {
                jQuery('#ajax_imgtitle').val('');
                if(jQuery('#ajax_filecounter').length > 0 ) {
                  jQuery('#ajax_filecounter').val('');
                }
                jQuery('#ajax_imgtitle').removeAttr('aria-required').removeAttr('aria-invalid').removeAttr('required');
                jQuery('#ajax_imgtitle-lbl').removeAttr('aria-invalid');
                jQuery('#ajax_imgtitle').removeClass('invalid');
                jQuery('#ajax_imgtitle-lbl').removeClass('invalid');
                jQuery('#ajax_imgtitle').parent().parent().hide(750);
                if(jQuery('#ajax_filecounter').length > 0 ) {
                  jQuery('#ajax_filecounter').parent().parent().hide(750);
                }
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  var fileItemContainer = uploader.getItemByFileId(uploader._storedIds[i]);
                  jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="ajax_imgtitleid-' + uploader._storedIds[i] +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
                  jQuery('#ajax_imgtitleid-' + uploader._storedIds[i]).change(function() {
                    if(jQuery(this).val().trim() != '') {
                      jQuery(this).removeClass('invalid').attr('aria-invalid', 'false');
                    }
                  });
                }
              }
            });
          }
        });
      </script>
      <script type="text/template" id="qq-template">
        <div class="qq-uploader-selector qq-uploader span12">
          <div class="qq-upload-drop-area-selector qq-upload-drop-area span12" qq-hide-dropzone>
            <span><?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_DRAGZONETEXT', true); ?></span>
          </div>
          <div class="qq-upload-button-selector qq-upload-button btn btn-large btn-success">
            <div><i class="icon-plus icon-plus"></i> <?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_SELECT_IMAGES', true); ?></div>
          </div>
          <div class="small"><?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_DRAGNDROPHINT'); ?></div>
          <span class="qq-drop-processing-selector qq-drop-processing">
            <span><?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_DROPPROCESSINGTEXT', true); ?></span>
            <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
          </span>
          <ul class="qq-upload-list-selector qq-upload-list">
            <li class="alert">
              <div class="qq-progress-bar-container-selector">
                <div class="qq-progress-bar-selector qq-progress-bar"></div>
              </div>
              <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
              <img class="qq-thumbnail-selector thumbnail" qq-max-size="50" qq-server-scale>
              <?php if($this->editFilename) echo '<span class="qq-edit-filename-icon-selector qq-edit-filename-icon"></span>'; ?>
              <span class="qq-upload-file-selector qq-upload-file"></span>
              <?php if($this->editFilename) echo '<input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">'; ?>
              <span class="qq-upload-size-selector qq-upload-size badge"></span>
              <a class="qq-upload-cancel-selector qq-upload-cancel btn btn-mini" href="#"><?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL', true); ?></a>
              <span class="qq-upload-status-text-selector qq-upload-status-text"></span>
              <span class="qq-upload-debug-text-selector qq-upload-debug-text"></span>
            </li>
          </ul>
        </div>
      </script>
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