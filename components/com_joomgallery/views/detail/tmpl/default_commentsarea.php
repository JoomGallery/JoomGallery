<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
      <a name="joomcomments"></a>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
<?php     if($this->params->get('show_comments')): ?>
          <tr>
            <td class="jg_cmtl">
              <?php echo JText::_('COM_JOOMGALLERY_DETAIL_AUTHOR'); ?>
            </td>
            <td class="jg_cmtr">
              <?php echo JText::_('COM_JOOMGALLERY_COMMON_COMMENT'); ?>
            </td>
          </tr>
<?php       foreach($this->comments as $comment): ?>
          <tr class="jg_row<?php $this->i++; echo ($this->i % 2) + 1; ?>">
            <td class="jg_cmtl">
              <span><?php echo $comment->author; ?></span>
<?php         if($this->params->get('manager_logged')): ?>
              <div class="jg_cmticons">
                <a href="http://www.db.ripe.net/whois?form_type=simple&full_query_string=&searchtext=<?php echo $comment->cmtip;?>&do_search=Search" target="_blank">
                  <img src="<?php echo $this->_ambit->get('icon_url').'ip.gif'; ?>" alt="<?php echo $comment->cmtip; ?>" title="<?php echo $comment->cmtip; ?>" hspace="3" border="0" /></a>
                <a href="<?php echo JRoute::_('index.php?task=comments.remove&id='.$this->image->id.'&cmtid='.$comment->cmtid); ?>">
                  <img src="<?php echo $this->_ambit->get('icon_url').'del.gif'; ?>" alt="<?php echo JText::_('COM_JOOMGALLERY_DETAIL_ALT_DELETE_COMMENT'); ?>" hspace="3" border="0" /></a>
              </div>
<?php         endif; ?>
            </td>
            <td class="jg_cmtr">
              <span class="small">
                <?php echo JText::sprintf('COM_JOOMGALLERY_DETAIL_COMMENTS_COMMENT_ADDED', JHTML::_('date', $comment->cmtdate, JText::_('DATE_FORMAT_LC1'))); ?>
              </span>
              <hr />
              <?php echo stripslashes($comment->text); ?>
            </td>
          </tr>
<?php       endforeach;
          endif;
          if($this->params->get('no_comments_message')): ?>
          <tr class="jg_row<?php $this->i++; echo ($this->i % 2) + 1; ?>">
            <td class="jg_cmtf">
              <?php echo $this->params->get('no_comments_message'); ?>
              <?php echo $this->params->get('no_comments_message2'); ?>
            </td>
          </tr>
<?php     endif;?>
        </tbody>
      </table>