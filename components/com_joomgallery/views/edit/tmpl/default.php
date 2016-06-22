<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
// JHtml::_('formbehavior.chosen', 'select');

echo $this->loadTemplate('header'); ?>
  <script language="javascript" type="text/javascript">
  Joomla.submitbutton = function(task)
  {
    var form = document.id('adminForm');
    if(document.formvalidator.isValid(form)) {
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
      alert(msg.join('\n'));
    }
  }
  </script>
  <div class="edit">
    <form action = "<?php echo JRoute::_('index.php?task=image.save'.$this->redirect.$this->slimitstart); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
      <div class="btn-toolbar">
        <div class="btn-group">
          <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton()">
            <i class="icon-ok"></i> <?php echo JText::_('COM_JOOMGALLERY_COMMON_SAVE'); ?>
          </button>
        </div>
        <div class="btn-group">
          <?php $url = !empty($this->redirecturl) ? JRoute::_($this->redirecturl) : JRoute::_('index.php?view=userpanel'.$this->slimitstart, false); ?>
          <button type="button" class="btn" onclick="javascript:location.href='<?php echo $url; ?>';">
            <i class="icon-cancel"></i> <?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL'); ?>
          </button>
        </div>
      </div>
      <fieldset>
        <?php $this->fieldSets = $this->form->getFieldsets(); ?>
        <ul class="nav nav-tabs">
          <li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDIT_EDIT_IMAGE') ?></a></li>
          <li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDIT_PUBLISHING') ?></a></li>
          <?php if($this->_config->get('jg_edit_metadata')): ?>
          <li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDIT_METADATA') ?></a></li>
          <?php endif; ?>
<?php if(count($this->fieldSets) > 0) : ?>
          <li><a href="#other" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDIT_OTHER') ?></a></li>
<?php endif; ?>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="editor">
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
              </div>
              <div class="span6">
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('owner'); ?>
                  </div>
                  <div class="controls">
                    <span class="uneditable-input input-medium"><strong><?php echo JHTML::_('joomgallery.displayname', $this->image->owner) ?></strong></span>
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
              </div>
            </div>
            <div class="row-fluid">
              <div class="span12">
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('imgtext'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('imgtext'); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="publishing">
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('imgauthor'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('imgauthor'); ?>
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
                <?php echo $this->form->getLabel('access'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('access'); ?>
              </div>
            </div>
          </div>
          <?php if($this->_config->get('jg_edit_metadata')): ?>
          <div class="tab-pane" id="metadata">
            <?php echo $this->form->renderField('metadesc'); ?>
            <?php echo $this->form->renderField('metakey'); ?>
          </div>
          <?php endif; ?>
<?php if(count($this->fieldSets) > 0) : ?>
          <div class="tab-pane" id="other">
            <?php echo $this->loadTemplate('options'); ?>
          </div>
<?php endif; ?>
        </div>
        <?php echo $this->form->getInput('id'); ?>
      </fieldset>
    </form>
  </div>
<?php
echo $this->loadTemplate('footer');