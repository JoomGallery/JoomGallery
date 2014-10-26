<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

echo $this->loadTemplate('header');

if(count($this->categories)):
  echo $this->loadTemplate('subcategories');
endif;

if(count($this->images)):
  echo $this->loadTemplate('head');
  echo $this->loadTemplate('images');
endif;

echo $this->loadTemplate('footer');