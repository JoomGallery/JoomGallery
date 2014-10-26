<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
            <div class="well well-small">
              <div class="module-title nav-header">
                <?php echo JText::_('COM_JOOMGALLERY_ADMENU_TOP_DOWNLOADS'); ?>
              </div>
<?php       if(!empty($this->topDownloads)): ?>
              <table class="table table-striped">
                <tbody>
<?php             foreach($this->topDownloads as $i => $item):
                    $canEdit    = $this->_user->authorise('core.edit', _JOOM_OPTION.'.image.'.$item->id);
                    $canEditOwn = $this->_user->authorise('core.edit.own', _JOOM_OPTION.'.image.'.$item->id) && $item->owner == $this->_user->get('id');
?>
                    <tr>
                    <td class="center nowrap">
                      <span class="badge badge-info hasTooltip" title="<?php echo JText::_('COM_JOOMGALLERY_COMMON_DOWNLOADS');?>"><?php echo $item->downloads;?></span>
                    </td>
                    <td class="center" width="25">
                      <?php echo JHTML::_('joomgallery.minithumbimg', $item, 'jg_minithumb', $canEdit || $canEditOwn ? 'href="'.JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&task=edit&cid='.$item->id) : null, true); ?>
                    </td>
                    <td>
<?php               if($canEdit || $canEditOwn): ?>
                      <a href="<?php echo JRoute::_('index.php?option='._JOOM_OPTION.'&controller=images&task=edit&cid='.$item->id);?>">
                        <?php echo $this->escape($item->imgtitle); ?></a>
<?php               else: ?>
                        <?php echo $this->escape($item->imgtitle); ?>
<?php               endif; ?>
                      <span class="small">
                        <?php echo JText::sprintf('COM_JOOMGALLERY_COMMON_CATEGORY_VAR', $this->escape($item->category_name)); ?>
                      </span>
                    </td>
                    <td class="small nowrap">
                      <i class="icon-calendar"></i><?php echo JHTML::_('date', $item->imgdate, JText::_('DATE_FORMAT_LC4')); ?>
                    </td>
                  </tr>
<?php             endforeach; ?>
                </tbody>
              </table>
<?php       endif ?>
            </div>