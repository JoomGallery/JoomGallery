<?php defined('_JEXEC') or die('Restricted access');

$i = 0;
foreach($this->fieldSets as $name => $fieldSet):
  if($name != ''):

       echo '<h4 class="">'.$this->escape(JText::_($fieldSet->label)).'</h4>';
      if(isset($fieldSet->description) && trim($fieldSet->description)):
        echo '<p class="">'.$this->escape(JText::_($fieldSet->description)).'</p>';
      endif;
      foreach($this->form->getFieldset($name) as $field): ?>
          <div class="control-group">
            <div class="control-label">
              <?php echo $field->label; ?>
            </div>
            <div class="controls">
              <?php echo $field->input; ?>
            </div>
          </div>
<?php endforeach;
        echo '<hr>';
  endif;
endforeach;