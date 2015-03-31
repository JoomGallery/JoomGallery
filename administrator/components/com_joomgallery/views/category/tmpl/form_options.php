<?php defined('_JEXEC') or die('Restricted access');
echo JHtml::_('bootstrap.startAccordion', 'categoryOptions', array('active' => 'collapse0'));
$fieldSets = $this->form->getFieldsets();
$i = 0;

foreach($fieldSets as $name => $fieldSet):
  if($name != ''):
    echo JHtml::_('bootstrap.addSlide', 'categoryOptions', JText::_($fieldSet->label), 'collapse'.$i++);
      if(isset($fieldSet->description) && trim($fieldSet->description)):
        echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
      endif;
      ?>
        <?php foreach($this->form->getFieldset($name) as $field): ?>
          <div class="control-group">
            <div class="control-label">
              <?php echo $field->label; ?>
            </div>
            <div class="controls">
              <?php echo $field->input; ?>
            </div>
          </div>
        <?php endforeach;
    echo JHtml::_('bootstrap.endSlide');
  endif;
endforeach;
echo JHtml::_('bootstrap.endAccordion');
