<?php defined('_JEXEC') or die('Restricted access');?>
<script language="javascript" type="text/javascript">
  Joomla.submitbutton = function(task)
  {
    var form = document.id('item-form');
    if (task == 'cancel' || document.formvalidator.isValid(form)) {
      <?php echo $this->form->getField('description')->save(); ?>
      Joomla.submitform(task, form);
    }
    else {
      var msg = new Array();
      msg.push('<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>');
      if (form.name.hasClass('invalid')) {
          msg.push('<?php echo JText::_('COM_JOOMGALLERY_CATMAN_ALERT_CATEGORY_MUST_HAVE_TITLE', true);?>');
      }
      alert(msg.join('\n'));
    }
  }
  // Ensure that changing permissions via AJAX is working correctly by adding some additional URL parameters
  jQuery(document).ready(function() {
    var modifiedURL = window.location.href;
    if(modifiedURL.search('&view=category') == (-1)) {
      modifiedURL += '&view=category';
    }
    if(modifiedURL.search('&cid=') > 0 && modifiedURL.search('&id=') == (-1)) {
      modifiedURL += '&id=' + '<?php echo $this->item->cid; ?>';
    }
    history.replaceState(null, null, modifiedURL);
  });
</script>
<form action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=categories'); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
  <fieldset>
    <ul class="nav nav-tabs">
      <li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_FIELDSET_CATEGORY');?></a></li>
      <li><a href="#options" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_COMMON_PARAMETERS');?></a></li>
      <li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
      <?php if($this->_user->authorise('core.admin', _JOOM_OPTION.'.category.'.$this->item->cid)): ?>
        <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('COM_JOOMGALLERY_FIELDSET_CATEGORY_RULES');?></a></li>
      <?php endif; ?>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="details">
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
          </div>
          <div class="span6">
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('publishhiddenstate'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('publishhiddenstate'); ?>
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
        <div class="row-fluid">
          <h4><?php echo JText::_('JDETAILS');?></h4>
          <hr />
        </div>
        <div class="row-fluid">
          <div class="span6">
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('parent_id'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('parent_id'); ?>
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
                <?php echo $this->form->getLabel('access'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('access'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('exclude_toplists'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('exclude_toplists'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('exclude_search'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('exclude_search'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('cid'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('cid'); ?>
              </div>
            </div>
<?php   if($this->form->getValue('notice')): ?>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('notice'); ?>
              </div>
              <div class="controls">
                <span id="notice"><?php echo $this->form->getValue('notice'); ?></span>
              </div>
            </div>
<?php   endif; ?>
          </div>
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
              <div class="controls">
                <?php echo $this->form->getLabel('imagelib'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('imagelib'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('thumbnail'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('thumbnail'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('img_position'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('img_position'); ?>
              </div>
            </div>
            <?php if(!$this->_config->get('jg_disableunrequiredchecks')): ?>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('ordering'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('ordering'); ?>
              </div>
            </div>
            <?php endif; ?>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('password'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('password'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane" id="options">
        <div class="row-fluid">
          <div class="span6">
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('allow_download'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('allow_download'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('allow_comment'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('allow_comment'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('allow_rating'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('allow_rating'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('allow_watermark'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('allow_watermark'); ?>
              </div>
            </div>
            <div class="control-group">
              <div class="control-label">
                <?php echo $this->form->getLabel('allow_watermark_download'); ?>
              </div>
              <div class="controls">
                <?php echo $this->form->getInput('allow_watermark_download'); ?>
              </div>
            </div>
          </div>
          <div class="span6">
            <?php echo $this->loadTemplate('options'); ?>
          </div>
        </div>
      </div>
      <div class="tab-pane" id="metadata">
        <?php echo $this->loadTemplate('metadata'); ?>
      </div>
      <?php if($this->_user->authorise('core.admin', _JOOM_OPTION.'.category.'.$this->item->cid)): ?>
        <div class="tab-pane" id="permissions">
          <?php echo $this->form->getInput('rules'); ?>
        </div>
      <?php endif; ?>
    </div>
  </fieldset>

  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token'); ?>
</form>
<?php JHtml::_('behavior.formvalidation');
      JHtml::_('behavior.keepalive');
      JHtml::_('bootstrap.tooltip');
      JHtml::_('formbehavior.chosen', 'select');
      JHtml::_('joomgallery.credits');