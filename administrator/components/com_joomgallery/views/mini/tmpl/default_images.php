<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php echo JHtml::_('bootstrap.startaccordion', 'joomgallery-images-sliders', array('active' => $this->extended > 0 ? 'param-page-images-options' : 'param-page-images-search', 'parent' => 'joomgallery-images-sliders'));
      if($this->extended > 0):
        echo JHtml::_('bootstrap.addslide', 'joomgallery-images-sliders', JText::_('COM_JOOMGALLERY_MINI_EXTENDED'), 'param-page-images-options'); ?>
  <script type="text/javascript">
    document.id('param-page-images-options').getPrevious().addEvent('click', function(){
      document.id('jg_bu_minis').addClass('hide');
      jg_minis_visible = false;
    });
  </script>
  <form action="index.php" name="imagesForm" id="imagesForm" onsubmit="return false;" class="form-horizontal">
    <div class="control-group">
      <?php echo $this->images_form->getLabel('type'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('type'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->images_form->getLabel('position'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('position'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->images_form->getLabel('linked'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('linked'); ?>
      </div>
    </div>
    <div class="control-group" id="jg_bu_linked_type_options">
      <?php echo $this->images_form->getLabel('linked_type'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('linked_type'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->images_form->getLabel('alttext'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('alttext'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->images_form->getLabel('class'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('class'); ?>
      </div>
    </div>
    <div class="control-group">
      <?php echo $this->images_form->getLabel('linkedtext'); ?>
      <div class="controls">
        <?php echo $this->images_form->getInput('linkedtext'); ?>
      </div>
    </div>
  </form>
<?php   echo JHtml::_('bootstrap.endslide');
      endif;
      echo JHtml::_('bootstrap.addslide', 'joomgallery-images-sliders', JText::_('COM_JOOMGALLERY_MINI_SEARCH'), 'param-page-images-search'); ?>
  <script type="text/javascript">
    document.id('param-page-images-search').getPrevious().addEvent('click', function(){
      document.id('jg_bu_minis').removeClass('hide');
      jg_minis_visible = true;
    });
<?php if($this->extended > 0 && (($this->params->get('openimage') != 0 && $this->params->get('openimage') != 'default') || ($this->params->get('openimage') == 'default' && (!is_numeric($this->_config->get('jg_detailpic_open')) || $this->_config->get('jg_detailpic_open') != 0)))): ?>
    document.addEvent('domready', function(){
      if(!document.id('jg_bu_type0').checked || !document.id('jg_bu_linked1').checked)
      {
        document.id('jg_bu_linked_type_options').addClass('hide');
      }
    });
    $$('#jg_bu_type0, #jg_bu_type1, #jg_bu_type2, #jg_bu_linked0, #jg_bu_linked1, #jg_bu_linked2').addEvent('click', function(){
      if(document.id('jg_bu_type0').checked && document.id('jg_bu_linked1').checked)
      {
        document.id('jg_bu_linked_type_options').addClass('hide');
        document.id('jg_bu_linked_type_options').removeClass('hide');
      }
      else
      {
        document.id('jg_bu_linked_type_options').removeClass('hide');
        document.id('jg_bu_linked_type_options').addClass('hide');
      }
    });
<?php endif; ?>
  </script>
  <div class="center">
    <form action="index.php?option=<?php echo _JOOM_OPTION; ?>&amp;view=mini&amp;tmpl=component&amp;e_name=<?php echo $this->e_name; ?>&amp;object=<?php echo JRequest::getVar('object'); ?>" name="adminForm" method="post" onsubmit="javascript:ajaxRequest('<?php echo 'index.php?option='._JOOM_OPTION.'&view=mini&format=json'; ?>', 1, 'search=' + this.search.value); return false;">
      <div id="jg_bu_pagelinks" class="pageslinks">
        <?php echo $this->loadTemplate('pagination'); ?>
      </div>
      <div class="row-fluid">
        <div class="pull-left">
          <input type="text" name="search" id="filter_search" value="<?php echo $this->search; ?>" class="inputbox" placeholder="<?php echo JText::_('COM_JOOMGALLERY_MINI_SEARCH_IMAGE'); ?>" />
        </div>
        <div class="btn-group pull-left">
          <button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
          <button class="btn hasTooltip" type="submit" onclick="document.id('filter_search').value='';" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
        </div>
        <div class="pull-right">
          <?php echo JText::_('JGLOBAL_DISPLAY_NUM').$this->lists['limit']; ?>
        </div>
      </div>
<?php if($this->extended != -1): ?>
      <div class="jg_bu_filter jg_clearboth row">
        <?php echo JText::_('COM_JOOMGALLERY_MINI_FILTER_BY_CATEGORY'); ?>
        <?php echo $this->lists['image_categories']; ?>
      </div>
<?php endif; ?>
    </form>
  </div>
<?php echo JHtml::_('bootstrap.endslide');
      echo JHtml::_('bootstrap.endaccordion');