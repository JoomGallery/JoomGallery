<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
JHtml::_('behavior.formvalidation');
$this->_doc->addStyleSheet($this->_ambit->getStyleSheet('joomgallery.css')); ?>
<div class="gallery minigallery" style="text-align:center;">
  <fieldset style="width:550px;margin-right:auto; margin-left:auto;">
    <legend><?php echo JText::_('COM_JOOMGALLERY_DETAIL_REPORT_IMAGE'); ?></legend>
    <form action="<?php echo JRoute::_('index.php'); ?>" id="reportimageform" name="reportimageform" method="post" class="form-validate">
<?php if(!$this->_user->get('id')): ?>
      <div>
        <label for="name"><?php echo JText::_('COM_JOOMGALLERY_COMMON_REPORT_YOUR_NAME'); ?></label><br />
        <input type="text" id="name" name="name" value="<?php echo $this->_mainframe->getUserState('report.image.name'); ?>" class="inputbox required" style="width:50%;" />
      </div>
      <div>
        <label for="email"><?php echo JText::_('COM_JOOMGALLERY_COMMON_REPORT_YOUR_EMAIL'); ?></label><br />
        <input type="text" id="email" name="email" value="<?php echo $this->_mainframe->getUserState('report.image.email'); ?>" class="inputbox required validate-email" style="width:50%;" />
      </div>
<?php endif; ?>
      <div>
        <label for="report"><?php echo JText::_('COM_JOOMGALLERY_COMMON_REPORT_YOUR_REPORT'); ?></label><br />
        <textarea id="report" name="report" class="inputbox required" style="width:100%; height:200px;"><?php echo $this->_mainframe->getUserState('report.image.report'); ?></textarea>
      </div>
      <div>
        <?php echo implode('', $this->_mainframe->triggerEvent('onJoomGetCaptcha', array('report'))); ?>
        <input type="hidden" name="id" value="<?php echo JRequest::getInt('id'); ?>" />
        <input type="hidden" name="task" value="report.send" />
        <input type="hidden" name="tmpl" value="component" />
      </div>
      <div><input type="submit" name="button" value="<?php echo JText::_('COM_JOOMGALLERY_COMMON_REPORT_SEND_REPORT'); ?>" class="btn btn-primary validate" /></div>
    </form>
  </fieldset>
</div>