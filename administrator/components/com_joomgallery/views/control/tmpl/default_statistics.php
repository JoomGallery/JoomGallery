<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
            <div class="well well-small">
              <div class="module-title nav-header">
                <?php echo JText::_('COM_JOOMGALLERY_ADMENU_STATISTIC'); ?>
              </div>
<?php       if(!empty($this->statisticinfo)): ?>
              <table class="table table-striped">
                <tbody>
<?php             foreach($this->statisticinfo as $elem):
?>
                    <tr>
                    <td class="center nowrap">
                      <span class="badge badge-info hasTooltip" title="<?php echo JText::_($elem->outputtext);?>"><?php echo $elem->outputresult;?></span>
                    </td>
                    <td>
                      <?php echo $this->escape($elem->outputtext); ?>
                    </td>
                  </tr>
<?php
                  endforeach;?>
                </tbody>
              </table>
<?php       endif ?>
            </div>