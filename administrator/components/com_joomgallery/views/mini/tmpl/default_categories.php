<?php defined('_JEXEC') or die('Restricted access'); ?>
  <form action="index.php" name="imagesForm" onsubmit="return false;" class="form-horizontal">
    <div class="control-group">
      <?php echo $this->categories_form->getLabel('category_mode'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_mode'); ?>
      </div>
    </div>
    <hr />
    <div class="control-group jg_bu_category_thumbnails_option">
      <?php echo $this->categories_form->getLabel('category_limit'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_limit'); ?>
      </div>
    </div>
    <div class="control-group jg_bu_category_thumbnails_option">
      <?php echo $this->categories_form->getLabel('category_columns'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_columns'); ?>
      </div>
    </div>
    <div class="control-group jg_bu_category_thumbnails_option">
      <?php echo $this->categories_form->getLabel('category_ordering'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_ordering'); ?>
      </div>
    </div>
    <div class="control-group jg_bu_category_linkedtext_option">
      <?php echo $this->categories_form->getLabel('category_linkedtext'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_linkedtext'); ?>
      </div>
    </div>
    <hr />
    <div class="control-group">
      <?php echo $this->categories_form->getLabel('category_catid'); ?>
      <div class="controls">
        <?php echo $this->categories_form->getInput('category_catid'); ?>
      </div>
    </div>
  </form>
  <script type="text/javascript">
    document.addEvent('domready', function(){
      if(document.id('jg_bu_category0').checked)
      {
        $$('.jg_bu_category_linkedtext_option').addClass('hide');
      }
      else
      {
        $$('.jg_bu_category_thumbnails_option').addClass('hide');
      }
    });
    document.id('jg_bu_category0').addEvent('click', function(){
      $$('.jg_bu_category_linkedtext_option').addClass('hide');
      $$('.jg_bu_category_thumbnails_option').removeClass('hide');
    });
    document.id('jg_bu_category1').addEvent('click', function(){
      $$('.jg_bu_category_linkedtext_option').removeClass('hide');
      $$('.jg_bu_category_thumbnails_option').addClass('hide');
    });
  </script>