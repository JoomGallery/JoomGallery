<?php defined('_JEXEC') or die('Restricted access');
if($this->_mainframe->isSite())
{
  $url = JRoute::_('index.php?option='._JOOM_OPTION.'&task=ajaxupload.upload', false);
}
else
{
  $url = JRoute::_('index.php?option='._JOOM_OPTION.'&controller=ajaxupload&task=upload', false);
} ?>
  <form action="index.php" method="post" class="form-validate form-horizontal" id="SingleUploadForm" name="SingleUploadForm">
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
    <div id="fine-uploader"></div>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        var uploader = new qq.FineUploader({
          element: jQuery('#fine-uploader')[0],
          request: {
            endpoint: '<?php echo $url.'&format=raw'; ?>',
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
              debugText: 'qq-upload-debug-text',
              thumb: 'qq-upload-thumb',
              options: 'qq-upload-options',
              note: 'qq-upload-note'
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
            onComplete: function(id, fileName, r) {
              var item = this.getItemByFileId(id);

<?php if(!$this->upload_catid): ?>
              // Display options if the upload was successful and if the image is published
              if(r.success && !document.id('published0').checked)
              {
                displayInsertOptions(this, item, fileName, r);
              }
<?php else: ?>
              // Reload the page if published images were uploaded (so the user can see them instantly on the page)
              if(r.success && !document.id('published0').checked)
              {
                window.parent.SqueezeBox.addEvent('onClose', function(){window.parent.location.reload();});
              }
<?php endif; ?>

              if(r.debug_output) {
                var element = item.getElementsByClassName("qq-upload-debug-text-selector")[0];
                element.innerHTML = r.debug_output;
              }
              if(this.requestParams.hasOwnProperty("filecounter")) {
                this.requestParams.filecounter =  this.requestParams.filecounter + 1;
                this.setParams(this.requestParams);
              }
              if(jQuery('#generictitle').length > 0) {
                if(!jQuery('#generictitle').prop('checked')) {
                  jQuery('#imgtitleid-' + id).remove();
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
          Joomla.removeMessages();

          if(uploader._storedIds.length == 0) {
            alert('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_ONE_IMAGE', true); ?>');
            return false;
          }
          var form = document.getElementById('SingleUploadForm');
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

          // For new upload procedure scrolling is allowed for the first image
          jg_scrolled = false;

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
          uploader.requestParams.catid = jQuery('#upload_categories').val();
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
          //uploader.requestParams.imgtext = jQuery('#imgtext').val();
          //uploader.requestParams.imgauthor = jQuery('#imgauthor').val();
          uploader.requestParams.published = jQuery('#published0').prop('checked') ? 0 : 1;
          //uploader.requestParams.access = jQuery('#access').val();
          if(jQuery('#original_delete').length > 0) {
            uploader.requestParams.original_delete = jQuery('#original_delete').prop('checked') ? 1 : 0;
          }
          uploader.requestParams.create_special_gif = jQuery('#create_special_gif').prop('checked') ? 1 : 0;
          //uploader.requestParams.debug = jQuery('#debug').prop('checked') ? 1 : 0;
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
            <img class="qq-thumbnail-selector thumbnail" qq-max-size="50" qq-server-scale="false">
            <?php if($this->editFilename) echo '<span class="qq-edit-filename-icon-selector qq-edit-filename-icon"></span>'; ?>
            <span class="qq-upload-file-selector qq-upload-file"></span>
            <?php if($this->editFilename) echo '<input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">'; ?>
            <span class="qq-upload-size-selector qq-upload-size badge"></span>
            <a class="qq-upload-cancel-selector qq-upload-cancel btn btn-mini" href="#"><?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL', true); ?></a>
            <span class="qq-upload-status-text-selector qq-upload-status-text"></span>
            <span class="qq-upload-debug-text-selector qq-upload-debug-text"></span>
            <div class="qq-upload-options-selector qq-upload-options form-horizontal hide"></div>
            <div class="qq-upload-note-selector qq-upload-note hide small center"><?php echo JText::_('COM_JOOMGALLERY_MINI_AJAX_UPLOAD_NOTE', true); ?></div>
          </li>
        </ul>
      </div>
      </script>
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