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
      <?php echo ';' //$this->form->getField('description')->save(); ?>
      Joomla.submitform(task, form);
    }
    else {
      var msg = new Array();
      msg.push('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true); ?>');
      if(form.name.hasClass('invalid')) {
          msg.push('<?php echo JText::_('COM_JOOMGALLERY_COMMON_ALERT_CATEGORY_MUST_HAVE_TITLE', true); ?>');
      }
      alert(msg.join('\n'));
    }
  }
  </script>
  <div class="edit">
    <form action = "<?php echo JRoute::_('index.php?task=category.save'.$this->slimitstart); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
      <div class="btn-toolbar">
        <div class="btn-group">
          <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton()">
            <i class="icon-ok"></i> <?php echo JText::_('COM_JOOMGALLERY_COMMON_SAVE'); ?>
          </button>
        </div>
        <div class="btn-group">
          <?php $url = !empty($this->redirecturl) ? JRoute::_($this->redirecturl) : JRoute::_('index.php?view=usercategories'.$this->slimitstart, false); ?>
          <button type="button" class="btn" onclick="javascript:location.href='<?php echo $url; ?>';">
            <i class="icon-cancel"></i> <?php echo JText::_('COM_JOOMGALLERY_COMMON_CANCEL'); ?>
          </button>
        </div>
      </div>
      <fieldset>
        <?php $this->fieldSets = $this->form->getFieldsets(); ?>
        <ul class="nav nav-tabs">
          <li class="active"><a href="#editor" data-toggle="tab"><?php echo (!$this->category->cid) ? JText::_('COM_JOOMGALLERY_COMMON_NEW_CATEGORY') : JText::_('COM_JOOMGALLERY_EDITCATEGORY_MODIFY_CATEGORY'); ?></a></li>
          <li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDITCATEGORY_PUBLISHING') ?></a></li>
          <?php if($this->_config->get('jg_edit_metadata')): ?>
          <li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDIT_METADATA') ?></a></li>
          <?php endif; ?>
<?php if(count($this->fieldSets) > 0) : ?>
          <li><a href="#other" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_EDITCATEGORY_OTHER') ?></a></li>
<?php endif; ?>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="editor">
            <div class="row-fluid">
              <div class="span6">
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('name'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('name'); ?>
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
                    <?php echo $this->form->getLabel('parent_id'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('parent_id'); ?>
                  </div>
                </div>
<?php           if(!$this->_config->get('jg_disableunrequiredchecks')): ?>
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('ordering'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('ordering'); ?>
                  </div>
                </div>
<?php           endif; ?>
              </div>
              <div class="span6">
<?php           if(   $this->_config->get('jg_showcatthumb') >= 2
                   || $this->_config->get('jg_showsubthumbs') == 1
                   || $this->_config->get('jg_showsubthumbs') == 3
                  ) : ?>
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('thumbnail'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('thumbnail'); ?>
                  </div>
                </div>
<?php           endif ?>
<?php           if($this->_config->get('jg_usercatthumbalign')): ?>
                <div class="control-group">
                  <div class="control-label">
                    <?php echo $this->form->getLabel('img_position'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('img_position'); ?>
                  </div>
                </div>
<?php           endif; ?>
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
                    <?php echo $this->form->getLabel('description'); ?>
                  </div>
                  <div class="controls">
                    <?php echo $this->form->getInput('description'); ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="publishing">
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('published'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('published'); ?>
              </div>
            </div>
<?php if($this->_config->get('jg_usercatacc')): ?>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('access'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('access'); ?>
              </div>
            </div>
<?php endif; ?>
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
        <?php echo $this->form->getInput('cid'); ?>
      </fieldset>
    </form>
  </div>
<?php echo $this->loadTemplate('footer');