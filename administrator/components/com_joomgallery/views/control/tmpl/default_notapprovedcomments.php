<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
$approved_states  = array(1 => array('reject', 'COM_JOOMGALLERY_COMMON_APPROVED', 'COM_JOOMGALLERY_COMMAN_REJECT_COMMENT', 'COM_JOOMGALLERY_COMMON_APPROVED', false, 'publish', 'publish'),
                          0 => array('approve', 'COM_JOOMGALLERY_COMMON_REJECTED', 'COM_JOOMGALLERY_COMMAN_APPROVE_COMMENT', 'COM_JOOMGALLERY_COMMON_REJECTED', false, 'unpublish', 'unpublish'));
?>
          <div class="well well-small">
            <div class="module-title nav-header">
              <?php echo JText::_('COM_JOOMGALLERY_ADMENU_NOT_APPROVED_COMMENTS'); ?>
            </div>
<?php       if(!empty($this->notApprovedComments)): ?>
              <table class="table table-striped">
                <tbody>
<?php             foreach($this->notApprovedComments as $i => $item): ?>
                  <tr>
                    <td class="center nowrap">
                      <?php echo JHTML::_('jgrid.state', $approved_states, $item->approved, $i, '', false); ?>
                    </td>
                    <td class="center" width="25">
                      <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', 'href="'.JRoute::_('index.php?option='._JOOM_OPTION.'&controller=comments&filter[state]=4&list[fullordering]=c.cmtdate DESC'), true); ?>
                    </td>
                    <td>
                      <a href="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=comments&filter[state]=4&list[fullordering]=c.cmtdate DESC');?>">
                        <?php echo $item->cmttext; ?></a>
                    </td>
                    <td class="small nowrap">
                      <?php echo $this->escape($item->cmtname); ?>
                    </td>
                    <td class="small nowrap">
                      <i class="icon-calendar"></i><?php echo JHTML::_('date', $item->cmtdate, JText::_('DATE_FORMAT_LC4')); ?>
                    </td>
                  </tr>
<?php             endforeach; ?>
                </tbody>
              </table>
<?php       else: ?>
              <div class="alert alert-info">
                <?php echo JText::_('COM_JOOMGALLERY_ADMENU_MESSAGE_NO_COMMENTS_NOT_APPROVED'); ?>
              </div>
<?php       endif; ?>
          </div>