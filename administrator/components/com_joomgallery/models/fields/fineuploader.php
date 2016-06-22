<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/fineuploader.php $
// $Id: fineuploader.php 4076 2015-05-19 10:35:29Z chraneco $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2015  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Renders a FineUploader field
 *
 * @package     JoomGallery
 * @since       3.2
 */
class JFormFieldFineuploader extends JFormField
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 3.2
   */
  protected $type = 'fineuploader';

  /**
   * Returns the HTML for the FineUploader inclusion
   *
   * @return  string  The HTML for the FineUploader inclusion
   * @since   3.2
   */
  protected function getInput()
  {
    $app    = JFactory::getApplication();
    $config = JoomConfig::getInstance();

    $url = JRoute::_('index.php?option='._JOOM_OPTION.'&controller=ajaxupload&task=upload', false);
    if($app->isSite())
    {
      $url = JRoute::_('index.php?option='._JOOM_OPTION.'&task=ajaxupload.upload', false);
    }

    $fileSizeLimit = $app->isSite() ? $config->get('jg_maxfilesize') : 0;
    $chunkSize     = 0;
    $post_max_size = @ini_get('post_max_size');
    if(!empty($post_max_size))
    {
      $post_max_size   = JoomHelper::iniToBytes($post_max_size);
      $chunkSize = (int) min(500000, (int)(0.8 * $post_max_size));
    }
    $upload_max_filesize = @ini_get('upload_max_filesize');
    if(!empty($upload_max_filesize))
    {
      $upload_max_filesize = JoomHelper::iniToBytes($upload_max_filesize);

      if($fileSizeLimit <= 0 || $fileSizeLimit > $upload_max_filesize)
      {
        $fileSizeLimit = $upload_max_filesize;
      }
    }

    $editFilename   = $app->isSite() ? $config->get('jg_useruseorigfilename') : $config->get('jg_useorigfilename');
    $prefix         = $this->element['field_id_prefix'];
    $isMini         = $this->element['mini'] && $this->element['mini'] != 'false';
    $redirect       = $this->element['redirect'];
    $insertOptions  = $this->element['insert_options'] && $this->element['insert_options'] != 'false';
    $formId         = $this->element['form_id'];
    $catidField     = $this->element['catid_field'] ? $this->element['catid_field'] : 'catid';

    if(!$formId)
    {
      throw new RuntimeException('Attribute \'form_id\' is required for the fineuploader field.');
    }

    ob_start(); ?>
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
        partSize: <?php echo $chunkSize; ?>
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
        sizeLimit: <?php echo $fileSizeLimit; ?>
      },
      messages: {
        typeError: '{file}: ' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_WRONG_EXTENSION', true); ?>',
        sizeError: '{file}: ' + '<?php echo JText::sprintf('COM_JOOMGALLERY_UPLOAD_OUTPUT_MAX_ALLOWED_FILESIZE', $fileSizeLimit, array('jsSafe' => true)) ?>',
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
        onComplete: function(id, fileName, response) {
          var item = this.getItemByFileId(id);
<?php if($isMini):
        if($insertOptions): ?>
          // Display options if the upload was successful and if the image is published
          if(response.success && !document.id('published0').checked) {
            displayInsertOptions(this, item, fileName, response);
          }
<?php   else: ?>
          // Reload the page if published images were uploaded (so the user can see them instantly on the page)
          if(response.success && !document.id('published0').checked) {
            window.parent.SqueezeBox.addEvent('onClose', function(){window.parent.location.reload();});
          }
<?php   endif;
      endif; ?>
          if(response.debug_output) {
            var element = item.getElementsByClassName("qq-upload-debug-text-selector")[0];
            element.innerHTML = response.debug_output;
          }
          if(this.requestParams.hasOwnProperty("filecounter")) {
            this.requestParams.filecounter =  this.requestParams.filecounter + 1;
            this.setParams(this.requestParams);
          }
          if(jQuery('#<?php echo $prefix; ?>generictitle').length > 0) {
            if(!jQuery('#<?php echo $prefix; ?>generictitle').prop('checked')) {
              jQuery('#<?php echo $prefix; ?>imgtitleid-' + id)<?php echo $isMini ? '.remove();' : ".attr('readonly', 'true');"; ?>
            }
          }
          <?php if($redirect): ?>
          if(response.success) {
            uploader.fileCount--;
            var redirect = '<?php echo $redirect; ?>';
            if(uploader.fileCount == 0 && redirect != '') {
              // Redirect only if all file uploads were successful
              location.href = redirect;
            }
          }
          <?php endif; ?>
        },
        onValidate: function(fileData) {
          if(!jg_filenamewithjs) {
            var searchwrongchars = /[^a-zA-Z0-9 _-]/;
            if(searchwrongchars.test(fileData.name.substr(0, fileData.name.lastIndexOf('.')))) {
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
          if(jQuery('#<?php echo $prefix; ?>generictitle').length > 0) {
            if(!jQuery('#<?php echo $prefix; ?>generictitle').prop('checked')) {
              var fileItemContainer = this.getItemByFileId(id);
              jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="<?php echo $prefix; ?>imgtitleid-' + id +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
              jQuery('#<?php echo $prefix; ?>imgtitleid-' + id).change(function() {
                if(jQuery(this).val().trim() != '') {
                  jQuery(this).removeClass('invalid').attr('aria-invalid', 'false');
                }
              });
            }
          }
        },
        onUpload: function(id, fileName) {
          if(jQuery('#<?php echo $prefix; ?>generictitle').length > 0) {
            if(!jQuery('#<?php echo $prefix; ?>generictitle').prop('checked')) {
              this.requestParams.imgtitle = jQuery('#<?php echo $prefix; ?>imgtitleid-' + id).val();
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
      var form = document.getElementById('<?php echo $formId; ?>');
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

      <?php if($isMini): ?>
      // For new upload procedure scrolling is allowed for the first image
      jg_scrolled = false;
      <?php endif; ?>

      if(jQuery('#<?php echo $prefix; ?>generictitle').length > 0) {
        if(!jQuery('#<?php echo $prefix; ?>generictitle').prop('checked')) {
          var valid = true;
          for(var i = 0; i < uploader._storedIds.length; i++) {
            if(jQuery('#<?php echo $prefix; ?>imgtitleid-' + uploader._storedIds[i]).val().trim() == '') {
              valid = false;
              jQuery('#<?php echo $prefix; ?>imgtitleid-' + uploader._storedIds[i]).addClass('invalid').attr('aria-invalid', 'true');
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
      uploader.requestParams.catid = jQuery('#<?php echo $prefix.$catidField; ?>').val();
      if(jQuery('#<?php echo $prefix; ?>imgtitle').length > 0) {
        if(jQuery('#<?php echo $prefix; ?>generictitle').prop('checked')) {
          uploader.requestParams.imgtitle = jQuery('#<?php echo $prefix; ?>imgtitle').val();
        }
      }
      if(jQuery('#<?php echo $prefix; ?>filecounter').length > 0) {
        var filecounter = parseInt(jQuery('#<?php echo $prefix; ?>filecounter').val());
        if(!isNaN(filecounter)) {
          uploader.requestParams.filecounter = filecounter;
        }
      }
      <?php if(!$isMini): ?>
      uploader.requestParams.imgtext = jQuery('#<?php echo $prefix; ?>imgtext').val();
      uploader.requestParams.debug = jQuery('#<?php echo $prefix; ?>debug').prop('checked') ? 1 : 0;
      <?php   if($app->isSite()): ?>
      uploader.requestParams.published = jQuery('#<?php echo $prefix; ?>published').val();
      <?php   else: ?>
      uploader.requestParams.published = jQuery('#<?php echo $prefix; ?>published0').prop('checked') ? 0 : 1;
      uploader.requestParams.imgauthor = jQuery('#<?php echo $prefix; ?>imgauthor').val();
      uploader.requestParams.access = jQuery('#<?php echo $prefix; ?>access').val();
      <?php   endif;
            else: ?>
      uploader.requestParams.published = jQuery('#<?php echo $prefix; ?>published0').prop('checked') ? 0 : 1;
      <?php endif; ?>
      if(jQuery('#<?php echo $prefix; ?>original_delete').length > 0) {
        uploader.requestParams.original_delete = jQuery('#<?php echo $prefix; ?>original_delete').prop('checked') ? 1 : 0;
      }
      uploader.requestParams.create_special_gif = jQuery('#<?php echo $prefix; ?>create_special_gif').prop('checked') ? 1 : 0;
      uploader.setParams(uploader.requestParams);
      uploader.fileCount = uploader._storedIds.length;
      uploader.uploadStoredFiles();
      jQuery('#triggerClearUploadList').removeClass('hidden');
    });
    if(jQuery('#<?php echo $prefix; ?>generictitle').length > 0) {
      jQuery('#<?php echo $prefix; ?>generictitle').change(function() {
        if(jQuery(this).prop('checked')) {
          jQuery('#<?php echo $prefix; ?>imgtitle').addClass('required');
          jQuery('#<?php echo $prefix; ?>imgtitle').attr('aria-required', 'true').attr('required', 'required');
          jQuery('#<?php echo $prefix; ?>imgtitle-lbl').attr('aria-invalid', 'false');
          jQuery('#<?php echo $prefix; ?>imgtitle').parent().parent().show(750);
          if(jQuery('#<?php echo $prefix; ?>filecounter').length > 0 ) {
            jQuery('#<?php echo $prefix; ?>filecounter').val('1');
            jQuery('#<?php echo $prefix; ?>filecounter').parent().parent().show(750);
          }
          for(var i = 0; i < uploader._storedIds.length; i++) {
            jQuery('#<?php echo $prefix; ?>imgtitleid-' + uploader._storedIds[i]).remove();
          }
        }
        else {
          jQuery('#<?php echo $prefix; ?>imgtitle').val('');
          if(jQuery('#<?php echo $prefix; ?>filecounter').length > 0 ) {
            jQuery('#<?php echo $prefix; ?>filecounter').val('');
          }
          jQuery('#<?php echo $prefix; ?>imgtitle').removeClass('required');
          jQuery('#<?php echo $prefix; ?>imgtitle').removeAttr('aria-required').removeAttr('aria-invalid').removeAttr('required');
          jQuery('#<?php echo $prefix; ?>imgtitle-lbl').removeAttr('aria-invalid');
          jQuery('#<?php echo $prefix; ?>imgtitle').removeClass('invalid');
          jQuery('#<?php echo $prefix; ?>imgtitle-lbl').removeClass('invalid');
          jQuery('#<?php echo $prefix; ?>imgtitle').parent().parent().hide(750);
          if(jQuery('#<?php echo $prefix; ?>filecounter').length > 0 ) {
            jQuery('#<?php echo $prefix; ?>filecounter').parent().parent().hide(750);
          }
          for(var i = 0; i < uploader._storedIds.length; i++) {
            var fileItemContainer = uploader.getItemByFileId(uploader._storedIds[i]);
            jQuery(fileItemContainer).find('.qq-upload-cancel').after('<input id="<?php echo $prefix; ?>imgtitleid-' + uploader._storedIds[i] +'" class="qq-edit-imgtitle qq-editing" tabindex="0" type="text" value="" placeholder="' + '<?php echo JText::_('COM_JOOMGALLERY_COMMON_ENTER_IMAGE_TITLE', true); ?>' + '" required aria-required="true">');
            jQuery('#<?php echo $prefix; ?>imgtitleid-' + uploader._storedIds[i]).change(function() {
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
        <img class="qq-thumbnail-selector thumbnail" qq-max-size="50" qq-server-scale="<?php echo !$isMini ? 'true' : 'false'; ?>">
        <?php if($editFilename) echo '<span class="qq-edit-filename-icon-selector qq-edit-filename-icon"></span>'; ?>
        <span class="qq-upload-file-selector qq-upload-file"></span>
        <?php if($editFilename) echo '<input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">'; ?>
        <span class="qq-upload-size-selector qq-upload-size badge"></span>
        <a class="qq-upload-cancel-selector qq-upload-cancel btn btn-mini" href="#"><?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL', true); ?></a>
        <span class="qq-upload-status-text-selector qq-upload-status-text"></span>
        <span class="qq-upload-debug-text-selector qq-upload-debug-text"></span>
        <?php if($isMini): ?>
        <div class="qq-upload-options-selector qq-upload-options form-horizontal hide"></div>
        <div class="qq-upload-note-selector qq-upload-note hide small center"><?php echo JText::_('COM_JOOMGALLERY_MINI_AJAX_UPLOAD_NOTE', true); ?></div>
        <?php endif; ?>
      </li>
    </ul>
  </div>
</script>
<?php
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}
