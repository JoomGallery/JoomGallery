<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.'); ?>
          <li>
            <a href="<?php echo JRoute::_($this->item->link); ?>">
              <?php echo JHTML::image($this->item->img, JText::_($this->item->title)); ?>
              <span><?php echo JText::_($this->item->title); ?></span>
            </a>
          </li>