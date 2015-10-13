<?php defined('_JEXEC') or die('Restricted access');
$debug = JFactory::getConfig()->get('debug'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->_doc->language; ?>" lang="<?php echo $this->_doc->language; ?>" dir="<?php echo $this->_doc->direction; ?>">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>MiniJoom</title>
  <link rel="stylesheet" href="<?php echo $this->_ambit->getStyleSheet('admin.joomgallery.css'); ?>" type="text/css" />
  <link href="<?php echo $this->_ambit->getScript('fineuploader/fineuploader.css'); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap-extended.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap-responsive<?php echo $debug ? '' : '.min'; ?>.css" rel="stylesheet" type="text/css" />
  <?php if(JFactory::getLanguage()->isRTL()): ?>
  <link href="<?php echo JUri::root(); ?>media/jui/css/bootstrap-rtl.css" rel="stylesheet" type="text/css" />
  <?php endif; ?>
  <link href="<?php echo JUri::root(); ?>media/jui/css/icomoon.css" rel="stylesheet" type="text/css" />
  <script src="<?php echo JUri::root(); ?>media/jui/js/jquery<?php echo $debug ? '' : '.min'; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/jui/js/jquery-noconflict.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/system/js/mootools-core<?php echo $debug ? '-uncompressed' : ''; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/system/js/core<?php echo $debug ? '-uncompressed' : ''; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/system/js/mootools-more<?php echo $debug ? '-uncompressed' : ''; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/system/js/validate<?php echo $debug ? '-uncompressed' : ''; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/jui/js/bootstrap<?php echo $debug ? '' : '.min'; ?>.js" type="text/javascript"></script>
  <script src="<?php echo JUri::root(); ?>media/joomgallery/js/mini.js" type="text/javascript"></script>
  <?php if($this->_config->get('jg_ajaxcategoryselection')): ?>
  <script src="<?php echo JUri::root(); ?>media/joomgallery/js/categories.js" type="text/javascript"></script>
  <?php endif; ?>
  <script src="<?php echo $this->_ambit->getScript('fineuploader/js/fineuploader'.($debug ? '' : '.min').'.js'); ?>" type="text/javascript"></script>
  <script type="text/javascript">
    var jg_scrolled = false;
    var jg_minis_page = <?php echo $this->page; ?>;
    var jg_e_name = '<?php echo $this->e_name; ?>';
    var jg_filenamewithjs = <?php echo $this->_config->get('jg_filenamewithjs') ? 'true' : 'false'; ?>;
    var default_values = {
      <?php foreach($this->images_fields as $field) echo $field.':\''.str_replace('\'', '\\\'', $this->params->get('default_'.$field)).'\','; ?>
    };
    (function()
    {
      var strings = <?php echo json_encode(JText::script()); ?>;
      if (typeof Joomla == 'undefined') {
        Joomla = {};
        Joomla.JText = strings;
      }
      else {
        Joomla.JText.load(strings);
      }
    })();
  </script>
  <!--[if lt IE 9]>
    <script src="<?php echo JUri::root(); ?>media/jui/js/html5.js"></script>
  <![endif]-->
</head>
<body>
<div class="gallery minigallery">
  <div id="system-message-container">
<?php $alert = array('error' => 'alert-error', 'warning' => '', 'notice' => 'alert-info', 'message' => 'alert-success');
      foreach($this->messages as $type => $msgs): ?>
    <div class="alert <?php echo $alert[$type]; ?>">
      <h4 class="alert-heading"><?php echo JText::_($type); ?></h4>
<?php   foreach($msgs as $msg): ?>
      <p><?php echo $msg; ?></p>
<?php   endforeach; ?>
    </div>
<?php endforeach; ?>
  </div>
  <ul class="nav nav-tabs">
<?php if(isset($this->tabs['images'])): ?>
    <li class="active"><a href="#jg-mini-images" data-toggle="tab" onclick="if(jg_minis_visible)document.id('jg_bu_minis').removeClass('hide');"><?php echo JText::_('COM_JOOMGALLERY_MINI_INSERT_IMAGE');?></a></li>
<?php endif;
      if(isset($this->tabs['categories'])): ?>
    <li><a href="#jg-mini-categories" data-toggle="tab" onclick="document.id('jg_bu_minis').addClass('hide');"><?php echo JText::_('COM_JOOMGALLERY_MINI_INSERT_CATEGORY');?></a></li>
<?php endif;
      if(isset($this->tabs['upload']) && $this->upload_enabled): ?>
    <li<?php echo (!isset($this->tabs['images']) && !isset($this->tabs['categories'])) ? ' class="active"' : ''; ?>><a href="#jg-mini-upload" data-toggle="tab" onclick="document.id('jg_bu_minis').addClass('hide');"><?php echo JText::_('COM_JOOMGALLERY_MINI_UPLOAD_IMAGE');?></a></li>
<?php endif;
      if(isset($this->tabs['createcategory']) && $this->createcat_enabled): ?>
    <li><a href="#jg-mini-createcategory" data-toggle="tab" onclick="document.id('jg_bu_minis').addClass('hide');"><?php echo JText::_('COM_JOOMGALLERY_MINI_CREATE_CATEGORY');?></a></li>
<?php endif; ?>
  </ul>
  <div class="tab-content">
<?php if(isset($this->tabs['images'])): ?>
    <div class="tab-pane active" id="jg-mini-images">
      <?php echo $this->loadTemplate('images'); ?>
    </div>
<?php endif;
      if(isset($this->tabs['categories'])): ?>
    <div class="tab-pane" id="jg-mini-categories">
      <?php echo $this->loadTemplate('categories'); ?>
    </div>
<?php endif;
      if(isset($this->tabs['upload']) && $this->upload_enabled): ?>
    <div class="tab-pane<?php echo (!isset($this->tabs['images']) && !isset($this->tabs['categories'])) ? ' active' : ''; ?>" id="jg-mini-upload">
      <?php echo $this->loadTemplate('upload'); ?>
    </div>
<?php endif;
      if(isset($this->tabs['createcategory']) && $this->createcat_enabled): ?>
    <div class="tab-pane" id="jg-mini-createcategory">
      <?php echo $this->loadTemplate('createcategory'); ?>
    </div>
<?php endif; ?>
  </div>
<?php if(isset($this->tabs['images'])): ?>
  <div id="jg_bu_minis" class="jg_bu_minis <?php echo $this->extended > 0 ? ' hide' : ''; ?>">
<?php echo $this->loadTemplate('minis'); ?>
  </div>
<?php endif; ?>
</div>
</body>
</html>