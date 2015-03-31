<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHtml::_('bootstrap.tooltip');
?>
<div id="j-main-container">
<?php if($this->state->get('message') || $this->state->get('extension_message')): ?>
  <?php echo $this->loadTemplate('messages'); ?>
<?php endif; ?>
  <div class="row-fluid">
    <div class="<?php echo $this->params->get('show_available_extensions') ? 'span2' : 'span4'; ?>">
      <div class="well well-small">
        <div class="module-title nav-header">
          <?php echo JText::_('COM_JOOMGALLERY_ADMENU_QUICKICON'); ?>
        </div>
        <ul class="unstyled list-striped">
<?php     foreach($this->items as $item):
            $this->item = $item;
            echo $this->loadTemplate('button');
          endforeach; ?>
        </ul>
      </div>
    </div>
    <div class="<?php echo $this->params->get('show_available_extensions') ? 'span6' : 'span8'; ?>">
      <div class="row-fluid">
        <!-- Begin News -->
        <div class="span12">
<?php     foreach ($this->modules as $module)
          {
            $output = JModuleHelper::renderModule($module, array('style' => 'well well-small'));
            echo $output;
          }
?>
        </div>
        <!-- End News -->
        <!-- Begin Statistics -->
        <div class="row-fluid">
          <div class="span12">
<?php       echo $this->loadTemplate('popularimages');  ?>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12">
<?php       echo $this->loadTemplate('topdownloads');  ?>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12">
<?php       echo $this->loadTemplate('notapprovedimages');  ?>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12">
<?php       echo $this->loadTemplate('notapprovedcomments');  ?>
          </div>
        </div>
        <!-- End Statistics -->
      </div>
    </div>
    <!-- Begin Available Extensions -->
<?php if($this->params->get('show_available_extensions')): ?>
    <div class="span4">
      <div class="well well-small">
        <div class="module-title nav-header">
          <?php echo JText::_('COM_JOOMGALLERY_ADMENU_EXTENSIONS'); ?>
        </div>
<?php   if($this->params->get('show_update_info_text')): ?>
        <div class="alert alert-success">
          <?php echo JText::_('COM_JOOMGALLERY_ADMENU_SYSTEM_UPTODATE'); ?>
        </div>
<?php   endif;
        $this->entry = 1;
        $this->datedCount = 0;
        if($this->params->get('dated_extensions')):
?>
        <ul class="nav nav-tabs" id="jg_extension_tab">
          <li class="active"><a data-toggle="tab" href="#jg_tab_dated-extensions"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_UPDATECHECK_TITLE'); ?></a></li>
          <li><a data-toggle="tab" href="#jg_tab_installed-extensions"><?php echo JText::_('COM_JOOMGALLERY_ADMENU_INSTALLED_EXTENSIONS'); ?></a></li>
        </ul>
<?php     $this->datedCount = count($this->dated_extensions);
          echo JHtml::_('bootstrap.startPane', 'jg_extension_tab', array('active' => 'jg_tab_dated-extensions'));
          echo JHtml::_('bootstrap.addPanel', 'jg_extension_tab', 'jg_tab_dated-extensions');
          foreach($this->dated_extensions as $name => $extension):
            $this->extension  = $extension;
            $this->name       = $name;
            echo $this->loadTemplate('extension');
            $this->entry++;
            if($this->entry <= $this->datedCount): ?>
          <hr />
<?php       endif;
          endforeach;
          echo JHtml::_('bootstrap.endPanel');
          echo JHtml::_('bootstrap.addPanel', 'jg_extension_tab', 'jg_tab_installed-extensions');
        endif;
        echo JHtml::_('bootstrap.startAccordion', 'jg_accord_available-extensions');
        foreach($this->available_extensions as $name => $extension):
          $title = $name;
          if(isset($this->installed_extensions[$name]))
          {
            if(isset($this->dated_extensions[$name]))
            {
              $title .= ' <span class="label label-important">'.JText::_('COM_JOOMGALLERY_ADMENU_EXTENSION_INSTALLED_BUT_NOT_UPTODATE').'</span>';
            }
            else
            {
              $title .= ' <span class="label label-success">'.JText::_('COM_JOOMGALLERY_ADMENU_EXTENSION_INSTALLED').'</span>';
            }
          }
          else
          {
            $title .= ' <span class="label">'.JText::_('COM_JOOMGALLERY_ADMENU_EXTENSION_NOT_INSTALLED').'</span>';
          }
          echo JHtml::_('bootstrap.addSlide', 'jg_accord_available-extensions', $title, 'collapse'.$this->entry);
          $this->extension  = $extension;
          $this->name       = $name;
          echo $this->loadTemplate('availableextension');
          echo JHtml::_('bootstrap.endSlide');
          if($this->entry < (count($this->available_extensions) + $this->datedCount)):
            $this->entry++;
          endif;
        endforeach;
        echo JHtml::_('bootstrap.endAccordion');
        if($this->datedCount):
          echo JHtml::_('bootstrap.endPanel');
          echo JHtml::_('bootstrap.endPane');
        endif;
        ?>
      </div>
    </div>
<?php endif; ?>
    <!-- End Available Extensions -->
  </div>
</div>
<?php JHTML::_('joomgallery.credits');