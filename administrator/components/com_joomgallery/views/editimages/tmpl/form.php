<?php defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task)
{
  var form = document.id('editimages-form');
  if(task == 'cancel' || document.formvalidator.isValid(form)) {
    <?php echo $this->form->getField('imgtext')->save(); ?>
    Joomla.submitform(task, form);
  }
  else {
    var msg = new Array();
    msg.push('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>');
    if(form.imgtitle.hasClass('invalid')) {
        msg.push('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE', true); ?>');
    }
    if(form.catid.hasClass('invalid')) {
      msg.push('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_CATEGORY', true);?>');
    }
    alert(msg.join('\n'));
  }
}
</script>
<form  class="form-validate form-horizontal" action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images'); ?>" method="post" name="adminForm" id="editimages-form">
  <div class="row-fluid">
    <div class="span6">
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('imgtitle'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('imgtitle'); ?>
          <?php echo $this->form->getInput('imgtitlestartcounter'); ?>
        </div>
      </div>
      <div class="control-group">
        <div class="control-label">
          <?php echo $this->form->getLabel('catid'); ?>
        </div>
        <div class="controls">
          <div class="pull-left"><?php echo $this->form->getInput('catid'); ?></div>
          <?php echo $this->form->getInput('movecopy'); ?>
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
      <div class="control-group">
        <div class="control-label">
          <?php echo $this->form->getLabel('imgtext'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('imgtext'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('owner'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('owner'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('imgauthor'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('imgauthor'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('metadesc'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('metadesc'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('metakey'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('metakey'); ?>
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
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('txtclearvotes'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('txtclearvotes'); ?>
          <?php echo $this->form->getInput('clearvotes'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('txtclearhits'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('txtclearhits'); ?>
          <?php echo $this->form->getInput('clearhits'); ?>
        </div>
      </div>
      <div class="control-group form-inline">
        <div class="control-label">
          <?php echo $this->form->getLabel('txtcleardownloads'); ?>
        </div>
        <div class="controls">
          <?php echo $this->form->getInput('txtcleardownloads'); ?>
          <?php echo $this->form->getInput('cleardownloads'); ?>
        </div>
      </div>
    </div>
  </div>
  <div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="cids" value="<?php echo $this->cids; ?>" />
  </div>
</form>
<?php JHTML::_('joomgallery.credits');