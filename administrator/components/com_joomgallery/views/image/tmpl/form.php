<?php defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task)
{
  var form = document.id('item-form');
  if(task == 'cancel' || task == 'resethits' || task == 'resetdownloads' || task == 'resetvotes' || document.formvalidator.isValid(form)) {
    <?php echo $this->form->getField('imgtext')->save(); ?>
    Joomla.submitform(task, form);
  }
  else {
    var msg = new Array();
    msg.push('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true); ?>');
    if(form.imgtitle.hasClass('invalid')) {
        msg.push('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE', true); ?>');
    }
    if(form.catid.hasClass('invalid')) {
      msg.push('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_CATEGORY', true); ?>');
    }
    if(form.imgfilename && form.imgfilename.hasClass('invalid')) {
      msg.push('<?php echo JText::_('COM_JOOMGALLERY_IMGMAN_ALERT_SELECT_IMAGE_FILENAME', true); ?>');
    }
    if(form.imgthumbname && form.imgthumbname.hasClass('invalid')) {
      msg.push('<?php echo JText::_('COM_JOOMGALLERY_IMGMAN_ALERT_SELECT_THUMBNAIL_FILENAME', true); ?>');
    }
    alert(msg.join('\n'));
  }
}
// Ensure that changing permissions via AJAX is working correctly by adding some additional URL parameters
jQuery(document).ready(function() {
  var modifiedURL = window.location.href;
  if(modifiedURL.search('&view=image') == (-1)) {
    modifiedURL += '&view=image';
  }
  if(modifiedURL.search('&cid=') > 0 && modifiedURL.search('&id=') == (-1)) {
    modifiedURL += '&id=' + '<?php echo $this->item->id; ?>';
  }
  history.replaceState(null, null, modifiedURL);
});
</script>
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images'); ?>" method="post" name="adminForm" id="item-form" enctype="multipart/form-data" class="form-validate">
  <div class="row-fluid">
    <!-- Begin Content -->
    <div class="span12 form-horizontal">
      <!-- Begin Navigation -->
      <ul class="nav nav-tabs">
        <li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_FIELDSET_IMAGE');?></a></li>
        <li><a href="#parameters" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_COMMON_PARAMETERS');?></a></li>
<?php if(!$this->isNew): ?>
        <li><a href="#replace_files" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REPLACE_FILES');?></a></li>
<?php endif; ?>
        <li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
<?php if($this->_user->authorise('core.admin', _JOOM_OPTION.'.image.'.$this->item->id)): ?>
          <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_FIELDSET_IMAGE_RULES');?></a></li>
<?php endif; ?>
      </ul>
      <!-- End Navigation -->
      <div class="tab-content">
        <!-- Begin Tabs -->
        <!-- Begin Tab Details -->
        <div class="tab-pane active" id="details">
          <div class="row-fluid">
            <div class="span6">
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imgtitle'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imgtitle'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('alias'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('alias'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('catid'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('catid'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('published'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('published'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('hidden'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('hidden'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('featured'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('featured'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('access'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('access'); ?>
                </div>
              </div>
            </div>
<?php if(!$this->isNew): ?>
            <div class="span6">
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('id'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('id'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('publishhiddenstate'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('publishhiddenstate'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('hits'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('hits'); ?>
<?php if($this->item->hits): ?>
                  <button class="btn btn-small" name="reset_hits" type="button" onclick="Joomla.submitbutton('resethits');">
                    <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_RESET_IMAGE_HITS'); ?>
                  </button>
<?php endif; ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('downloads'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('downloads'); ?>
<?php if($this->item->downloads): ?>
                  <button class="btn btn-small" name="reset_downloads" type="button" onclick="Joomla.submitbutton('resetdownloads');">
                    <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_RESET_IMAGE_DOWNLOADS'); ?>
                  </button>
<?php endif; ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('rating'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('rating'); ?>
<?php if($this->item->imgvotes): ?>
                <button class="btn btn-small" name="reset_votes" type="button" onclick="Joomla.submitbutton('resetvotes');">
                  <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_RESET_IMAGE_VOTES'); ?>
                </button>
<?php endif; ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('date'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('date'); ?>
                </div>
              </div>
            </div>
<?php else: ?>
            <div class="span6">
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('detail_catid'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('detail_catid'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imgfilename'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imgfilename'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('original_exists'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('original_exists'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('copy_original'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('copy_original'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('thumb_catid'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('thumb_catid'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imgthumbname'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imgthumbname'); ?>
                </div>
              </div>
            </div>
<?php endif; ?>
          </div>
          <div class="row-fluid" >
            <div class="span6">
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imgtext'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imgtext'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imagelib'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imagelib'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label pull-left">
                  <?php echo $this->form->getLabel('imagelib2'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imagelib2'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Tab Details -->
        <!-- Begin Tab Parameters -->
        <div class="tab-pane" id="parameters">
          <div class="row-fluid">
            <div class="span6">
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('owner'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('owner'); ?>
                </div>
              </div>
              <div class="control-group">
                <div class="control-label">
                  <?php echo $this->form->getLabel('imgauthor'); ?>
                </div>
                <div class="controls">
                  <?php echo $this->form->getInput('imgauthor'); ?>
                </div>
              </div>
            </div>
            <div class="span6">
              <?php echo $this->loadTemplate('options'); ?>
            </div>
          </div>
        </div>
        <!-- End Tab Parameters -->
        <!-- Begin Tab Replace Files -->
<?php if(!$this->isNew): ?>
        <div class="tab-pane" id="replace_files">
          <div class="control-group">
            <div class="alert alert-info">
              <?php echo $this->form->getLabel('spacer', 'files'); ?>
            </div>
            <div class="controls">
              <?php echo $this->form->getInput('spacer', 'files'); ?>
            </div>
          </div>
          <div class="control-group">
            <div class="control-label">
              <?php echo $this->form->getLabel('thumb', 'files'); ?>
            </div>
            <div class="controls">
              <?php echo $this->form->getInput('thumb', 'files'); ?>
            </div>
          </div>
          <div class="control-group">
            <div class="control-label">
              <?php echo $this->form->getLabel('img', 'files'); ?>
            </div>
            <div class="controls">
              <?php echo $this->form->getInput('img', 'files'); ?>
            </div>
          </div>
          <div class="control-group">
            <div class="control-label">
              <?php echo $this->form->getLabel('orig', 'files'); ?>
            </div>
            <div class="controls">
              <?php echo $this->form->getInput('orig', 'files'); ?>
            </div>
          </div>
        </div>
<?php endif; ?>
        <!-- End Tab Tab Replace Files -->
        <!-- Begin Tab Metadata -->
        <div class="tab-pane" id="metadata">
          <?php echo $this->loadTemplate('metadata'); ?>
        </div>
        <!-- End Tab Metadata -->
        <!-- Begin Tab Permissions -->
<?php if($this->_user->authorise('core.admin', _JOOM_OPTION.'.image.'.$this->item->id)): ?>
        <div class="tab-pane" id="permissions">
          <?php echo $this->form->getInput('rules'); ?>
        </div>
<?php endif; ?>
        <!-- End Tab Permissions -->
        <!-- End Tabs -->
      </div>
    </div>
    <!-- End Content -->
    <input type="hidden" name="task" value="new" />
    <input type="hidden" name="cid" value="<?php echo $this->item->id; ?>" />
    <?php echo JHtml::_('form.token'); ?>
  </div>
</form>
<?php JHTML::_('joomgallery.credits');