<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
JHtml::_('behavior.formvalidation');
JHtml::_('bootstrap.tooltip'); ?>
<?php if(!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
  <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
  <div class="row-fluid">
    <div class="span6 well">
      <div class="legend"><?php echo JText::_('COM_JOOMGALLERY_COMMON_IMAGE_SELECTION'); ?></div>
      <div id="triggerClearUploadList" class="btn btn-info pull-right hidden">
        <i class="icon-list icon-black"></i> <?php echo JText::_('COM_JOOMGALLERY_AJAXUPLOAD_CLEAR_UPLOAD_LIST'); ?>
      </div>
      <div id="fine-uploader"></div>
      <script type="text/javascript">
        jQuery(document).ready(function() {
          var uploader = new qq.FineUploader({
            element: jQuery('#fine-uploader')[0],
            request: {
              endpoint: 'index.php?option=<?php echo _JOOM_OPTION; ?>&controller=ajaxupload&task=upload&format=raw',
              paramsInBody: true
            },
            chunking: {
              enabled: true,
              partSize: <?php echo $this->chunkSize; ?>
            },
            autoUpload: false,
            display: {
              fileSizeOnSubmit: true,
              prependFiles: false
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
                if(jQuery('#generictitle').length > 0) {
                  if(!jQuery('#generictitle').prop('checked')) {
                    jQuery('#imgtitleid-' + id).attr('readonly', 'true');
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
                if(jQuery('#generictitle').length > 0) {
                  if(!jQuery('#generictitle').prop('checked')) {
                    var fileItemContainer = this.getItemByFileId(id);
                    jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="imgtitleid-' + id +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
                    jQuery('#imgtitleid-' + id).change(function() {
                      if(jQuery(this).val().trim() != '') {
                        jQuery(this).removeClass('invalid').attr('aria-invalid', 'false');
                      }
                    });
                  }
                }
              },
              onUpload: function(id, fileName) {
                if(jQuery('#generictitle').length > 0) {
                  if(!jQuery('#generictitle').prop('checked')) {
                    this.requestParams.imgtitle = jQuery('#imgtitleid-' + id).val();
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
            var form = document.getElementById('upload-form');
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
            if(jQuery('#generictitle').length > 0) {
              if(!jQuery('#generictitle').prop('checked')) {
                var valid = true;
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  if(jQuery('#imgtitleid-' + uploader._storedIds[i]).val().trim() == '') {
                    valid = false;
                    jQuery('#imgtitleid-' + uploader._storedIds[i]).addClass('invalid').attr('aria-invalid', 'true');
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
            uploader.requestParams.catid = jQuery('#catid').val();
            if(jQuery('#imgtitle').length > 0) {
              if(jQuery('#generictitle').prop('checked')) {
                uploader.requestParams.imgtitle = jQuery('#imgtitle').val();
              }
            }
            if(jQuery('#filecounter').length > 0) {
              var filecounter = parseInt(jQuery('#filecounter').val());
              if(!isNaN(filecounter)) {
                uploader.requestParams.filecounter = filecounter;
              }
            }
            uploader.requestParams.imgtext = jQuery('#imgtext').val();
            uploader.requestParams.imgauthor = jQuery('#imgauthor').val();
            uploader.requestParams.published = jQuery('#published0').prop('checked') ? 0 : 1;
            uploader.requestParams.access = jQuery('#access').val();
            if(jQuery('#original_delete').length > 0) {
              uploader.requestParams.original_delete = jQuery('#original_delete').prop('checked') ? 1 : 0;
            }
            uploader.requestParams.create_special_gif = jQuery('#create_special_gif').prop('checked') ? 1 : 0;
            uploader.requestParams.debug = jQuery('#debug').prop('checked') ? 1 : 0;
            uploader.setParams(uploader.requestParams);
            uploader.uploadStoredFiles();
            jQuery('#triggerClearUploadList').removeClass('hidden');
          });
          if(jQuery('#generictitle').length > 0) {
            jQuery('#generictitle').change(function() {
              if(jQuery(this).prop('checked')) {
                jQuery('#imgtitle').attr('aria-required', 'true').attr('required', 'required');
                jQuery('#imgtitle-lbl').attr('aria-invalid', 'false');
                jQuery('#imgtitle').parent().parent().show(750);
                if(jQuery('#filecounter').length > 0 ) {
                  jQuery('#filecounter').val('1');
                  jQuery('#filecounter').parent().parent().show(750);
                }
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  jQuery('#imgtitleid-' + uploader._storedIds[i]).remove();
                }
              }
              else {
                jQuery('#imgtitle').val('');
                if(jQuery('#filecounter').length > 0 ) {
                  jQuery('#filecounter').val('');
                }
                jQuery('#imgtitle').removeAttr('aria-required').removeAttr('aria-invalid').removeAttr('required');
                jQuery('#imgtitle-lbl').removeAttr('aria-invalid');
                jQuery('#imgtitle').removeClass('invalid');
                jQuery('#imgtitle-lbl').removeClass('invalid');
                jQuery('#imgtitle').parent().parent().hide(750);
                if(jQuery('#filecounter').length > 0 ) {
                  jQuery('#filecounter').parent().parent().hide(750);
                }
                for(var i = 0; i < uploader._storedIds.length; i++) {
                  var fileItemContainer = uploader.getItemByFileId(uploader._storedIds[i]);
                  jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="imgtitleid-' + uploader._storedIds[i] +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
                  jQuery('#imgtitleid-' + uploader._storedIds[i]).change(function() {
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
    <div class="span6 well">
      <div class="legend"><?php echo JText::_('COM_JOOMGALLERY_COMMON_OPTIONS'); ?></div>
      <form action="index.php" method="post" name="adminForm" id="upload-form" enctype="multipart/form-data" class="form-validate form-horizontal" onsubmit="">
        <div class="control-group">
          <?php echo $this->form->getLabel('catid'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('catid'); ?>
          </div>
        </div>
        <?php if(!$this->_config->get('jg_useorigfilename')): ?>
        <div class="control-group">
          <?php echo $this->form->getLabel('generictitle'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('generictitle'); ?>
          </div>
        </div>
        <div class="control-group">
          <?php echo $this->form->getLabel('imgtitle'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('imgtitle'); ?>
          </div>
        </div>
        <?php endif;
              if(!$this->_config->get('jg_useorigfilename') && $this->_config->get('jg_filenamenumber')): ?>
        <div class="control-group">
          <?php echo $this->form->getLabel('filecounter'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('filecounter'); ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="control-group">
          <?php echo $this->form->getLabel('imgtext'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('imgtext'); ?>
          </div>
        </div>
        <div class="control-group">
          <?php echo $this->form->getLabel('imgauthor'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('imgauthor'); ?>
          </div>
        </div>
        <div class="control-group">
          <?php echo $this->form->getLabel('published'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('published'); ?>
          </div>
        </div>
        <div class="control-group">
          <?php echo $this->form->getLabel('access'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('access'); ?>
          </div>
        </div>
        <?php if($this->_config->get('jg_delete_original') == 2): ?>
        <div class="control-group">
          <?php echo $this->form->getLabel('original_delete'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('original_delete'); ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="control-group">
          <?php echo $this->form->getLabel('create_special_gif'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('create_special_gif'); ?>
          </div>
        </div>
        <div class="control-group">
          <?php echo $this->form->getLabel('debug'); ?>
          <div class="controls">
            <?php echo $this->form->getInput('debug'); ?>
          </div>
        </div>
        <div class="control-group">
          <div class="controls">
            <div id="triggerUpload" class="btn btn-large btn-primary">
              <i class="icon-upload icon-white"></i> <?php echo JText::_('COM_JOOMGALLERY_UPLOAD_UPLOAD'); ?>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php JHtml::_('joomgallery.credits'); ?>
</div>