<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" id="jg-info-popup">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3 id="myModalLabel"><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_WELCOME'); ?></h3>
  </div>
  <div class="modal-body">
    <div class="jg-configs-introduction alert alert-info"><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INTRODUCTION_INTRO'); ?></div>
    <div><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_INTRODUCTION'); ?></div>
  </div>
  <div class="modal-footer">
    <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JOOMGALLERY_CONFIGS_OK'); ?></button>
  </div>
</div>