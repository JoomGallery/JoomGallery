<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');
      if($this->state->get('message')): ?>
  <div class="row-fluid">
    <div class="alert alert-info">
      <?php echo $this->state->get('message'); ?>
    </div>
  </div>
<?php endif;if($this->state->get('extension_message')): ?>
  <div class="row-fluid">
    <div class="span12 well">
      <?php echo $this->state->get('extension_message'); ?>
    </div>
  </div>
<?php endif;
