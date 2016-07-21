<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="PopupNewModalLabel" aria-hidden="true" id="jg-reset-popup">
  <form id="FormResetConfig" method="post" action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&amp;controller=config&amp;&task=resetconfigs&amp;'); ?>">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3 id="PopupNewModalLabel"><?php echo JText::_('COM_JOOMGALLERY_CONFIG_RESETCONFIG_LONG'); ?></h3>
    </div>
    <div class="modal-body">
      <p><?php echo JText::_('COM_JOOMGALLERY_CONFIG_RESETCONFIG_INFO'); ?></p>
    </div>
    <div class="modal-footer">
      <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
      <button class="btn btn-primary" type="submit"><?php echo JText::_('COM_JOOMGALLERY_CONFIG_RESETCONFIG'); ?></button>
    </div>
  </form>
</div>