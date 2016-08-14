<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
<?php if(!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
  <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
  <div class="row-fluid">
    <div class="span7">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_LINKS'); ?></div>
        <table class="table table-striped">
          <tr>
            <td>
              <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_PROJECT_WEBSITE'); ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/de.png'); ?>" border="0" align="top" width="16" height="11" alt="german" />&nbsp;
            </td>
            <td>
              <a href='http://www.joomgallery.net/' target='_blank'>http://www.joomgallery.net</a>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_PROJECT_WEBSITE'); ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/gb.png'); ?>" border="0" align="top" width="16" height="11" alt="english" />&nbsp;
            </td>
            <td >
              <a href='http://www.en.joomgallery.net/' target='_blank'>http://www.en.joomgallery.net/</a>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_SUPPORT_FORUM'); ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/de.png'); ?>" border="0" align="top" width="16" height="11" alt="german" />&nbsp;
            </td>
            <td>
              <a href='http://www.forum.joomgallery.net/' target='_blank'>http://www.forum.joomgallery.net/</a>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_SUPPORT_FORUM'); ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/gb.png'); ?>" border="0" align="top" width="16" height="11" alt="english" />&nbsp;
            </td>
            <td>
              <a href='http://www.forum.en.joomgallery.net/' target='_blank'>http://www.forum.en.joomgallery.net/</a>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_CHANGELOG_LONG'); ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/gb.png'); ?>" border="0" align="top" width="16" height="11" alt="english" />&nbsp;
            </td>
            <td>
              <button class="btn btn-info" data-toggle="modal" data-target="#jg-changelog-popup"><i class="icon-list" title="<?php echo JText::_('COM_JOOMGALLERY_HLPIFO_CHANGELOG'); ?>"></i>
                <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_CHANGELOG'); ?></button>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="span5">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_TEAM'); ?></div>
        <div class="text-center">
          <p><?php echo JText::sprintf('COM_JOOMGALLERY_HLPIFO_TEAM_TEXT', '<a href="http://en.joomgallery.net/team.html" target="_blank">JoomGallery::ProjectTeam</a>', '<a href="https://github.com/JoomGallery" target="_blank">GitHub</a>'); ?></p>
          <p><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_TEAM_CONTRIBUTORS'); ?></p>
          <p><small class="muted"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_TEAM_CONTRIBUTORS_HINT'); ?></small></p>
          <a href="https://github.com/JoomGallery" target="_blank"><img width="200" src="<?php echo $this->_ambit->getIcon('others/GitHub_Logo.png'); ?>" alt="GitHub Logo"></a></div>
      </div>
    </div>
  </div>
  <div class="row-fluid">
    <div class="span7">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_TRANSLATION'); ?></div>
        <div class="alert alert-info"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_DOWNLOAD_TRANSLATIONS'); ?>&sup1;</div>
        <ul class="list-striped list-condensed">
          <li><img src="<?php echo $this->_ambit->getIcon('flags/gb.png'); ?>" border="0" align="top" width="16" height="11" alt="" /> JoomGallery::ProjectTeam en-GB</li>
<?php foreach($this->languages as $key => $lang): ?>
          <li>
<?php   if($this->params->get('autoinstall_possible')): ?>
            <a href="index.php?option=<?php echo _JOOM_OPTION; ?>&amp;controller=help&amp;task=install&amp;language=<?php echo $key; ?>&amp;downloadlink=<?php echo base64_encode($lang['downloadlink']); ?>"  title="<?php echo $key; ?>">
<?php   endif;
        if(!$this->params->get('autoinstall_possible')): ?>
            <a href="<?php echo $lang['downloadlink']; ?>" title="<?php echo $key; ?>" target="_blank">
<?php   endif; ?>
              <img src="<?php echo $this->_ambit->getIcon('flags/'.$lang['flag']); ?>" border="0" align="top" width="16" height="11" alt="<?php echo $key; ?>" /></a>
            <?php echo $lang['translator']; ?></li>
<?php endforeach; ?>
        </ul>
        <div class="alert alert-block">&sup1; <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_NOTE_TRANSLATIONS'); ?></div>
      </div>
    </div>
  <!--</div>
  <div class="row-fluid">-->
    <div class="span5">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_THANKS'); ?></div>
        <ul class="unstyled list-striped">
<?php foreach($this->credits as $credit): ?>
          <li class="center"><?php echo $credit['title']; ?>
<?php   if($credit['author']): ?>
            <br />Author: <?php echo $credit['author']; ?>
<?php   endif; ?>
            <br /><a href="<?php echo $credit['link']; ?>" target="_blank"><?php echo $credit['link']; ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="row-fluid">
    <div class="span12">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_DONATIONS'); ?></div>
        <div class="center">
          <p><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_DONATIONS_LONG'); ?></p>
          <p>
            <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate%40joomgallery%2enet&amp;item_name=JoomGallery&amp;tax=0&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF&amp;charset=UTF%2d8" title="Donate" target="_blank">
              <img src="<?php echo $this->_ambit->getIcon('others/donate.gif'); ?>"  alt="Donate!" title="Donate!" border="0"/></a>
          </p>
        </div>
        <div class="center">
          <?php echo JText::_('COM_JOOMGALLERY_HLPIFO_SPONSORS'); ?>
          <a href="mailto:joom@joomgallery.net">joom@joomgallery.net</a>
        </div>
      </div>
    </div>
  </div>
  <div class="row-fluid">
    <div class="span12">
      <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_LICENCE'); ?></div>
        <div class="alert center"><?php echo JText::_('COM_JOOMGALLERY_HLPIFO_NO_GUARANTEE'); ?></div>
      </div>
    </div>
  </div>
<?php JHTML::_('joomgallery.credits'); ?>
</div>
<?php echo $this->loadTemplate('changelog');