<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/administrator/components/com_joomgallery/models/fields/jupload.php $
// $Id: jupload.php 4076 2013-02-12 10:35:29Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Renders a JUpload applet field
 *
 * @package     JoomGallery
 * @since       2.1
 */
class JFormFieldJupload extends JFormField
{
  /**
   * The form field type.
   *
   * @var   string
   * @since 2.1
   */
  protected $type = 'jupload';

  /**
   * Returns the HTML for the JUpload applet inclusion
   *
   * @return  string  The HTML for the JUpload applet inclusion
   * @since   2.1
   */
  protected function getInput()
  {
    $app    = JFactory::getApplication();
    $config = JoomConfig::getInstance();

    // Check the php.ini setting 'session.cookie_httponly'
    // If set and = 1 then build the parameter 'readCookieFrom Navigator=false'
    // in Applet (new since V 4.2.1c)
    // and provide the cookie with sessionname=token in parameter 'specificHeaders' 
    $cookieNavigator  = true;
    $sesscook         = @ini_get('session.cookie_httponly');
    if(!empty($sesscook) && $sesscook == 1)
    {
      $cookieNavigator    = false;
      // Get the current session
      $currentSession     = JSession::getInstance('', array());
      $sessionname        = $currentSession->getName();
      // Function getToken() delivers wrong token, so get the right one
      // from $_COOKIE array (since PHP 4.1.0)
      $sessiontoken       = $_COOKIE[$sessionname];
    }

    ob_start(); ?>
<!-- --------------------------------------------------------------------------------------------------- -->
<!-- --------     A DUMMY APPLET, THAT ALLOWS THE NAVIGATOR TO CHECK THAT JAVA IS INSTALLED   ---------- -->
<!-- --------               If no Java: Java installation is prompted to the user.            ---------- -->
<!-- --------------------------------------------------------------------------------------------------- -->
<!--"CONVERTED_APPLET"-->
<!-- HTML CONVERTER -->
<script language="JavaScript" type="text/javascript"><!--
    var _info = navigator.userAgent;
    var _ns = false;
    var _ns6 = false;
    var _ie = (_info.indexOf("MSIE") > 0 && _info.indexOf("Win") > 0 && _info.indexOf("Windows 3.1") < 0);
//--></script>
    <comment>
        <script language="JavaScript" type="text/javascript"><!--
        var _ns = (navigator.appName.indexOf("Netscape") >= 0 && ((_info.indexOf("Win") > 0 && _info.indexOf("Win16") < 0 && java.lang.System.getProperty("os.version").indexOf("3.5") < 0) || (_info.indexOf("Sun") > 0) || (_info.indexOf("Linux") > 0) || (_info.indexOf("AIX") > 0) || (_info.indexOf("OS/2") > 0) || (_info.indexOf("IRIX") > 0)));
        var _ns6 = ((_ns == true) && (_info.indexOf("Mozilla/5") >= 0));
//--></script>
    </comment>

<script language="JavaScript" type="text/javascript"><!--
    if (_ie == true) document.writeln('<object classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93" WIDTH = "0" HEIGHT = "0" NAME = "JUploadApplet"  codebase="http://java.sun.com/update/1.5.0/jinstall-1_5-windows-i586.cab#Version=5,0,0,3"><noembed><xmp>');
    else if (_ns == true && _ns6 == false) document.writeln('<embed ' +
      'type="application/x-java-applet;version=1.5" \
            CODE = "wjhk.jupload2.EmptyApplet" \
            ARCHIVE = "<?php echo JURI::root(); ?>media/joomgallery/java/wjhk.jupload.jar" \
            NAME = "JUploadApplet" \
            WIDTH = "0" \
            HEIGHT = "0" \
            type ="application/x-java-applet;version=1.6" \
            scriptable ="false" ' +
      'scriptable=false ' +
      'pluginspage="http://java.sun.com/products/plugin/index.html#download"><noembed><xmp>');
//--></script>
<applet  code = "wjhk.jupload2.EmptyApplet" ARCHIVE = "<?php echo JURI::root(); ?>media/joomgallery/java/wjhk.jupload.jar" WIDTH = "0" HEIGHT = "0" NAME = "JUploadApplet"></xmp>
    <param name = CODE VALUE = "wjhk.jupload2.EmptyApplet" >
    <param name = ARCHIVE VALUE = "<?php echo JURI::root(); ?>media/joomgallery/java/wjhk.jupload.jar" >
    <param name = NAME VALUE = "JUploadApplet" >
    <param name = "type" value="application/x-java-applet;version=1.5">
    <param name = "scriptable" value="false">
    <param name = "type" VALUE="application/x-java-applet;version=1.6">
    <param name = "scriptable" VALUE="false">
</xmp>
Java 1.5 or higher plugin required.
</applet>
</noembed>
</embed>
</object>

<applet name="JUpload" code="wjhk.jupload2.JUploadApplet" archive="<?php echo JURI::root(); ?>media/joomgallery/java/wjhk.jupload.jar" width="100%" height="400" mayscript>
<?php if($app->isSite()): ?>
  <param name="postURL" value="<?php echo JURI::root(); ?>index.php?option=<?php echo _JOOM_OPTION; ?>&task=upload.upload&type=java">
<?php else: ?>
  <param name="postURL" value="<?php echo JURI::root(); ?>administrator/index.php?option=<?php echo _JOOM_OPTION; ?>&controller=jupload&task=upload">
<?php endif; ?>
  <param name="lookAndFeel" value="system">
  <param name="showLogWindow" value="false">
  <param name="showStatusBar" value="true">
<?php if($app->isSite()): ?>
  <param name="formdata" value="JavaUploadForm">
<?php else: ?>
  <param name="formdata" value="adminForm">
<?php endif; ?>
  <param name="debugLevel" value="0">
<?php if($app->isSite()): ?>
  <param name="afterUploadURL" value="<?php echo JRoute::_('index.php?task=upload.concludejavaupload'); ?>">
<?php else: ?>
  <param name="afterUploadURL" value="javascript:alert('<?php echo JText::_('COM_JOOMGALLERY_UPLOAD_OUTPUT_UPLOAD_COMPLETE', true); ?>');">
<?php endif; ?>
  <param name="nbFilesPerRequest" value="1">
  <param name="stringUploadSuccess" value="JOOMGALLERYUPLOADSUCCESS">
  <param name="stringUploadError" value="JOOMGALLERYUPLOADERROR (.*)">
  <param name="uploadPolicy" value="PictureUploadPolicy">
  <param name="allowedFileExtensions" value="jpg/jpeg/jpe/png/gif">
  <param name="pictureTransmitMetadata" value="true">
<?php if((($app->isSite() && $config->get('jg_delete_original_user') == 1) || ($app->isAdmin() && $config->get('jg_delete_original') == 1)) && $config->get('jg_resizetomaxwidth')): ?>
  <param name="maxPicHeight" value="<?php echo $config->get('jg_maxwidth'); ?>">
  <param name="maxPicWidth" value="<?php echo $config->get('jg_maxwidth'); ?>">
  <param name="pictureCompressionQuality" value="<?php echo ($config->get('jg_picturequality')/100); ?>">
<?php else:?>
  <param name="pictureCompressionQuality" value="0.8">
<?php endif; ?>
  <param name="fileChooserImagePreview" value="false">
  <param name="fileChooserIconFromFileContent" value="-1">
<?php if(!$cookieNavigator): ?>
  <param name="readCookieFromNavigator" value="false">
  <param name="specificHeaders" value="Cookie: <?php echo $sessionname.'='.$sessiontoken; ?>">
<?php endif; ?>
  Java 1.5 or higher plugin required.
</applet>
<?php
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}