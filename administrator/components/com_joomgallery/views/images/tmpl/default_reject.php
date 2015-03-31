<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<div class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="PopupRejectModalLabel" aria-hidden="true" id="jg-reject-popup">
  <script type="text/javascript">
    jQuery('#jg-reject-popup').on('shown', function ()
    {
      jQuery('#jg-message').focus();
    });
  </script>
  <form id="formReject" method="post" action="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&task=reject'); ?>">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h3 id="PopupRejectModalLabel"><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_HEADING'); ?></h3>
    </div>
    <div class="modal-body">
      <div class="pull-right span6">
        <img src="../media/system/images/blank.png" class="img-polaroid span12" id="jg-reject-image" alt="Thumbnail" />
      </div>
      <p id="jg-reject-no-owner"><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_NOOWNER'); ?></p>
      <div id="jg-reject-owner">
        <p><?php echo JText::sprintf('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_OWNER', '<span id="jg-reject-owner-name"></span>'); ?></p>
        <div class="clearfix"></div>
        <div class="control-group">
          <div class="control-label span6">
            <label rel="tooltip" class="spn6" title="<?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_MESSAGE_TIP'); ?>">
              <?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_MESSAGE_LABEL'); ?>
            </label>
          </div>
          <div class="controls">
            <textarea id="jg-message" name="message" class="span12" cols="30" rows="7"></textarea>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('JTOOLBAR_CANCEL'); ?></button>
      <button class="btn btn-primary" type="submit"><?php echo JText::_('COM_JOOMGALLERY_IMGMAN_REJECT_IMAGE_BUTTON'); ?></button>
      <input type="hidden" id="jg-reject-cid" name="cid" value="" />
    </div>
  </form>
</div>