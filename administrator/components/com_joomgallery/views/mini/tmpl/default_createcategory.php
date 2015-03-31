<?php defined('_JEXEC') or die('Restricted access');
if($this->_mainframe->isSite()):
  $action = JRoute::_('index.php?option='._JOOM_OPTION.'&task=category.save&tmpl=component&redirect='.base64_encode('index.php?option='._JOOM_OPTION.'&view=mini&format=raw'));
else:
  $action = JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories&task=save&tmpl=component&redirect='.base64_encode('index.php?option='._JOOM_OPTION.'&view=mini&format=raw'));
endif; ?>
  <form action="<?php echo $action; ?>" method="post" class="form-validate form-horizontal" name="CreateCategoryForm" onsubmit="if(!document.formvalidator.isValid(this)){alert('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true); ?>');return false;}">
    <div class="control-group">
      <?php echo $this->category_form->getLabel('name'); ?>
      <div class="controls">
        <?php echo $this->category_form->getInput('name'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->category_form->getLabel('parent_id'); ?>
      <div class="controls">
        <?php echo $this->parent_categories ? $this->parent_categories : $this->category_form->getInput('parent_id'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->category_form->getLabel('published'); ?>
      <div class="controls">
        <?php echo $this->category_form->getInput('published'); ?>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <button id="button" class="btn btn-large btn-primary" type="submit"><?php echo JText::_('COM_JOOMGALLERY_MINI_SAVE'); ?></button>
      </div>
      <input type="hidden" name="cid" value="" />
    </div>
  </form>