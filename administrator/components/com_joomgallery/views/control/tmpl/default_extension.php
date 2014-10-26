<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
  <div class="jg_updexistpanel">
    <div class="jg_updexisthead">
      <?php echo '<span class="label label-important">'.JText::sprintf('COM_JOOMGALLERY_ADMENU_EXTENSION_NOT_UPTODATE', $this->name).'<span>'; ?>
    </div>
    <div class="jg_updexistversion">
      <table class="table table-bordered">
        <tr>
          <td><?php echo JText::_('COM_JOOMGALLERY_ADMENU_YOUR_VERSION'); ?></td>
          <td><?php echo $this->extension['installed_version']; ?></td>
<?php if(isset($this->extension['releaselink'])): ?>
          <td></td>
<?php endif; ?>
        </tr>
        <tr>
          <td><?php echo JText::_('COM_JOOMGALLERY_ADMENU_CURRENT_VERSION'); ?></td>
          <td><?php echo $this->extension['version']; ?></td>
<?php if(isset($this->extension['releaselink'])): ?>
          <td><a href="<?php echo $this->extension['releaselink']; ?>" target="_blank"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_MORE_INFO_LINK'); ?></a></td>
<?php endif; ?>
        </tr>
      </table>
    </div>
<?php if(isset($this->extension['downloadlink'])): ?>
    <div class="jg_updexistdownload"><a class="btn" href="<?php echo $this->extension['downloadlink']; ?>" target="_blank"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_DOWNLOAD_UPDATE_LINK'); ?></a></div>
<?php endif;
      if(isset($this->extension['updatelink'])):
        if($this->params->get('curl_loaded')): ?>
    <div class="jg_updexistauto"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_AUTOUPDATE_TEXT'); ?></div>
    <div class="jg_updexistautolink">
      <a class="btn btn-primary" href="index.php?option=<?php echo _JOOM_OPTION; ?>&amp;task=update&amp;extension=<?php echo $this->name; ?>"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_AUTOUPDATE_LINK'); ?></a></div>
<?php   endif;
        if(!$this->params->get('curl_loaded')):
          if($this->params->get('url_fopen_allowed')): ?>
    <div class="jg_updexistautoform"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_AUTOUPDATE_TEXT'); ?>
      <form enctype="multipart/form-data" action="index.php" method="post" name="JoomUpdateForm<?php echo $this->entry; ?>">
        <input type="hidden" name="installtype" value="url" />
        <input type="hidden" name="install_url" value="<?php echo $this->extension['updatelink']; ?>" />
        <input type="hidden" name="task" value="install.install" />
        <input type="hidden" name="option" value="com_installer" />
        <?php echo JHTML::_('form.token'); ?>
      </form>
    </div>
    <div class="jg_updexistformsubmit">
      <a class="btn btn-primary" href="javascript:document.JoomUpdateForm<?php echo $this->entry; ?>.submit();"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_AUTOUPDATE_LINK'); ?></a>
    </div>
<?php     endif;
          if(!$this->params->get('url_fopen_allowed')): ?>
    <div class="jg_updexistautonot"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_AUTOUPDATE_NOT_POSSIBLE'); ?></div>
<?php     endif;
        endif;
      endif; ?>
  </div>